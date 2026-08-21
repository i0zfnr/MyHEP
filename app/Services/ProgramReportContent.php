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

        $jawatankuasa = is_array($decoded['jawatankuasa'] ?? null) ? $decoded['jawatankuasa'] : [];
        $penceramah = is_array($decoded['penceramah'] ?? null) ? $decoded['penceramah'] : [];

        return [
            'kluster_kpi' => $this->text($decoded['kluster_kpi'] ?? null, $fallback['kluster_kpi']),
            'peringkat' => $this->text($decoded['peringkat'] ?? null, $fallback['peringkat']),
            'executive_summary' => $this->text($decoded['executive_summary'] ?? $decoded['ringkasan_program'] ?? null, $fallback['executive_summary']),
            'objectives' => $this->list($decoded['objectives'] ?? $decoded['objektif'] ?? null, $fallback['objectives']),
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
            'kewangan' => $this->text($decoded['kewangan'] ?? null, $fallback['kewangan']),
            'survey_summary' => $this->text($decoded['survey_summary'] ?? $decoded['maklum_balas'] ?? null, $fallback['survey_summary']),
            'achievements' => $this->list($decoded['achievements'] ?? $decoded['pencapaian'] ?? null, $fallback['achievements']),
            'issues' => $this->list($decoded['issues'] ?? $decoded['isu'] ?? null, $fallback['issues']),
            'improvements' => $this->list($decoded['improvements'] ?? $decoded['cadangan'] ?? null, $fallback['improvements']),
            'conclusion' => $this->text($decoded['conclusion'] ?? $decoded['kesimpulan'] ?? null, $fallback['conclusion']),
        ];
    }

    public function fallback(object $program, array $data): array
    {
        $objectives = preg_split('/\R+|(?<=\.)\s+(?=[A-Z])/u', trim((string) ($program->objectives ?? ''))) ?: [];
        $objectives = array_values(array_filter(array_map('trim', $objectives)));
        if ($objectives === []) {
            $objectives = [
                'Meningkatkan kemahiran dan pengetahuan peserta melalui pengisian program yang komprehensif.',
                'Memupuk semangat kerjasama dan kepimpinan dalam kalangan warga Politeknik Besut.',
                'Memastikan penglibatan aktif peserta dalam aktiviti pembangunan sahsiah dan kecemerlangan.'
            ];
        }

        $startsAt = $program->starts_at ? date('d.m.Y', strtotime($program->starts_at)) : date('d.m.Y');
        $startTime = $program->starts_at ? date('h:i A', strtotime($program->starts_at)) : '08:30 AM';
        $endTime = $program->ends_at ? date('h:i A', strtotime($program->ends_at)) : '05:00 PM';

        $feedbackText = ($data['survey_responses'] ?? 0) > 0
            ? $data['survey_responses'].' maklum balas peserta direkodkan melalui sistem MyHEP dengan skor purata '.$data['average_rating'].' / 5.00. Majoriti peserta menyatakan program memberi manfaat yang amat tinggi.'
            : 'Program ini mencatatkan kehadiran '.$data['attendance_total'].' peserta internal yang disahkan melalui imbasan QR kehadiran MyHEP.';

        return [
            'kluster_kpi' => 'Kemahiran dan Inovasi',
            'peringkat' => 'Politeknik / Institusi',
            'executive_summary' => ($program->description ?? null)
                ? (string) $program->description
                : 'Program '.(string) $program->title.' telah berjaya dilaksanakan di '.(($program->venue ?? null) ?: 'Politeknik Besut').' dengan penyertaan seramai '.$data['attendance_total'].' orang peserta. Program ini bertujuan mencapai objektif pembangunan dan kecemerlangan yang digariskan dalam kertas kerja kelulusan.',
            'objectives' => $objectives,
            'jawatankuasa' => [
                'penaung' => 'Pengarah Politeknik Besut',
                'penasihat1' => 'Timbalan Pengarah Akademik / Hal Ehwal Pelajar',
                'penasihat2' => 'Ketua Jabatan '.(($data['organizer'] ?? null) ?: 'JHEP'),
                'pengarah_program' => ($data['prepared_by'] ?? null) ?: ($program->director_name ?? 'Pengarah Program'),
                'setiausaha' => 'Setiausaha Program',
                'ajk' => 'Jawatankuasa Pelaksana & AJK Pelajar',
                'urusetia' => 'Urusetia '.(($data['organizer'] ?? null) ?: 'Politeknik Besut'),
            ],
            'penceramah' => [
                'nama' => ($program->director_name ?? 'Pegawai Terlibat'),
                'jawatan' => 'Pegawai Pendidikan / Penceramah Jemputan',
                'gred' => 'Gred Berkenaan',
                'institusi' => 'Politeknik Besut Terengganu',
            ],
            'aturcara' => [
                ['tarikh' => $startsAt, 'masa' => $startTime.' – '.date('h:i A', strtotime($startTime . ' +30 minutes')), 'aktiviti' => 'Pendaftaran & Ketibaan Peserta'],
                ['tarikh' => $startsAt, 'masa' => date('h:i A', strtotime($startTime . ' +30 minutes')).' – '.date('h:i A', strtotime($startTime . ' +3 hours')), 'aktiviti' => 'Sesi Utama & Pengisian Program '.$program->title],
                ['tarikh' => $startsAt, 'masa' => date('h:i A', strtotime($startTime . ' +3 hours')).' – '.$endTime, 'aktiviti' => 'Rumusan, Sesi Maklum Balas & Penutup'],
            ],
            'kewangan' => 'Tiada / Peruntukan Dalaman Jabatan',
            'survey_summary' => $feedbackText,
            'achievements' => [
                'Program berjaya mencapai objektif utama dan memberi pendedahan menyeluruh kepada semua peserta.',
                'Kehadiran seramai '.$data['attendance_total'].' orang peserta direkodkan dan disahkan secara digital dalam sistem MyHEP.',
                'Peningkatan kefahaman dan komitmen peserta terhadap pengisian aktiviti yang dianjurkan.'
            ],
            'issues' => [
                'Kekangan masa program memerlukan perancangan jadual yang lebih fleksibel pada masa hadapan.',
            ],
            'improvements' => [
                'Memperluaskan hebahan awal program bagi meningkatkan jumlah penyertaan sasaran.',
                'Memantapkan lagi kelengkapan teknikal dan logistik sepanjang program berlangsung.'
            ],
            'conclusion' => 'Secara keseluruhannya, program '.(string) $program->title.' telah berjalan dengan lancar dan mencapai matlamat yang disasarkan selaras dengan perancangan kertas kerja dan garis panduan Politeknik Besut.',
        ];
    }

    public function toPlainText(array $report): string
    {
        $objText = implode("\n- ", $report['objectives'] ?? []);
        $achieveText = implode("\n- ", $report['achievements'] ?? []);
        $issuesText = implode("\n- ", $report['issues'] ?? []);
        $improveText = implode("\n- ", $report['improvements'] ?? []);

        return implode("\n\n", array_filter([
            "1. KLUSTER KPI & PERINGKAT\nKluster: ".($report['kluster_kpi'] ?? 'Kemahiran dan Inovasi')." | Peringkat: ".($report['peringkat'] ?? 'Politeknik'),
            "2. RINGKASAN PROGRAM\n".($report['executive_summary'] ?? ''),
            "3. OBJEKTIF PROGRAM\n- ".$objText,
            "4. MAKLUM BALAS & KAJI SELIDIK\n".($report['survey_summary'] ?? ''),
            "5. PENCAPAIAN & HASIL / IMPAK\n- ".$achieveText,
            "6. ISU PELAKSANAAN\n- ".$issuesText,
            "7. CADANGAN PENAMBAHBAIKAN\n- ".$improveText,
            "8. KESIMPULAN\n".($report['conclusion'] ?? ''),
        ]));
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
