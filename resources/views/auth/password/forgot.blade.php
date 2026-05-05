<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lupa Kata Laluan</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logohep.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logohep.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background:#fdf8f3; margin:0; min-height:100vh; display:flex; align-items:center; justify-content:center; padding:1rem; }
        .card { width:100%; max-width:460px; background:#fff; border:1px solid #eadfd2; border-radius:14px; padding:18px; }
        h1 { margin:0 0 6px; font-size:22px; }
        p { margin:0 0 14px; color:#6b7280; font-size:14px; }
        label { display:block; font-size:13px; font-weight:600; color:#6b7280; margin-bottom:6px; }
        input, select { width:100%; border:1px solid #e5d8c8; border-radius:8px; padding:9px 10px; font-size:14px; margin-bottom:10px; }
        .actions { display:flex; gap:8px; margin-top:8px; }
        .btn { display:inline-block; border:1px solid #cbb9a4; background:#fff; color:#8a7362; border-radius:8px; padding:9px 12px; text-decoration:none; font-size:13px; font-weight:600; cursor:pointer; }
        .btn-primary { background:linear-gradient(135deg,#A48D78,#CBB9A4); color:#fff; border:none; }
        .ok { margin-bottom:10px; background:#f0fdf4; border:1px solid #bbf7d0; color:#166534; border-radius:8px; padding:9px; font-size:13px; }
        .warn { margin-bottom:10px; background:#fff7ed; border:1px solid #fed7aa; color:#9a3412; border-radius:8px; padding:9px; font-size:13px; }
        .err { margin-bottom:10px; background:#fef2f2; border:1px solid #fecaca; color:#991b1b; border-radius:8px; padding:9px; font-size:13px; }
        .app-footer {
            position: fixed;
            left: 0;
            right: 0;
            bottom: .75rem;
            text-align: center;
            font-size: .76rem;
            color: #7a6555;
            pointer-events: none;
        }
    </style>
</head>
<body>
<div class="card">
    <h1>Lupa Kata Laluan</h1>
    <p>Masukkan maklumat akaun dan email berdaftar untuk menerima kod verifikasi.</p>

    @if(session('success'))<div class="ok">{{ session('success') }}</div>@endif
    @if(session('delivery_info'))<div class="warn">{{ session('delivery_info') }}</div>@endif
    @if($errors->any())<div class="err">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif

    <form method="POST" action="{{ route('password.forgot.send') }}">
        @csrf
        <label for="role">Peranan</label>
        <select id="role" name="role" required>
            <option value="student" {{ old('role', 'student') === 'student' ? 'selected' : '' }}>Pelajar</option>
            <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
        </select>

        <label for="identifier">ID Akaun (No. Matrik pelajar / No. IC admin)</label>
        <input id="identifier" name="identifier" type="text" value="{{ old('identifier') }}" required>

        <label for="email">Email berdaftar</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" required>

        <div class="actions">
            <button class="btn btn-primary" type="submit">Hantar Kod Verifikasi</button>
            <a class="btn" href="{{ route('login') }}">Kembali Login</a>
        </div>
    </form>
</div>
@include('partials.app_footer')
</body>
</html>

