@extends('layouts.app')

@section('title', $title)

@push('styles')
<style>
    .manage-form-wrap{max-width:960px;margin:0 auto;display:grid;gap:1rem}.manage-form-card{border:1px solid var(--glass-line);border-radius:20px;background:var(--glass-bg-strong);box-shadow:var(--glass-shadow);overflow:hidden;backdrop-filter:blur(var(--glass-blur))}.manage-form-head{padding:1.3rem 1.4rem;border-bottom:1px solid rgba(255,255,255,.16);background:linear-gradient(135deg,#3b291d 0%,#765237 60%,#a77950 100%);color:#fff}.manage-form-head::before{content:'Account administration';display:block;margin-bottom:.35rem;color:#f2d5b5;font-size:.67rem;font-weight:800;letter-spacing:.11em;text-transform:uppercase}.manage-form-head h1{margin:0;font-size:1.45rem;letter-spacing:-.02em}.manage-form-head p{margin:.4rem 0 0;max-width:680px;color:rgba(255,255,255,.76);font-size:.8rem;line-height:1.5}.manage-form-body{padding:1.35rem}.manage-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:1rem}.manage-field{display:grid;gap:.4rem}.manage-field.full{grid-column:1/-1}.manage-field label{font-size:.72rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:.04em}.manage-field small{color:var(--text-muted)!important;line-height:1.45}.manage-field input,.manage-field select{width:100%;min-height:47px;border:1px solid var(--border);border-radius:11px;padding:.72rem .8rem;background:var(--surface);color:var(--text);font:inherit;outline:none}.manage-field input:focus,.manage-field select:focus{border-color:var(--primary);box-shadow:0 0 0 3px color-mix(in srgb,var(--primary) 18%,transparent)}.manage-field input:-webkit-autofill{-webkit-text-fill-color:var(--text);-webkit-box-shadow:0 0 0 1000px var(--surface) inset;caret-color:var(--text)}.manage-access{display:grid;gap:.7rem;margin-top:1.25rem;padding-top:1.1rem;border-top:1px solid var(--glass-line)}.manage-access>div>strong{font-size:1rem;color:var(--text)}.manage-access>div>div{color:var(--text-muted)!important}.manage-access-option{display:flex;align-items:flex-start;gap:.75rem;padding:.9rem;border:1px solid var(--border);border-radius:13px;background:var(--surface);color:var(--text);cursor:pointer;transition:border-color var(--dur-fast),background-color var(--dur-fast)}.manage-access-option:hover{border-color:var(--primary);background:color-mix(in srgb,var(--primary) 5%,var(--surface))}.manage-access-option:has(input:checked){border-color:color-mix(in srgb,var(--primary) 60%,var(--border));background:color-mix(in srgb,var(--primary) 9%,var(--surface))}.manage-access-option input{width:17px;height:17px;margin-top:.1rem;accent-color:var(--primary-dark)}.manage-access-option strong{display:block;color:var(--text);font-size:.88rem}.manage-access-option small{display:block;color:var(--text-muted);margin-top:.2rem;line-height:1.4}.manage-actions{display:flex;gap:.65rem;flex-wrap:wrap}.manage-btn{min-height:44px;display:inline-flex;align-items:center;justify-content:center;border:1px solid var(--border);border-radius:10px;padding:.7rem 1rem;background:var(--surface);color:var(--text);text-decoration:none;font-weight:800;cursor:pointer}.manage-btn:hover{border-color:var(--primary);color:var(--primary-dark)}.manage-btn.primary{background:var(--primary-dark);border-color:var(--primary-dark);color:var(--surface);box-shadow:0 8px 18px color-mix(in srgb,var(--primary-dark) 24%,transparent)}body[data-theme="dark"] .manage-btn.primary{background:var(--primary);color:#21170f}.password-visibility-toggle{color:var(--text-muted)}@media(max-width:640px){.manage-form-wrap{gap:.8rem}.manage-grid{grid-template-columns:1fr;gap:.85rem}.manage-field.full{grid-column:auto}.manage-form-head{padding:1.1rem}.manage-form-head h1{font-size:1.25rem}.manage-form-body{padding:1rem}.manage-access{margin-top:1rem}.manage-actions{display:grid;grid-template-columns:1fr 1fr}.manage-btn{width:100%}}@media(max-width:380px){.manage-actions{grid-template-columns:1fr}}
</style>
@endpush

@section('header')<h2 style="margin:0;font-size:1rem;font-weight:700;">{{ $title }}</h2>@endsection

@section('content')
<div class="manage-form-wrap">
    @if($errors->any())<div class="msg-err">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
    <form method="POST" action="{{ $submitRoute }}">
        @csrf
        @if($account) @method('PUT') @endif
        <section class="manage-form-card">
            <div class="manage-form-head"><h1>{{ $title }}</h1><p>The IC number identifies this account automatically on the shared Admin/Staff login.</p></div>
            <div class="manage-form-body">
                <div class="manage-grid">
                    <div class="manage-field"><label for="full_name">Full name</label><input id="full_name" name="full_name" value="{{ old('full_name',$account->full_name ?? '') }}" required></div>
                    <div class="manage-field"><label for="ic_no">IC number</label><input id="ic_no" name="ic_no" value="{{ old('ic_no',$account->ic_no ?? '') }}" required></div>
                    <div class="manage-field"><label for="email">Email</label><input id="email" type="email" name="email" value="{{ old('email',$account->email ?? '') }}"></div>
                    @if($mode === 'staff')
                        <div class="manage-field"><label for="staff_category">Staff category</label><select id="staff_category" name="staff_category" required>@foreach($categories as $value=>$label)<option value="{{ $value }}" @selected(old('staff_category',$account->staff_category ?? '')===$value)>{{ $label }}</option>@endforeach</select><small style="color:#806f62;">Discipline and Scholarship categories automatically receive their matching operational module.</small></div>
                    @endif
                    <div class="manage-field"><label for="is_active">Account status</label><select id="is_active" name="is_active" required><option value="1" @selected((string)old('is_active',$account->is_active ?? 1)==='1')>Active</option><option value="0" @selected((string)old('is_active',$account->is_active ?? 1)==='0')>Inactive</option></select></div>
                    <div class="manage-field"><label for="password">{{ $account ? 'New password (optional)' : 'Password' }}</label><div class="password-input-wrap"><input id="password" type="password" name="password" minlength="8" @required(!$account)><button type="button" class="password-visibility-toggle" data-password-toggle aria-controls="password" aria-pressed="false" aria-label="Show password" title="Show password"><svg class="password-eye" viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg><svg class="password-eye-off" viewBox="0 0 24 24" aria-hidden="true"><path d="m3 3 18 18"/><path d="M10.6 6.2A11.7 11.7 0 0 1 12 6c6.5 0 10 6 10 6a18 18 0 0 1-2.2 3"/><path d="M6.2 6.2C3.5 8 2 12 2 12s3.5 6 10 6c1 0 2-.2 2.8-.5"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/></svg></button></div></div>
                </div>

                @if($mode === 'staff')
                    <div class="manage-access">
                        <div><strong>Page access</strong><div style="font-size:.78rem;color:#806f62;margin-top:.2rem;">Choose only the pages required by this staff member.</div></div>
                        @foreach($pageOptions as $page)
                            <label class="manage-access-option"><input type="checkbox" name="lecturer_pages[]" value="{{ $page['key'] }}" @checked(in_array($page['key'],old('lecturer_pages',collect($pageOptions)->where('enabled',true)->pluck('key')->all()),true))><span><strong>{{ $page['label'] }}</strong><small>{{ $page['description'] }}</small></span></label>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
        <div class="manage-actions"><button class="manage-btn primary" type="submit">Save {{ $mode === 'staff' ? 'Staff' : 'Guard' }}</button><a class="manage-btn" href="{{ $backRoute }}">Cancel</a></div>
    </form>
</div>
@endsection
