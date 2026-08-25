<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use ZipArchive;

class ProgramPaperworkContent
{
    public function buildPrompt(array $input, ?string $extractedAjk = null): string
    {
        $title = $input['title'] ?? 'Program Politeknik';
        $date = $input['date_text'] ?? date('d.m.Y');
        $venue = $input['venue'] ?? 'Politeknik Besut Terengganu';
        $organizer = $input['organizer'] ?? 'Politeknik Besut Terengganu';
        $targetGroup = $input['target_group'] ?? 'Pelajar Politeknik Besut';
        $participantCount = $input['participant_count'] ?? '30';
        $itinerary = $input['itinerary'] ?? '';
        $financialDetails = $input['financial_details'] ?? '';
        $ajkText = trim(($extractedAjk ?: '') . "\n" . ($input['ajk_text'] ?? ''));

        return <<<PROMPT
Anda adalah pembantu profesional pengurusan kertas kerja rasmi Politeknik Besut Terengganu.
Tugasan anda adalah menjana kandungan Kertas Kerja Program yang lengkap, rasmi, dan berstruktur tinggi berdasarkan maklumat yang dibekalkan oleh pengguna di bawah.

MAKLUMAT ASAS DARI PENGGUNA:
1. Tajuk Program: {$title}
2. Tarikh: {$date}
3. Tempat: {$venue}
4. Anjuran: {$organizer}
5. Kumpulan Sasaran: {$targetGroup}
6. Bilangan Peserta: {$participantCount}
7. Senarai AJK Program / Jawatankuasa:
{$ajkText}
8. Aturcara Program (Tentatif):
{$itinerary}
9. Perincian Kewangan & Belanjawan:
{$financialDetails}

FORMAT KERTAS KERJA POLIBESUT 2025:
Sila kembalikan jawapan HANYA dalam format JSON tulen (tanpa sebarang teks atau kod Markdown di luar JSON) dengan struktur berikut:
{
  "kluster_kpi": "Kemahiran dan Inovasi",
  "kategori_kursus": "09",
  "peringkat": "Politeknik / Institusi",
  "ringkasan_program": "Penerangan latar belakang dan ringkasan rasmi program 2-3 perenggan yang mantap...",
  "objektif": [
    "Objektif SMART 1 yang jelas dan boleh diukur...",
    "Objektif SMART 2...",
    "Objektif SMART 3..."
  ],
  "impak_program": [
    "Hasil atau impak positif 1 terhadap peserta / politeknik...",
    "Hasil atau impak positif 2...",
    "Hasil atau impak positif 3..."
  ],
  "jawatankuasa": {
    "penaung": "Udom A/L Ewon (Pengarah Politeknik Besut)",
    "penasihat1": "Nama Penasihat 1",
    "penasihat2": "Nama Penasihat 2",
    "pengarah_program": "Nama Pengarah Program",
    "setiausaha": "Nama Setiausaha",
    "ajk": "Nama AJK Pelaksana",
    "urusetia": "Nama Urusetia"
  },
  "penceramah": {
    "nama": "Nama Penceramah / Pegawai Terlibat",
    "jawatan": "Jawatan Pegawai",
    "gred": "DH48 / DH52 / —",
    "institusi": "Politeknik Besut Terengganu / Institusi Luar"
  },
  "aturcara": [
    {"tarikh": "{$date}", "masa": "08.00 pagi - 08.30 pagi", "aktiviti": "Pendaftaran Peserta"},
    {"tarikh": "{$date}", "masa": "08.30 pagi - 10.30 pagi", "aktiviti": "Sesi 1: Taklimat & Pengenalan"},
    {"tarikh": "{$date}", "masa": "10.30 pagi - 01.00 petang", "aktiviti": "Sesi 2: Bengkel Praktikal"},
    {"tarikh": "{$date}", "masa": "01.00 petang - 02.30 petang", "aktiviti": "Makan Tengahari & Solat"},
    {"tarikh": "{$date}", "masa": "02.30 petang - 04.30 petang", "aktiviti": "Sesi 3 & Penutup"}
  ],
  "anggaran_belanja": [
    {"perkara": "Makan Tengahari Peserta", "harga_seunit": 10.00, "kuantiti": 50, "jumlah": 500.00, "sumber": "OS29000"}
  ],
  "jumlah_perbelanjaan": "RM 500.00",
  "sumber_kewangan": "Kerajaan / Akaun Amanah",
  "penutup": "Diharapkan program ini dapat mencapai objektif yang telah digariskan serta memberi impak positif kepada semua peserta."
}
PROMPT;
    }

    public function fromAiResponse(string $response, array $input, ?string $extractedAjk = null): array
    {
        $fallback = $this->fallback($input, $extractedAjk);
        $clean = trim(preg_replace('/^```(?:json)?\s*|\s*```$/iu', '', trim($response)) ?? '');
        $start = strpos($clean, '{');
        $end = strrpos($clean, '}');

        if ($start === false || $end === false || $end < $start) {
            return $fallback;
        }

        $decoded = json_decode(substr($clean, $start, $end - $start + 1), true);
        if (! is_array($decoded)) {
            return $fallback;
        }

        $jawatankuasa = is_array($decoded['jawatankuasa'] ?? null) ? $decoded['jawatankuasa'] : [];
        $penceramah = is_array($decoded['penceramah'] ?? null) ? $decoded['penceramah'] : [];

        return [
            'kluster_kpi' => $this->text($decoded['kluster_kpi'] ?? null, $fallback['kluster_kpi']),
            'kategori_kursus' => $this->text($decoded['kategori_kursus'] ?? null, $fallback['kategori_kursus']),
            'peringkat' => $this->text($decoded['peringkat'] ?? null, $fallback['peringkat']),
            'ringkasan_program' => $this->text($decoded['ringkasan_program'] ?? null, $fallback['ringkasan_program']),
            'objektif' => $this->list($decoded['objektif'] ?? null, $fallback['objektif']),
            'impak_program' => $this->list($decoded['impak_program'] ?? null, $fallback['impak_program']),
            'jawatankuasa' => [
                'penaung' => $this->text($jawatankuasa['penaung'] ?? null, $fallback['jawatankuasa']['penaung']),
                'penasihat1' => $this->text($jawatankuasa['penasihat1'] ?? null, $fallback['jawatankuasa']['penasihat1']),
                'penasihat2' => $this->text($jawatankuasa['penasihat2'] ?? null, $fallback['jawatankuasa']['penasihat2']),
                'pengarah_program' => $this->text($jawatankuasa['pengarah_program'] ?? null, $fallback['jawatankuasa']['pengarah_program']),
                'setiausaha' => $this->text($jawatankuasa['setiausaha'] ?? null, $fallback['jawatankuasa']['setiausaha']),
                'ajk' => $this->text($jawatankuasa['ajk'] ?? null, $fallback['jawatankuasa']['ajk']),
                'urusetia' => $this->text($jawatankuasa['urusetia'] ?? null, $fallback['jawatankuasa']['urusetia']),
            ],
            'penceramah' => [
                'nama' => $this->text($penceramah['nama'] ?? null, $fallback['penceramah']['nama']),
                'jawatan' => $this->text($penceramah['jawatan'] ?? null, $fallback['penceramah']['jawatan']),
                'gred' => $this->text($penceramah['gred'] ?? null, $fallback['penceramah']['gred']),
                'institusi' => $this->text($penceramah['institusi'] ?? null, $fallback['penceramah']['institusi']),
            ],
            'aturcara' => is_array($decoded['aturcara'] ?? null) && count($decoded['aturcara']) > 0
                ? $decoded['aturcara']
                : $fallback['aturcara'],
            'anggaran_belanja' => is_array($decoded['anggaran_belanja'] ?? null) && count($decoded['anggaran_belanja']) > 0
                ? $decoded['anggaran_belanja']
                : $fallback['anggaran_belanja'],
            'jumlah_perbelanjaan' => $this->text($decoded['jumlah_perbelanjaan'] ?? null, $fallback['jumlah_perbelanjaan']),
            'sumber_kewangan' => $this->text($decoded['sumber_kewangan'] ?? null, $fallback['sumber_kewangan']),
            'penutup' => $this->text($decoded['penutup'] ?? null, $fallback['penutup']),
        ];
    }

    public function fallback(array $input, ?string $extractedAjk = null): array
    {
        $title = trim($input['title'] ?? 'Program Politeknik');
        $date = trim($input['date_text'] ?? date('d.m.Y'));
        $venue = trim($input['venue'] ?? 'Politeknik Besut Terengganu');
        $organizer = trim($input['organizer'] ?? 'Politeknik Besut Terengganu');
        $targetGroup = trim($input['target_group'] ?? 'Pelajar Politeknik Besut');
        $participantCount = trim($input['participant_count'] ?? '30 orang');
        $itinerary = trim($input['itinerary'] ?? '');
        $financialDetails = trim($input['financial_details'] ?? '');
        $ajkText = trim(($extractedAjk ?: '') . "\n" . ($input['ajk_text'] ?? ''));

        // Parse Itinerary lines if provided
        $aturcara = [];
        if ($itinerary !== '') {
            $lines = preg_split('/\R+/u', $itinerary);
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '') continue;
                if (preg_match('/^(\d{1,2}[\.\:]\d{2}\s*(?:pagi|petang|tgh|malam|am|pm)?\s*[-–]\s*\d{1,2}[\.\:]\d{2}\s*(?:pagi|petang|tgh|malam|am|pm)?)\s*[:–-]?\s*(.*)$/ui', $line, $m)) {
                    $aturcara[] = ['tarikh' => $date, 'masa' => trim($m[1]), 'aktiviti' => trim($m[2])];
                } elseif (preg_match('/^(\d{1,2}[\.\:]\d{2}\s*(?:pagi|petang|tgh|malam|am|pm)?)\s*[:–-]?\s*(.*)$/ui', $line, $m)) {
                    $aturcara[] = ['tarikh' => $date, 'masa' => trim($m[1]), 'aktiviti' => trim($m[2])];
                } else {
                    $aturcara[] = ['tarikh' => $date, 'masa' => 'Masa Ditetapkan', 'aktiviti' => $line];
                }
            }
        }

        if ($aturcara === []) {
            $aturcara = [
                ['tarikh' => $date, 'masa' => '08.00 pg – 08.30 pg', 'aktiviti' => 'Pendaftaran & Taklimat Awal'],
                ['tarikh' => $date, 'masa' => '08.30 pg – 10.30 pg', 'aktiviti' => 'Sesi 1: Pengenalan & Pengisian Utama'],
                ['tarikh' => $date, 'masa' => '10.30 pg – 10.45 pg', 'aktiviti' => 'Rehat & Minum Pagi'],
                ['tarikh' => $date, 'masa' => '10.45 pg – 01.00 tgh', 'aktiviti' => 'Sesi 2: Bengkel & Aktiviti Interaktif'],
                ['tarikh' => $date, 'masa' => '01.00 tgh – 02.30 ptg', 'aktiviti' => 'Makan Tengahari & Solat'],
                ['tarikh' => $date, 'masa' => '02.30 ptg – 04.30 ptg', 'aktiviti' => 'Sesi 3: Pembentangan & Rumusan'],
                ['tarikh' => $date, 'masa' => '04.30 ptg', 'aktiviti' => 'Majlis Penutup'],
            ];
        }

        // Parse Financial details
        $anggaranBelanja = [];
        $totalAmount = 0.0;
        if ($financialDetails !== '') {
            $lines = preg_split('/\R+/u', $financialDetails);
            foreach ($lines as $index => $line) {
                $line = trim($line);
                if ($line === '') continue;
                if (preg_match('/RM\s*([\d\.,]+)/i', $line, $m)) {
                    $val = (float) str_replace(',', '', $m[1]);
                    $totalAmount += $val;
                    $anggaranBelanja[] = [
                        'perkara' => $line,
                        'harga_seunit' => $val,
                        'kuantiti' => 1,
                        'jumlah' => $val,
                        'sumber' => 'OS29000',
                    ];
                } else {
                    $anggaranBelanja[] = [
                        'perkara' => $line,
                        'harga_seunit' => 0.0,
                        'kuantiti' => 1,
                        'jumlah' => 0.0,
                        'sumber' => 'OS29000',
                    ];
                }
            }
        }

        if ($anggaranBelanja === []) {
            $anggaranBelanja = [
                ['perkara' => 'Makan Tengahari Peserta', 'harga_seunit' => 10.00, 'kuantiti' => (int)$participantCount ?: 26, 'jumlah' => ((int)$participantCount ?: 26) * 10.00, 'sumber' => 'OS29000'],
            ];
            $totalAmount = ((int)$participantCount ?: 26) * 10.00;
        }

        // Extract AJK names if available
        $penasihat1 = 'Saifuddin Bin Semail';
        $penasihat2 = 'Ts. Elisnorazmaliza Bt Ab. Hamid';
        $pengarahProg = session('auth_user.name') ?: 'Pengarah Program';
        $setiausaha = 'Setiausaha Program';
        $ajkPelaksana = 'Jawatankuasa Pelaksana Program';
        $urusetia = $organizer;

        if ($ajkText !== '') {
            if (preg_match('/penasihat\s*(?:1|i)?\s*[:–-]\s*([^\r\n]+)/iu', $ajkText, $m)) $penasihat1 = trim($m[1]);
            if (preg_match('/penasihat\s*(?:2|ii)?\s*[:–-]\s*([^\r\n]+)/iu', $ajkText, $m)) $penasihat2 = trim($m[1]);
            if (preg_match('/pengarah(?:\s*program)?\s*[:–-]\s*([^\r\n]+)/iu', $ajkText, $m)) $pengarahProg = trim($m[1]);
            if (preg_match('/setiausaha\s*[:–-]\s*([^\r\n]+)/iu', $ajkText, $m)) $setiausaha = trim($m[1]);
            if (preg_match('/ajk\s*[:–-]\s*([^\r\n]+)/iu', $ajkText, $m)) $ajkPelaksana = trim($m[1]);
            if (preg_match('/urusetia\s*[:–-]\s*([^\r\n]+)/iu', $ajkText, $m)) $urusetia = trim($m[1]);
        }

        return [
            'kluster_kpi' => 'Kemahiran dan Inovasi',
            'kategori_kursus' => '09',
            'peringkat' => 'Politeknik / Institusi',
            'ringkasan_program' => "Program {$title} merupakan satu inisiatif pembangunan yang dirangka khusus bagi {$targetGroup}. Pelaksanaan program ini di {$venue} bertujuan memantapkan kemahiran, pengetahuan serta kecekapan peserta dalam bidang yang berkaitan agar dapat diaplikasikan dengan lebih efektif dan berkesan.",
            'objektif' => [
                "Memberikan pendedahan secara komprehensif kepada peserta mengenai skop dan pengisian {$title}.",
                "Memantapkan kefahaman dan kemahiran praktikal peserta dalam mengendalikan tugasan berkaitan.",
                "Memupuk semangat kerjasama dan komitmen tinggi dalam kalangan warga Politeknik Besut Terengganu."
            ],
            'impak_program' => [
                "Peningkatan kefahaman dan kemahiran teknikal peserta dalam modul yang dipelajari.",
                "Peningkatan kecekapan, produktiviti dan kualiti penyampaian program di peringkat politeknik.",
                "Pengukuhan jaringan kolaborasi dan pemantapan sahsiah peserta secara menyeluruh."
            ],
            'jawatankuasa' => [
                'penaung' => 'Udom A/L Ewon (Pengarah Politeknik Besut)',
                'penasihat1' => $penasihat1,
                'penasihat2' => $penasihat2,
                'pengarah_program' => $pengarahProg,
                'setiausaha' => $setiausaha,
                'ajk' => $ajkPelaksana,
                'urusetia' => $urusetia,
            ],
            'penceramah' => [
                'nama' => 'Penceramah Jemputan / Pegawai Terlibat',
                'jawatan' => 'Pegawai Pendidikan Pengajian Tinggi',
                'gred' => 'DH48 / DH52',
                'institusi' => 'Politeknik Besut Terengganu',
            ],
            'aturcara' => $aturcara,
            'anggaran_belanja' => $anggaranBelanja,
            'jumlah_perbelanjaan' => 'RM ' . number_format($totalAmount, 2),
            'sumber_kewangan' => 'Kerajaan / Akaun Amanah',
            'penutup' => "Diharapkan program {$title} yang akan dilaksanakan ini dapat mencapai objektif yang telah ditetapkan serta mendapat sokongan dan kelulusan daripada pihak pengurusan.",
        ];
    }

    public function extractTextFromAjkFile(?UploadedFile $file): ?string
    {
        if (! $file || ! $file->isValid()) {
            return null;
        }

        $extension = strtolower($file->getClientOriginalExtension());

        if ($extension === 'docx') {
            return $this->extractTextFromDocx($file->getRealPath());
        }

        if ($extension === 'pdf') {
            return $this->extractTextFromPdf($file->getRealPath());
        }

        if (in_array($extension, ['txt', 'csv'])) {
            return file_get_contents($file->getRealPath());
        }

        return null;
    }

    private function extractTextFromDocx(string $filePath): ?string
    {
        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            return null;
        }

        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        if ($xml === false) {
            return null;
        }

        // Strip tags and clean whitespace
        $text = strip_tags(str_replace(['</w:p>', '</w:tr>'], ["\n", "\n"], $xml));
        return trim(preg_replace('/[ \t]+/u', ' ', $text));
    }

    private function extractTextFromPdf(string $filePath): ?string
    {
        // Simple raw stream extraction fallback
        $content = @file_get_contents($filePath);
        if ($content === false) {
            return null;
        }

        // Extract readable text lines from PDF streams
        if (preg_match_all('/\((.*?)\)\s*T[jJ]/s', $content, $matches)) {
            $extracted = implode(' ', $matches[1]);
            if (strlen(trim($extracted)) > 10) {
                return trim($extracted);
            }
        }

        return null;
    }

    private function text(?string $value, string $fallback): string
    {
        $value = trim((string) $value);
        return $value !== '' ? $value : $fallback;
    }

    private function list($value, array $fallback): array
    {
        if (is_array($value)) {
            $filtered = array_values(array_filter(array_map('trim', $value)));
            if ($filtered !== []) {
                return $filtered;
            }
        }

        if (is_string($value) && trim($value) !== '') {
            $lines = preg_split('/\R+/u', trim($value)) ?: [];
            $filtered = array_values(array_filter(array_map('trim', $lines)));
            if ($filtered !== []) {
                return $filtered;
            }
        }

        return $fallback;
    }
}
