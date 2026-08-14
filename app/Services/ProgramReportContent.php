<?php

namespace App\Services;

use Illuminate\Support\Str;

class ProgramReportContent
{
    public function fromAiResponse(string $response, object $program, array $data): array
    {
        $fallback = $this->fallback($program, $data);
        $response = trim(preg_replace('/^```(?:json)?\s*|\s*```$/iu', '', trim($response)) ?? '');
        $start = strpos($response, '{');
        $end = strrpos($response, '}');

        if ($start === false || $end === false || $end < $start) {
            return $fallback;
        }

        $decoded = json_decode(substr($response, $start, $end - $start + 1), true);
        if (! is_array($decoded)) {
            return $fallback;
        }

        return [
            'executive_summary' => $this->text($decoded['executive_summary'] ?? null, $fallback['executive_summary']),
            'objectives' => $this->list($decoded['objectives'] ?? null, $fallback['objectives']),
            'survey_summary' => $this->text($decoded['survey_summary'] ?? null, $fallback['survey_summary']),
            'achievements' => $this->list($decoded['achievements'] ?? null, $fallback['achievements']),
            'issues' => $this->list($decoded['issues'] ?? null, $fallback['issues']),
            'improvements' => $this->list($decoded['improvements'] ?? null, $fallback['improvements']),
            'conclusion' => $this->text($decoded['conclusion'] ?? null, $fallback['conclusion']),
        ];
    }

    public function fallback(object $program, array $data): array
    {
        $objectives = preg_split('/\R+|(?<=\.)\s+(?=[A-Z])/u', trim((string) ($program->objectives ?? ''))) ?: [];
        $objectives = array_values(array_filter(array_map('trim', $objectives)));

        return [
            'executive_summary' => 'Program '.(string) $program->title.' telah dilaksanakan berdasarkan rekod program yang diluluskan, dengan '.$data['attendance_total'].' orang peserta direkodkan.',
            'objectives' => $objectives !== [] ? $objectives : ['Objektif program tidak direkodkan.'],
            'survey_summary' => $data['survey_responses'].' respons soal selidik direkodkan dengan purata penilaian '.$data['average_rating'].' daripada 5.',
            'achievements' => ['Pelaksanaan program dan kehadiran peserta telah direkodkan dalam StudentEdge.'],
            'issues' => ['Tiada isu yang disahkan dalam rekod sistem.'],
            'improvements' => ['Cadangan penambahbaikan perlu disahkan oleh Pengarah Program.'],
            'conclusion' => 'Secara keseluruhannya, laporan ini disediakan berdasarkan maklumat program, rekod kehadiran dan maklum balas yang terdapat dalam StudentEdge.',
        ];
    }

    public function toPlainText(array $report): string
    {
        return implode("\n\n", [
            "1. Ringkasan Eksekutif\n".$report['executive_summary'],
            "2. Objektif\n- ".implode("\n- ", $report['objectives']),
            "3. Maklum Balas Peserta\n".$report['survey_summary'],
            "4. Hasil / Impak\n- ".implode("\n- ", $report['achievements']),
            "5. Isu\n- ".implode("\n- ", $report['issues']),
            "6. Cadangan Penambahbaikan\n- ".implode("\n- ", $report['improvements']),
            "7. Kesimpulan\n".$report['conclusion'],
        ]);
    }

    private function text(mixed $value, string $fallback): string
    {
        if (! is_string($value) || trim($value) === '') {
            return $fallback;
        }

        return Str::limit(trim(strip_tags($value)), 4000, '');
    }

    private function list(mixed $value, array $fallback): array
    {
        if (is_string($value)) {
            $value = preg_split('/\R+|^\s*[-*]\s*/mu', $value) ?: [];
        }
        if (! is_array($value)) {
            return $fallback;
        }

        $items = array_values(array_filter(array_map(
            fn ($item): string => is_scalar($item) ? Str::limit(trim(strip_tags((string) $item)), 1000, '') : '',
            $value
        )));

        return $items !== [] ? array_slice($items, 0, 8) : $fallback;
    }
}
