@extends('layouts.app')

@section('title', __('Tambah Admin'))



@section('header')
    <h2 style="margin:0;font-size:1.1rem;font-weight:700;color:#2d1f14;">{{ __('Tambah Admin') }}</h2>
@endsection

@section('content')
<div class="wrap">
    @if($errors->any())<div class="err">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
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
                        <input id="ic_no" type="text" name="ic_no" value="{{ old('ic_no') }}" inputmode="numeric" autocomplete="off" maxlength="20" required>
                    </div>
                </div>

                <div class="grid grid-2" style="margin-top:12px;">
                    <div>
                        <label for="email">{{ __('Email') }}</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required>
                    </div>
                    <div>
                        <label for="role">{{ __('Role') }}</label>
                        <select id="role" name="role" required>
                            @foreach($roleOptions as $role => $label)
                                <option value="{{ $role }}" {{ old('role') === $role ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="password">{{ __('Kata Laluan') }}</label>
                        <div class="password-input-wrap"><input id="password" type="password" name="password" minlength="8" required><button type="button" class="password-visibility-toggle" data-password-toggle aria-controls="password" aria-pressed="false" aria-label="{{ __('Show password') }}" title="{{ __('Show password') }}"><svg class="password-eye" viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg><svg class="password-eye-off" viewBox="0 0 24 24" aria-hidden="true"><path d="m3 3 18 18"/><path d="M10.6 6.2A11.7 11.7 0 0 1 12 6c6.5 0 10 6 10 6a18 18 0 0 1-2.2 3"/><path d="M6.2 6.2C3.5 8 2 12 2 12s3.5 6 10 6c1 0 2-.2 2.8-.5"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/></svg></button></div>
                    </div>
                </div>
                @if($canConfigureLecturerPages)
                    <div style="margin-top:14px;">
                        <label>{{ __('Lecturer Page Access') }}</label>
                        <div style="display:grid;gap:8px;">
                            @foreach($lecturerPages as $page)
                                <label style="display:flex;gap:8px;align-items:flex-start;font-weight:600;color:var(--text);">
                                    <input type="checkbox" name="lecturer_pages[]" value="{{ $page['key'] }}" @checked(in_array($page['key'], old('lecturer_pages', array_column($lecturerPages, 'key')), true)) style="width:auto;margin-top:3px;">
                                    <span><strong>{{ __($page['label']) }}</strong><br><small style="color:var(--text-muted);">{{ __($page['description']) }}</small></span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="actions">
            <button class="btn btn-primary" type="submit">{{ __('Simpan Admin') }}</button>
            <a class="btn" href="{{ route('admin.admin-users.index') }}">{{ __('Batal') }}</a>
        </div>
    </form>
</div>
@endsection
