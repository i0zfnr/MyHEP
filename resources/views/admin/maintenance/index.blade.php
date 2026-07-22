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
    </div>
</div>
@endsection
