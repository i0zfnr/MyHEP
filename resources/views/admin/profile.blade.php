@extends('layouts.app')

@section('title', __('Admin Profile'))



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

    <section class="admin-profile-card">
        <div class="admin-profile-head"><h3>{{ __('Change password') }}</h3></div>
        <div class="admin-profile-body">
            <form class="admin-profile-password-form" method="POST" action="{{ route('admin.profile.password') }}">
                @csrf
                @method('PUT')
                <div class="admin-profile-password-field">
                    <label for="current_password">{{ __('Current password') }}</label>
                    <input id="current_password" type="password" name="current_password" autocomplete="current-password" required>
                </div>
                <div class="admin-profile-password-field">
                    <label for="password">{{ __('New password') }}</label>
                    <input id="password" type="password" name="password" autocomplete="new-password" minlength="8" required>
                </div>
                <div class="admin-profile-password-field">
                    <label for="password_confirmation">{{ __('Confirm new password') }}</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" minlength="8" required>
                </div>
                <button class="btn btn-primary" type="submit">{{ __('Update password') }}</button>
            </form>
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
