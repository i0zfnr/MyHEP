@extends('layouts.app')

@section('title', __('Admin Profile'))

@push('styles')
<style>
    .admin-profile-shell { width:min(820px, 100%); margin:0 auto; display:grid; gap:1rem; }
    .admin-profile-card { overflow:hidden; border:1px solid var(--se-border); border-radius:14px; background:var(--se-surface); box-shadow:var(--se-shadow-md); }
    .admin-profile-head { padding:1rem 1.15rem; border-bottom:1px solid var(--se-border); background:var(--se-surface-soft); }
    .admin-profile-head h3 { margin:0; font-size:1rem; color:var(--se-text); }
    .admin-profile-body { padding:1.15rem; }
    .admin-profile-photo-row { display:flex; align-items:center; gap:1.25rem; }
    .admin-profile-photo { width:128px; height:128px; flex:0 0 128px; border-radius:50%; border:1px solid var(--se-border-strong); object-fit:cover; background:var(--se-surface-muted); }
    .admin-profile-photo-fallback { display:flex; align-items:center; justify-content:center; color:var(--se-primary-strong); font-size:2rem; font-weight:800; }
    .admin-profile-upload { min-width:0; flex:1; }
    .admin-profile-upload label { display:block; margin-bottom:.45rem; color:var(--se-text-soft); font-size:.82rem; font-weight:700; }
    .admin-profile-upload input { width:100%; min-height:44px; padding:.55rem; border:1px solid var(--se-border); border-radius:9px; background:var(--se-surface); color:var(--se-text); }
    .admin-profile-upload small { display:block; margin-top:.45rem; color:var(--se-text-muted); }
    .admin-profile-grid { display:grid; grid-template-columns:repeat(3, minmax(0, 1fr)); gap:.8rem; }
    .admin-profile-field { padding:.85rem; border:1px solid var(--se-border); border-radius:10px; background:var(--se-surface-soft); }
    .admin-profile-field span { display:block; margin-bottom:.3rem; color:var(--se-text-muted); font-size:.68rem; font-weight:800; letter-spacing:.06em; text-transform:uppercase; }
    .admin-profile-field strong { display:block; color:var(--se-text); font-size:.9rem; overflow-wrap:anywhere; }
    .admin-profile-actions { display:flex; gap:.65rem; flex-wrap:wrap; margin-top:1rem; }
    .admin-profile-actions .btn { min-height:44px; }
    body.profile-crop-open { overflow:hidden !important; }
    .profile-crop-modal { position:fixed; inset:0; z-index:1200; display:grid; place-items:center; padding:16px; background:rgba(17,16,15,.72); opacity:0; visibility:hidden; pointer-events:none; transition:opacity .18s ease, visibility 0s linear .18s; }
    .profile-crop-modal.is-open { opacity:1; visibility:visible; pointer-events:auto; transition-delay:0s; }
    .profile-crop-dialog { width:min(620px, 100%); max-height:calc(100dvh - 32px); overflow:auto; border:1px solid var(--se-border); border-radius:18px; background:var(--se-surface); box-shadow:0 24px 70px rgba(0,0,0,.32); transform:translateY(10px) scale(.98); transition:transform .18s ease; }
    .profile-crop-modal.is-open .profile-crop-dialog { transform:translateY(0) scale(1); }
    .profile-crop-head { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:14px 16px; border-bottom:1px solid var(--se-border); }
    .profile-crop-head h2 { margin:0; font-size:1rem; color:var(--se-text); }
    .profile-crop-close, .profile-crop-tool { min-height:40px; border:1px solid var(--se-border); border-radius:9px; background:var(--se-surface); color:var(--se-text); cursor:pointer; }
    .profile-crop-close { width:40px; padding:0; font-size:1.4rem; }
    .profile-crop-stage { height:min(52dvh, 440px); min-height:280px; overflow:hidden; background:#11100f; }
    .profile-crop-stage img { display:block; max-width:100%; }
    .profile-crop-controls { display:grid; gap:12px; padding:14px 16px; border-top:1px solid var(--se-border); }
    .profile-crop-tools { display:flex; justify-content:center; gap:8px; flex-wrap:wrap; }
    .profile-crop-tool { padding:0 12px; font-size:.78rem; font-weight:700; }
    .profile-crop-actions { display:grid; grid-template-columns:1fr 1fr; gap:10px; }
    .profile-crop-actions .btn { min-height:44px; text-align:center; }
    .cropper-view-box, .cropper-face { border-radius:50%; }
    @media (max-width:680px) { .admin-profile-grid { grid-template-columns:1fr; } .admin-profile-photo-row { align-items:flex-start; } .admin-profile-photo { width:88px; height:88px; flex-basis:88px; } }
    @media (max-width:440px) { .admin-profile-photo-row { flex-direction:column; } .admin-profile-upload { width:100%; } .profile-crop-modal { padding:8px; } .profile-crop-stage { min-height:0; height:48dvh; } .profile-crop-tool { flex:1 1 calc(33.333% - 8px); } }
    @media (prefers-reduced-motion:reduce) { .profile-crop-modal, .profile-crop-dialog { transition:none; } }
</style>
@endpush

@section('header')
    <h2>{{ __('Admin Profile') }}</h2>
@endsection

@section('content')
<div class="admin-profile-shell">
    @if(session('success'))
        <div class="se-feedback se-feedback--success" role="status">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="se-feedback se-feedback--error" role="alert">
            @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    <section class="admin-profile-card">
        <div class="admin-profile-head"><h3>{{ __('Profile photo') }}</h3></div>
        <div class="admin-profile-body">
            <form method="POST" action="{{ route('admin.profile.photo') }}" enctype="multipart/form-data">
                @csrf
                <div class="admin-profile-photo-row">
                    @if($photoUrl)
                        <img class="admin-profile-photo" src="{{ $photoUrl }}" alt="{{ __('Profile photo') }}" data-profile-photo-preview>
                    @else
                        <img class="admin-profile-photo" src="" alt="{{ __('Profile photo') }}" data-profile-photo-preview hidden>
                        <div class="admin-profile-photo admin-profile-photo-fallback" aria-hidden="true" data-profile-photo-placeholder>{{ strtoupper(substr($admin->full_name ?? 'A', 0, 2)) }}</div>
                    @endif
                    <div class="admin-profile-upload">
                        <label for="profile_photo">{{ __('Choose a new profile photo') }}</label>
                        <input id="profile_photo" type="file" name="profile_photo" accept="image/jpeg,image/png,image/webp" required data-profile-photo-input data-invalid-type="{{ __('Choose a JPG, PNG, or WEBP image.') }}">
                        <small>{{ __('JPG, PNG, or WEBP. You can crop and reposition the image before uploading.') }}</small>
                        <div class="admin-profile-actions">
                            <button class="btn btn-primary" type="submit">{{ __('Upload photo') }}</button>
                            <a class="btn" href="{{ route('admin.dashboard') }}">{{ __('Back to Dashboard') }}</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <section class="admin-profile-card">
        <div class="admin-profile-head"><h3>{{ __('Account information') }}</h3></div>
        <div class="admin-profile-body">
            <div class="admin-profile-grid">
                <div class="admin-profile-field"><span>{{ __('Name') }}</span><strong>{{ $admin->full_name }}</strong></div>
                <div class="admin-profile-field"><span>{{ __('NRIC') }}</span><strong>{{ maskIdentityNumber($admin->ic_no) }}</strong></div>
                <div class="admin-profile-field"><span>{{ __('Role') }}</span><strong>{{ adminRoleLabel($admin->role) }}</strong></div>
            </div>
        </div>
    </section>
</div>

<div class="profile-crop-modal" data-profile-crop-modal aria-hidden="true">
    <section class="profile-crop-dialog" role="dialog" aria-modal="true" aria-labelledby="profileCropTitle">
        <header class="profile-crop-head">
            <h2 id="profileCropTitle">{{ __('Adjust profile photo') }}</h2>
            <button type="button" class="profile-crop-close" data-profile-crop-action="cancel" aria-label="{{ __('Cancel photo crop') }}">&times;</button>
        </header>
        <div class="profile-crop-stage">
            <img data-profile-crop-image alt="{{ __('Selected profile photo') }}">
        </div>
        <footer class="profile-crop-controls">
            <div class="profile-crop-tools">
                <button type="button" class="profile-crop-tool" data-profile-crop-action="rotate-left">{{ __('Rotate left') }}</button>
                <button type="button" class="profile-crop-tool" data-profile-crop-action="rotate-right">{{ __('Rotate right') }}</button>
                <button type="button" class="profile-crop-tool" data-profile-crop-action="reset">{{ __('Reset') }}</button>
            </div>
            <div class="profile-crop-actions">
                <button type="button" class="btn" data-profile-crop-action="cancel">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-primary" data-profile-crop-action="apply">{{ __('Use photo') }}</button>
            </div>
        </footer>
    </section>
</div>
@endsection
