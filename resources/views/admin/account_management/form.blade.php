@extends('layouts.app')

@section('title', $title)



@section('header')<h2 style="margin:0;font-size:1rem;font-weight:700;">{{ $title }}</h2>@endsection

@section('content')
<div class="manage-form-wrap">
    @if($errors->any())<div class="msg-err">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
    <form method="POST" action="{{ $submitRoute }}">
        @csrf
        @if($account) @method('PUT') @endif
        <section class="manage-form-card">
            <div class="manage-form-head"><h1>{{ $title }}</h1><p>{{ __('Email is used as the login ID. The NRIC is the default password for new and reset accounts.') }}</p></div>
            <div class="manage-form-body">
                <div class="manage-grid">
                    <div class="manage-field"><label for="full_name">{{ __('Full name') }}</label><input id="full_name" name="full_name" value="{{ old('full_name',$account->full_name ?? '') }}" required></div>
                    <div class="manage-field"><label for="ic_no">{{ __('NRIC number') }}</label><input id="ic_no" name="ic_no" value="{{ old('ic_no',$account->ic_no ?? '') }}" inputmode="numeric" autocomplete="off" maxlength="20" required><small>{{ __('Numbers only. Hyphens and spaces are removed automatically.') }}</small></div>
                    <div class="manage-field"><label for="email">{{ __('Email') }}</label><input id="email" type="email" name="email" value="{{ old('email',$account->email ?? '') }}" required></div>
                    @if($mode === 'staff')
                        <div class="manage-field"><label for="staff_category">{{ __('Staff category') }}</label><select id="staff_category" name="staff_category" required>@foreach($categories as $value=>$label)<option value="{{ $value }}" @selected(old('staff_category',$account->staff_category ?? '')===$value)>{{ $label }}</option>@endforeach</select><small style="color:#806f62;">{{ __('Discipline and Scholarship categories automatically receive their matching operational module.') }}</small></div>
                        <div class="manage-field"><label for="staff_department">{{ __('Department / Unit') }}</label><select id="staff_department" name="staff_department"><option value="">{{ __('Not specified') }}</option>@foreach($departments as $value=>$label)<option value="{{ $value }}" @selected(old('staff_department',$account->staff_department ?? '')===$value)>{{ $label }}</option>@endforeach</select></div>
                        <div class="manage-field"><label for="position">{{ __('Position') }}</label><input id="position" name="position" value="{{ old('position',$account->position ?? '') }}" maxlength="180" placeholder="{{ __('Example: Head of Department') }}"></div>
                    @endif
                    <div class="manage-field"><label for="is_active">{{ __('Account status') }}</label><select id="is_active" name="is_active" required><option value="1" @selected((string)old('is_active',$account->is_active ?? 1)==='1')>{{ __('Active') }}</option><option value="0" @selected((string)old('is_active',$account->is_active ?? 1)==='0')>{{ __('Inactive') }}</option></select></div>
                    <div class="manage-field"><label for="password">{{ $account ? 'New password (optional)' : 'Password' }}</label><div class="password-input-wrap"><input id="password" type="password" name="password" minlength="8" @required(!$account)><button type="button" class="password-visibility-toggle" data-password-toggle aria-controls="password" aria-pressed="false" aria-label="{{ __('Show password') }}" title="{{ __('Show password') }}"><svg class="password-eye" viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg><svg class="password-eye-off" viewBox="0 0 24 24" aria-hidden="true"><path d="m3 3 18 18"/><path d="M10.6 6.2A11.7 11.7 0 0 1 12 6c6.5 0 10 6 10 6a18 18 0 0 1-2.2 3"/><path d="M6.2 6.2C3.5 8 2 12 2 12s3.5 6 10 6c1 0 2-.2 2.8-.5"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/></svg></button></div></div>
                </div>

                @if($mode === 'staff')
                    <div class="manage-access">
                        <div><strong>{{ __('Page access') }}</strong><div style="font-size:.78rem;color:#806f62;margin-top:.2rem;">{{ __('KJ HEP and System Admin can choose the pages available to this staff member. All page access is disabled by default until explicitly enabled here.') }}</div></div>
                        @foreach($pageOptions as $page)
                            <label class="manage-access-option"><input type="checkbox" name="lecturer_pages[]" value="{{ $page['key'] }}" @checked(in_array($page['key'],old('lecturer_pages',collect($pageOptions)->where('enabled',true)->pluck('key')->all()),true))><span><strong>{{ $page['label'] }}</strong><small>{{ $page['description'] }}</small></span></label>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
        <div class="manage-actions"><button class="manage-btn primary" type="submit">Save {{ $mode === 'staff' ? 'Staff' : 'Guard' }}</button><a class="manage-btn" href="{{ $backRoute }}">{{ __('Cancel') }}</a></div>
    </form>
</div>
@endsection
