<!doctype html><html><head><meta charset="utf-8"><style>
@page{margin:18mm 16mm} body{font-family:DejaVu Sans,sans-serif;font-size:10pt;line-height:1.5;color:#111} h1{text-align:center;font-size:18pt;margin:0 0 18px} h2{font-size:12pt;border-bottom:1px solid #777;padding-bottom:5px} .meta{width:100%;border-collapse:collapse;margin-bottom:18px}.meta td{border:1px solid #999;padding:7px}.label{font-weight:bold;width:28%}.content{white-space:pre-wrap}
</style></head><body>
<h1>{{ __('LAPORAN PELAKSANAAN PROGRAM') }}</h1>
<table class="meta"><tr><td class="label">{{ __('Nama Program') }}</td><td>{{ $program->title }}</td></tr><tr><td class="label">{{ __('Tarikh') }}</td><td>{{ ($program->starts_at ?? null) ?: 'Tidak direkodkan' }}</td></tr><tr><td class="label">{{ __('Tempat') }}</td><td>{{ ($program->venue ?? null) ?: 'Tidak direkodkan' }}</td></tr><tr><td class="label">{{ __('Jumlah Peserta') }}</td><td>{{ $data['attendance_total'] }}</td></tr><tr><td class="label">{{ __('Respons Soal Selidik') }}</td><td>{{ $data['survey_responses'] }}</td></tr></table>
<h2>{{ __('Perincian Laporan') }}</h2><div class="content">{{ $content }}</div>
</body></html>
