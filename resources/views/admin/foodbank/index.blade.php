@extends('layouts.app')

@section('title', __('Pengurusan Food Bank Siswa'))

@section('header')
    <div class="fb-page-title">
        <h2>{{ __('Pengurusan Food Bank Siswa') }}</h2>
    </div>
@endsection



@section('content')
<div class="ui-shell">
    @if(session('success'))
        <div class="fb-alert fb-alert-success">
            <svg class="fb-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="fb-kpi-grid">
        <div class="fb-kpi-card">
            <span class="fb-kpi-label">{{ __('Jumlah Bantuan Ditebus') }}</span>
            <strong class="fb-kpi-value">{{ number_format($totalClaims) }}</strong>
        </div>

        <div class="fb-kpi-card">
            <span class="fb-kpi-label">{{ __('Pelajar Unik (Penerima)') }}</span>
            <strong class="fb-kpi-value">{{ number_format($uniqueStudents) }}</strong>
        </div>

        <div class="fb-kpi-card">
            <span class="fb-kpi-label">{{ __('Penebusan Hari Ini') }}</span>
            <strong class="fb-kpi-value">{{ number_format($claimsToday) }}</strong>
        </div>

        <div class="fb-kpi-card">
            <span class="fb-kpi-label">{{ __('Penebusan Bulan Ini') }}</span>
            <strong class="fb-kpi-value">{{ number_format($claimsThisMonth) }}</strong>
        </div>
    </div>

    <div class="fb-top-bar">
        <div>
            <h3 class="fb-section-title">{{ __('Rekod Penebusan Makanan Siswa') }}</h3>
            <p class="fb-section-copy">{{ __('Pelajar mengimbas QR kod statik di kaunter Food Bank untuk mengambil makanan percuma.') }}</p>
        </div>
        <div class="fb-actions">
            <a href="{{ route('admin.foodbank.qr') }}" target="_blank" class="fb-btn fb-btn-primary">
                <svg class="fb-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                {{ __('Cetak Poster QR Kod') }}
            </a>
            <a href="{{ route('admin.foodbank.export', request()->query()) }}" class="fb-btn fb-btn-success">
                <svg class="fb-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                {{ __('Eksport Data HQ (Excel/CSV)') }}
            </a>
        </div>
    </div>

    <div class="fb-filter-card">
        <form method="GET" action="{{ route('admin.foodbank.index') }}" class="fb-filter-form">
            <div class="fb-filter-group fb-filter-search">
                <label for="filterQ">{{ __('Carian Pelajar') }}</label>
                <input type="text" id="filterQ" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="{{ __('Nama, No. Matrik, atau No. K/P...') }}">
            </div>

            <div class="fb-filter-group">
                <label for="filterProgram">{{ __('Program') }}</label>
                <select id="filterProgram" name="program">
                    <option value="">{{ __('Semua Program') }}</option>
                    @foreach($availablePrograms as $prg)
                        <option value="{{ $prg }}" @selected(($filters['program'] ?? '') === $prg)>{{ $prg }}</option>
                    @endforeach
                </select>
            </div>

            <div class="fb-filter-group">
                <label for="filterSemester">{{ __('Semester') }}</label>
                <select id="filterSemester" name="semester">
                    <option value="">{{ __('Semua Semester') }}</option>
                    @for($s = 1; $s <= 6; $s++)
                        <option value="{{ $s }}" @selected(($filters['semester'] ?? '') == $s)>Semester {{ $s }}</option>
                    @endfor
                </select>
            </div>

            <div class="fb-filter-group">
                <label for="filterDateFrom">{{ __('Tarikh Dari') }}</label>
                <input type="date" id="filterDateFrom" name="date_from" value="{{ $filters['date_from'] ?? '' }}">
            </div>

            <div class="fb-filter-group">
                <label for="filterDateTo">{{ __('Tarikh Hingga') }}</label>
                <input type="date" id="filterDateTo" name="date_to" value="{{ $filters['date_to'] ?? '' }}">
            </div>

            <div class="fb-filter-actions">
                <button type="submit" class="fb-btn fb-btn-primary">
                    <svg class="fb-action-icon fb-action-icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    {{ __('Tapis') }}
                </button>
                @if(!empty(array_filter($filters)))
                    <a href="{{ route('admin.foodbank.index') }}" class="fb-btn fb-btn-icon" title="{{ __('Reset Penapis') }}" aria-label="{{ __('Reset Penapis') }}">&times;</a>
                @endif
            </div>
        </form>
    </div>

    <div class="fb-table-container">
        <div class="fb-table-header">
            <h4 class="fb-table-title">{{ __('Senarai Rekod Penebusan') }} ({{ $records->total() }})</h4>
            <span class="fb-table-count">{{ __('Menunjukkan :from-:to daripada :total rekod', ['from' => $records->firstItem() ?? 0, 'to' => $records->lastItem() ?? 0, 'total' => $records->total()]) }}</span>
        </div>

        @if($records->isEmpty())
            <div class="fb-empty">
                <p class="fb-empty-title">{{ __('Tiada rekod penebusan Food Bank dijumpai.') }}</p>
                <p class="fb-empty-copy">{{ __('Pelajar yang mengimbas QR di kaunter Food Bank akan dipaparkan secara automatik di sini.') }}</p>
            </div>
        @else
        <div class="fb-table-responsive">
            <table class="fb-table">
                <thead>
                    <tr>
                        <th class="fb-col-number">{{ __('Bil') }}</th>
                        <th>{{ __('Tarikh & Masa') }}</th>
                        <th>{{ __('Maklumat Pelajar') }}</th>
                        <th>{{ __('Program / Jabatan') }}</th>
                        <th>{{ __('Semester') }}</th>
                        <th>{{ __('No. Telefon') }}</th>
                        <th>{{ __('Lokasi') }}</th>
                        <th class="fb-col-action">{{ __('Tindakan') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($records as $index => $record)
                    <tr>
                        <td class="fb-muted-strong">{{ $records->firstItem() + $index }}</td>
                        <td>
                            <strong class="fb-date">{{ \Carbon\Carbon::parse($record->claimed_at)->format('d/m/Y') }}</strong>
                            <span class="fb-subtext">{{ \Carbon\Carbon::parse($record->claimed_at)->format('h:i A') }}</span>
                        </td>
                        <td>
                            <div class="fb-student-cell">
                                <span class="fb-student-name">{{ $record->student_name }}</span>
                                <span class="fb-student-meta">{{ $record->matric_no }} &middot; {{ $record->ic_no }}</span>
                            </div>
                        </td>
                        <td>
                            <span class="fb-badge">{{ $record->program ?: __('N/A') }}</span>
                        </td>
                        <td>
                            <span class="fb-badge fb-badge-muted">{{ __('Sem') }} {{ $record->semester ?: '-' }}</span>
                        </td>
                        <td>
                            <span class="fb-subtext">{{ $record->phone ?: '-' }}</span>
                        </td>
                        <td>
                            <span class="fb-subtext">{{ $record->location ?: __('Food Bank Siswa') }}</span>
                        </td>
                        <td class="fb-col-action">
                            <form method="POST" action="{{ route('admin.foodbank.destroy', $record->id) }}" onsubmit="return confirm('{{ __('Adakah anda pasti mahu memadam rekod penebusan ini?') }}');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="fb-delete-btn" title="{{ __('Padam Rekod') }}" aria-label="{{ __('Padam Rekod') }}">
                                    <svg class="fb-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($records->hasPages())
            <div class="fb-pagination">
                {{ $records->links() }}
            </div>
        @endif
        @endif
    </div>
</div>
@endsection
