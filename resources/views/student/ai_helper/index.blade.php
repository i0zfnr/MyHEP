@extends('layouts.app')

@section('title', __('AI Helper (Student)'))

@push('styles')
<style>
    .st-ai {
        width: min(100%, 1140px);
        margin: 0 auto;
        display: grid;
        grid-template-columns: minmax(0, 1.2fr) minmax(300px, .8fr);
        gap: 1rem;
        align-items: start;
    }

    .st-ai-shell,
    .st-ai-side {
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(220, 205, 191, .72);
        border-radius: 24px;
        background:
            linear-gradient(180deg, rgba(255,255,255,.90), rgba(248,242,235,.82)),
            radial-gradient(circle at 100% 0%, rgba(140, 195, 164, .12), transparent 36%);
        box-shadow: 0 20px 44px rgba(43, 34, 28, .10);
        backdrop-filter: blur(18px) saturate(128%);
        -webkit-backdrop-filter: blur(18px) saturate(128%);
    }

    body[data-theme="dark"] .st-ai-shell,
    body[data-theme="dark"] .st-ai-side {
        border-color: rgba(226, 209, 192, .14);
        background:
            linear-gradient(180deg, rgba(35,31,28,.94), rgba(20,18,16,.92)),
            radial-gradient(circle at 100% 0%, rgba(95, 190, 145, .08), transparent 36%);
        box-shadow: 0 22px 48px rgba(0, 0, 0, .30);
    }

    .st-ai-shell::before,
    .st-ai-side::before {
        content: '';
        position: absolute;
        inset: 0;
        background:
            radial-gradient(360px 220px at 100% 0%, rgba(255, 220, 180, .14) 0%, transparent 58%),
            linear-gradient(120deg, rgba(255,255,255,.08), transparent 36%);
        pointer-events: none;
    }

    .st-ai-shell > *,
    .st-ai-side > * {
        position: relative;
        z-index: 1;
    }

    .st-ai-hero {
        padding: 1.1rem 1.15rem 0;
    }

    .st-ai-hero-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
    }

    .st-ai-clock {
        font-size: 1.06rem;
        font-weight: 800;
        color: #2d221a;
        letter-spacing: .02em;
    }

    body[data-theme="dark"] .st-ai-clock {
        color: #f7efe8;
    }

    .st-ai-close {
        width: 42px;
        height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        text-decoration: none;
        border: 1px solid rgba(220, 205, 191, .8);
        background: rgba(255,255,255,.60);
        color: #3d2e24;
        transition: transform 160ms ease, background-color 160ms ease, border-color 160ms ease;
    }

    .st-ai-close:hover {
        transform: translateY(-1px);
        background: rgba(255,255,255,.88);
        border-color: rgba(191, 162, 132, .90);
    }

    body[data-theme="dark"] .st-ai-close {
        background: rgba(255,255,255,.06);
        border-color: rgba(226, 209, 192, .18);
        color: #f7efe8;
    }

    .st-ai-close svg {
        width: 20px;
        height: 20px;
    }

    .st-ai-intro {
        display: grid;
        justify-items: center;
        text-align: center;
        padding: 1rem 1.3rem .85rem;
    }

    .st-ai-orb {
        position: relative;
        width: 98px;
        height: 98px;
        border-radius: 50%;
        display: grid;
        place-items: center;
        margin-bottom: 1rem;
        background:
            radial-gradient(circle at 30% 25%, rgba(255,255,255,.82), rgba(255,255,255,.24) 34%, transparent 35%),
            linear-gradient(145deg, rgba(126, 193, 153, .30), rgba(88, 145, 113, .16));
        border: 1px solid rgba(255,255,255,.72);
        box-shadow: 0 18px 34px rgba(64, 107, 84, .14);
    }

    body[data-theme="dark"] .st-ai-orb {
        border-color: rgba(255,255,255,.10);
        background:
            radial-gradient(circle at 30% 25%, rgba(255,255,255,.24), rgba(255,255,255,.08) 34%, transparent 35%),
            linear-gradient(145deg, rgba(126, 193, 153, .18), rgba(88, 145, 113, .08));
        box-shadow: 0 20px 34px rgba(0, 0, 0, .24);
    }

    .st-ai-orb svg {
        width: 36px;
        height: 36px;
        color: #2d8b55;
    }

    .st-ai-badge {
        position: absolute;
        right: -2px;
        bottom: 12px;
        border-radius: 999px;
        padding: 4px 9px;
        font-size: .68rem;
        font-weight: 800;
        letter-spacing: .05em;
        color: #2f5a48;
        background: rgba(222, 241, 232, .94);
        border: 1px solid rgba(190, 215, 204, .78);
    }

    body[data-theme="dark"] .st-ai-badge {
        color: #dff4e8;
        background: rgba(50, 95, 74, .44);
        border-color: rgba(125, 175, 151, .40);
    }

    .st-ai-kicker {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        margin-bottom: 10px;
        padding: 7px 11px;
        border-radius: 999px;
        border: 1px solid rgba(215, 200, 186, .82);
        background: rgba(255,255,255,.58);
        color: #6f5b4c;
        font-size: .72rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .06em;
    }

    body[data-theme="dark"] .st-ai-kicker {
        color: #ceb8a2;
        background: rgba(255,255,255,.04);
        border-color: rgba(226, 209, 192, .12);
    }

    .st-ai-title {
        margin: 0;
        max-width: 620px;
        font-size: clamp(1.7rem, 4vw, 2.6rem);
        line-height: 1.08;
        font-weight: 800;
        color: #1d1611;
    }

    body[data-theme="dark"] .st-ai-title {
        color: #fff7ef;
    }

    .st-ai-sub {
        margin: 12px auto 0;
        max-width: 620px;
        color: #655344;
        font-size: .95rem;
        line-height: 1.72;
    }

    body[data-theme="dark"] .st-ai-sub {
        color: rgba(247,239,232,.76);
    }

    .st-ai-content {
        padding: 0 1.15rem 1.15rem;
        display: grid;
        gap: .95rem;
    }

    .st-ai-section-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        margin-bottom: .1rem;
    }

    .st-ai-section-title {
        margin: 0;
        color: #241a14;
        font-size: .96rem;
        font-weight: 800;
    }

    .st-ai-section-note {
        color: #7a6858;
        font-size: .8rem;
        line-height: 1.5;
    }

    body[data-theme="dark"] .st-ai-section-title {
        color: #fff7ef;
    }

    body[data-theme="dark"] .st-ai-section-note {
        color: rgba(247,239,232,.66);
    }

    .st-ai-cards {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .8rem;
    }

    .st-ai-card {
        border: 1px solid rgba(220, 205, 191, .78);
        border-radius: 16px;
        padding: .95rem;
        background:
            linear-gradient(180deg, rgba(255,255,255,.88), rgba(248,242,235,.76));
        box-shadow: inset 0 1px 0 rgba(255,255,255,.44);
    }

    body[data-theme="dark"] .st-ai-card {
        border-color: rgba(226, 209, 192, .12);
        background:
            linear-gradient(180deg, rgba(49,44,39,.76), rgba(31,28,25,.82));
    }

    .st-ai-card-label {
        color: #816d5d;
        font-size: .73rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .05em;
    }

    .st-ai-card-value {
        margin-top: .35rem;
        color: #241a14;
        font-size: 1.35rem;
        font-weight: 800;
    }

    .st-ai-card-note {
        margin-top: .4rem;
        color: #7a6858;
        font-size: .8rem;
        line-height: 1.5;
    }

    body[data-theme="dark"] .st-ai-card-label,
    body[data-theme="dark"] .st-ai-card-note {
        color: rgba(247,239,232,.68);
    }

    body[data-theme="dark"] .st-ai-card-value {
        color: #fff7ef;
    }

    .st-ai-prompts {
        display: grid;
        gap: .8rem;
    }

    .st-ai-row {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .8rem;
    }

    .st-ai-chip {
        display: flex;
        align-items: flex-start;
        gap: .8rem;
        min-height: 100%;
        border: 1px solid rgba(220, 205, 191, .78);
        border-radius: 16px;
        padding: .95rem 1rem;
        background:
            linear-gradient(180deg, rgba(255,255,255,.80), rgba(246,239,231,.72));
        color: #2d221a;
        text-decoration: none;
        box-shadow: 0 10px 18px rgba(43, 34, 28, .06);
        transition: transform 170ms ease, box-shadow 170ms ease, border-color 170ms ease;
    }

    .st-ai-chip:hover {
        transform: translateY(-2px);
        border-color: rgba(189, 158, 126, .90);
        box-shadow: 0 16px 24px rgba(43, 34, 28, .10);
    }

    body[data-theme="dark"] .st-ai-chip {
        border-color: rgba(226, 209, 192, .12);
        background:
            linear-gradient(180deg, rgba(49,44,39,.76), rgba(31,28,25,.82));
        color: #f7efe8;
    }

    .st-ai-chip-icon {
        width: 42px;
        height: 42px;
        flex: 0 0 42px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, rgba(126, 193, 153, .20), rgba(194, 223, 206, .52));
        color: #2d8b55;
        border: 1px solid rgba(126, 193, 153, .18);
    }

    body[data-theme="dark"] .st-ai-chip-icon {
        background: linear-gradient(135deg, rgba(95,190,145,.18), rgba(76, 121, 98, .26));
        color: #d8f1e2;
        border-color: rgba(95,190,145,.18);
    }

    .st-ai-chip-icon svg {
        width: 20px;
        height: 20px;
    }

    .st-ai-chip-title {
        font-size: .96rem;
        font-weight: 800;
        color: inherit;
        line-height: 1.3;
    }

    .st-ai-chip-desc {
        margin-top: 4px;
        color: #756253;
        font-size: .82rem;
        line-height: 1.55;
    }

    body[data-theme="dark"] .st-ai-chip-desc {
        color: rgba(247,239,232,.70);
    }

    .st-ai-input-card {
        border: 1px solid rgba(220, 205, 191, .78);
        border-radius: 20px;
        padding: 1rem;
        background:
            linear-gradient(180deg, rgba(255,255,255,.86), rgba(249,243,236,.82));
        box-shadow: 0 16px 28px rgba(43, 34, 28, .08);
    }

    body[data-theme="dark"] .st-ai-input-card {
        border-color: rgba(226, 209, 192, .12);
        background:
            linear-gradient(180deg, rgba(49,44,39,.76), rgba(31,28,25,.82));
    }

    .st-ai-input-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        margin-bottom: .8rem;
    }

    .st-ai-input-title {
        color: #2d221a;
        font-size: .95rem;
        font-weight: 800;
    }

    body[data-theme="dark"] .st-ai-input-title {
        color: #fff7ef;
    }

    .st-ai-input-note {
        color: #7c6958;
        font-size: .78rem;
        line-height: 1.55;
    }

    body[data-theme="dark"] .st-ai-input-note {
        color: rgba(247,239,232,.66);
    }

    .st-ai-compose {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: .65rem;
        align-items: center;
        border: 1px solid rgba(218, 200, 183, .82);
        border-radius: 999px;
        padding: .55rem .55rem .55rem .95rem;
        background: rgba(255,255,255,.74);
    }

    body[data-theme="dark"] .st-ai-compose {
        border-color: rgba(226, 209, 192, .14);
        background: rgba(19,17,15,.66);
    }

    .st-ai-input {
        width: 100%;
        border: 0;
        background: transparent;
        color: #2c3934;
        font-size: 1rem;
        outline: none;
    }

    .st-ai-input::placeholder {
        color: #8c9792;
    }

    body[data-theme="dark"] .st-ai-input {
        color: #f7efe8;
    }

    body[data-theme="dark"] .st-ai-input::placeholder {
        color: rgba(247,239,232,.44);
    }

    .st-ai-send {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        border: 1px solid rgba(113, 171, 138, .32);
        background: linear-gradient(135deg, #d8f0e2, #9ed0b0);
        color: #1b6643;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 10px 18px rgba(63, 114, 85, .16);
    }

    .st-ai-send svg {
        width: 20px;
        height: 20px;
    }

    .st-ai-disclaimer {
        margin-top: .8rem;
        color: #5c4a3d;
        font-size: .83rem;
        line-height: 1.6;
    }

    body[data-theme="dark"] .st-ai-disclaimer {
        color: rgba(247,239,232,.74);
    }

    .st-ai-side-head {
        padding: 1.05rem 1.05rem .9rem;
        border-bottom: 1px solid rgba(220, 205, 191, .64);
    }

    body[data-theme="dark"] .st-ai-side-head {
        border-bottom-color: rgba(226, 209, 192, .10);
    }

    .st-ai-side-title {
        margin: 0;
        color: #2d221a;
        font-size: 1rem;
        font-weight: 800;
    }

    .st-ai-side-sub {
        margin: .38rem 0 0;
        color: #7b6758;
        font-size: .83rem;
        line-height: 1.6;
    }

    body[data-theme="dark"] .st-ai-side-title {
        color: #fff7ef;
    }

    body[data-theme="dark"] .st-ai-side-sub {
        color: rgba(247,239,232,.68);
    }

    .st-ai-side-body {
        padding: 1rem;
        display: grid;
        gap: .85rem;
    }

    .st-ai-side-box {
        border: 1px solid rgba(220, 205, 191, .70);
        border-radius: 16px;
        padding: .95rem;
        background:
            linear-gradient(180deg, rgba(255,255,255,.80), rgba(246,239,231,.72));
    }

    body[data-theme="dark"] .st-ai-side-box {
        border-color: rgba(226, 209, 192, .12);
        background:
            linear-gradient(180deg, rgba(49,44,39,.76), rgba(31,28,25,.82));
    }

    .st-ai-side-box h4 {
        margin: 0 0 .55rem;
        font-size: .84rem;
        color: #725e4f;
        text-transform: uppercase;
        letter-spacing: .05em;
    }

    body[data-theme="dark"] .st-ai-side-box h4 {
        color: #ceb8a2;
    }

    .st-ai-side-list {
        display: grid;
        gap: .55rem;
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .st-ai-side-list li {
        color: #4e3a2c;
        font-size: .84rem;
        line-height: 1.6;
        padding-left: 1rem;
        position: relative;
    }

    .st-ai-side-list li::before {
        content: '';
        position: absolute;
        left: 0;
        top: .6rem;
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #7ac193;
    }

    body[data-theme="dark"] .st-ai-side-list li {
        color: rgba(247,239,232,.74);
    }

    .st-ai-terms {
        color: #655344;
        font-size: .83rem;
        line-height: 1.65;
    }

    .st-ai-terms a {
        color: #355bc9;
        text-decoration: none;
        font-weight: 700;
    }

    @media (max-width: 980px) {
        .st-ai {
            grid-template-columns: 1fr;
        }

        .st-ai-shell {
            order: 1;
        }

        .st-ai-side {
            order: 2;
        }
    }

    @media (max-width: 700px) {
        .st-ai {
            gap: .85rem;
        }

        .st-ai-cards,
        .st-ai-row {
            grid-template-columns: 1fr;
        }

        .st-ai-shell,
        .st-ai-side {
            border-radius: 18px;
        }

        .st-ai-title {
            font-size: 1.85rem;
            line-height: 1.1;
        }

        .st-ai-content,
        .st-ai-intro,
        .st-ai-hero,
        .st-ai-side-body,
        .st-ai-side-head {
            padding-left: .95rem;
            padding-right: .95rem;
        }

        .st-ai-hero {
            padding-top: .9rem;
        }

        .st-ai-hero-top {
            gap: .5rem;
        }

        .st-ai-clock {
            font-size: .98rem;
        }

        .st-ai-close {
            width: 38px;
            height: 38px;
        }

        .st-ai-intro {
            padding-top: .6rem;
            padding-bottom: .5rem;
        }

        .st-ai-orb {
            width: 76px;
            height: 76px;
            margin-bottom: .8rem;
        }

        .st-ai-orb svg {
            width: 28px;
            height: 28px;
        }

        .st-ai-badge {
            bottom: 8px;
            padding: 3px 8px;
            font-size: .62rem;
        }

        .st-ai-kicker {
            margin-bottom: .65rem;
            padding: 6px 10px;
            font-size: .67rem;
        }

        .st-ai-sub {
            margin-top: .7rem;
            font-size: .88rem;
            line-height: 1.62;
        }

        .st-ai-content {
            gap: .8rem;
        }

        .st-ai-input-card {
            order: -1;
            padding: .9rem;
            border-radius: 18px;
        }

        .st-ai-input-top {
            align-items: flex-start;
            flex-direction: column;
            gap: .25rem;
            margin-bottom: .7rem;
        }

        .st-ai-compose {
            grid-template-columns: 1fr 44px;
            padding: .45rem .45rem .45rem .8rem;
        }

        .st-ai-input {
            font-size: .95rem;
        }

        .st-ai-send {
            width: 44px;
            height: 44px;
        }

        .st-ai-cards {
            gap: .65rem;
        }

        .st-ai-card {
            padding: .85rem .9rem;
        }

        .st-ai-card-value {
            font-size: 1.18rem;
        }

        .st-ai-prompts {
            gap: .65rem;
        }

        .st-ai-chip {
            gap: .7rem;
            padding: .85rem .9rem;
            border-radius: 15px;
        }

        .st-ai-chip-icon {
            width: 38px;
            height: 38px;
            flex-basis: 38px;
            border-radius: 11px;
        }

        .st-ai-chip-title {
            font-size: .92rem;
        }

        .st-ai-chip-desc {
            font-size: .8rem;
            line-height: 1.45;
        }

        .st-ai-side-head {
            padding-top: .95rem;
            padding-bottom: .8rem;
        }

        .st-ai-side-body {
            gap: .75rem;
        }

        .st-ai-side-box {
            padding: .9rem;
        }

        .st-ai-side-list li {
            font-size: .82rem;
            line-height: 1.55;
        }
    }

    @media (max-width: 480px) {
        .st-ai {
            width: 100%;
        }

        .st-ai-shell,
        .st-ai-side {
            border-radius: 16px;
        }

        .st-ai-title {
            font-size: 1.72rem;
        }

        .st-ai-section-head {
            align-items: flex-start;
            flex-direction: column;
            gap: .2rem;
        }

        .st-ai-card-note,
        .st-ai-section-note,
        .st-ai-input-note,
        .st-ai-disclaimer,
        .st-ai-side-sub,
        .st-ai-terms {
            font-size: .78rem;
        }

        .st-ai-side {
            display: none;
        }
    }
</style>
@endpush

@section('header')
    <h2 style="margin:0;font-size:1.1rem;font-weight:700;color:#2d1f14;">{{ __('AI Helper (Student)') }}</h2>
@endsection

@section('content')
<div class="st-ai">
    <section class="st-ai-shell">
        <div class="st-ai-hero">
            <div class="st-ai-hero-top">
                <span class="st-ai-clock" id="miyaClock">--:--</span>
                <a href="{{ route('student.dashboard') }}" class="st-ai-close" aria-label="{{ __('Close') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M18 6L6 18"></path>
                        <path d="M6 6l12 12"></path>
                    </svg>
                </a>
            </div>
        </div>

        <div class="st-ai-intro">
            <div class="st-ai-orb" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2l1.8 5.2L19 9l-5.2 1.8L12 16l-1.8-5.2L5 9l5.2-1.8L12 2z"></path>
                </svg>
                <span class="st-ai-badge">BETA</span>
            </div>

            <span class="st-ai-kicker">{{ __('AI Helper') }}</span>
            <h3 class="st-ai-title">{{ __("Hi, I'm your AI assistant") }}</h3>
            <p class="st-ai-sub">{{ __('Ask about scholarship status, offense records, payments, rules, or basic student portal guidance in one place.') }}</p>
        </div>

        <div class="st-ai-content">
            <div class="st-ai-input-card">
                <div class="st-ai-input-top">
                    <div>
                        <div class="st-ai-input-title">{{ __('Start a question') }}</div>
                        <div class="st-ai-input-note">{{ __('Use a quick topic below or type your own question here.') }}</div>
                    </div>
                </div>
                <div class="st-ai-compose">
                    <input class="st-ai-input" type="text" value="" placeholder="{{ __('Ask Changxie something') }}" disabled>
                    <button type="button" class="st-ai-send" aria-label="{{ __('Voice input') }}" disabled>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 1a3 3 0 0 1 3 3v8a3 3 0 0 1-6 0V4a3 3 0 0 1 3-3z"></path>
                            <path d="M19 10v2a7 7 0 0 1-14 0v-2"></path>
                            <path d="M12 19v4"></path>
                            <path d="M8 23h8"></path>
                        </svg>
                    </button>
                </div>
                <p class="st-ai-disclaimer">{{ __('Beta: Changxie is still learning. Independently verify before use.') }}</p>
            </div>

            <div class="st-ai-cards">
                <article class="st-ai-card">
                    <div class="st-ai-card-label">{{ __('Module') }}</div>
                    <div class="st-ai-card-value">{{ __('Student Portal') }}</div>
                    <div class="st-ai-card-note">{{ __('Built for scholarship and discipline guidance.') }}</div>
                </article>
                <article class="st-ai-card">
                    <div class="st-ai-card-label">{{ __('Mode') }}</div>
                    <div class="st-ai-card-value">Beta</div>
                    <div class="st-ai-card-note">{{ __('Answers should still be verified when important.') }}</div>
                </article>
                <article class="st-ai-card">
                    <div class="st-ai-card-label">{{ __('Status') }}</div>
                    <div class="st-ai-card-value">{{ __('Ready') }}</div>
                    <div class="st-ai-card-note">{{ __('Choose a prompt or type your own question below.') }}</div>
                </article>
            </div>

            <div class="st-ai-prompts">
                <div class="st-ai-section-head">
                    <h4 class="st-ai-section-title">{{ __('Quick actions') }}</h4>
                    <div class="st-ai-section-note">{{ __('Tap a topic to understand what you can ask.') }}</div>
                </div>

                <div class="st-ai-row">
                    <a href="#" class="st-ai-chip">
                        <span class="st-ai-chip-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                            </svg>
                        </span>
                        <span>
                            <span class="st-ai-chip-title">{{ __('What can Changxie help me with?') }}</span>
                            <span class="st-ai-chip-desc">{{ __('See the main things this assistant can explain for students.') }}</span>
                        </span>
                    </a>
                    <a href="#" class="st-ai-chip">
                        <span class="st-ai-chip-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="4" width="18" height="16" rx="2"></rect>
                                <path d="M7 8h10"></path>
                                <path d="M7 12h6"></path>
                            </svg>
                        </span>
                        <span>
                            <span class="st-ai-chip-title">{{ __('My Scholarship') }}</span>
                            <span class="st-ai-chip-desc">{{ __('Ask about scholarship records, sponsor, or current status.') }}</span>
                        </span>
                    </a>
                </div>

                <div class="st-ai-row">
                    <a href="#" class="st-ai-chip">
                        <span class="st-ai-chip-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 1v22"></path>
                                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7H14.5a3.5 3.5 0 0 1 0 7H6"></path>
                            </svg>
                        </span>
                        <span>
                            <span class="st-ai-chip-title">{{ __('Make Payment Now') }}</span>
                            <span class="st-ai-chip-desc">{{ __('Ask how to review fines or continue payment-related steps.') }}</span>
                        </span>
                    </a>
                    <a href="#" class="st-ai-chip">
                        <span class="st-ai-chip-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                            </svg>
                        </span>
                        <span>
                            <span class="st-ai-chip-title">{{ __('View Rules') }}</span>
                            <span class="st-ai-chip-desc">{{ __('Ask for rules, procedures, or what to do before applying.') }}</span>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <aside class="st-ai-side">
        <div class="st-ai-side-head">
            <h3 class="st-ai-side-title">{{ __('Quick Guide') }}</h3>
            <p class="st-ai-side-sub">{{ __('Use this helper for light guidance before you move to the actual scholarship or discipline page.') }}</p>
        </div>

        <div class="st-ai-side-body">
            <section class="st-ai-side-box">
                <h4>{{ __('Good topics to ask') }}</h4>
                <ul class="st-ai-side-list">
                    <li>{{ __('How do I check my scholarship status?') }}</li>
                    <li>{{ __('How do I review my offense record?') }}</li>
                    <li>{{ __('How do I submit a fine payment application?') }}</li>
                    <li>{{ __('Where can I read the latest scholarship announcements?') }}</li>
                </ul>
            </section>

            <section class="st-ai-side-box">
                <h4>{{ __('Before you rely on an answer') }}</h4>
                <ul class="st-ai-side-list">
                    <li>{{ __('Double-check important dates, amounts, and status changes in the actual module page.') }}</li>
                    <li>{{ __('Use the official scholarship, offense, or settings page for final actions.') }}</li>
                </ul>
            </section>

            <section class="st-ai-side-box">
                <h4>{{ __('Terms') }}</h4>
                <div class="st-ai-terms">{{ __('By messaging Changxie, you agree to our') }} <a href="#">{{ __('Terms') }}</a>.</div>
            </section>
        </div>
    </aside>
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
