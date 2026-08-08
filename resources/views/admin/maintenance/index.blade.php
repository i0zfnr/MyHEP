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
    .maint-push-layout { display: grid; gap: 1rem; }
    @media (min-width: 760px) { .maint-push-layout { grid-template-columns: .8fr 1.2fr; } }
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
</style>
@endpush

@section('header')
    <h2 style="margin:0;font-size:1rem;font-weight:700;color:var(--text,#2d1f14);">System Maintenance</h2>
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
        <h3>Maintenance Control</h3>
        <p>Use this page before planned downtime, database work, or system updates. When enabled, normal visitors will see Laravel maintenance mode while admins can continue through the bypass URL.</p>
    </section>

    <div class="maint-grid">
        <section class="maint-card">
            <div class="maint-card-head">Current Status</div>
            <div class="maint-card-body">
                <div class="maint-row">
                    <span class="maint-key">Mode</span>
                    <span class="maint-val">{{ $maintenance['enabled'] ? 'Maintenance enabled' : 'System public' }}</span>
                </div>
                <div class="maint-row">
                    <span class="maint-key">Retry After</span>
                    <span class="maint-val">{{ $maintenance['retry'] ? $maintenance['retry'] . ' seconds' : '-' }}</span>
                </div>
                <div class="maint-row">
                    <span class="maint-key">Server Time</span>
                    <span class="maint-val">{{ $maintenance['server_time'] }}</span>
                </div>
                <div class="maint-row">
                    <span class="maint-key">System Cache</span>
                    <span class="maint-val">{{ $maintenance['cache_enabled'] ? 'Enabled' : 'Disabled' }}</span>
                </div>
                <div class="maint-row">
                    <span class="maint-key">Tracked Cache Keys</span>
                    <span class="maint-val">{{ $maintenance['cache_key_count'] }}</span>
                </div>
                <div class="maint-row">
                    <span class="maint-key">Last Cache Clear</span>
                    <span class="maint-val">{{ $maintenance['cache_last_cleared_at'] ?: '-' }}</span>
                </div>
                <div class="maint-row">
                    <span class="maint-key">Cache Toggle Updated</span>
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
            <div class="maint-card-head">Actions</div>
            <div class="maint-card-body">
                <div class="maint-note">
                    Save the bypass URL after enabling maintenance. If you lose browser access while maintenance is enabled, run <strong>php artisan up</strong> from the terminal.
                </div>

                <div class="maint-actions">
                    @if($maintenance['enabled'])
                        <form method="POST" action="{{ route('admin.maintenance.update') }}">
                            @csrf
                            <input type="hidden" name="action" value="disable">
                            <button type="submit" class="maint-btn ok">Disable Maintenance</button>
                        </form>
                    @else
            <form method="POST" action="{{ route('admin.maintenance.update') }}"
                data-confirm-title="{{ __('Enable maintenance mode') }}"
                data-confirm-message="{{ __('Enable maintenance mode now? Visitors will be blocked until it is disabled.') }}"
                data-confirm-action="{{ __('Enable') }}">
                            @csrf
                            <input type="hidden" name="action" value="enable">
                            <button type="submit" class="maint-btn warn">Enable Maintenance</button>
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
                            <button type="submit" class="maint-btn warn">Disable Cache</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('admin.maintenance.update') }}">
                            @csrf
                            <input type="hidden" name="action" value="cache_enable">
                            <button type="submit" class="maint-btn ok">Enable Cache</button>
                        </form>
                    @endif
                </div>
            </div>
        </section>

        <section class="maint-card push-centre">
            <div class="maint-card-head">Push Notification Centre</div>
            <div class="maint-card-body">
                <div class="maint-push-layout">
                    <div class="maint-push-panel">
                        <h4>Test this admin device</h4>
                        <p>Send a private test notification only to devices registered under your System Admin account.</p>
                        <div class="maint-stats">
                            <div class="maint-stat"><strong>{{ $pushSubscriptions['current_admin_devices'] }}</strong><span>Your devices</span></div>
                            <div class="maint-stat"><strong>{{ $pushSubscriptions['devices'] }}</strong><span>All active devices</span></div>
                            <div class="maint-stat"><strong>{{ $pushSubscriptions['students'] }}</strong><span>Subscribed students</span></div>
                            <div class="maint-stat"><strong>{{ $pushSubscriptions['admins'] }}</strong><span>Subscribed admins</span></div>
                        </div>
                        <form method="POST" action="{{ route('admin.maintenance.push.test') }}">
                            @csrf
                            <button type="submit" class="maint-btn ok">Send Test Notification</button>
                        </form>
                    </div>

                    <div class="maint-push-panel">
                        <h4>Announce scheduled maintenance</h4>
                        <p>Notify every subscribed student and admin. This announcement does not enable maintenance mode automatically.</p>
                        <form method="POST" action="{{ route('admin.maintenance.push.broadcast') }}"
                            data-confirm-title="Send maintenance notification"
                            data-confirm-message="Send this announcement to all subscribed students and admins?"
                            data-confirm-action="Send notification">
                            @csrf
                            <div class="maint-fields two">
                                <div class="maint-field">
                                    <label for="starts_at">Maintenance starts</label>
                                    <input id="starts_at" name="starts_at" type="datetime-local" required min="{{ now()->format('Y-m-d\TH:i') }}" value="{{ old('starts_at', now()->addHour()->startOfHour()->format('Y-m-d\TH:i')) }}">
                                </div>
                                <div class="maint-field">
                                    <label for="ends_at">Expected completion (optional)</label>
                                    <input id="ends_at" name="ends_at" type="datetime-local" value="{{ old('ends_at') }}">
                                </div>
                            </div>
                            <div class="maint-field" style="margin-top:.75rem;">
                                <label for="message">Custom message (optional)</label>
                                <textarea id="message" name="message" maxlength="300" placeholder="Leave blank to use the automatic message with the selected schedule.">{{ old('message') }}</textarea>
                                <small>Maximum 300 characters. The schedule is added automatically only when this field is blank.</small>
                            </div>
                            <div class="maint-actions">
                                <button type="submit" class="maint-btn warn">Send Maintenance Notification</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection
