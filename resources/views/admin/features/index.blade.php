@extends('layouts.app')

@section('title', __('Feature Controls'))
@section('header')<h2>{{ __('Feature Controls') }}</h2>@endsection
@push('styles')
<style>
    .feature-list { display:grid; gap:14px; }
    .feature-row { display:flex; justify-content:space-between; align-items:center; gap:18px; padding:16px; border:1px solid var(--border); border-radius:14px; }
    .feature-copy { min-width:0; }
    .feature-copy p { margin:5px 0 0; color:var(--text-muted); }
    .feature-row form { flex:0 0 auto; }
    @media(max-width:640px) {
        .feature-row { align-items:stretch; flex-direction:column; }
        .feature-row .ui-btn { width:100%; }
    }
</style>
@endpush
@section('content')
<div class="ui-shell" style="max-width:900px;margin:0 auto;">
    @if(session('success'))<div class="se-feedback se-feedback--success">{{ session('success') }}</div>@endif
    <section class="ui-card">
        <div class="ui-card-head"><strong>{{ __('Page Availability') }}</strong></div>
        <div class="ui-card-body feature-list">
            @foreach($features as $feature)
                <div class="feature-row">
                    <div class="feature-copy"><strong>{{ __($feature['label']) }}</strong><p>{{ __($feature['description']) }}</p></div>
                    <form method="POST" action="{{ route('admin.features.update', $feature['key']) }}">@csrf @method('PATCH')<input type="hidden" name="enabled" value="{{ $feature['enabled'] ? 0 : 1 }}"><button class="ui-btn {{ $feature['enabled'] ? 'btn-danger' : 'primary' }}" type="submit">{{ $feature['enabled'] ? __('Turn Off') : __('Turn On') }}</button></form>
                </div>
            @endforeach
        </div>
    </section>
</div>
@endsection
