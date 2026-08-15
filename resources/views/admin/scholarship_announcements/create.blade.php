@extends('layouts.app')

@section('title', 'Tambah Pengumuman Scholarship')

@push('styles')
<style>
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
        width: min(900px, 100%);
        margin: 0 auto;
        position: relative;
        isolation: isolate;
    }
    .wrap > * + * { margin-top: 1rem; }
    .card {
        border: 1px solid var(--admin-line);
        border-radius: 16px;
        background: linear-gradient(180deg, #fff 0%, #fffdfa 100%);
        box-shadow: 0 1px 2px rgba(36,26,18,.07), 0 10px 26px rgba(61,46,34,.06);
        overflow: hidden;
        transition: transform 220ms ease, box-shadow 220ms ease, border-color 220ms ease;
    }
    .card:hover {
        transform: translateY(-2px);
        border-color: #dfccb6;
        box-shadow: 0 4px 14px rgba(36,26,18,.10), 0 18px 34px rgba(61,46,34,.10);
    }
    .card h2 {
        position: relative;
        margin: 0;
        padding: 14px 16px;
        border-bottom: 1px solid var(--admin-line);
        background: linear-gradient(180deg, #fff 0%, #fbf5ee 100%);
        color: var(--admin-ink);
        font-size: 15px;
        letter-spacing: .01em;
    }
    .card h2::before {
        content: '';
        position: absolute;
        left: 0; top: 0; bottom: 0; width: 4px;
        background: linear-gradient(180deg, var(--admin-accent) 0%, var(--admin-accent-2) 100%);
    }
    .card .body { padding: 16px; }
    label { font-size: 13px; font-weight: 600; color: #7a6555; display: block; margin-bottom: 6px; }
    input, textarea, select {
        width: 100%;
        border: 1px solid #dfceb9;
        border-radius: 8px;
        padding: 9px 10px;
        font-size: 14px;
        background: #fffdfb;
        color: var(--admin-ink);
        transition: border-color 150ms ease, box-shadow 150ms ease, background-color 150ms ease;
        box-sizing: border-box;
    }
    input::placeholder, textarea::placeholder { color: #9e8a78; }
    input:focus, select:focus, textarea:focus {
        border-color: #b69372 !important;
        box-shadow: 0 0 0 4px rgba(182,147,114,.19);
        outline: none;
        background: #fff;
    }
    .grid { display: grid; grid-template-columns: 1fr; gap: 12px; }
    @media (min-width: 600px) { .grid-2 { grid-template-columns: 1fr 1fr; } }
    .actions { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 14px; }
    .btn {
        display: inline-block;
        border-radius: 10px;
        border: 1px solid #ceb79f;
        background: linear-gradient(180deg, #ffffff 0%, #f9f3ec 100%);
        color: #6e5745;
        font-weight: 700;
        font-size: 14px;
        padding: 9px 14px;
        text-decoration: none;
        cursor: pointer;
        transition: transform 170ms ease, box-shadow 170ms ease, border-color 170ms ease, color 170ms ease;
    }
    .btn:hover {
        transform: translateY(-1px);
        border-color: #bb9c7d;
        color: #5d4737;
        box-shadow: 0 8px 18px rgba(98,74,53,.14);
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
    .error {
        margin-bottom: 12px;
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #991b1b;
        border-radius: 10px;
        padding: 10px 14px;
        font-size: 13px;
    }
    .section-divider { height: 1px; background: var(--admin-line); margin: 1.25rem 0; }
    .field-hint { font-size: 11.5px; color: #9a8476; margin-top: 4px; }

    /* Poster upload zone */
    .poster-zone {
        border: 2px dashed #dfceb9;
        border-radius: 12px;
        background: #fffdfb;
        padding: 2rem 1.5rem;
        text-align: center;
        cursor: pointer;
        transition: border-color 200ms, background 200ms;
        position: relative;
    }
    .poster-zone:hover { border-color: #b69372; background: #fff9f4; }
    .poster-zone input[type="file"] {
        position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%;
    }
    .poster-zone__icon { font-size: 2.2rem; margin-bottom: 0.4rem; }
    .poster-zone__text { font-size: 13px; color: #6e5745; font-weight: 600; }
    .poster-zone__hint { font-size: 11.5px; color: #b49a87; margin-top: 4px; }
    #posterPreview {
        display: none;
        margin-top: 1rem;
        position: relative;
        border-radius: 10px;
        overflow: hidden;
        border: 1px solid #dfceb9;
        max-height: 280px;
    }
    #posterPreview img { width: 100%; max-height: 280px; object-fit: contain; display: block; background: #f8f2ea; }
    #posterRemoveBtn {
        position: absolute; top: 8px; right: 8px;
        background: rgba(36,26,18,.72); color: #fff; border: none; border-radius: 6px;
        padding: 5px 12px; font-size: 12px; cursor: pointer; font-weight: 700;
        transition: background 150ms;
    }
    #posterRemoveBtn:hover { background: rgba(36,26,18,.92); }
</style>
@endpush

@section('header')
    <h2 style="margin:0;font-size:1.1rem;font-weight:700;color:#2d1f14;">{{ __('Tambah Pengumuman Scholarship') }}</h2>
@endsection

@section('content')
<div class="wrap">
    @if($errors->any())
        <div class="error">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
    @endif

    <form method="POST" action="{{ route('admin.scholarship-announcements.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="card">
            <h2>{{ __('Maklumat Pengumuman') }}</h2>
            <div class="body">

                <!-- Title & Type -->
                <div class="grid grid-2">
                    <div>
                        <label for="title">{{ __('Tajuk') }}</label>
                        <input id="title" type="text" name="title" value="{{ old('title') }}" required>
                    </div>
                    <div>
                        <label for="type">{{ __('Jenis') }}</label>
                        <select id="type" name="type" required>
                            @foreach(['scholarship','welfare','general'] as $t)
                                <option value="{{ $t }}" {{ old('type','general') === $t ? 'selected' : '' }}>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Body -->
                <div style="margin-top:12px;">
                    <label for="body">{{ __('Penerangan') }}</label>
                    <textarea id="body" name="body" rows="7" required>{{ old('body') }}</textarea>
                </div>

                <div class="section-divider"></div>

                <!-- Poster Image -->
                <div>
                    <label>{{ __('Gambar Poster') }} <span style="font-weight:400;color:#9a8476;">({{ __('optional') }})</span></label>
                    <div class="poster-zone" id="posterZone">
                        <input type="file" id="posterImageInput" name="poster_image" accept="image/jpeg,image/png,image/webp">
                        <div class="poster-zone__icon">🖼️</div>
                        <div class="poster-zone__text">{{ __('Klik atau seret gambar poster di sini') }}</div>
                        <div class="poster-zone__hint">JPG, PNG atau WEBP &middot; Maks 4MB</div>
                    </div>
                    <div id="posterPreview">
                        <img id="posterPreviewImg" src="" alt="Poster preview">
                        <button type="button" id="posterRemoveBtn">&#10005; {{ __('Buang') }}</button>
                    </div>
                </div>

                <div class="section-divider"></div>

                <!-- Contact Info -->
                <div class="grid grid-2">
                    <div>
                        <label for="contact_email">{{ __('E-mel Hubungi') }} <span style="font-weight:400;color:#9a8476;">({{ __('optional') }})</span></label>
                        <input id="contact_email" type="email" name="contact_email" value="{{ old('contact_email') }}" placeholder="contoh@edu.my">
                        <p class="field-hint">{{ __('Pelajar boleh menghubungi melalui e-mel ini.') }}</p>
                    </div>
                    <div>
                        <label for="contact_phone">{{ __('No. Telefon Hubungi') }} <span style="font-weight:400;color:#9a8476;">({{ __('optional') }})</span></label>
                        <input id="contact_phone" type="tel" name="contact_phone" value="{{ old('contact_phone') }}" placeholder="+60123456789">
                        <p class="field-hint">{{ __('Nombor telefon untuk dihubungi berkaitan pengumuman ini.') }}</p>
                    </div>
                </div>

                <div class="section-divider"></div>

                <!-- Link -->
                <div class="grid grid-2">
                    <div>
                        <label for="link_url">Link URL <span style="font-weight:400;color:#9a8476;">(optional)</span></label>
                        <input id="link_url" type="url" name="link_url" value="{{ old('link_url') }}" placeholder="https://example.com">
                    </div>
                    <div>
                        <label for="link_label">Label Butang Link <span style="font-weight:400;color:#9a8476;">(optional)</span></label>
                        <input id="link_label" type="text" name="link_label" value="{{ old('link_label') }}" placeholder="{{ __('Contoh: Mohon Sekarang') }}">
                    </div>
                </div>

            </div>
        </div>

        <div class="actions">
            <button class="btn btn-primary" type="submit">{{ __('Simpan Pengumuman') }}</button>
            <a class="btn" href="{{ route('admin.scholarship-announcements.index') }}">{{ __('Batal') }}</a>
        </div>
    </form>
</div>

<script>
(function () {
    const input    = document.getElementById('posterImageInput');
    const preview  = document.getElementById('posterPreview');
    const img      = document.getElementById('posterPreviewImg');
    const removeBtn= document.getElementById('posterRemoveBtn');
    const zone     = document.getElementById('posterZone');

    input.addEventListener('change', function () {
        if (!this.files || !this.files[0]) return;
        const reader = new FileReader();
        reader.onload = (e) => {
            img.src = e.target.result;
            preview.style.display = 'block';
            zone.style.display = 'none';
        };
        reader.readAsDataURL(this.files[0]);
    });

    removeBtn.addEventListener('click', function () {
        input.value = '';
        img.src = '';
        preview.style.display = 'none';
        zone.style.display = 'block';
    });
})();
</script>
@endsection
