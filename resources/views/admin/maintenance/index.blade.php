@extends('layouts.app')

@section('title', 'System Maintenance')

@push('styles')
<style>
    .maint-wrap { max-width: 980px; margin: 0 auto; display: grid; gap: 1rem; }
    .maint-hero {
        border-radius: 18px;
        border: 1px solid rgba(255,255,255,.74);
        background:
            linear-gradient(145deg, rgba(255,255,255,.78), rgba(255,250,245,.52)),
            radial-gradient(circle at 92% 12%, rgba(164,141,120,.14), transparent 34%);
        box-shadow: var(--glass-shadow, 0 16px 38px rgba(61,46,34,.10));
        backdrop-filter: blur(var(--glass-blur, 16px)) saturate(136%);
        -webkit-backdrop-filter: blur(var(--glass-blur, 16px)) saturate(136%);
        padding: 1.4rem;
        overflow: hidden;
    }
    .maint-eyebrow {
        display: inline-flex;
        border-radius: 999px;
        padding: .25rem .65rem;
        font-size: .68rem;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
        margin-bottom: .7rem;
    }
    .maint-eyebrow.on { background: #fff7ed; border: 1px solid #fed7aa; color: #9a3412; }
    .maint-eyebrow.off { background: #e7f3f3; border: 1px solid #b9ddde; color: #1f5559; }
    .maint-eyebrow.cache-on { background: #ecfeff; border: 1px solid #a5f3fc; color: #155e75; }
    .maint-eyebrow.cache-off { background: #fff1f2; border: 1px solid #fecdd3; color: #9f1239; }
    .maint-hero h3 { margin: 0; font-size: 1.55rem; color: #1f1712; letter-spacing: -.02em; }
    .maint-hero p { margin: .45rem 0 0; color: #74675d; line-height: 1.65; max-width: 720px; }
    .maint-grid { display: grid; grid-template-columns: 1fr; gap: 1rem; }
    @media (min-width: 860px) { .maint-grid { grid-template-columns: .9fr 1.1fr; } }
    .maint-card {
        border: 1px solid rgba(255,255,255,.74);
        border-radius: 16px;
        background: rgba(255,255,255,.66);
        box-shadow: var(--glass-shadow, 0 16px 38px rgba(61,46,34,.10));
        backdrop-filter: blur(var(--glass-blur, 16px)) saturate(136%);
        -webkit-backdrop-filter: blur(var(--glass-blur, 16px)) saturate(136%);
        overflow: hidden;
    }
    .maint-card-head {
        padding: .9rem 1rem;
        border-bottom: 1px solid rgba(234,223,210,.72);
        background: linear-gradient(180deg, rgba(255,255,255,.68), rgba(255,255,255,.28));
        font-weight: 800;
        color: #241a12;
    }
    .maint-card-body { padding: 1rem; }
    .maint-row {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 1rem;
        padding: .7rem 0;
        border-bottom: 1px dashed rgba(122,101,85,.25);
        font-size: .86rem;
    }
    .maint-row:last-child { border-bottom: none; }
    .maint-key { color: #7a6555; font-weight: 700; }
    .maint-val { color: #2d1f14; font-weight: 800; text-align: right; word-break: break-word; }
    .maint-url {
        display: block;
        margin-top: .75rem;
        padding: .75rem .85rem;
        border-radius: 12px;
        border: 1px solid rgba(203,185,164,.72);
        background: rgba(255,255,255,.68);
        color: #5f4a3a;
        font-size: .84rem;
        font-weight: 700;
        word-break: break-all;
        text-decoration: none;
    }
    .maint-actions { display: flex; gap: .65rem; flex-wrap: wrap; margin-top: 1rem; }
    .maint-btn {
        border: 1px solid #cbb9a4;
        border-radius: 10px;
        padding: .65rem 1rem;
        font: inherit;
        font-size: .86rem;
        font-weight: 800;
        cursor: pointer;
        background: rgba(255,255,255,.62);
        color: #6e5745;
    }
    .maint-btn.warn { background: #fff7ed; border-color: #fed7aa; color: #9a3412; }
    .maint-btn.ok { background: #e7f3f3; border-color: #b9ddde; color: #1f5559; }
    .maint-note {
        border-radius: 12px;
        padding: .8rem .9rem;
        background: #fff7ed;
        border: 1px solid #fed7aa;
        color: #9a3412;
        font-size: .84rem;
        line-height: 1.6;
    }
    .maint-card.push-centre { grid-column: 1 / -1; }
    .maint-card.email-centre { grid-column: 1 / -1; }
    .maint-push-layout { display: grid; gap: 1rem; }
    @media (min-width: 900px) { .maint-push-layout { grid-template-columns: minmax(270px, .9fr) minmax(0, 1.55fr); } }
    .maint-push-panel { border: 1px solid rgba(203,185,164,.6); border-radius: 14px; padding: 1rem; background: rgba(255,255,255,.42); }
    .maint-push-panel h4 { margin: 0 0 .35rem; color: #2d1f14; font-size: 1rem; }
    .maint-push-panel p { margin: 0 0 .9rem; color: #74675d; font-size: .83rem; line-height: 1.55; }
    .maint-stats { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .6rem; margin-bottom: 1rem; }
    .maint-stat { border-radius: 12px; padding: .75rem; background: rgba(255,255,255,.72); border: 1px solid rgba(203,185,164,.5); }
    .maint-stat strong { display: block; color: #2d1f14; font-size: 1.25rem; }
    .maint-stat span { display: block; color: #7a6555; font-size: .72rem; font-weight: 700; margin-top: .1rem; }
    .maint-fields { display: grid; gap: .75rem; }
    @media (min-width: 640px) { .maint-fields.two { grid-template-columns: 1fr 1fr; } }
    .maint-field label { display: block; margin-bottom: .35rem; color: #5f4a3a; font-size: .78rem; font-weight: 800; }
    .maint-field input, .maint-field textarea { width: 100%; box-sizing: border-box; border: 1px solid #d8c8b7; border-radius: 10px; padding: .68rem .75rem; background: rgba(255,255,255,.78); color: #2d1f14; font: inherit; font-size: .86rem; }
    .maint-field textarea { min-height: 92px; resize: vertical; }
    .maint-field small { display: block; margin-top: .3rem; color: #8a796c; font-size: .72rem; }
    .msg-ok { padding: .75rem .9rem; border-radius: 12px; background: #e7f3f3; border: 1px solid #b9ddde; color: #1f5559; font-size: .86rem; font-weight: 700; }
    .msg-err { padding: .75rem .9rem; border-radius: 12px; background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; font-size: .86rem; font-weight: 700; }
    .maint-push-head { display: flex; align-items: center; justify-content: space-between; gap: .8rem; }
    .maint-push-head-main { display: flex; align-items: center; gap: .65rem; }
    .maint-push-head-icon { width: 34px; height: 34px; display: grid; place-items: center; border-radius: 10px; background: #1f5559; color: #fff; }
    .maint-push-head-icon svg { width: 18px; height: 18px; }
    .maint-push-head-copy strong { display: block; color: #241a12; font-size: .94rem; }
    .maint-push-head-copy span { display: block; margin-top: .12rem; color: #806f61; font-size: .68rem; font-weight: 600; }
    .maint-push-badge { flex: 0 0 auto; padding: .3rem .58rem; border: 1px solid #b9ddde; border-radius: 999px; background: #edf8f8; color: #1f5559; font-size: .65rem; font-weight: 800; letter-spacing: .07em; text-transform: uppercase; }
    .push-centre .maint-card-body { padding: 1rem; }
    .push-centre .maint-push-panel { position: relative; padding: 1.1rem; border-color: #ddd0c3; background: rgba(255,255,255,.84); overflow: hidden; }
    .push-centre .maint-push-panel::before { content: ''; position: absolute; inset: 0 auto 0 0; width: 4px; background: #2f7378; }
    .push-centre .maint-push-panel.broadcast-panel::before { background: #c2814d; }
    .maint-panel-kicker { display: inline-flex; align-items: center; gap: .42rem; margin-bottom: .55rem; color: #1f5559; font-size: .67rem; font-weight: 800; letter-spacing: .07em; text-transform: uppercase; }
    .maint-panel-kicker svg { width: 15px; height: 15px; }
    .broadcast-panel .maint-panel-kicker { color: #935b31; }
    .push-centre .maint-push-panel h4 { margin-bottom: .38rem; font-size: 1.05rem; letter-spacing: -.015em; }
    .push-centre .maint-push-panel > p { max-width: 640px; margin-bottom: 1rem; }
    .push-centre .maint-stats { gap: .65rem; }
    .push-centre .maint-stat { min-width: 0; padding: .8rem; border-color: #d8e5e5; background: #f4f9f9; }
    .push-centre .maint-stat strong { color: #173f42; font-size: 1.35rem; }
    .push-centre .maint-stat span { color: #667879; line-height: 1.35; }
    .push-centre .maint-field input,
    .push-centre .maint-field textarea { border-color: #d6c7b8; background: #fff; }
    .push-centre .maint-field input:focus,
    .push-centre .maint-field textarea:focus { outline: 3px solid rgba(31,85,89,.12); border-color: #4f8083; }
    .push-centre .maint-field textarea { min-height: 118px; }
    .maint-push-test-btn,
    .maint-broadcast-btn { display: inline-flex; align-items: center; justify-content: center; gap: .48rem; min-height: 44px; border-radius: 11px; padding: .7rem 1rem; font: inherit; font-size: .82rem; font-weight: 800; cursor: pointer; }
    .maint-push-test-btn { width: 100%; border: 1px solid #1f5559; background: linear-gradient(135deg, #1f5559, #32777b); color: #fff; box-shadow: 0 10px 22px rgba(31,85,89,.18); }
    .maint-broadcast-btn { border: 1px solid #b56f3e; background: linear-gradient(135deg, #a96334, #c47c48); color: #fff; box-shadow: 0 10px 22px rgba(169,99,52,.18); }
    .maint-push-test-btn svg, .maint-broadcast-btn svg { width: 17px; height: 17px; }
    .maint-push-test-btn:hover, .maint-broadcast-btn:hover { transform: translateY(-1px); filter: brightness(1.05); }
    .maint-push-test-btn:focus-visible, .maint-broadcast-btn:focus-visible { outline: 3px solid rgba(31,85,89,.24); outline-offset: 2px; }
    body[data-theme="dark"] .push-centre { border-color: rgba(226,209,192,.14); background: rgba(20,18,16,.86); }
    body[data-theme="dark"] .push-centre .maint-card-head { border-color: rgba(226,209,192,.12); background: linear-gradient(180deg, rgba(38,34,30,.96), rgba(27,24,21,.92)); }
    body[data-theme="dark"] .maint-push-head-copy strong { color: #f7efe8; }
    body[data-theme="dark"] .maint-push-head-copy span { color: #b9a99b; }
    body[data-theme="dark"] .maint-push-badge { border-color: rgba(127,184,188,.32); background: rgba(31,85,89,.24); color: #c7ecee; }
    body[data-theme="dark"] .push-centre .maint-push-panel { border-color: rgba(226,209,192,.16); background: linear-gradient(145deg, rgba(35,31,27,.96), rgba(26,24,21,.96)); }
    body[data-theme="dark"] .push-centre .maint-push-panel h4 { color: #f7efe8; }
    body[data-theme="dark"] .push-centre .maint-push-panel > p { color: #c8b8a9; }
    body[data-theme="dark"] .push-centre .maint-stat { border-color: rgba(127,184,188,.2); background: rgba(31,85,89,.15); }
    body[data-theme="dark"] .push-centre .maint-stat strong { color: #e6f5f5; }
    body[data-theme="dark"] .push-centre .maint-stat span { color: #a9c2c3; }
    body[data-theme="dark"] .push-centre .maint-field label { color: #ead9ca; }
    body[data-theme="dark"] .push-centre .maint-field small { color: #ad9d90; }
    body[data-theme="dark"] .push-centre .maint-field input,
    body[data-theme="dark"] .push-centre .maint-field textarea { border-color: rgba(226,209,192,.2); background: rgba(11,11,10,.66); color: #f7efe8; }
    body[data-theme="dark"] .maint-push-test-btn { border-color: #77aeb1; background: linear-gradient(135deg, #346f73, #285b5f); }
    body[data-theme="dark"] .maint-broadcast-btn { border-color: #d39970; background: linear-gradient(135deg, #8f5631, #ab6840); }
    @media (max-width: 520px) {
        .maint-push-head { align-items: flex-start; }
        .maint-push-badge { display: none; }
        .push-centre .maint-stats { grid-template-columns: 1fr 1fr; }
        .maint-broadcast-btn { width: 100%; }
    }
    .maint-email-shell {
        display: grid;
        grid-template-columns: minmax(220px, .7fr) minmax(0, 1.3fr);
        gap: 1rem;
        padding: 1rem;
        border: 1px solid rgba(185,221,222,.88);
        border-radius: 16px;
        background:
            radial-gradient(circle at 10% 0%, rgba(185,221,222,.46), transparent 38%),
            linear-gradient(145deg, rgba(248,253,253,.96), rgba(255,250,245,.9));
    }
    .maint-email-intro { padding: .3rem .25rem; align-self: center; }
    .maint-email-icon {
        width: 48px;
        height: 48px;
        display: grid;
        place-items: center;
        margin-bottom: .9rem;
        border-radius: 14px;
        background: #1f5559;
        color: #fff;
        box-shadow: 0 10px 24px rgba(31,85,89,.22);
    }
    .maint-email-icon svg { width: 24px; height: 24px; }
    .maint-email-intro h4 { margin: 0 0 .45rem; color: #213d3f; font-size: 1.08rem; }
    .maint-email-intro p { margin: 0; color: #6f6156; font-size: .82rem; line-height: 1.65; }
    .maint-email-status {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        margin-top: .85rem;
        padding: .35rem .62rem;
        border: 1px solid #b9ddde;
        border-radius: 999px;
        background: rgba(255,255,255,.72);
        color: #1f5559;
        font-size: .7rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .06em;
    }
    .maint-email-status::before { content: ''; width: 7px; height: 7px; border-radius: 50%; background: #2f8f75; box-shadow: 0 0 0 3px rgba(47,143,117,.14); }
    .maint-email-form { padding: 1rem; border: 1px solid rgba(216,200,183,.72); border-radius: 14px; background: rgba(255,255,255,.82); }
    .maint-email-form .maint-field input { min-height: 48px; padding-inline: .9rem; border-color: #c9dede; background: #fff; }
    .maint-email-form .maint-field input:focus { outline: 3px solid rgba(31,85,89,.14); border-color: #3b7478; }
    .maint-email-submit {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .5rem;
        min-height: 44px;
        padding: .7rem 1rem;
        border: 1px solid #1f5559;
        border-radius: 11px;
        background: linear-gradient(135deg, #1f5559, #2f7378);
        color: #fff;
        font: inherit;
        font-size: .84rem;
        font-weight: 800;
        cursor: pointer;
        box-shadow: 0 10px 22px rgba(31,85,89,.2);
    }
    .maint-email-submit:hover { transform: translateY(-1px); box-shadow: 0 13px 27px rgba(31,85,89,.25); }
    .maint-email-submit:focus-visible { outline: 3px solid rgba(31,85,89,.24); outline-offset: 2px; }
    .maint-email-submit svg { width: 17px; height: 17px; }
    body[data-theme="dark"] .maint-email-shell {
        border-color: rgba(127,184,188,.34);
        background:
            radial-gradient(circle at 8% 0%, rgba(70,137,142,.2), transparent 38%),
            linear-gradient(145deg, rgba(27,32,31,.96), rgba(24,21,18,.94));
    }
    body[data-theme="dark"] .maint-email-intro h4 { color: #f7efe8; }
    body[data-theme="dark"] .maint-email-intro p { color: #c8b8a9; }
    body[data-theme="dark"] .maint-email-status { border-color: rgba(127,184,188,.32); background: rgba(31,85,89,.24); color: #c7ecee; }
    body[data-theme="dark"] .maint-email-form { border-color: rgba(226,209,192,.14); background: rgba(13,13,12,.44); }
    body[data-theme="dark"] .maint-email-form .maint-field label { color: #f0dfcf; }
    body[data-theme="dark"] .maint-email-form .maint-field small { color: #ae9d8e; }
    body[data-theme="dark"] .maint-email-form .maint-field input { border-color: rgba(127,184,188,.28); background: rgba(15,16,15,.78); color: #f7efe8; }
    body[data-theme="dark"] .maint-email-submit { border-color: #83b8bb; background: linear-gradient(135deg, #346f73, #285b5f); color: #fff; }
    @media (max-width: 720px) {
        .maint-email-shell { grid-template-columns: 1fr; }
        .maint-email-intro { padding: .15rem; }
    }
    @media (prefers-reduced-motion: reduce) { .maint-email-submit, .maint-push-test-btn, .maint-broadcast-btn { transition: none; } .maint-email-submit:hover, .maint-push-test-btn:hover, .maint-broadcast-btn:hover { transform: none; } }
</style>
@endpush

@section('header')
    <h2 style="margin:0;font-size:1rem;font-weight:700;color:var(--text,#2d1f14);">{{ __('System Maintenance') }}</h2>
@endsection

@section('content')
<div class="maint-wrap">
    @if(session('success'))
        <div class="msg-ok">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="msg-err">{{ $errors->first() }}</div>
    @endif

    <section class="maint-hero">
        <span class="maint-eyebrow {{ $maintenance['enabled'] ? 'on' : 'off' }}">
            {{ $maintenance['enabled'] ? 'Maintenance On' : 'Maintenance Off' }}
        </span>
        <h3>{{ __('Maintenance Control') }}</h3>
        <p>{{ __('Use this page before planned downtime, database work, or system updates. When enabled, normal visitors will see Laravel maintenance mode while admins can continue through the bypass URL.') }}</p>
    </section>

    <div class="maint-grid">
        <section class="maint-card">
            <div class="maint-card-head">{{ __('Current Status') }}</div>
            <div class="maint-card-body">
                <div class="maint-row">
                    <span class="maint-key">{{ __('Mode') }}</span>
                    <span class="maint-val">{{ $maintenance['enabled'] ? 'Maintenance enabled' : 'System public' }}</span>
                </div>
                <div class="maint-row">
                    <span class="maint-key">{{ __('Retry After') }}</span>
                    <span class="maint-val">{{ $maintenance['retry'] ? $maintenance['retry'] . ' seconds' : '-' }}</span>
                </div>
                <div class="maint-row">
                    <span class="maint-key">{{ __('Server Time') }}</span>
                    <span class="maint-val">{{ $maintenance['server_time'] }}</span>
                </div>
                <div class="maint-row">
                    <span class="maint-key">{{ __('System Cache') }}</span>
                    <span class="maint-val">{{ $maintenance['cache_enabled'] ? 'Enabled' : 'Disabled' }}</span>
                </div>
                <div class="maint-row">
                    <span class="maint-key">{{ __('Tracked Cache Keys') }}</span>
                    <span class="maint-val">{{ $maintenance['cache_key_count'] }}</span>
                </div>
                <div class="maint-row">
                    <span class="maint-key">{{ __('Last Cache Clear') }}</span>
                    <span class="maint-val">{{ $maintenance['cache_last_cleared_at'] ?: '-' }}</span>
                </div>
                <div class="maint-row">
                    <span class="maint-key">{{ __('Cache Toggle Updated') }}</span>
                    <span class="maint-val">{{ $maintenance['cache_updated_at'] ?: '-' }}</span>
                </div>

                @if($maintenance['bypass_url'])
                    <a class="maint-url" href="{{ $maintenance['bypass_url'] }}" target="_blank" rel="noopener">
                        {{ $maintenance['bypass_url'] }}
                    </a>
                @endif
            </div>
        </section>

        <section class="maint-card">
            <div class="maint-card-head">{{ __('Actions') }}</div>
            <div class="maint-card-body">
                <div class="maint-note">
                    {{ __('Save the bypass URL after enabling maintenance. If you lose browser access while maintenance is enabled, run') }} <strong>{{ __('php artisan up') }}</strong> {{ __('from the terminal.') }}
                </div>

                <div class="maint-actions">
                    @if($maintenance['enabled'])
                        <form method="POST" action="{{ route('admin.maintenance.update') }}">
                            @csrf
                            <input type="hidden" name="action" value="disable">
                            <button type="submit" class="maint-btn ok">{{ __('Disable Maintenance') }}</button>
                        </form>
                    @else
            <form method="POST" action="{{ route('admin.maintenance.update') }}"
                data-confirm-title="{{ __('Enable maintenance mode') }}"
                data-confirm-message="{{ __('Enable maintenance mode now? Visitors will be blocked until it is disabled.') }}"
                data-confirm-action="{{ __('Enable') }}">
                            @csrf
                            <input type="hidden" name="action" value="enable">
                            <button type="submit" class="maint-btn warn">{{ __('Enable Maintenance') }}</button>
                        </form>
                    @endif
                </div>

                <hr style="border:none;border-top:1px dashed rgba(122,101,85,.25);margin:1rem 0;">

                <span class="maint-eyebrow {{ $maintenance['cache_enabled'] ? 'cache-on' : 'cache-off' }}">
                    {{ $maintenance['cache_enabled'] ? 'Cache On' : 'Cache Off' }}
                </span>
                <div class="maint-actions">
                    @if($maintenance['cache_enabled'])
                        <form method="POST" action="{{ route('admin.maintenance.update') }}">
                            @csrf
                            <input type="hidden" name="action" value="cache_disable">
                            <button type="submit" class="maint-btn warn">{{ __('Disable Cache') }}</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('admin.maintenance.update') }}">
                            @csrf
                            <input type="hidden" name="action" value="cache_enable">
                            <button type="submit" class="maint-btn ok">{{ __('Enable Cache') }}</button>
                        </form>
                    @endif
                </div>
            </div>
        </section>

        <section class="maint-card push-centre">
            <div class="maint-card-head maint-push-head">
                <div class="maint-push-head-main">
                    <span class="maint-push-head-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M10 21h4"/></svg>
                    </span>
                    <span class="maint-push-head-copy">
                        <strong>{{ __('Push Notification Centre') }}</strong>
                        <span>{{ __('Test delivery and announce scheduled downtime') }}</span>
                    </span>
                </div>
                <span class="maint-push-badge">{{ __('Web Push') }}</span>
            </div>
            <div class="maint-card-body">
                <div class="maint-push-layout">
                    <div class="maint-push-panel test-panel">
                        <span class="maint-panel-kicker">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 3 5 6v5c0 4.7 2.8 8 7 10 4.2-2 7-5.3 7-10V6z"/><path d="m9 12 2 2 4-4"/></svg>
                            {{ __('Private test') }}
                        </span>
                        <h4>{{ __('Test this admin device') }}</h4>
                        <p>{{ __('Send a private test notification only to devices registered under your System Admin account.') }}</p>
                        <div class="maint-stats">
                            <div class="maint-stat"><strong>{{ $pushSubscriptions['current_admin_devices'] }}</strong><span>{{ __('Your devices') }}</span></div>
                            <div class="maint-stat"><strong>{{ $pushSubscriptions['devices'] }}</strong><span>{{ __('All active devices') }}</span></div>
                            <div class="maint-stat"><strong>{{ $pushSubscriptions['students'] }}</strong><span>{{ __('Subscribed students') }}</span></div>
                            <div class="maint-stat"><strong>{{ $pushSubscriptions['admins'] }}</strong><span>{{ __('Subscribed admins') }}</span></div>
                        </div>
                        <form method="POST" action="{{ route('admin.maintenance.push.test') }}">
                            @csrf
                            <button type="submit" class="maint-push-test-btn">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M10 21h4"/></svg>
                                {{ __('Send Test Notification') }}
                            </button>
                        </form>
                    </div>

                    <div class="maint-push-panel broadcast-panel">
                        <span class="maint-panel-kicker">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 13V6l16-3v13"/><circle cx="6" cy="17" r="3"/><circle cx="18" cy="18" r="3"/></svg>
                            {{ __('System announcement') }}
                        </span>
                        <h4>{{ __('Announce scheduled maintenance') }}</h4>
                        <p>{{ __('Notify every subscribed student and admin. This announcement does not enable maintenance mode automatically.') }}</p>
                        <form method="POST" action="{{ route('admin.maintenance.push.broadcast') }}"
                            data-confirm-title="{{ __('Send maintenance notification') }}"
                            data-confirm-message="Send this announcement to all subscribed students and admins?"
                            data-confirm-action="Send notification">
                            @csrf
                            <div class="maint-fields two">
                                <div class="maint-field">
                                    <label for="starts_at">{{ __('Maintenance starts') }}</label>
                                    <input id="starts_at" name="starts_at" type="datetime-local" required min="{{ now()->format('Y-m-d\TH:i') }}" value="{{ old('starts_at', now()->addHour()->startOfHour()->format('Y-m-d\TH:i')) }}">
                                </div>
                                <div class="maint-field">
                                    <label for="ends_at">Expected completion (optional)</label>
                                    <input id="ends_at" name="ends_at" type="datetime-local" value="{{ old('ends_at') }}">
                                </div>
                            </div>
                            <div class="maint-field" style="margin-top:.75rem;">
                                <label for="message">Custom message (optional)</label>
                                <textarea id="message" name="message" maxlength="300" placeholder="{{ __('Leave blank to use the automatic message with the selected schedule.') }}">{{ old('message') }}</textarea>
                                <small>{{ __('Maximum 300 characters. The schedule is added automatically only when this field is blank.') }}</small>
                            </div>
                            <div class="maint-actions">
                                <button type="submit" class="maint-broadcast-btn">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m4 4 17 8-17 8 3-8z"/><path d="M7 12h14"/></svg>
                                    {{ __('Send Maintenance Notification') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>

        <section class="maint-card email-centre">
            <div class="maint-card-head">{{ __('Email Delivery Test') }}</div>
            <div class="maint-card-body">
                <div class="maint-email-shell">
                    <div class="maint-email-intro">
                        <div class="maint-email-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 6.5h16v11H4z"/><path d="m5 8 7 5 7-5"/></svg>
                        </div>
                        <h4>{{ __('Verify outbound email') }}</h4>
                        <p>{{ __('Send a private message through the same mailer used for password-reset codes. Provider acceptance and inbox delivery are reported separately.') }}</p>
                        <span class="maint-email-status">{{ config('mail.default') }} mailer active</span>
                    </div>
                    <form class="maint-email-form" method="POST" action="{{ route('admin.maintenance.email.test') }}">
                        @csrf
                        <div class="maint-field">
                            <label for="test_email">{{ __('Recipient email') }}</label>
                            <input id="test_email" name="email" type="email" maxlength="150" autocomplete="email" required value="{{ old('email') }}" placeholder="admin@example.com">
                            <small>{{ __('Use an approved test inbox. API keys and mail credentials are never shown here.') }}</small>
                        </div>
                        <div class="maint-actions">
                            <button type="submit" class="maint-email-submit">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m4 4 17 8-17 8 3-8z"/><path d="M7 12h14"/></svg>
                                {{ __('Send Test Email') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection
