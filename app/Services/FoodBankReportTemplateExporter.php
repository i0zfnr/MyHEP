<?php

namespace App\Services;

use Carbon\Carbon;
use DOMDocument;
use DOMElement;
use DOMXPath;
use RuntimeException;
use Throwable;
use ZipArchive;

class FoodBankReportTemplateExporter
{
    private const TEMPLATE_PATH = 'templates/foodbank/hq-report-template.xlsx';
    private const SHEET_PATH = 'xl/worksheets/sheet1.xml';
    private const SHARED_STRINGS_PATH = 'xl/sharedStrings.xml';
    private const XML_NS = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';

    /** @param iterable<object> $records */
    public function generate(iterable $records): string
    {
        $templatePath = resource_path(self::TEMPLATE_PATH);
        if (! is_file($templatePath)) {
            throw new RuntimeException('Food Bank Excel report template is missing.');
        }

        $tempPath = tempnam(storage_path('app'), 'foodbank-report-');
        if ($tempPath === false) {
            throw new RuntimeException('Could not create a temporary Food Bank report file.');
        }

        $outputPath = $tempPath . '.xlsx';
        if (! rename($tempPath, $outputPath) || ! copy($templatePath, $outputPath)) {
            @unlink($tempPath);
            @unlink($outputPath);
            throw new RuntimeException('Could not prepare the Food Bank Excel report.');
        }

        $zip = new ZipArchive();
        if ($zip->open($outputPath) !== true) {
            @unlink($outputPath);
            throw new RuntimeException('Could not open the Food Bank Excel report template.');
        }

        try {
            $worksheet = $zip->getFromName(self::SHEET_PATH);
            $sharedStrings = $zip->getFromName(self::SHARED_STRINGS_PATH);
            if ($worksheet === false || $sharedStrings === false) {
                throw new RuntimeException('The Food Bank Excel report template is incomplete.');
            }

            $sheetDocument = $this->loadXml($worksheet);
            $this->fillWorksheet($sheetDocument, $records);
            $sharedStringsDocument = $this->loadXml($sharedStrings);
            $this->setInstitutionHeading($sharedStringsDocument);

            if (! $zip->addFromString(self::SHEET_PATH, $sheetDocument->saveXML())
                || ! $zip->addFromString(self::SHARED_STRINGS_PATH, $sharedStringsDocument->saveXML())) {
                throw new RuntimeException('Could not write Food Bank report data into the workbook.');
            }

            if (! $zip->close()) {
                throw new RuntimeException('Could not finish the Food Bank Excel report.');
            }
        } catch (Throwable $exception) {
            $zip->close();
            @unlink($outputPath);
            throw $exception;
        }

        return $outputPath;
    }

    private function fillWorksheet(DOMDocument $document, iterable $records): void
    {
        $xpath = $this->xpath($document);
        $sheetData = $xpath->query('/x:worksheet/x:sheetData')->item(0);
        if (! $sheetData instanceof DOMElement) {
            throw new RuntimeException('The Food Bank worksheet does not contain a sheet data section.');
        }

        $rows = [];
        foreach ($xpath->query('./x:row', $sheetData) as $row) {
            if ($row instanceof DOMElement) {
                $rows[(int) $row->getAttribute('r')] = $row;
            }
        }

        $styleTemplate = $rows[41] ?? null;
        if (! $styleTemplate instanceof DOMElement) {
            throw new RuntimeException('The Food Bank worksheet is missing its formatted record rows.');
        }

        foreach ($records as $index => $record) {
            $rowNumber = 6 + (int) $index;
            $row = $rows[$rowNumber] ?? null;
            if (! $row instanceof DOMElement || $xpath->query('./x:c', $row)->length === 0) {
                $row = $this->cloneStyledRow($document, $styleTemplate, $rowNumber);
                $this->replaceOrInsertRow($sheetData, $row, $rows, $rowNumber);
            }

            $values = [
                'B' => ['value' => (string) ($index + 1), 'numeric' => true],
                'C' => ['value' => Carbon::parse($record->claimed_at)->format('d/m/Y h:i A'), 'numeric' => false],
                'D' => ['value' => (string) ($record->student_name ?? ''), 'numeric' => false],
                'E' => ['value' => (string) ($record->matric_no ?? ''), 'numeric' => false],
                'F' => ['value' => $record->item_count === null ? '' : (string) $record->item_count, 'numeric' => $record->item_count !== null],
                'G' => ['value' => $record->is_b40 === null ? '' : ((int) $record->is_b40 === 1 ? 'YA' : 'TIDAK'), 'numeric' => false],
            ];

            foreach ($values as $column => $cellValue) {
                $cell = $xpath->query('./x:c[@r="' . $column . $rowNumber . '"]', $row)->item(0);
                if (! $cell instanceof DOMElement) {
                    throw new RuntimeException('The Food Bank worksheet is missing a formatted data cell.');
                }
                $this->setCellValue($document, $cell, $cellValue['value'], $cellValue['numeric']);
            }
        }
    }

    private function cloneStyledRow(DOMDocument $document, DOMElement $template, int $rowNumber): DOMElement
    {
        $row = $template->cloneNode(true);
        if (! $row instanceof DOMElement) {
            throw new RuntimeException('Could not copy the Food Bank worksheet row style.');
        }

        $row->setAttribute('r', (string) $rowNumber);
        foreach ($row->getElementsByTagNameNS(self::XML_NS, 'c') as $cell) {
            if ($cell instanceof DOMElement) {
                $cell->setAttribute('r', preg_replace('/\d+$/', (string) $rowNumber, $cell->getAttribute('r')));
            }
        }

        return $row;
    }

    /** @param array<int, DOMElement> $rows */
    private function replaceOrInsertRow(DOMElement $sheetData, DOMElement $row, array &$rows, int $rowNumber): void
    {
        if (isset($rows[$rowNumber])) {
            $sheetData->replaceChild($row, $rows[$rowNumber]);
        } else {
            $nextRow = null;
            foreach ($rows as $existingNumber => $existingRow) {
                if ($existingNumber > $rowNumber && ($nextRow === null || $existingNumber < (int) $nextRow->getAttribute('r'))) {
                    $nextRow = $existingRow;
                }
            }
            if ($nextRow) {
                $sheetData->insertBefore($row, $nextRow);
            } else {
                $sheetData->appendChild($row);
            }
        }

        $rows[$rowNumber] = $row;
    }

    private function setCellValue(DOMDocument $document, DOMElement $cell, string $value, bool $numeric): void
    {
        while ($cell->firstChild) {
            $cell->removeChild($cell->firstChild);
        }

        if ($value === '') {
            $cell->removeAttribute('t');
            return;
        }

        if ($numeric) {
            $cell->removeAttribute('t');
            $node = $document->createElementNS(self::XML_NS, 'v');
            $node->appendChild($document->createTextNode($value));
            $cell->appendChild($node);
            return;
        }

        $cell->setAttribute('t', 'inlineStr');
        $inlineString = $document->createElementNS(self::XML_NS, 'is');
        $text = $document->createElementNS(self::XML_NS, 't');
        $text->appendChild($document->createTextNode($value));
        if (trim($value) !== $value) {
            $text->setAttributeNS('http://www.w3.org/XML/1998/namespace', 'xml:space', 'preserve');
        }
        $inlineString->appendChild($text);
        $cell->appendChild($inlineString);
    }

    private function setInstitutionHeading(DOMDocument $document): void
    {
        $xpath = $this->xpath($document);
        $heading = $xpath->query('/x:sst/x:si[1]/x:r[2]/x:t')->item(0);
        if ($heading === null) {
            throw new RuntimeException('The Food Bank template heading could not be located.');
        }

        $heading->nodeValue = 'POLITEKNIK BESUT TERENGGANU';
    }

    private function loadXml(string $xml): DOMDocument
    {
        $previous = libxml_use_internal_errors(true);
        $document = new DOMDocument();
        $document->preserveWhiteSpace = false;
        $loaded = $document->loadXML($xml, LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if (! $loaded) {
            throw new RuntimeException('The Food Bank Excel report template contains invalid XML.');
        }

        return $document;
    }

    private function xpath(DOMDocument $document): DOMXPath
    {
        $xpath = new DOMXPath($document);
        $xpath->registerNamespace('x', self::XML_NS);
        return $xpath;
    }
}
