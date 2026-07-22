@extends('layouts.app')

@section('title', 'SCHOLARSHIP B40 TVET')

@push('styles')
<style>
    .wrap { max-width: 1180px; margin: 0 auto; }
    .grid { display:grid; gap:14px; grid-template-columns:1fr; }
    @media (min-width: 980px) { .grid-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); } .grid-main { grid-template-columns: .95fr 1.35fr; } }
    .card { background:#fff; border:1px solid #eadfce; border-radius:12px; overflow:hidden; box-shadow:0 10px 26px rgba(61,46,34,.06); }
    .head { padding:14px 16px; border-bottom:1px solid #eadfce; display:flex; justify-content:space-between; gap:10px; align-items:center; background:linear-gradient(180deg,#fff,#fbf5ee); }
    .head h1, .head h2 { margin:0; color:#241a12; }
    .head h1 { font-size:20px; }
    .head h2 { font-size:16px; }
    .body { padding:16px; }
    .stat { padding:15px 16px; border-left:4px solid #8f6f52; }
    .stat span { display:block; font-size:12px; color:#7b6757; text-transform:uppercase; letter-spacing:.06em; font-weight:700; }
    .stat strong { display:block; margin-top:5px; font-size:28px; color:#241a12; }
    .btn { display:inline-flex; align-items:center; justify-content:center; border:1px solid #ceb79f; background:linear-gradient(180deg,#fff,#f9f3ec); color:#6e5745; border-radius:10px; padding:9px 13px; text-decoration:none; font-weight:700; font-size:13px; cursor:pointer; }
    .btn-primary { border-color:#7f6249; background:linear-gradient(135deg,#8f6f52,#c0a183); color:#fff; }
    .btn:hover { transform:translateY(-1px); box-shadow:0 8px 18px rgba(98,74,53,.14); }
    label { display:block; margin-bottom:6px; font-size:13px; font-weight:700; color:#7b6757; }
    input, select { width:100%; border:1px solid #dfceb9; border-radius:10px; padding:9px 10px; background:#fffdfb; color:#241a12; font-size:13px; }
    .hint { margin-top:8px; color:#7b6757; font-size:12px; line-height:1.55; }
    .ok { margin-bottom:12px; background:#e7f3f3; border:1px solid #b9ddde; color:#1f5559; border-radius:10px; padding:10px 12px; font-size:13px; }
    .err { margin-bottom:12px; background:#fef2f2; border:1px solid #fecaca; color:#991b1b; border-radius:10px; padding:10px 12px; font-size:13px; }
    .summary { display:grid; gap:8px; grid-template-columns:repeat(2, minmax(0, 1fr)); margin-top:12px; }
    .summary div { border:1px solid #eadfce; border-radius:10px; padding:10px; background:#fcfaf8; }
    .summary span { display:block; color:#7b6757; font-size:11px; text-transform:uppercase; font-weight:700; }
    .summary strong { display:block; margin-top:3px; color:#241a12; font-size:18px; }
    .filters { padding:12px 16px; border-bottom:1px solid #eadfce; background:#fcfaf8; }
    .filter-grid { display:grid; gap:8px; grid-template-columns:1fr; }
    @media (min-width: 820px) { .filter-grid { grid-template-columns:2fr 1fr auto; } }
    table { width:100%; border-collapse:collapse; }
    th, td { padding:10px 12px; border-bottom:1px solid #f0e7dc; font-size:13px; text-align:left; vertical-align:top; }
    th { font-size:11px; text-transform:uppercase; color:#7b6757; letter-spacing:.06em; background:#f9f1e8; }
    .pill { display:inline-block; border-radius:99px; padding:.2rem .6rem; font-size:11px; font-weight:800; text-transform:uppercase; border:1px solid #ede4d9; }
    .pending { background:#fff7ed; color:#b45309; border-color:#fed7aa; }
    .confirmed { background:#e7f3f3; color:#28686c; border-color:#b9ddde; }
    .rejected { background:#fef2f2; color:#b91c1c; border-color:#fecaca; }
    .error-list { margin:10px 0 0; padding-left:18px; color:#991b1b; font-size:12px; line-height:1.5; }
</style>
@endpush

@section('header')
    <h2 style="margin:0;font-size:1.1rem;font-weight:700;color:var(--se-text);">SCHOLARSHIP B40 TVET</h2>
@endsection

@section('content')
<div class="wrap b40-tvet-page">
    @if(session('success'))<div class="ok">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="err">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif

    <div class="grid grid-3">
        <div class="card stat"><span>Total B40 TVET</span><strong>{{ $stats['total'] }}</strong></div>
        <div class="card stat"><span>Confirmed</span><strong>{{ $stats['confirmed'] }}</strong></div>
        <div class="card stat"><span>Pending</span><strong>{{ $stats['pending'] }}</strong></div>
    </div>

    <div class="grid grid-main" style="margin-top:14px;">
        <div class="card">
            <div class="head">
                <h1>Import Politeknik Besut</h1>
            </div>
            <div class="body">
                <form method="POST" action="{{ route('admin.scholarships.b40-tvet.import') }}" enctype="multipart/form-data">
                    @csrf
                    <label for="student_file">Excel / CSV file</label>
                    <input id="student_file" type="file" name="student_file" accept=".csv,.txt,.xlsx" required>
                    <div class="hint">
                        System akan baca semua row, cari row yang mengandungi Politeknik Besut, kemudian import student dan rekod SCHOLARSHIP B40 TVET secara automatik.
                        Header yang disokong termasuk Nama, No Matrik, No IC, Program, Institusi, Telefon, Email dan Jumlah.
                    </div>
                    <div style="margin-top:12px;display:flex;gap:8px;flex-wrap:wrap;">
                        <button class="btn btn-primary" type="submit">Import File</button>
                        <a class="btn" href="{{ route('admin.scholarships.b40-tvet.export', request()->query()) }}" download>Export CSV</a>
                        <a class="btn" href="{{ route('admin.scholarships.index') }}">Rekod Scholarship</a>
                    </div>
                </form>

                @if(session('import_result'))
                    @php($result = session('import_result'))
                    <div class="summary">
                        <div><span>Total rows</span><strong>{{ $result['total_rows'] ?? 0 }}</strong></div>
                        <div><span>Matched</span><strong>{{ $result['matched_politeknik_besut'] ?? 0 }}</strong></div>
                        <div><span>Students new</span><strong>{{ $result['students_created'] ?? 0 }}</strong></div>
                        <div><span>Students updated</span><strong>{{ $result['students_updated'] ?? 0 }}</strong></div>
                        <div><span>Scholarships new</span><strong>{{ $result['scholarships_created'] ?? 0 }}</strong></div>
                        <div><span>Scholarships updated</span><strong>{{ $result['scholarships_updated'] ?? 0 }}</strong></div>
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
                <h2>Data SCHOLARSHIP B40 TVET</h2>
                <a class="btn" href="{{ route('admin.dashboard') }}">Dashboard</a>
            </div>
            <div class="filters" data-filter-sheet data-filter-title="{{ __('B40 TVET filters') }}">
                <form method="GET" action="{{ route('admin.scholarships.b40-tvet') }}">
                    <div class="filter-grid">
                        <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Cari nama/matrik/IC/program">
                        <select name="status">
                            <option value="">Semua status</option>
                            @foreach(['pending','confirmed','rejected'] as $status)
                                <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ __($status) }}</option>
                            @endforeach
                        </select>
                        <div style="display:flex;gap:8px;">
                            <button class="btn" type="submit">Filter</button>
                            <a class="btn" href="{{ route('admin.scholarships.b40-tvet') }}">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
            <div style="overflow-x:auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Pelajar</th>
                            <th>Program</th>
                            <th>Jumlah</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $record)
                            <tr>
                                <td>
                                    {{ $record->student_name }}<br>
                                    <span style="color:#7b6757;">{{ $record->matric_no ?: '-' }} / {{ $record->ic_no }}</span>
                                </td>
                                <td>{{ $record->program }}</td>
                                <td>{{ $record->amount !== null ? 'RM ' . number_format((float) $record->amount, 2) : '-' }}</td>
                                <td><span class="pill {{ strtolower($record->status) }}">{{ __($record->status) }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" style="text-align:center;color:#7b6757;">Tiada data SCHOLARSHIP B40 TVET.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div style="margin-top:14px;">{{ $records->links() }}</div>
</div>
@endsection
