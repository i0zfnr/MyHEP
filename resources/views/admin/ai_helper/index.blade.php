@extends('layouts.app')

@section('title', __('AI Helper (Admin)'))

@push('styles')
<style>
    .ai-admin {
        --ai-panel-border: rgba(214, 194, 169, .72);
        --ai-panel-bg: linear-gradient(180deg, rgba(255, 252, 248, .95), rgba(247, 239, 229, .92));
        --ai-panel-shadow: 0 18px 36px rgba(52, 38, 25, .14);
        --ai-head-title: #2d1f14;
        --ai-head-sub: #705a48;
        --ai-badge-border: #d1b394;
        --ai-badge-bg: rgba(201, 152, 103, .18);
        --ai-badge-text: #7a4c1b;
        --ai-chat-bg-start: #fbf5ee;
        --ai-chat-bg-end: #f3e8db;
        --ai-msg-user-bg: linear-gradient(135deg, rgba(76, 103, 132, .92), rgba(55, 76, 101, .92));
        --ai-msg-user-border: rgba(86, 116, 149, .45);
        --ai-msg-user-text: #f3f8ff;
        --ai-msg-ai-bg: linear-gradient(180deg, rgba(255, 253, 250, .96), rgba(250, 242, 232, .92));
        --ai-msg-ai-border: rgba(214, 190, 165, .7);
        --ai-msg-ai-text: #312319;
        --ai-msg-meta: #826c57;
        --ai-toolbar-bg: rgba(250, 242, 233, .92);
        --ai-btn-border: #ccb194;
        --ai-btn-bg: rgba(201, 152, 103, .10);
        --ai-btn-text: #5f432f;
        --ai-compose-bg: rgba(255, 250, 244, .94);
        --ai-input-border: #cfb497;
        --ai-input-bg: #fffdfa;
        --ai-input-text: #2e1e13;
        --ai-input-placeholder: #89715d;
        --ai-send-border: #b68b60;
        --ai-send-bg: linear-gradient(135deg, #c99867, #e0b182);
        --ai-send-text: #25190f;
        --ai-hint: #7a6554;
        --ai-card-border: rgba(215, 193, 170, .8);
        --ai-card-bg: linear-gradient(180deg, rgba(255, 252, 247, .95), rgba(248, 241, 233, .92));
        --ai-card-title: #7c6451;
        --ai-task-border: rgba(206, 180, 150, .72);
        --ai-task-bg: linear-gradient(180deg, rgba(255, 251, 246, .96), rgba(246, 235, 221, .92));
        --ai-task-text: #4e3524;
        --ai-label: #735d4a;
        --ai-field-border: #cfb497;
        --ai-field-bg: #fffdfa;
        --ai-field-text: #2e1e13;
        --ai-kv: #5f4635;
        --ai-kv-strong: #2d1f14;
        display: grid;
        grid-template-columns: minmax(0, 1.5fr) minmax(320px, .9fr);
        gap: 1.1rem;
        width: min(100%, 1180px);
        margin: 0 auto;
        align-items: start;
    }

    body[data-theme="dark"] .ai-admin {
        --ai-panel-border: rgba(126, 114, 102, .58);
        --ai-panel-bg: linear-gradient(180deg, rgba(34, 31, 28, .94), rgba(23, 21, 19, .92));
        --ai-panel-shadow: 0 18px 34px rgba(0, 0, 0, .38);
        --ai-head-title: #f2e5d5;
        --ai-head-sub: #b9a795;
        --ai-badge-border: #8f765d;
        --ai-badge-bg: rgba(201, 152, 103, .2);
        --ai-badge-text: #e9c7a2;
        --ai-chat-bg-start: rgba(43, 38, 34, .96);
        --ai-chat-bg-end: rgba(27, 24, 21, .98);
        --ai-msg-user-bg: linear-gradient(135deg, rgba(56, 83, 110, .96), rgba(37, 55, 74, .94));
        --ai-msg-user-border: rgba(127, 168, 204, .45);
        --ai-msg-user-text: #d9ebff;
        --ai-msg-ai-bg: linear-gradient(180deg, rgba(59, 51, 44, .92), rgba(40, 35, 31, .90));
        --ai-msg-ai-border: rgba(130, 113, 96, .52);
        --ai-msg-ai-text: #f3e6d7;
        --ai-msg-meta: #bda791;
        --ai-toolbar-bg: rgba(30, 27, 24, .92);
        --ai-btn-border: #866a4e;
        --ai-btn-bg: rgba(201, 152, 103, .14);
        --ai-btn-text: #f1d9bf;
        --ai-compose-bg: rgba(30, 27, 24, .92);
        --ai-input-border: #7f6955;
        --ai-input-bg: rgba(22, 20, 18, .9);
        --ai-input-text: #f3e6d7;
        --ai-input-placeholder: #a88f78;
        --ai-send-border: #b68b60;
        --ai-send-bg: linear-gradient(135deg, #c99867, #e0b182);
        --ai-send-text: #23180f;
        --ai-hint: #9f8872;
        --ai-card-border: rgba(123, 109, 94, .56);
        --ai-card-bg: linear-gradient(180deg, rgba(47, 41, 36, .90), rgba(33, 29, 25, .88));
        --ai-card-title: #c8b29a;
        --ai-task-border: rgba(141, 119, 95, .58);
        --ai-task-bg: linear-gradient(180deg, rgba(77, 67, 57, .72), rgba(59, 50, 42, .76));
        --ai-task-text: #f1dbc4;
        --ai-label: #bda58e;
        --ai-field-border: #7f6955;
        --ai-field-bg: rgba(22, 20, 18, .9);
        --ai-field-text: #f3e6d7;
        --ai-kv: #d2bcaa;
        --ai-kv-strong: #f4e6d7;
    }

    .ai-panel {
        border: 1px solid var(--ai-panel-border);
        border-radius: 18px;
        background: var(--ai-panel-bg);
        box-shadow: var(--ai-panel-shadow);
        overflow: hidden;
        backdrop-filter: blur(18px) saturate(128%);
        -webkit-backdrop-filter: blur(18px) saturate(128%);
    }

    .ai-head {
        padding: 1.05rem 1.1rem;
        border-bottom: 1px solid rgba(221, 209, 195, .7);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        background: linear-gradient(180deg, rgba(255, 255, 255, .22), rgba(255, 255, 255, .08));
    }

    .ai-head h3 {
        margin: 0;
        font-size: 1.12rem;
        color: var(--ai-head-title);
    }

    .ai-sub {
        margin: .38rem 0 0;
        color: var(--ai-head-sub);
        font-size: .84rem;
        line-height: 1.55;
    }

    .ai-badges {
        display: flex;
        gap: .45rem;
    }

    .ai-badge {
        border: 1px solid var(--ai-badge-border);
        background: var(--ai-badge-bg);
        color: var(--ai-badge-text);
        border-radius: 999px;
        padding: .26rem .62rem;
        font-size: .69rem;
        font-weight: 800;
        letter-spacing: .04em;
    }

    .ai-chat-log {
        padding: 1.1rem;
        min-height: 500px;
        max-height: 62vh;
        overflow: auto;
        display: grid;
        gap: .95rem;
        background: linear-gradient(180deg, var(--ai-chat-bg-start), var(--ai-chat-bg-end));
    }

    .msg {
        max-width: 88%;
        border-radius: 16px;
        padding: .9rem 1rem;
        font-size: .9rem;
        line-height: 1.65;
        box-shadow: 0 10px 22px rgba(46, 33, 21, .08);
    }

    .msg.user {
        margin-left: auto;
        background: var(--ai-msg-user-bg);
        border: 1px solid var(--ai-msg-user-border);
        color: var(--ai-msg-user-text);
        border-bottom-right-radius: 8px;
    }

    .msg.ai {
        margin-right: auto;
        background: var(--ai-msg-ai-bg);
        border: 1px solid var(--ai-msg-ai-border);
        color: var(--ai-msg-ai-text);
        border-bottom-left-radius: 8px;
    }

    .msg-meta {
        display: block;
        margin-top: .45rem;
        color: var(--ai-msg-meta);
        font-size: .74rem;
    }

    .ai-toolbar {
        display: flex;
        gap: .55rem;
        flex-wrap: wrap;
        padding: .95rem 1rem;
        border-top: 1px solid rgba(221, 209, 195, .7);
        background: var(--ai-toolbar-bg);
    }

    .ai-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--ai-btn-border);
        background: var(--ai-btn-bg);
        color: var(--ai-btn-text);
        border-radius: 10px;
        padding: .52rem .78rem;
        font-size: .78rem;
        font-weight: 800;
        text-decoration: none;
        transition: transform 160ms ease, box-shadow 160ms ease, border-color 160ms ease;
    }

    .ai-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 16px rgba(46, 33, 21, .10);
    }

    .ai-compose {
        border-top: 1px solid rgba(221, 209, 195, .7);
        padding: .95rem 1rem 1rem;
        background: var(--ai-compose-bg);
    }

    .ai-compose-row {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: .55rem;
    }

    .ai-input {
        border: 1px solid var(--ai-input-border);
        border-radius: 12px;
        padding: .78rem .88rem;
        background: var(--ai-input-bg);
        color: var(--ai-input-text);
        font-size: .9rem;
    }

    .ai-input::placeholder {
        color: var(--ai-input-placeholder);
    }

    .ai-send {
        border: 1px solid var(--ai-send-border);
        background: var(--ai-send-bg);
        color: var(--ai-send-text);
        border-radius: 12px;
        padding: .78rem 1.05rem;
        font-weight: 800;
        font-size: .86rem;
        box-shadow: 0 10px 18px rgba(182, 139, 96, .20);
    }

    .ai-hint {
        margin: .55rem 0 0;
        color: var(--ai-hint);
        font-size: .74rem;
    }

    .ops-body {
        padding: 1rem;
        display: grid;
        gap: .95rem;
    }

    .ops-card {
        border: 1px solid var(--ai-card-border);
        background: var(--ai-card-bg);
        border-radius: 14px;
        padding: .9rem;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .14);
    }

    .ops-title {
        margin: 0 0 .7rem;
        font-size: .84rem;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: var(--ai-card-title);
    }

    .task-list {
        display: grid;
        gap: .45rem;
    }

    .task-btn {
        width: 100%;
        text-align: left;
        border: 1px solid var(--ai-task-border);
        background: var(--ai-task-bg);
        color: var(--ai-task-text);
        border-radius: 10px;
        padding: .72rem;
        font-size: .82rem;
        font-weight: 800;
        transition: transform 160ms ease, box-shadow 160ms ease, border-color 160ms ease;
    }

    .task-btn:hover {
        transform: translateY(-1px);
        border-color: rgba(190, 156, 122, .92);
        box-shadow: 0 10px 16px rgba(46, 33, 21, .08);
    }

    .ops-field {
        display: grid;
        gap: .3rem;
        margin-bottom: .5rem;
    }

    .ops-field label {
        font-size: .75rem;
        color: var(--ai-label);
        font-weight: 700;
    }

    .ops-field input,
    .ops-field select {
        width: 100%;
        border: 1px solid var(--ai-field-border);
        border-radius: 10px;
        padding: .65rem .72rem;
        font-size: .82rem;
        background: var(--ai-field-bg);
        color: var(--ai-field-text);
    }

    .ops-field input::placeholder {
        color: var(--ai-input-placeholder);
    }

    .ops-kv {
        margin: 0;
        display: grid;
        gap: .35rem;
        padding: 0;
    }

    .ops-kv li {
        list-style: none;
        display: flex;
        justify-content: space-between;
        gap: .75rem;
        font-size: .78rem;
        color: var(--ai-kv);
        padding: .3rem 0;
        border-bottom: 1px dashed rgba(209, 187, 162, .35);
    }

    .ops-kv li:last-child {
        border-bottom: 0;
    }

    .ops-kv strong {
        color: var(--ai-kv-strong);
        text-transform: uppercase;
        font-size: .72rem;
        letter-spacing: .04em;
    }

    @media (max-width: 980px) {
        .ai-admin {
            grid-template-columns: 1fr;
        }

        .ai-chat-log {
            min-height: 320px;
            max-height: 48vh;
        }
    }
</style>
@endpush

@section('header')
    <h2 style="margin:0;font-size:1.1rem;font-weight:700;color:#2d1f14;">{{ __('AI Helper (Admin)') }}</h2>
@endsection

@section('content')
<div class="ai-admin">
    <section class="ai-panel">
        <div class="ai-head">
            <div>
                <h3>{{ __('AI Assistant for Admin Operations') }}</h3>
                <p class="ai-sub">{{ __('Use templates and filters to generate actionable admin outputs quickly.') }}</p>
            </div>
            <div class="ai-badges">
                <span class="ai-badge">BETA</span>
                <span class="ai-badge" id="aiClock">--:--</span>
            </div>
        </div>

        <div class="ai-chat-log" aria-live="polite">
            <article class="msg ai">
                {{ __('Ready. Choose a task template or enter a custom request.') }}
                <span class="msg-meta">{{ __('Scope: students, scholarships, offenses, applications') }} · <span id="aiStamp">{{ now()->format('Y-m-d H:i') }}</span></span>
            </article>
            <article class="msg user">
                {{ __('Generate monthly report for current month and list pending fine applications.') }}
            </article>
            <article class="msg ai">
                {{ __('Template selected. Set report month in the right panel, then press Send.') }}
            </article>
        </div>

        <div class="ai-toolbar">
            <button type="button" class="ai-btn">{{ __('Copy') }}</button>
            <button type="button" class="ai-btn">{{ __('Export CSV') }}</button>
            <button type="button" class="ai-btn">{{ __('Export PDF') }}</button>
            <button type="button" class="ai-btn">{{ __('Create Draft Announcement') }}</button>
            <button type="button" class="ai-btn">{{ __('Regenerate') }}</button>
        </div>

        <div class="ai-compose">
            <div class="ai-compose-row">
                <input class="ai-input" type="text" placeholder="{{ __('Type admin instruction...') }}">
                <button type="button" class="ai-send">{{ __('Send') }}</button>
            </div>
            <p class="ai-hint">{{ __('Enter sends. Shift+Enter adds newline. Independently verify AI-generated conclusions.') }}</p>
        </div>
    </section>

    <aside class="ai-panel">
        <div class="ai-head">
            <div>
                <h3>{{ __('Quick Actions & Filters') }}</h3>
                <p class="ai-sub">{{ __('Pre-structured prompts for common admin workflows.') }}</p>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="ai-btn">{{ __('Back') }}</a>
        </div>

        <div class="ops-body">
            <section class="ops-card">
                <h4 class="ops-title">{{ __('Task Templates') }}</h4>
                <div class="task-list">
                    <button type="button" class="task-btn">{{ __('Generate Monthly Report') }}</button>
                    <button type="button" class="task-btn">{{ __('Review Pending Fine Applications') }}</button>
                    <button type="button" class="task-btn">{{ __('Find Student by Matric No') }}</button>
                    <button type="button" class="task-btn">{{ __('Summarize Scholarship Status') }}</button>
                </div>
            </section>

            <section class="ops-card">
                <h4 class="ops-title">{{ __('Task Filters') }}</h4>
                <div class="ops-field">
                    <label for="reportMonth">{{ __('Report Month') }}</label>
                    <input id="reportMonth" type="month">
                </div>
                <div class="ops-field">
                    <label for="statusFilter">{{ __('Status Filter') }}</label>
                    <select id="statusFilter">
                        <option>{{ __('All') }}</option>
                        <option>{{ __('Pending') }}</option>
                        <option>{{ __('Approved') }}</option>
                        <option>{{ __('Rejected') }}</option>
                    </select>
                </div>
                <div class="ops-field">
                    <label for="matricFilter">{{ __('Matric Number') }}</label>
                    <input id="matricFilter" type="text" placeholder="23DIB23F1001">
                </div>
            </section>

            <section class="ops-card">
                <h4 class="ops-title">{{ __('Data Sources') }}</h4>
                <ul class="ops-kv">
                    <li><span>{{ __('students') }}</span><strong>table</strong></li>
                    <li><span>{{ __('scholarships') }}</span><strong>table</strong></li>
                    <li><span>{{ __('offenses') }}</span><strong>table</strong></li>
                    <li><span>{{ __('fine_payment_applications') }}</span><strong>table</strong></li>
                </ul>
            </section>
        </div>
    </aside>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const clockNode = document.getElementById('aiClock');
    const stampNode = document.getElementById('aiStamp');
    if (!clockNode) return;
    const locale = @json(app()->getLocale() === 'ms' ? 'ms-MY' : 'en-GB');
    const tick = () => {
        const now = new Date();
        clockNode.textContent = now.toLocaleTimeString(locale, {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        });
        if (stampNode) {
            stampNode.textContent = now.toLocaleString(locale, {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            });
        }
    };
    tick();
    setInterval(tick, 1000);
})();
</script>
@endpush
