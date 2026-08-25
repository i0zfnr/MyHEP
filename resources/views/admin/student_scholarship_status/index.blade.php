@extends('layouts.app')

@section('title', __('Pengumpulan Data Biasiswa Pelajar'))



@section('header')
    <h2 style="margin:0;font-size:1.1rem;font-weight:700;color:#2d1f14;">{{ __('Pengumpulan Data Biasiswa Pelajar') }}</h2>
@endsection

@section('content')
<div class="wrap student-sch-wrap">
    <div class="stats student-sch-stats">
        <div class="stat"><div class="label">{{ __('Jumlah Pelajar') }}</div><div class="value">{{ number_format($summary['total_students']) }}</div></div>
        <div class="stat"><div class="label">{{ __('Borang Dihantar') }}</div><div class="value">{{ number_format($summary['submitted']) }}</div></div>
        <div class="stat"><div class="label">{{ __('Menerima Biasiswa') }}</div><div class="value" style="color:var(--se-primary, #0284c7);">{{ number_format($summary['scholarship']) }}</div></div>
        <div class="stat"><div class="label">{{ __('Bantuan Kebajikan') }}</div><div class="value" style="color:var(--se-success, #059669);">{{ number_format($summary['welfare']) }}</div></div>
        <div class="stat"><div class="label">{{ __('Tiada Biasiswa/Bantuan') }}</div><div class="value" style="color:var(--se-text-muted, #7a6555);">{{ number_format($summary['none']) }}</div></div>
    </div>

    <div class="card student-sch-card">
        <div class="head student-sch-head">
            <div class="student-sch-title-wrap">
                <h1 class="student-sch-title">{{ __('Senarai Status Biasiswa & Bantuan Kebajikan Pelajar') }}</h1>
                <span class="student-sch-count">{{ number_format($records->total()) }} {{ __('rekod') }}</span>
            </div>
            <form method="GET" action="{{ route('admin.student-scholarship-status.index') }}" class="student-sch-filter-form">
                <div class="filter-field sch-filter-q">
                    <svg class="filter-icon" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="{{ __('Cari nama / matrik / penaja...') }}" autocomplete="off">
                </div>
                <div class="filter-field sch-filter-type">
                    <select name="type">
                        <option value="all" {{ ($filters['type'] ?? 'all') === 'all' ? 'selected' : '' }}>{{ __('Semua Status') }}</option>
                        <option value="scholarship" {{ ($filters['type'] ?? '') === 'scholarship' ? 'selected' : '' }}>{{ __('Biasiswa / Penajaan') }}</option>
                        <option value="welfare" {{ ($filters['type'] ?? '') === 'welfare' ? 'selected' : '' }}>{{ __('Bantuan Kebajikan') }}</option>
                        <option value="none" {{ ($filters['type'] ?? '') === 'none' ? 'selected' : '' }}>{{ __('Tiada Biasiswa / Bantuan') }}</option>
                        <option value="unsubmitted" {{ ($filters['type'] ?? '') === 'unsubmitted' ? 'selected' : '' }}>{{ __('Belum Hantar') }}</option>
                    </select>
                </div>
                <button class="sch-btn sch-btn-primary" type="submit">{{ __('Tapis') }}</button>
                @if(!empty(array_filter($filters ?? [])) || ($filters['type'] ?? 'all') !== 'all')
                    <a class="sch-btn sch-btn-reset" href="{{ route('admin.student-scholarship-status.index') }}">{{ __('Reset') }}</a>
                @endif
            </form>
        </div>
        <div style="overflow-x:auto;">
            <table class="student-sch-table">
                <thead>
                    <tr>
                        <th>{{ __('Pelajar') }}</th>
                        <th>{{ __('Program') }}</th>
                        <th>{{ __('Kategori / Status') }}</th>
                        <th>{{ __('Butiran / Penaja / Kebajikan') }}</th>
                        <th>{{ __('Maklumat Waris / Pendapatan') }}</th>
                        <th>{{ __('Jumlah (RM)') }}</th>
                        <th>{{ __('Dokumen Sokongan') }}</th>
                        <th>{{ __('Tarikh Hantar') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $row)
                        @php
                            $appType = $row->application_type ?? ($row->has_scholarship === 'yes' ? 'scholarship' : ($row->has_scholarship === 'no' ? 'none' : null));
                        @endphp
                        <tr>
                            <td>
                                <strong style="font-size:0.83rem;color:var(--se-text);">{{ $row->full_name }}</strong><br>
                                <span style="font-family:monospace;font-size:0.75rem;color:var(--se-text-muted);">{{ $row->matric_no }}</span>
                            </td>
                            <td><span style="font-weight:600;">{{ $row->program }}</span></td>
                            <td>
                                @if($appType === 'scholarship')
                                    <span class="sch-badge sch-badge-scholarship">{{ __('Biasiswa') }}</span>
                                @elseif($appType === 'welfare')
                                    <span class="sch-badge sch-badge-welfare">{{ __('Kebajikan') }}</span>
                                @elseif($appType === 'none')
                                    <span class="sch-badge sch-badge-none">{{ __('Tiada') }}</span>
                                @else
                                    <span class="sch-badge sch-badge-unsubmitted">{{ __('Belum Hantar') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($appType === 'scholarship')
                                    <strong>{{ $row->sponsor_name ?: '-' }}</strong>
                                    @if($row->notes)<br><small style="color:var(--se-text-muted);">{{ $row->notes }}</small>@endif
                                @elseif($appType === 'welfare')
                                    <strong style="color:#047857;">{{ $row->welfare_category ?: 'Bantuan Kebajikan' }}</strong>
                                    @if($row->welfare_description)<br><small style="color:var(--se-text-soft);font-style:italic;">{{ Str::limit($row->welfare_description, 50) }}</small>@endif
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($appType === 'welfare')
                                    <div style="font-size:11.5px;line-height:1.4;">
                                        <strong>Waris:</strong> {{ $row->guardian_name ?: '-' }} ({{ $row->guardian_relationship ?: 'Waris' }})<br>
                                        <strong>Tel:</strong> {{ $row->guardian_phone ?: '-' }}<br>
                                        <strong>Gaji:</strong> {{ $row->family_income !== null ? 'RM ' . number_format((float) $row->family_income, 2) : '-' }}
                                        @if($row->dependents_count) ({{ $row->dependents_count }} tanggungan) @endif
                                    </div>
                                @else
                                    <span style="color:var(--se-text-muted);">-</span>
                                @endif
                            </td>
                            <td>
                                @if($appType === 'scholarship')
                                    {{ $row->monthly_amount !== null ? 'RM ' . number_format((float) $row->monthly_amount, 2) . '/bln' : '-' }}
                                @elseif($appType === 'welfare')
                                    {{ $row->welfare_amount !== null ? 'RM ' . number_format((float) $row->welfare_amount, 2) : 'Bantuan Khas' }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($row->doc_id)
                                    <a class="sch-btn-doc" href="{{ route('admin.student-scholarship-status.documents.download', $row->doc_id) }}">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                        <span>{{ __('Muat Turun') }}</span>
                                    </a>
                                    <br><small style="font-size:10px;color:var(--se-text-muted);">{{ Str::limit($row->doc_name, 20) }}</small>
                                @else
                                    <span style="color:var(--se-text-muted);">-</span>
                                @endif
                            </td>
                            <td><span style="font-size:0.75rem;color:var(--se-text-soft);">{{ $row->submitted_at ? \Illuminate\Support\Carbon::parse($row->submitted_at)->format('d/m/Y H:i') : '-' }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" style="text-align:center;color:#7a6555;padding:24px;">{{ __('Tiada rekod dijumpai.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="student-sch-pagination">{{ $records->onEachSide(1)->links('vendor.pagination.myhep') }}</div>
    </div>
</div>
@endsection


