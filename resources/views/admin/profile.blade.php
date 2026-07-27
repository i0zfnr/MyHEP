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
    @media (max-width:680px) { .admin-profile-grid { grid-template-columns:1fr; } .admin-profile-photo-row { align-items:flex-start; } .admin-profile-photo { width:88px; height:88px; flex-basis:88px; } }
    @media (max-width:440px) { .admin-profile-photo-row { flex-direction:column; } .admin-profile-upload { width:100%; } }
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
                        <img class="admin-profile-photo" src="{{ $photoUrl }}" alt="{{ __('Profile photo') }}">
                    @else
                        <div class="admin-profile-photo admin-profile-photo-fallback" aria-hidden="true">{{ strtoupper(substr($admin->full_name ?? 'A', 0, 2)) }}</div>
                    @endif
                    <div class="admin-profile-upload">
                        <label for="profile_photo">{{ __('Choose a new profile photo') }}</label>
                        <input id="profile_photo" type="file" name="profile_photo" accept="image/jpeg,image/png,image/webp" required>
                        <small>{{ __('JPG, PNG, or WEBP. Maximum 50MB for testing.') }}</small>
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
                <div class="admin-profile-field"><span>{{ __('NRIC') }}</span><strong>{{ $admin->ic_no }}</strong></div>
                <div class="admin-profile-field"><span>{{ __('Role') }}</span><strong>{{ adminRoleLabel($admin->role) }}</strong></div>
            </div>
        </div>
    </section>
</div>
@endsection
