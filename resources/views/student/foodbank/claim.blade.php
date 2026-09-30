<!doctype html>
<html lang="ms">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#173b34">
    <title>{{ __('Borang Penerimaan Food Bank') }} · MyHEP</title>
    <style>
        *{box-sizing:border-box}body{margin:0;background:#f4f6f2;color:#17312c;font-family:system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;line-height:1.5}main{width:min(100% - 32px,520px);margin:0 auto;padding:32px 0 calc(40px + env(safe-area-inset-bottom))}.brand{display:flex;align-items:center;gap:12px;margin-bottom:28px}.brand img{width:46px;height:46px;object-fit:contain}.brand strong{display:block;font-size:1rem}.brand span{display:block;font-size:.78rem;color:#60726b}.card{background:#fff;border:1px solid #dce7df;border-radius:22px;box-shadow:0 18px 42px rgba(22,60,48,.09);overflow:hidden}.card::before{content:"";display:block;height:6px;background:#bc9b50}.content{padding:28px}.eyebrow{font-size:.72rem;font-weight:800;letter-spacing:.12em;text-transform:uppercase;color:#7f6934}h1{font-size:clamp(1.5rem,5vw,2rem);line-height:1.15;margin:10px 0}p{margin:0 0 22px;color:#60726b;font-size:.92rem}.field{margin-bottom:18px}label,legend{display:block;margin-bottom:7px;font-size:.86rem;font-weight:700}input[type=text],input[type=number]{display:block;width:100%;min-height:48px;border:1px solid #c9d8cd;border-radius:11px;padding:10px 13px;font:inherit;color:#17312c;background:#fff}input:focus{outline:3px solid #cce7d5;border-color:#248658}.hint{display:block;margin-top:5px;font-size:.76rem;color:#6d7b72}.error{color:#a12424;font-size:.8rem;margin-top:5px}.field.has-error input{border-color:#bd4444}fieldset{border:0;padding:0;margin:0 0 22px}.choices{display:grid;grid-template-columns:1fr 1fr;gap:10px}.choice{margin:0;display:flex;align-items:center;gap:9px;border:1px solid #c9d8cd;border-radius:11px;padding:12px;cursor:pointer}.choice:has(input:checked){background:#edf7ef;border-color:#248658}.choice input{width:18px;height:18px;accent-color:#21734e}.notice{font-size:.78rem;color:#5c7065;background:#f2f7f2;border-radius:10px;padding:11px 13px;margin-bottom:20px}button{width:100%;min-height:50px;border:0;border-radius:11px;background:#1d6e4d;color:#fff;font:inherit;font-weight:800;cursor:pointer}button:hover{background:#155b3e}.receipt{border:1px solid #dce7df;border-radius:12px;overflow:hidden;margin:20px 0}.receipt div{display:flex;justify-content:space-between;gap:12px;padding:11px 14px;border-bottom:1px solid #e6ece7;font-size:.86rem}.receipt div:last-child{border-bottom:0}.receipt span{color:#60726b}.receipt strong{text-align:right;overflow-wrap:anywhere}.success-mark{display:grid;place-items:center;width:54px;height:54px;border-radius:50%;background:#e2f4e7;color:#167548;font-size:1.8rem;font-weight:800;margin-bottom:14px}.again{display:block;text-align:center;margin-top:18px;color:#1d6e4d;font-weight:700;text-decoration:none}@media(max-width:430px){main{width:min(100% - 24px,520px);padding-top:18px}.content{padding:22px}.receipt div{display:block}.receipt strong{display:block;text-align:left}}
    </style>
</head>
<body>
<main>
    <div class="brand"><img src="{{ asset('images/myhep-mark.png') }}" alt=""><div><strong>MyHEP · Food Bank Siswa</strong><span>Politeknik Besut Terengganu</span></div></div>
    <section class="card"><div class="content">
        @if(session('foodbank_receipt'))
            @php($receipt = session('foodbank_receipt'))
            <div class="success-mark" aria-hidden="true">✓</div>
            <div class="eyebrow">{{ __('Rekod berjaya disimpan') }}</div>
            <h1>{{ __('Terima kasih!') }}</h1>
            <p>{{ __('Maklumat penerimaan Food Bank anda telah direkodkan. Sila tunjukkan halaman ini kepada petugas di kaunter.') }}</p>
            <div class="receipt">
                <div><span>{{ __('Nama Pelajar') }}</span><strong>{{ $receipt['student_name'] }}</strong></div>
                <div><span>{{ __('No. Matrik') }}</span><strong>{{ $receipt['matric_no'] }}</strong></div>
                <div><span>{{ __('Jumlah Item') }}</span><strong>{{ $receipt['item_count'] }}</strong></div>
                <div><span>{{ __('Pelajar B40') }}</span><strong>{{ $receipt['is_b40'] ? __('Ya') : __('Tidak') }}</strong></div>
                <div><span>{{ __('Tarikh & Masa') }}</span><strong>{{ $receipt['claimed_at'] }}</strong></div>
            </div>
            <a class="again" href="{{ route('student.foodbank.claim') }}">{{ __('Isi borang baharu') }}</a>
        @else
            <div class="eyebrow">{{ __('Borang Penerimaan Food Bank') }}</div>
            <h1>{{ __('Rekod penerimaan makanan') }}</h1>
            <p>{{ __('Isi maklumat di bawah selepas mengimbas QR Food Bank. Tarikh dan masa direkodkan secara automatik apabila anda hantar borang.') }} {{ __('Tiada log masuk diperlukan.') }}</p>
            <form method="POST" action="{{ route('student.foodbank.store') }}">
                @csrf
                <div class="field @error('student_name') has-error @enderror">
                    <label for="student_name">{{ __('Nama Pelajar') }}</label>
                    <input id="student_name" name="student_name" type="text" maxlength="255" autocomplete="name" value="{{ old('student_name') }}" required>
                    @error('student_name')<div class="error">{{ $message }}</div>@enderror
                </div>
                <div class="field @error('matric_no') has-error @enderror">
                    <label for="matric_no">{{ __('No. Matrik / No. Pendaftaran') }}</label>
                    <input id="matric_no" name="matric_no" type="text" maxlength="50" autocapitalize="characters" value="{{ old('matric_no') }}" required>
                    @error('matric_no')<div class="error">{{ $message }}</div>@enderror
                </div>
                <div class="field @error('item_count') has-error @enderror">
                    <label for="item_count">{{ __('Jumlah Item') }}</label>
                    <input id="item_count" name="item_count" type="number" inputmode="numeric" min="1" max="999" step="1" value="{{ old('item_count') }}" required>
                    <span class="hint">{{ __('Masukkan bilangan barang makanan yang diterima.') }}</span>
                    @error('item_count')<div class="error">{{ $message }}</div>@enderror
                </div>
                <fieldset>
                    <legend>{{ __('Adakah anda pelajar B40?') }}</legend>
                    <div class="choices">
                        <label class="choice"><input type="radio" name="is_b40" value="1" @checked(old('is_b40') === '1') required> {{ __('Ya') }}</label>
                        <label class="choice"><input type="radio" name="is_b40" value="0" @checked(old('is_b40') === '0') required> {{ __('Tidak') }}</label>
                    </div>
                    @error('is_b40')<div class="error">{{ $message }}</div>@enderror
                </fieldset>
                <div class="notice">{{ __('Tarikh dan masa akan direkodkan oleh sistem semasa anda menghantar borang.') }}</div>
                <button type="submit">{{ __('Hantar Rekod Penerimaan') }}</button>
            </form>
        @endif
    </div></section>
</main>
</body>
</html>
