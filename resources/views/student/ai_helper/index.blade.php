@extends('layouts.app')

@section('title', __('AI Helper (Student)'))

@push('styles')
<style>
    .miya-page {
        width: min(100%, 520px);
        margin: 0 auto;
        min-height: calc(100vh - 170px);
        border-radius: 22px;
        border: 1px solid rgba(255,255,255,.72);
        background:
            radial-gradient(760px 320px at 50% -10%, rgba(215,233,228,.86) 0%, rgba(225,236,233,.58) 48%, transparent 76%),
            linear-gradient(145deg, rgba(255,255,255,.58), rgba(232,240,237,.38));
        box-shadow: 0 24px 58px rgba(26, 44, 37, .14), inset 0 1px 0 rgba(255,255,255,.78);
        backdrop-filter: blur(22px) saturate(142%);
        -webkit-backdrop-filter: blur(22px) saturate(142%);
        padding: 18px 16px 22px;
        position: relative;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    .miya-page::before {
        content: '';
        position: absolute;
        inset: 0;
        background:
            linear-gradient(120deg, rgba(255,255,255,.50), transparent 42%),
            radial-gradient(circle at 86% 18%, rgba(48,165,59,.10), transparent 32%);
        pointer-events: none;
    }
    .miya-page > * { position: relative; z-index: 1; }

    .miya-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        margin-bottom: 16px;
    }
    .miya-clock {
        font-size: 1.05rem;
        font-weight: 700;
        color: #14261f;
        letter-spacing: .02em;
    }
    .miya-close {
        width: 40px;
        height: 40px;
        border: none;
        background: rgba(255,255,255,.28);
        border: 1px solid rgba(255,255,255,.46);
        border-radius: 50%;
        color: #0f1f19;
        font-size: 2rem;
        line-height: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
    }

    .miya-logo-wrap {
        display: flex;
        justify-content: center;
        margin: 6px 0 26px;
    }
    .miya-logo {
        width: 86px;
        height: 86px;
        border-radius: 50%;
        background: rgba(255,255,255,.62);
        border: 1px solid rgba(255,255,255,.78);
        box-shadow: 0 14px 30px rgba(59, 103, 84, .16), inset 0 0 0 5px rgba(196, 233, 216, .32);
        backdrop-filter: blur(16px) saturate(140%);
        -webkit-backdrop-filter: blur(16px) saturate(140%);
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .miya-logo-mark {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        color: #30a53b;
        font-size: 1.45rem;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .miya-beta {
        position: absolute;
        right: -4px;
        bottom: 12px;
        border-radius: 999px;
        font-size: .66rem;
        font-weight: 700;
        letter-spacing: .03em;
        padding: 2px 8px;
        border: 1px solid rgba(190,215,204,.78);
        color: #2f5a48;
        background: rgba(216,238,229,.82);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
    }

    .miya-main {
        display: flex;
        flex-direction: column;
        align-items: center;
        margin-top: 20px;
        text-align: center;
    }
    .miya-title {
        margin: 0;
        font-size: clamp(1.45rem, 5.5vw, 2.05rem);
        line-height: 1.2;
        color: #111f1a;
        font-weight: 600;
        letter-spacing: .01em;
    }
    .miya-suggests {
        margin-top: 18px;
        width: 100%;
        max-width: 430px;
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        justify-content: center;
    }
    .miya-chip {
        border: 1px solid rgba(255,255,255,.68);
        border-radius: 14px;
        background: rgba(255, 255, 255, .48);
        backdrop-filter: blur(14px) saturate(132%);
        -webkit-backdrop-filter: blur(14px) saturate(132%);
        color: #14261f;
        font-size: clamp(1rem, 3.6vw, 1.05rem);
        font-weight: 500;
        padding: 12px 16px;
        line-height: 1.1;
        white-space: nowrap;
        box-shadow: inset 0 1px 0 rgba(255,255,255,.62), 0 8px 18px rgba(26,44,37,.07);
    }
    .miya-terms {
        margin: 18px 0 0;
        color: #4d5f58;
        font-size: .98rem;
    }
    .miya-terms a {
        color: #3f5fc4;
        text-decoration: none;
    }

    .miya-footer {
        margin-top: auto;
        padding-top: 20px;
    }
    .miya-input-wrap {
        width: 100%;
        border: 1px solid rgba(255,255,255,.78);
        border-radius: 999px;
        background: rgba(255, 255, 255, .62);
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 10px 10px 16px;
        box-shadow: 0 16px 34px rgba(26, 44, 37, .12), inset 0 1px 0 rgba(255,255,255,.78);
        backdrop-filter: blur(18px) saturate(140%);
        -webkit-backdrop-filter: blur(18px) saturate(140%);
    }
    .miya-input {
        flex: 1;
        border: none;
        background: transparent;
        font-size: clamp(1.1rem, 4.3vw, 1.3rem);
        color: #2c3934;
        outline: none;
    }
    .miya-input::placeholder { color: #8c9792; }
    .miya-mic {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        border: none;
        background: rgba(245,251,248,.82);
        border: 1px solid rgba(255,255,255,.74);
        color: #1d6a4a;
        font-size: 1.6rem;
        box-shadow: 0 8px 14px rgba(28, 78, 56, .18);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
    }
    .miya-note {
        margin: 10px 0 0;
        text-align: center;
        color: #4f5f59;
        font-weight: 500;
        font-size: .95rem;
    }

    @media (max-width: 640px) {
        .miya-page {
            min-height: calc(100vh - 136px);
            border-radius: 0;
            border-left: none;
            border-right: none;
            width: calc(100% + 2rem);
            margin-left: -1rem;
            margin-right: -1rem;
            box-shadow: inset 0 1px 0 rgba(255,255,255,.68);
            padding: 16px 16px 14px;
        }
        .miya-main { margin-top: 12px; }
        .miya-logo-wrap { margin-bottom: 20px; }
        .miya-note { font-size: .9rem; }
    }
</style>
@endpush

@section('header')
    <h2 style="margin:0;font-size:1.1rem;font-weight:700;color:#2d1f14;">{{ __('AI Helper (Student)') }}</h2>
@endsection

@section('content')
<div class="miya-page">
    <div class="miya-top">
        <span class="miya-clock" id="miyaClock">--:--</span>
        <a href="{{ route('student.dashboard') }}" class="miya-close" aria-label="{{ __('Close') }}">×</a>
    </div>

    <div class="miya-logo-wrap">
        <div class="miya-logo" aria-hidden="true">
            <span class="miya-logo-mark">✶</span>
            <span class="miya-beta">BETA</span>
        </div>
    </div>

    <div class="miya-main">
        <h3 class="miya-title">{{ __("Hi, I'm your AI assistant") }}</h3>

        <div class="miya-suggests">
            <span class="miya-chip">✨ {{ __('What can Changxie help me with?') }}</span>
            <span class="miya-chip">🧾 {{ __('My bill') }}</span>
            <span class="miya-chip">🌍 {{ __('Roaming') }}</span>
            <span class="miya-chip">📝 {{ __('My contract') }}</span>
        </div>

        <p class="miya-terms">{{ __('By messaging Changxie, you agree to our') }} <a href="#">{{ __('Terms') }}</a>.</p>
    </div>

    <div class="miya-footer">
        <div class="miya-input-wrap">
            <input class="miya-input" type="text" value="" placeholder="{{ __('Ask Changxie something') }}" disabled>
            <button type="button" class="miya-mic" aria-label="{{ __('Voice input') }}" disabled>🎙</button>
        </div>
        <p class="miya-note">{{ __('Beta: Changxie is still learning. Independently verify before use.') }}</p>
    </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const clockNode = document.getElementById('miyaClock');
    if (!clockNode) return;
    const locale = @json(app()->getLocale() === 'ms' ? 'ms-MY' : 'en-GB');
    const tick = () => {
        clockNode.textContent = new Date().toLocaleTimeString(locale, {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        });
    };
    tick();
    setInterval(tick, 1000);
})();
</script>
@endpush
