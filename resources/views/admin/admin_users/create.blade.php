@extends('layouts.app')

@section('title', __('Tambah Admin'))

@push('styles')
<style>
    .wrap { max-width: 900px; margin: 0 auto; }
    .card { background:#fff; border:1px solid #ede4d9; border-radius:12px; overflow:hidden; }
    .card h2 { margin:0; padding:14px 16px; border-bottom:1px solid #ede4d9; font-size:16px; }
    .body { padding:16px; }
    .grid { display:grid; grid-template-columns:1fr; gap:12px; }
    @media (min-width:900px) { .grid-2 { grid-template-columns:1fr 1fr; } }
    label { font-size:13px; font-weight:600; color:#7a6555; display:block; margin-bottom:6px; }
    input, select { width:100%; border:1px solid #e5d8c8; border-radius:8px; padding:9px 10px; font-size:14px; }
    .actions { display:flex; gap:10px; flex-wrap:wrap; margin-top:14px; }
    .btn { display:inline-block; border:1px solid #cbb9a4; background:#fff; color:#8a7362; border-radius:8px; padding:9px 14px; text-decoration:none; font-weight:600; font-size:14px; cursor:pointer; }
    .btn-primary { background:linear-gradient(135deg,#A48D78,#CBB9A4); color:#fff; border:none; }
    .err { margin-bottom:12px; background:#fef2f2; border:1px solid #fecaca; color:#991b1b; border-radius:8px; padding:10px; font-size:13px; }
        /* Admin UX Identity v2 */
    :root {
        --admin-ink: #241a12;
        --admin-muted: #7b6757;
        --admin-line: #eadfce;
        --admin-soft: #f8f2ea;
        --admin-accent: #8f6f52;
        --admin-accent-2: #c7a98b;
        --admin-glow: rgba(143, 111, 82, 0.18);
    }
    body {
        background:
            radial-gradient(1200px 480px at -10% -15%, #efe3d6 0%, transparent 55%),
            radial-gradient(900px 360px at 110% -10%, #f4eadf 0%, transparent 52%),
            linear-gradient(180deg, #faf7f2 0%, #f6f1ea 100%);
    }
    .wrap {
        width: min(1180px, 100%);
        position: relative;
        isolation: isolate;
    }
    .wrap > * + * {
        margin-top: 1rem;
    }
    .card,
    .panel {
        border: 1px solid var(--admin-line);
        border-radius: 16px;
        background: linear-gradient(180deg, #fff 0%, #fffdfa 100%);
        box-shadow:
            0 1px 2px rgba(36, 26, 18, 0.07),
            0 10px 26px rgba(61, 46, 34, 0.06);
        overflow: hidden;
        transition: transform 220ms ease, box-shadow 220ms ease, border-color 220ms ease;
    }
    .card:hover,
    .panel:hover {
        transform: translateY(-2px);
        border-color: #dfccb6;
        box-shadow:
            0 4px 14px rgba(36, 26, 18, 0.10),
            0 18px 34px rgba(61, 46, 34, 0.10);
    }
    .head,
    .card h2 {
        position: relative;
        border-bottom: 1px solid var(--admin-line);
        background:
            linear-gradient(180deg, #fff 0%, #fbf5ee 100%);
    }
    .head::before,
    .card h2::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: linear-gradient(180deg, var(--admin-accent) 0%, var(--admin-accent-2) 100%);
    }
    .head h1,
    .card h2 {
        color: var(--admin-ink);
        letter-spacing: 0.01em;
    }
    .btn {
        border-radius: 10px;
        border: 1px solid #ceb79f;
        background: linear-gradient(180deg, #ffffff 0%, #f9f3ec 100%);
        color: #6e5745;
        font-weight: 700;
        transition: transform 170ms ease, box-shadow 170ms ease, background-color 170ms ease, border-color 170ms ease, color 170ms ease;
    }
    .btn:hover {
        transform: translateY(-1px);
        border-color: #bb9c7d;
        color: #5d4737;
        box-shadow: 0 8px 18px rgba(98, 74, 53, 0.14);
    }
    .btn:focus-visible {
        outline: none;
        box-shadow: 0 0 0 4px var(--admin-glow);
    }
    .btn-primary {
        border-color: #7f6249 !important;
        background: linear-gradient(135deg, #8f6f52 0%, #c0a183 100%) !important;
        color: #fff !important;
    }
    .btn-primary:hover {
        border-color: #6f533e !important;
        background: linear-gradient(135deg, #7a5e46 0%, #b08f70 100%) !important;
    }
    input,
    select,
    textarea {
        border-color: #dfceb9 !important;
        background: #fffdfb;
        color: var(--admin-ink);
        transition: border-color 150ms ease, box-shadow 150ms ease, background-color 150ms ease;
    }
    input::placeholder,
    textarea::placeholder {
        color: #9e8a78;
    }
    input:focus,
    select:focus,
    textarea:focus {
        border-color: #b69372 !important;
        box-shadow: 0 0 0 4px rgba(182, 147, 114, 0.19);
        outline: none;
        background: #fff;
    }
    .filters {
        background: linear-gradient(180deg, #fffdfb 0%, #faf4ed 100%);
        border-top: 1px solid #efe4d8;
        border-bottom: 1px solid #efe4d8;
    }
    table {
        width: 100%;
    }
    th {
        background: #f9f1e8 !important;
        color: #7b6757 !important;
        letter-spacing: 0.06em;
    }
    table tbody tr {
        transition: background-color 140ms ease;
    }
    table tbody tr:hover {
        background: #fcf7f1;
    }
    .ok,
    .msg-ok {
        border-radius: 12px;
        border-color: #b8e5c7 !important;
    }
    .err,
    .error,
    .msg-err {
        border-radius: 12px;
    }
    @media (max-width: 980px) {
        .head {
            align-items: flex-start;
        }
        .head > div,
        .head form {
            width: 100%;
        }
        .head .btn {
            width: auto;
        }
        .stats {
            grid-template-columns: 1fr !important;
        }
        th,
        td {
            font-size: 12px !important;
            padding: 9px 10px !important;
        }
    }</style>
@endpush

@section('header')
    <h2 style="margin:0;font-size:1.1rem;font-weight:700;color:#2d1f14;">{{ __('Tambah Admin') }}</h2>
@endsection

@section('content')
<div class="wrap">
    @if($errors->any())<div class="err">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
    @php($roleOptions = ['guard', 'scholarship_admin', 'discipline_admin', 'system_admin'])

    <form method="POST" action="{{ route('admin.admin-users.store') }}">
        @csrf
        <div class="card">
            <h2>{{ __('Maklumat Admin') }}</h2>
            <div class="body">
                <div class="grid grid-2">
                    <div>
                        <label for="full_name">{{ __('Nama Penuh') }}</label>
                        <input id="full_name" type="text" name="full_name" value="{{ old('full_name') }}" required>
                    </div>
                    <div>
                        <label for="ic_no">{{ __('No. IC') }}</label>
                        <input id="ic_no" type="text" name="ic_no" value="{{ old('ic_no') }}" required>
                    </div>
                </div>

                <div class="grid grid-2" style="margin-top:12px;">
                    <div>
                        <label for="email">Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}">
                    </div>
                    <div>
                        <label for="role">{{ __('Role') }}</label>
                        <select id="role" name="role" required>
                            @foreach($roleOptions as $role)
                                <option value="{{ $role }}" {{ old('role') === $role ? 'selected' : '' }}>{{ str_replace('_', ' ', ucfirst($role)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="password">{{ __('Kata Laluan') }}</label>
                        <input id="password" type="password" name="password" minlength="8" required>
                    </div>
                </div>
            </div>
        </div>

        <div class="actions">
            <button class="btn btn-primary" type="submit">{{ __('Simpan Admin') }}</button>
            <a class="btn" href="{{ route('admin.admin-users.index') }}">{{ __('Batal') }}</a>
        </div>
    </form>
</div>
@endsection


