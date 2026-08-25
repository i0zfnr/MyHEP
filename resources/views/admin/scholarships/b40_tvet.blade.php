@extends('layouts.app')

@section('title', 'SCHOLARSHIP B40 TVET')



@section('header')
    <h2 style="margin:0;font-size:1.1rem;font-weight:700;color:var(--se-text);">{{ __('SCHOLARSHIP B40 TVET') }}</h2>
@endsection

@section('content')
<div class="wrap b40-tvet-page">
    @if(session('success'))<div class="ok">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="err">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif

    <div class="grid grid-3">
        <div class="card stat"><span>{{ __('Total B40 TVET') }}</span><strong>{{ $stats['total'] }}</strong></div>
        <div class="card stat"><span>{{ __('Confirmed') }}</span><strong>{{ $stats['confirmed'] }}</strong></div>
        <div class="card stat"><span>{{ __('Pending') }}</span><strong>{{ $stats['pending'] }}</strong></div>
    </div>

    <div class="grid grid-main" style="margin-top:14px;">
        <div class="card">
            <div class="head">
                <h1>{{ __('Import Politeknik Besut') }}</h1>
            </div>
            <div class="body">
                <form method="POST" action="{{ route('admin.scholarships.b40-tvet.import') }}" enctype="multipart/form-data">
                    @csrf
                    <label for="student_file">{{ __('Excel / CSV file') }}</label>
                    <input id="student_file" type="file" name="student_file" accept=".csv,.txt,.xlsx" required>
                    <div class="hint">
                        {{ __('System akan baca semua row, cari row yang mengandungi Politeknik Besut, kemudian import student dan rekod SCHOLARSHIP B40 TVET secara automatik.
                        Header yang disokong termasuk Nama, No Matrik, No IC, Program, Institusi, Telefon, Email dan Jumlah.') }}
                    </div>
                    <div style="margin-top:12px;display:flex;gap:8px;flex-wrap:wrap;">
                        <button class="btn btn-primary" type="submit">{{ __('Import File') }}</button>
                        <a class="btn" href="{{ route('admin.scholarships.b40-tvet.export', request()->query()) }}" download>{{ __('Export CSV') }}</a>
                        <a class="btn" href="{{ route('admin.scholarships.index') }}">{{ __('Rekod Scholarship') }}</a>
                    </div>
                </form>

                @if(session('import_result'))
                    @php($result = session('import_result'))
                    <div class="summary">
                        <div><span>{{ __('Total rows') }}</span><strong>{{ $result['total_rows'] ?? 0 }}</strong></div>
                        <div><span>{{ __('Matched') }}</span><strong>{{ $result['matched_politeknik_besut'] ?? 0 }}</strong></div>
                        <div><span>{{ __('Students new') }}</span><strong>{{ $result['students_created'] ?? 0 }}</strong></div>
                        <div><span>{{ __('Students updated') }}</span><strong>{{ $result['students_updated'] ?? 0 }}</strong></div>
                        <div><span>{{ __('Scholarships new') }}</span><strong>{{ $result['scholarships_created'] ?? 0 }}</strong></div>
                        <div><span>{{ __('Scholarships updated') }}</span><strong>{{ $result['scholarships_updated'] ?? 0 }}</strong></div>
                    </div>
                    @if(!empty($result['errors']))
                        <ul class="error-list">
                            @foreach($result['errors'] as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    @endif
                @endif
            </div>
        </div>

        <div class="card">
            <div class="head">
                <h2>{{ __('Data SCHOLARSHIP B40 TVET') }}</h2>
                <a class="btn" href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a>
            </div>
            <div class="filters" data-filter-sheet data-filter-title="{{ __('B40 TVET filters') }}">
                <form method="GET" action="{{ route('admin.scholarships.b40-tvet') }}">
                    <div class="filter-grid">
                        <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="{{ __('Cari nama/matrik/IC/program') }}">
                        <select name="status">
                            <option value="">{{ __('Semua status') }}</option>
                            @foreach(['pending','confirmed','rejected'] as $status)
                                <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ __($status) }}</option>
                            @endforeach
                        </select>
                        <div style="display:flex;gap:8px;">
                            <button class="btn" type="submit">{{ __('Filter') }}</button>
                            <a class="btn" href="{{ route('admin.scholarships.b40-tvet') }}">{{ __('Reset') }}</a>
                        </div>
                    </div>
                </form>
            </div>
            <div style="overflow-x:auto;">
                <table>
                    <thead>
                        <tr>
                            <th>{{ __('Pelajar') }}</th>
                            <th>{{ __('Program') }}</th>
                            <th>{{ __('Jumlah') }}</th>
                            <th>{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $record)
                            <tr>
                                <td>
                                    {{ $record->student_name }}<br>
                                    <span style="color:#7b6757;">{{ $record->matric_no ?: '-' }} / {{ maskIdentityNumber($record->ic_no) }}</span>
                                </td>
                                <td>{{ $record->program }}</td>
                                <td>{{ $record->amount !== null ? 'RM ' . number_format((float) $record->amount, 2) : '-' }}</td>
                                <td><span class="pill {{ strtolower($record->status) }}">{{ __($record->status) }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" style="text-align:center;color:#7b6757;">{{ __('Tiada data SCHOLARSHIP B40 TVET.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="sch-pagination" style="margin-top:14px;">{{ $records->onEachSide(1)->links('vendor.pagination.myhep') }}</div>
</div>
@endsection
