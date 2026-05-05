@extends('layouts.app')

@section('title', __('Borang Status Biasiswa'))

@push('styles')
<style>
    .wrap { max-width: 860px; margin: 0 auto; }
    .card { background:#fff; border:1px solid #ede4d9; border-radius:12px; overflow:hidden; }
    .head { padding:12px 16px; border-bottom:1px solid #ede4d9; }
    .body { padding:16px; }
    .grid { display:grid; grid-template-columns:1fr; gap:12px; }
    @media (min-width: 900px) { .grid-2 { grid-template-columns:1fr 1fr; } }
    label { display:block; margin-bottom:6px; font-size:13px; font-weight:600; color:#7a6555; }
    input, select, textarea { width:100%; border:1px solid #e5d8c8; border-radius:8px; padding:9px 10px; font-size:14px; background:#fff; }
    input[readonly] { background:#faf7f4; color:#7a6555; }
    .actions { display:flex; gap:8px; margin-top:14px; flex-wrap:wrap; }
    .btn { display:inline-block; border:1px solid #cbb9a4; background:#fff; color:#8a7362; border-radius:8px; padding:9px 14px; text-decoration:none; font-weight:600; font-size:14px; cursor:pointer; }
    .btn-primary { background:linear-gradient(135deg,#A48D78,#CBB9A4); color:#fff; border:none; }
    .hint { margin:0 0 10px; color:#7a6555; font-size:13px; }
    .err { margin-bottom:12px; background:#fef2f2; border:1px solid #fecaca; color:#991b1b; border-radius:8px; padding:10px; font-size:13px; }
    /* Student UX Identity v2 */
    :root {
        --stu-ink: #1f1d1a;
        --stu-muted: #6f675f;
        --stu-line: #e9ded1;
        --stu-soft: #fbf7f1;
        --stu-accent: #7b5b43;
        --stu-accent-2: #b69172;
        --stu-glow: rgba(123, 91, 67, 0.18);
    }
    body {
        background:
            radial-gradient(1200px 520px at -8% -18%, #efe2d4 0%, transparent 56%),
            radial-gradient(900px 350px at 108% -14%, #f3e9de 0%, transparent 54%),
            linear-gradient(180deg, #faf7f2 0%, #f5efe7 100%);
    }
    .wrap,
    .student-dashboard,
    .dash-student,
    .sdash,
    .adash {
        width: min(1160px, 100%);
    }
    .card,
    .panel,
    .box,
    .stat,
    .summary,
    .tile {
        border: 1px solid var(--stu-line) !important;
        border-radius: 16px;
        background: linear-gradient(180deg, #fff 0%, #fffdfa 100%);
        box-shadow: 0 1px 2px rgba(31, 29, 26, 0.07), 0 10px 24px rgba(61, 46, 34, 0.06);
        transition: transform 220ms ease, box-shadow 220ms ease, border-color 220ms ease;
    }
    .card:hover,
    .panel:hover,
    .box:hover,
    .stat:hover,
    .summary:hover,
    .tile:hover {
        transform: translateY(-2px);
        border-color: #dcc7b0 !important;
        box-shadow: 0 5px 14px rgba(31, 29, 26, 0.11), 0 18px 30px rgba(61, 46, 34, 0.10);
    }
    .head,
    .card h2,
    .card h3,
    .section-head,
    .panel-head {
        position: relative;
        background: linear-gradient(180deg, #fff 0%, #fbf4ec 100%);
    }
    .head::before,
    .card h2::before,
    .card h3::before,
    .section-head::before,
    .panel-head::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 3px;
        background: linear-gradient(180deg, var(--stu-accent) 0%, var(--stu-accent-2) 100%);
    }
    .btn,
    .button,
    button[type='submit'],
    input[type='submit'] {
        border-radius: 10px;
        border: 1px solid #ceb79f;
        background: linear-gradient(180deg, #ffffff 0%, #f9f3ec 100%);
        color: #685141;
        font-weight: 700;
        transition: transform 170ms ease, box-shadow 170ms ease, border-color 170ms ease, color 170ms ease;
    }
    .btn:hover,
    .button:hover,
    button[type='submit']:hover,
    input[type='submit']:hover {
        transform: translateY(-1px);
        border-color: #ba9b7b;
        box-shadow: 0 8px 16px rgba(97, 73, 52, 0.13);
    }
    .btn-primary,
    .primary,
    .is-primary {
        border-color: #7f6249 !important;
        background: linear-gradient(135deg, #7b5b43 0%, #b69172 100%) !important;
        color: #fff !important;
    }
    input,
    select,
    textarea {
        border-color: #decdb8 !important;
        background: #fffdfb;
        color: var(--stu-ink);
        transition: border-color 150ms ease, box-shadow 150ms ease, background-color 150ms ease;
    }
    input:focus,
    select:focus,
    textarea:focus {
        border-color: #b89576 !important;
        box-shadow: 0 0 0 4px rgba(184, 149, 118, 0.18);
        outline: none;
        background: #fff;
    }
    .filters,
    .toolbar,
    .search-bar {
        background: linear-gradient(180deg, #fffdfb 0%, #faf3eb 100%);
        border-top: 1px solid #efe2d5;
        border-bottom: 1px solid #efe2d5;
    }
    table tbody tr {
        transition: background-color 140ms ease;
    }
    table tbody tr:hover {
        background: #fcf7f1;
    }
    .badge,
    .status,
    .pill {
        border-radius: 999px;
        font-weight: 700;
    }
    @media (max-width: 980px) {
        .head,
        .toolbar,
        .actions {
            align-items: flex-start;
        }
        .head > div,
        .head form,
        .toolbar > div,
        .toolbar form {
            width: 100%;
        }
        .stats,
        .summary-grid,
        .cards-grid {
            grid-template-columns: 1fr !important;
        }
        table th,
        table td {
            font-size: 12px !important;
            padding: 9px 10px !important;
        }
    }
</style>
@endpush

@section('header')
    <h2 style="margin:0;font-size:1.1rem;font-weight:700;color:#2d1f14;">{{ __('Borang Status Biasiswa') }}</h2>
@endsection

@section('content')
<div class="wrap">
    @if($errors->any())<div class="err">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif

    <form method="POST" action="{{ route('student.scholarship-status.submit') }}">
        @csrf
        <div class="card">
            <div class="head">
                <strong>{{ __('Maklumat Pengesahan Biasiswa Pelajar') }}</strong>
            </div>
            <div class="body">
                <p class="hint">{{ __('Sila lengkapkan borang ini untuk membantu pihak admin mengumpul data semua pelajar Politeknik Besut.') }}</p>

                <div class="grid grid-2">
                    <div>
                        <label>{{ __('Nama Penuh') }}</label>
                        <input type="text" value="{{ $student->full_name }}" readonly>
                    </div>
                    <div>
                        <label>{{ __('No. Matrik') }}</label>
                        <input type="text" value="{{ $student->matric_no }}" readonly>
                    </div>
                </div>

                <div class="grid grid-2" style="margin-top:12px;">
                    <div>
                        <label>{{ __('Program') }}</label>
                        <input type="text" value="{{ $student->program }}" readonly>
                    </div>
                    <div>
                        <label for="has_scholarship">{{ __('Adakah anda menerima biasiswa?') }}</label>
                        <select id="has_scholarship" name="has_scholarship" required>
                            @php $hasScholarship = old('has_scholarship', $submission->has_scholarship ?? ''); @endphp
                            <option value="">{{ __('Pilih') }}</option>
                            <option value="yes" {{ $hasScholarship === 'yes' ? 'selected' : '' }}>{{ __('Ya') }}</option>
                            <option value="no" {{ $hasScholarship === 'no' ? 'selected' : '' }}>{{ __('Tidak') }}</option>
                        </select>
                    </div>
                </div>

                <div id="scholarshipDetailFields">
                    <div class="grid grid-2" style="margin-top:12px;">
                        <div>
                            <label for="sponsor_name">{{ __('Nama Penaja Biasiswa') }}</label>
                            <input id="sponsor_name" type="text" name="sponsor_name" value="{{ old('sponsor_name', $submission->sponsor_name ?? '') }}" placeholder="{{ __('Contoh:') }} JPA / MARA / Zakat">
                        </div>
                        <div>
                            <label for="monthly_amount">{{ __('Jumlah Bulanan (RM)') }}</label>
                            <input id="monthly_amount" type="number" step="0.01" min="0" name="monthly_amount" value="{{ old('monthly_amount', $submission->monthly_amount ?? '') }}" placeholder="{{ __('Contoh:') }} 500.00">
                        </div>
                    </div>
                </div>

                <div style="margin-top:12px;">
                    <label for="notes">{{ __('Catatan Tambahan (Optional)') }}</label>
                    <textarea id="notes" name="notes" rows="4" placeholder="{{ __('Contoh:') }} {{ __('Biasiswa aktif sehingga tamat pengajian.') }}">{{ old('notes', $submission->notes ?? '') }}</textarea>
                </div>

                <div class="actions">
                    <button class="btn btn-primary" type="submit">{{ __('Hantar Borang') }}</button>
                    <a class="btn" href="{{ route('student.dashboard') }}">{{ __('Kembali ke Dashboard') }}</a>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var select = document.getElementById('has_scholarship');
    var details = document.getElementById('scholarshipDetailFields');
    var sponsor = document.getElementById('sponsor_name');
    var amount = document.getElementById('monthly_amount');
    if (!select || !details) return;

    function syncFields() {
        var isYes = select.value === 'yes';
        details.style.display = isYes ? '' : 'none';
        if (sponsor) sponsor.required = isYes;
        if (amount) amount.required = isYes;
    }

    select.addEventListener('change', syncFields);
    syncFields();
})();
</script>
@endpush

