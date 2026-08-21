@php
    $studentAiMode = $studentAiMode ?? false;
    $lecturerAiMode = $lecturerAiMode ?? false;
    $textOnlyAiMode = $studentAiMode || $lecturerAiMode;
    $canUploadAiFiles = ! $studentAiMode;
    $aiPageTitle = $studentAiMode ? __('AI Helper (Student)') : ($lecturerAiMode ? __('AI Helper (Staff)') : __('AI Helper (Admin)'));
@endphp
@extends('layouts.app')

@section('title', $aiPageTitle)

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
        position:relative;
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
        font-family: inherit;
        resize: vertical;
        min-height: 46px;
        max-height: 160px;
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
        cursor: pointer;
    }

    .ai-send:disabled,
    .ai-btn:disabled,
    .task-btn:disabled {
        cursor: not-allowed;
        opacity: .62;
        transform: none;
    }

    .msg.error {
        margin-right: auto;
        background: rgba(185, 28, 28, .10);
        border: 1px solid rgba(185, 28, 28, .28);
        color: #991b1b;
    }

    body[data-theme="dark"] .msg.error {
        background: rgba(127, 29, 29, .22);
        border-color: rgba(252, 165, 165, .34);
        color: #fecaca;
    }

    .msg.loading {
        opacity: .78;
    }

    .msg pre {
        margin: .5rem 0 0;
        white-space: pre-wrap;
        font: inherit;
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

    .ai-upload-drop { display:grid; gap:.55rem; border:1px dashed var(--ai-task-border); border-radius:12px; padding:.85rem; background:color-mix(in srgb,var(--ai-field-bg) 86%,var(--primary) 14%); cursor:pointer; transition:border-color .16s ease,transform .16s ease; }
    .ai-upload-drop:hover { border-color:var(--primary); transform:translateY(-1px); }
    .ai-upload-drop input { display:none !important; position:absolute !important; width:0 !important; height:0 !important; opacity:0 !important; pointer-events:none !important; }
    .ai-upload-title { font-size:.82rem; font-weight:850; color:var(--ai-task-text); }
    .ai-upload-note { color:var(--ai-label); font-size:.72rem; line-height:1.45; }
    .ai-upload-preview { display:none; align-items:center; gap:.7rem; margin-top:.7rem; padding:.65rem; border:1px solid var(--ai-card-border); border-radius:11px; background:var(--ai-field-bg); }
    .ai-upload-preview.is-visible { display:flex; }
    .ai-upload-thumb { width:48px; height:48px; flex:0 0 48px; border-radius:9px; object-fit:cover; display:grid; place-items:center; background:color-mix(in srgb,var(--primary) 15%,var(--ai-field-bg)); color:var(--primary); font-weight:900; }
    .ai-upload-copy { min-width:0; flex:1; }
    .ai-upload-name { display:block; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; color:var(--ai-field-text); font-size:.78rem; font-weight:800; }
    .ai-upload-size { color:var(--ai-label); font-size:.68rem; }
    .ai-upload-remove { border:0; background:transparent; color:var(--ai-label); font-size:1.2rem; cursor:pointer; }

    /* Follow the System Admin accent selected in Appearance settings. */
    .ai-admin {
        --ai-panel-border: color-mix(in srgb, var(--se-primary) 28%, var(--border));
        --ai-panel-bg: linear-gradient(155deg, color-mix(in srgb, var(--surface) 94%, var(--se-primary-soft)), var(--surface));
        --ai-panel-shadow: 0 18px 42px color-mix(in srgb, var(--se-primary-strong) 12%, transparent);
        --ai-head-title: var(--text);
        --ai-head-sub: var(--text-muted);
        --ai-badge-border: var(--se-primary-muted);
        --ai-badge-bg: var(--se-primary-soft);
        --ai-badge-text: var(--se-primary-strong);
        --ai-chat-bg-start: color-mix(in srgb, var(--surface) 94%, var(--se-primary-soft));
        --ai-chat-bg-end: color-mix(in srgb, var(--bg) 96%, var(--se-primary-muted));
        --ai-msg-user-bg: linear-gradient(135deg, var(--se-primary-button-start), var(--se-primary-button-end));
        --ai-msg-user-border: color-mix(in srgb, var(--se-primary) 72%, var(--border));
        --ai-msg-user-text: var(--se-primary-button-text);
        --ai-msg-ai-bg: linear-gradient(145deg, color-mix(in srgb, var(--surface) 92%, var(--se-primary-soft)), var(--surface));
        --ai-msg-ai-border: color-mix(in srgb, var(--se-primary) 26%, var(--border));
        --ai-msg-ai-text: var(--text);
        --ai-msg-meta: var(--text-muted);
        --ai-toolbar-bg: color-mix(in srgb, var(--surface) 92%, var(--se-primary-soft));
        --ai-btn-border: color-mix(in srgb, var(--se-primary) 45%, var(--border));
        --ai-btn-bg: var(--se-primary-soft);
        --ai-btn-text: var(--se-primary-strong);
        --ai-compose-bg: color-mix(in srgb, var(--surface) 94%, var(--se-primary-soft));
        --ai-input-border: color-mix(in srgb, var(--se-primary) 34%, var(--border));
        --ai-input-bg: var(--surface);
        --ai-input-text: var(--text);
        --ai-input-placeholder: var(--text-muted);
        --ai-send-border: var(--se-primary-strong);
        --ai-send-bg: linear-gradient(135deg, var(--se-primary-button-start), var(--se-primary-button-end));
        --ai-send-text: var(--se-primary-button-text);
        --ai-hint: var(--text-muted);
        --ai-card-border: color-mix(in srgb, var(--se-primary) 24%, var(--border));
        --ai-card-bg: linear-gradient(145deg, color-mix(in srgb, var(--surface) 94%, var(--se-primary-soft)), var(--surface));
        --ai-card-title: var(--se-primary-strong);
        --ai-task-border: color-mix(in srgb, var(--se-primary) 34%, var(--border));
        --ai-task-bg: linear-gradient(145deg, color-mix(in srgb, var(--surface) 86%, var(--se-primary-soft)), var(--surface));
        --ai-task-text: var(--text);
        --ai-label: var(--text-muted);
        --ai-field-border: color-mix(in srgb, var(--se-primary) 30%, var(--border));
        --ai-field-bg: var(--surface);
        --ai-field-text: var(--text);
        --ai-kv: var(--text-muted);
        --ai-kv-strong: var(--se-primary-strong);
    }
    .ai-head,.ai-toolbar,.ai-compose { border-color:color-mix(in srgb,var(--se-primary) 22%,var(--border)); }
    .ai-upload-drop { min-height:110px; place-content:center; text-align:center; border-width:1.5px; border-color:color-mix(in srgb,var(--se-primary) 50%,var(--border)); background:linear-gradient(145deg,var(--se-primary-soft),color-mix(in srgb,var(--surface) 90%,var(--se-primary-soft))); }
    .ai-upload-drop::before { content:'↥'; width:38px; height:38px; margin:0 auto .1rem; display:grid; place-items:center; border-radius:12px; background:var(--se-primary); color:var(--se-primary-button-text); font-size:1.15rem; font-weight:900; box-shadow:0 8px 20px color-mix(in srgb,var(--se-primary) 25%,transparent); }
    .ai-upload-title { color:var(--se-primary-strong); }
    .ai-upload-preview { border-color:color-mix(in srgb,var(--se-primary) 36%,var(--border)); box-shadow:0 8px 20px color-mix(in srgb,var(--se-primary) 9%,transparent); }

    /* Focused Gemini-style admin workspace. */
    .ai-admin { display:block; width:min(100%,1120px); min-height:calc(100vh - 176px); }
    .ai-admin > .ai-panel:first-child { min-height:calc(100vh - 176px); border:0; border-radius:0; background:transparent; box-shadow:none; backdrop-filter:none; display:grid; grid-template-rows:auto minmax(360px,1fr) auto auto; overflow:visible; }
    .ai-admin > .ai-panel:first-child > .ai-head { padding:.45rem .25rem; border:0; background:transparent; }
    .ai-admin > .ai-panel:first-child .ai-sub { display:none; }
    .ai-chat-log { min-height:430px; padding:2rem max(1rem,8%); background:radial-gradient(ellipse 52% 45% at 50% 52%,color-mix(in srgb,var(--se-primary) 18%,transparent),transparent 72%); border-radius:28px; align-content:start; }
    .ai-empty-state { min-height:390px; display:grid; place-content:center; justify-items:center; text-align:center; gap:.7rem; color:var(--text); }
    .ai-empty-orb { width:52px; height:52px; display:grid; place-items:center; border-radius:18px; color:var(--se-primary-button-text); background:linear-gradient(145deg,var(--se-primary-button-start),var(--se-primary-button-end)); box-shadow:0 18px 45px color-mix(in srgb,var(--se-primary) 28%,transparent); font-size:1.45rem; }
    .ai-empty-orb svg { width:27px; height:27px; }
    .ai-empty-state h3 { margin:.45rem 0 0; font-size:clamp(1.65rem,3vw,2.3rem); font-weight:550; letter-spacing:-.035em; }
    .ai-empty-state p { max-width:520px; margin:0; color:var(--text-muted); line-height:1.6; }
    .ai-empty-chips { display:flex; flex-wrap:wrap; justify-content:center; gap:.5rem; margin-top:.55rem; }
    .ai-empty-chip { border:1px solid color-mix(in srgb,var(--se-primary) 28%,var(--border)); border-radius:999px; padding:.5rem .75rem; background:color-mix(in srgb,var(--surface) 86%,var(--se-primary-soft)); color:var(--text); font:inherit; font-size:.74rem; font-weight:750; cursor:pointer; }
    .ai-toolbar { display:none; max-width:820px; width:100%; margin:0 auto; border:0; background:transparent; padding:.35rem 0 .7rem; }
    .ai-admin.has-chat .ai-toolbar { display:flex; }
    .ai-compose { position:sticky; bottom:12px; z-index:12; width:min(100%,820px); margin:0 auto 1rem; padding:0; border:0; background:transparent; }
    .ai-compose-row { position:relative; display:grid; grid-template-columns:44px minmax(0,1fr) auto 44px; align-items:end; gap:.35rem; padding:.42rem; border:1px solid color-mix(in srgb,var(--se-primary) 28%,var(--border)); border-radius:26px; background:color-mix(in srgb,var(--surface) 94%,transparent); box-shadow:0 18px 55px rgba(0,0,0,.16),0 0 0 1px color-mix(in srgb,var(--se-primary) 6%,transparent); backdrop-filter:blur(20px) saturate(130%); }
    .ai-input { min-height:46px; max-height:150px; padding:.75rem .4rem; border:0; background:transparent; box-shadow:none!important; resize:none; }
    .ai-input:focus { border:0; box-shadow:none; }
    .ai-compose-icon { width:42px; height:42px; border:0; border-radius:50%; display:grid; place-items:center; background:transparent; color:var(--text); font-size:1.4rem; cursor:pointer; }
    .ai-compose-icon:hover,.ai-compose-icon[aria-expanded="true"] { background:var(--se-primary-soft); color:var(--se-primary-strong); }
    .ai-format-pill { align-self:center; border:0; border-radius:999px; padding:.48rem .65rem; background:transparent; color:var(--text-muted); font-size:.72rem; font-weight:800; cursor:pointer; white-space:nowrap; }
    .ai-format-pill:hover { background:var(--se-primary-soft); color:var(--se-primary-strong); }
    .ai-send { width:42px; height:42px; padding:0; border-radius:50%; font-size:1.05rem; }
    .ai-hint { text-align:center; margin:.48rem 0 0; }
    .ai-add-menu { position:absolute; z-index:24; left:10px; bottom:calc(100% + 8px); width:236px; padding:.55rem; display:none; gap:.15rem; border:1px solid color-mix(in srgb,var(--se-primary) 18%,var(--border)); border-radius:18px; background:color-mix(in srgb,var(--surface) 96%,#202124); box-shadow:0 22px 55px rgba(0,0,0,.32); backdrop-filter:blur(22px); }
    .ai-add-menu.is-open { display:grid; }
    .ai-add-action { display:flex; align-items:center; gap:.7rem; width:100%; min-height:42px; padding:.58rem .65rem; border:0; border-radius:10px; background:transparent; color:var(--text); font:inherit; font-size:.77rem; font-weight:700; text-align:left; cursor:pointer; }
    .ai-add-action:hover { background:var(--se-primary-soft); color:var(--se-primary-strong); }
    .ai-add-action span { width:22px; height:22px; flex:0 0 22px; display:grid; place-items:center; border-radius:0; background:transparent; color:var(--text-muted); font-size:.9rem; }
    .ai-add-action + .ai-add-action { border-top:1px solid color-mix(in srgb,var(--se-primary) 10%,var(--border)); }
    .ai-tools-backdrop { position:fixed; inset:0; z-index:1070; border:0; background:rgba(0,0,0,.3); opacity:0; visibility:hidden; transition:.2s ease; }
    .ai-tools-backdrop.is-open { opacity:1; visibility:visible; }
    .ai-admin > aside.ai-panel { position:fixed; z-index:1071; top:18px; right:18px; bottom:18px; width:min(390px,calc(100vw - 28px)); max-height:none; overflow:auto; transform:translateX(calc(100% + 36px)); transition:transform .24s ease; border-radius:22px; }
    .ai-admin > aside.ai-panel.is-open { transform:none; }
    .ai-admin > aside.ai-panel .ai-head { position:sticky; top:0; z-index:2; backdrop-filter:blur(18px); }
    body.ai-tools-open { overflow:hidden; }
    @media(max-width:640px){.ai-chat-log{padding:1rem .25rem;min-height:380px}.ai-empty-state{min-height:350px}.ai-compose-row{grid-template-columns:42px minmax(0,1fr) 42px}.ai-format-pill{display:none}.ai-hint{font-size:.66rem}.ai-admin>.ai-panel:first-child{min-height:calc(100vh - 145px)}}

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

    .msg.ai { max-width:min(92%,760px); }
    .msg.user { max-width:min(78%,620px); }
    .ai-message-actions { display:flex; align-items:center; gap:.15rem; margin-top:.65rem; padding-top:.5rem; border-top:1px solid color-mix(in srgb,var(--se-primary) 16%,var(--border)); }
    .ai-message-actions[hidden] { display:none !important; }
    .ai-message-action { min-height:30px; display:inline-flex; align-items:center; gap:.32rem; padding:.32rem .48rem; border:0; border-radius:8px; background:transparent; color:var(--text-muted); font:inherit; font-size:.67rem; font-weight:700; cursor:pointer; }
    .ai-message-action svg { width:14px; height:14px; }
    .ai-message-action:hover,.ai-message-action:focus-visible { background:var(--se-primary-soft); color:var(--se-primary-strong); outline:0; }
    .ai-message-action:disabled { opacity:.42; cursor:not-allowed; }
    .ai-edit-actions button { min-height:32px; padding:.38rem .68rem; border:1px solid color-mix(in srgb,var(--se-primary) 30%,var(--border)); border-radius:9px; background:transparent; color:var(--se-primary-strong); font:inherit; font-size:.7rem; font-weight:750; cursor:pointer; }
    .msg.ai.is-writing { width:min(88%,760px); max-width:min(88%,760px); padding:0 !important; overflow:hidden; border-radius:20px !important; background:color-mix(in srgb,var(--surface) 94%,var(--se-primary-soft)) !important; }
    .ai-writing-head { display:flex; align-items:center; justify-content:space-between; gap:.75rem; padding:.68rem .8rem; border-bottom:1px solid color-mix(in srgb,var(--se-primary) 18%,var(--border)); }
    .ai-writing-label { display:inline-flex; align-items:center; gap:.42rem; min-height:34px; padding:.4rem .65rem; border:1px solid color-mix(in srgb,var(--se-primary) 24%,var(--border)); border-radius:999px; color:var(--text); font-size:.74rem; font-weight:750; }
    .ai-writing-label svg,.ai-writing-tools svg { width:16px; height:16px; }
    .ai-writing-tools { display:flex; gap:.3rem; }
    .ai-writing-tools button { width:34px; height:34px; display:grid; place-items:center; border:0; border-radius:9px; background:transparent; color:var(--text-muted); cursor:pointer; }
    .ai-writing-tools button:hover { background:var(--se-primary-soft); color:var(--se-primary-strong); }
    .ai-writing-body { padding:.75rem .8rem .8rem; }
    .ai-message-editor { width:100%; min-height:300px; max-height:52vh; resize:vertical; padding:.85rem; border:1px solid color-mix(in srgb,var(--se-primary) 34%,var(--border)); border-radius:12px; outline:0; background:color-mix(in srgb,var(--bg) 84%,var(--surface)); color:var(--text); font:inherit; font-size:.86rem; line-height:1.6; box-sizing:border-box; }
    .ai-message-editor:focus { box-shadow:0 0 0 3px color-mix(in srgb,var(--se-primary) 16%,transparent); }
    .ai-edit-prompt { display:grid; grid-template-columns:minmax(0,1fr) 34px; gap:.4rem; margin-bottom:.6rem; padding:.35rem; border:1px solid color-mix(in srgb,var(--se-primary) 26%,var(--border)); border-radius:12px; background:color-mix(in srgb,var(--surface) 88%,var(--se-primary-soft)); }
    .ai-edit-prompt input { min-width:0; border:0; outline:0; background:transparent; color:var(--text); font:inherit; font-size:.76rem; }
    .ai-edit-prompt button { width:34px; height:34px; border:0; border-radius:9px; background:var(--se-primary); color:var(--se-primary-button-text); cursor:pointer; }
    .ai-edit-actions { display:flex; justify-content:flex-end; gap:.45rem; margin-top:.55rem; }
    .ai-edit-actions .is-primary { background:var(--se-primary); color:var(--se-primary-button-text); }
    .msg.ai.is-writing.is-expanded { position:fixed; z-index:14500; inset:24px; width:auto; max-width:none; margin:0; }
    .msg.ai.is-writing.is-expanded .ai-writing-body { height:calc(100% - 60px); display:grid; grid-template-rows:auto minmax(0,1fr) auto; }
    .msg.ai.is-writing.is-expanded .ai-message-editor { height:100%; max-height:none; resize:none; }
    body.ai-writing-expanded #appSidebar,body.ai-writing-expanded #sbOverlay { display:none !important; }
    body.ai-writing-expanded .main-wrap { width:100%; }
    body.ai-writing-expanded { overflow:hidden !important; }
    body.ai-writing-expanded .ai-chat-log { overflow:visible !important; -webkit-mask-image:none !important; mask-image:none !important; }
    @media(max-width:640px){ .msg.ai.is-writing { width:100%; max-width:100% !important; } .msg.ai.is-writing.is-expanded { inset:8px; } }
    .ai-selection-tools { position:fixed; z-index:15000; display:flex; overflow:hidden; border:1px solid color-mix(in srgb,var(--se-primary) 30%,var(--border)); border-radius:11px; background:#202124; box-shadow:0 10px 30px rgba(0,0,0,.32); opacity:0; visibility:hidden; pointer-events:none; transform:translateY(4px); transition:opacity .12s ease,transform .12s ease,visibility 0s linear .12s; }
    .ai-selection-tools.is-open { opacity:1; visibility:visible; pointer-events:auto; transform:translateY(0); transition-delay:0s; }
    .ai-selection-tools button { min-height:38px; padding:.5rem .75rem; border:0; border-right:1px solid rgba(255,255,255,.1); background:transparent; color:#fff; font:inherit; font-size:.72rem; font-weight:750; cursor:pointer; white-space:nowrap; }
    .ai-selection-tools button:last-child { border-right:0; }
    .ai-selection-tools button:hover { background:color-mix(in srgb,var(--se-primary) 22%,#202124); }
    .msg-rich { white-space:normal; overflow-wrap:anywhere; }
    .msg-rich h3 { margin:0 0 .85rem; padding-bottom:.65rem; border-bottom:1px solid color-mix(in srgb,var(--se-primary) 28%,var(--border)); font-size:1.05rem; line-height:1.35; letter-spacing:.015em; color:var(--text); }
    .msg-rich h4 { margin:1rem 0 .45rem; font-size:.94rem; line-height:1.4; color:var(--se-primary-muted); }
    .msg-rich h4:first-child { margin-top:0; }
    .msg-rich p { margin:.42rem 0; line-height:1.7; }
    .msg-rich hr { height:1px; margin:.85rem 0; border:0; background:color-mix(in srgb,var(--se-primary) 25%,var(--border)); }
    .msg-rich ul,.msg-rich ol { margin:.4rem 0 .75rem; padding-left:1.35rem; }
    .msg-rich li { margin:.28rem 0; padding-left:.15rem; }
    .msg-rich strong { font-weight:850; color:var(--text); }
    .msg-rich code { padding:.1rem .32rem; border-radius:5px; background:var(--se-primary-soft); color:var(--se-primary-strong); font-size:.86em; }
    .msg-rich .report-meta { display:grid; grid-template-columns:minmax(105px,auto) 1fr; gap:.3rem .7rem; margin:.25rem 0; padding:.58rem .7rem; border:1px solid color-mix(in srgb,var(--se-primary) 22%,var(--border)); border-radius:10px; background:color-mix(in srgb,var(--surface) 88%,var(--se-primary-soft)); }
    .msg-rich .report-meta-label { color:var(--text-muted); font-size:.72rem; font-weight:800; text-transform:uppercase; letter-spacing:.035em; }
    .msg-rich .report-meta-value { color:var(--text); font-weight:700; }
    .msg-rich .report-table-wrap { max-width:100%; margin:.7rem 0; overflow-x:auto; border:1px solid color-mix(in srgb,var(--se-primary) 26%,var(--border)); border-radius:10px; }
    .msg-rich table { width:100%; border-collapse:collapse; background:transparent !important; font-size:.78rem; }
    .msg-rich th,.msg-rich td { padding:.55rem .65rem; border-bottom:1px solid color-mix(in srgb,var(--se-primary) 18%,var(--border)); text-align:left; vertical-align:top; }
    .msg-rich th { background:var(--se-primary-soft) !important; color:var(--se-primary-strong); font-weight:850; }
    .msg-rich tr:last-child td { border-bottom:0; }
    @media(max-width:640px){ .msg-rich .report-meta { grid-template-columns:1fr; gap:.08rem; } }
    .ai-input:focus,.ops-field input:focus,.ops-field select:focus { outline:none; border-color:var(--primary); box-shadow:0 0 0 3px color-mix(in srgb,var(--primary) 18%,transparent); }

    /* Final layout safeguards against global admin form and panel rules. */
    .ai-admin > .ai-panel:first-child > .ai-head { display:none !important; }
    .ai-admin > .ai-panel:first-child { position:relative; min-height:calc(100vh - 160px) !important; grid-template-rows:auto minmax(390px,1fr) auto auto !important; }
    .ai-chat-log { min-height:420px !important; max-height:none !important; }
    .ai-admin.has-chat .ai-chat-log.can-scroll-up:not(.can-scroll-down) {
        -webkit-mask-image:linear-gradient(to bottom,transparent 0,#000 42px,#000 100%);
        mask-image:linear-gradient(to bottom,transparent 0,#000 42px,#000 100%);
    }
    .ai-admin.has-chat .ai-chat-log.can-scroll-down:not(.can-scroll-up) {
        -webkit-mask-image:linear-gradient(to bottom,#000 0,#000 calc(100% - 72px),transparent 100%);
        mask-image:linear-gradient(to bottom,#000 0,#000 calc(100% - 72px),transparent 100%);
    }
    .ai-admin.has-chat .ai-chat-log.can-scroll-up.can-scroll-down {
        -webkit-mask-image:linear-gradient(to bottom,transparent 0,#000 42px,#000 calc(100% - 72px),transparent 100%);
        mask-image:linear-gradient(to bottom,transparent 0,#000 42px,#000 calc(100% - 72px),transparent 100%);
    }
    @media(min-width:641px){
        .ai-chat-log { gap:.78rem; padding-left:max(1rem,10%) !important; padding-right:max(1rem,10%) !important; }
        .msg { padding:.72rem .82rem; font-size:.8rem; line-height:1.5; }
        .msg.ai { max-width:min(88%,680px); }
        .msg.user { max-width:min(72%,560px); }
        .ai-admin .msg.user pre { padding:.58rem .84rem; font-size:.78rem; }
        .msg-rich h3 { font-size:.92rem; }
        .msg-rich h4 { font-size:.83rem; }
        .msg-rich p { line-height:1.56; }
        .msg-rich .report-meta { padding:.46rem .58rem; }
        .msg-rich .report-meta-label { font-size:.66rem; }
        .msg-rich table { font-size:.72rem; }
        .ai-toolbar .ai-btn { font-size:.69rem !important; }
        .ai-input { font-size:.8rem !important; }
        .ai-format-pill { font-size:.7rem; }
    }
    .ai-compose { bottom:18px; padding:0 10px !important; }
    .ai-compose-row { min-height:60px; grid-template-columns:46px minmax(0,1fr) auto 46px; align-items:center; padding:6px 7px; border-radius:30px; }
    .ai-input { width:100% !important; height:46px !important; min-height:46px !important; max-height:46px !important; padding:12px 6px !important; overflow-y:auto; resize:none !important; line-height:22px; border:0 !important; border-radius:0 !important; background:transparent !important; scrollbar-width:none; -ms-overflow-style:none; }
    .ai-input::-webkit-scrollbar { display:none; width:0; height:0; }
    .ai-compose-icon,.ai-send { align-self:center; flex:none; }
    .ai-compose-row .ai-send { background:linear-gradient(135deg,var(--se-primary-button-start),var(--se-primary-button-end)) !important; border-color:color-mix(in srgb,var(--se-primary-strong) 72%,var(--border)) !important; color:var(--se-primary-button-text) !important; box-shadow:0 10px 24px color-mix(in srgb,var(--se-primary) 28%,transparent) !important; }
    .ai-compose-row .ai-send:hover:not(:disabled) { transform:translateY(-1px); box-shadow:0 14px 30px color-mix(in srgb,var(--se-primary) 38%,transparent) !important; }
    .ai-admin--student .ai-compose-row { grid-template-columns:minmax(0,1fr) 46px !important; gap:.45rem; padding-left:18px; }
    .ai-admin--student .ai-input { min-width:0 !important; padding-left:14px !important; padding-right:10px !important; }
    .ai-admin.ai-admin--student .ai-compose-row textarea.ai-input,
    .ai-admin.ai-admin--student .ai-compose-row textarea.ai-input:hover,
    .ai-admin.ai-admin--student .ai-compose-row textarea.ai-input:focus,
    .ai-admin.ai-admin--student .ai-compose-row textarea.ai-input:focus-visible { border:none !important; outline:none !important; box-shadow:none !important; background:transparent !important; appearance:none; -webkit-appearance:none; }
    .ai-admin--student .ai-compose-row { background:color-mix(in srgb,var(--surface) 88%,var(--se-primary-soft)) !important; border-color:color-mix(in srgb,var(--se-primary) 24%,var(--border)) !important; }
    body[data-theme="dark"] .ai-admin--student .ai-compose-row { background:#1f1f1f !important; }
    .ai-admin.ai-admin--admin .ai-compose-row textarea.ai-input,
    .ai-admin.ai-admin--admin .ai-compose-row textarea.ai-input:hover,
    .ai-admin.ai-admin--admin .ai-compose-row textarea.ai-input:focus,
    .ai-admin.ai-admin--admin .ai-compose-row textarea.ai-input:focus-visible { border:none !important; outline:none !important; box-shadow:none !important; background:transparent !important; appearance:none; -webkit-appearance:none; }
    .ai-admin--admin .ai-compose-row { background:color-mix(in srgb,var(--surface) 88%,var(--se-primary-soft)) !important; border-color:color-mix(in srgb,var(--se-primary) 24%,var(--border)) !important; }
    body[data-theme="dark"] .ai-admin--admin .ai-compose-row { background:#1f1f1f !important; }
    .ai-admin--student .ai-send { justify-self:end; }
    .ai-admin--admin .ai-compose-row { grid-template-columns:minmax(0,1fr) 46px !important; gap:.45rem; background:color-mix(in srgb,var(--surface) 88%,var(--se-primary-soft)) !important; border-color:color-mix(in srgb,var(--se-primary) 24%,var(--border)) !important; }
    .ai-admin--lecturer .ai-compose-row { grid-template-columns:minmax(0,1fr) 46px !important; gap:.35rem; background:color-mix(in srgb,var(--surface) 88%,var(--se-primary-soft)) !important; border-color:color-mix(in srgb,var(--se-primary) 24%,var(--border)) !important; }
    .ai-admin.ai-admin--lecturer .ai-compose-row textarea.ai-input { border:none !important; outline:none !important; box-shadow:none !important; background:transparent !important; }
    body[data-theme="dark"] .ai-admin--lecturer .ai-compose-row { background:#1f1f1f !important; }
    .ai-compose-frame { width:min(100%,620px); margin:0 auto; overflow:visible; padding:0; border:0; border-radius:30px; background:transparent; box-shadow:none; }
    .ai-compose-context { display:none; height:34px; align-items:center; gap:.42rem; padding:0 .68rem; border-bottom:1px solid color-mix(in srgb,var(--se-primary) 16%,var(--border)); color:var(--text-muted); box-sizing:border-box; }
    .ai-compose-context[hidden] { display:none !important; }
    .ai-compose-context.is-visible { display:flex; }
    .ai-compose-context svg { width:14px; height:14px; flex:0 0 14px; color:var(--se-primary-strong); }
    .ai-compose-context blockquote { flex:1; min-width:0; margin:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; font-size:.66rem; line-height:1.2; }
    .ai-compose-context button { width:20px; height:20px; flex:0 0 20px; display:grid; place-items:center; padding:0; border:0; border-radius:6px; background:transparent; color:var(--text-muted); cursor:pointer; }
    .ai-compose-context button:hover { background:var(--se-primary-soft); color:var(--se-primary-strong); }
    .ai-compose-frame .ai-compose-row { min-height:58px; padding:5px 7px 5px 10px; border:0 !important; border-radius:999px !important; background:#1f1f1f !important; box-shadow:none !important; }
    .ai-compose-frame:has(.ai-compose-attachments.is-visible) { padding:.55rem; border:1px solid color-mix(in srgb,var(--se-primary) 20%,var(--border)); border-radius:26px; background:#202124; }
    .ai-compose-frame:has(.ai-compose-context.is-visible) { overflow:hidden; border:1px solid color-mix(in srgb,var(--se-primary) 24%,var(--border)); border-radius:24px; background:#202124; }
    .ai-compose-frame:has(.ai-compose-context.is-visible) .ai-compose-row { min-height:50px; padding-top:3px; padding-bottom:3px; border-radius:0 0 24px 24px !important; }
    .ai-compose:has(.ai-compose-context.is-visible) .ai-hint { display:none; }
    .ai-compose-frame:has(.ai-compose-attachments.is-visible) .ai-compose-row { min-height:54px; }
    body:not([data-theme="dark"]) .ai-compose-frame .ai-compose-row {
        border:1px solid color-mix(in srgb,var(--se-primary) 24%,var(--border)) !important;
        background:color-mix(in srgb,#fff 94%,var(--se-primary-soft)) !important;
        box-shadow:0 12px 30px rgba(82,58,42,.1),inset 0 1px 0 rgba(255,255,255,.92) !important;
    }
    .ai-compose-frame .ai-compose-row:focus-within {
        border-color:color-mix(in srgb,var(--se-primary) 62%,var(--border)) !important;
        box-shadow:0 12px 30px color-mix(in srgb,var(--se-primary) 14%,transparent),0 0 0 3px color-mix(in srgb,var(--se-primary) 12%,transparent) !important;
    }
    body:not([data-theme="dark"]) .ai-compose-frame:has(.ai-compose-attachments.is-visible) {
        border-color:color-mix(in srgb,var(--se-primary) 28%,var(--border));
        background:color-mix(in srgb,#fff 92%,var(--se-primary-soft));
        box-shadow:0 14px 34px rgba(82,58,42,.1);
    }
    body:not([data-theme="dark"]) .ai-compose-frame:has(.ai-compose-context.is-visible) { border-color:color-mix(in srgb,var(--se-primary) 28%,var(--border)); background:color-mix(in srgb,#fff 94%,var(--se-primary-soft)); box-shadow:0 14px 34px rgba(82,58,42,.1); }
    body:not([data-theme="dark"]) .ai-compose-icon { color:var(--text); }
    body:not([data-theme="dark"]) .ai-input { color:var(--text) !important; }
    body:not([data-theme="dark"]) .ai-input::placeholder { color:color-mix(in srgb,var(--text-muted) 86%,transparent) !important; opacity:1; }
    .ai-compose-attachments { display:none; flex-wrap:nowrap; gap:.5rem; max-width:100%; margin:0; padding:0 .05rem .45rem; overflow-x:auto; overscroll-behavior-inline:contain; scrollbar-width:thin; }
    .ai-compose-attachments.is-visible { display:flex; }
    .ai-compose-attachment { position:relative; flex:0 0 104px; width:104px; height:104px; display:flex; align-items:flex-end; overflow:visible; padding:.62rem; border:1px solid color-mix(in srgb,var(--text) 7%,transparent); border-radius:17px; background:color-mix(in srgb,var(--surface) 72%,#3c4043); box-shadow:none; }
    .ai-compose-attachment-thumb { position:absolute; inset:0; overflow:hidden; padding:.62rem; border-radius:17px; color:color-mix(in srgb,var(--text) 78%,transparent); font-size:.66rem; font-weight:850; background-position:center; background-size:cover; }
    .ai-compose-attachment.is-image .ai-compose-attachment-thumb::after { content:''; position:absolute; inset:0; background:linear-gradient(transparent 42%,rgba(0,0,0,.68)); }
    .ai-compose-attachment-copy { position:relative; z-index:1; min-width:0; width:100%; display:grid; gap:.1rem; }
    .ai-compose-attachment-name { display:-webkit-box; overflow:hidden; -webkit-line-clamp:2; -webkit-box-orient:vertical; color:var(--text); font-size:.68rem; line-height:1.25; font-weight:800; overflow-wrap:anywhere; }
    .ai-compose-attachment.is-image .ai-compose-attachment-name { color:#fff; text-shadow:0 1px 3px rgba(0,0,0,.7); }
    .ai-compose-attachment-meta { display:none; }
    .ai-compose-attachment.is-image .ai-compose-attachment-meta { color:rgba(255,255,255,.82); }
    .ai-compose-attachment-remove { position:absolute !important; z-index:3; top:6px; right:6px; width:25px !important; min-width:25px !important; max-width:25px !important; height:25px !important; min-height:25px !important; max-height:25px !important; aspect-ratio:1/1; display:grid !important; place-items:center; margin:0 !important; padding:0 !important; border:1px solid color-mix(in srgb,var(--text) 28%,transparent) !important; border-radius:999px !important; background:color-mix(in srgb,var(--surface) 88%,#202124) !important; color:var(--text) !important; font:700 15px/1 system-ui !important; cursor:pointer; box-sizing:border-box !important; }
    .ai-compose-attachment-remove:hover { background:var(--se-primary-soft) !important; color:var(--se-primary-strong) !important; border-color:var(--se-primary) !important; }
    .ai-compose-attachment-limit { flex:1 0 100%; color:var(--se-danger); font-size:.68rem; font-weight:750; }
    .ai-admin .msg.user { width:auto; max-width:min(78%,620px); padding:0 !important; border:0 !important; background:transparent !important; box-shadow:none !important; display:grid; justify-items:end; gap:.42rem; }
    .ai-admin .msg.user pre { width:auto; max-width:100%; margin:0; padding:.72rem 1.05rem; border:1px solid color-mix(in srgb,var(--se-primary) 42%,var(--border)); border-radius:999px; background:#171717; color:#f1f1f1; box-shadow:0 4px 14px color-mix(in srgb,var(--se-primary) 8%,transparent); white-space:pre-wrap; font-family:inherit; font-size:.88rem; font-weight:500; line-height:1.35; }
    body:not([data-theme="dark"]) .ai-admin .msg.user pre {
        border:1px solid color-mix(in srgb,var(--se-primary) 38%,var(--border));
        background:color-mix(in srgb,var(--se-primary-soft) 68%,#fff);
        color:var(--se-primary-strong);
        box-shadow:0 8px 20px color-mix(in srgb,var(--se-primary) 10%,transparent);
    }
    .ai-sent-attachments { display:flex; flex-wrap:wrap; justify-content:flex-end; gap:.42rem; max-width:100%; }
    .ai-sent-attachment { position:relative; width:102px; height:102px; display:flex; align-items:flex-end; overflow:hidden; padding:.62rem; border:1px solid color-mix(in srgb,var(--text) 7%,transparent); border-radius:17px; background:color-mix(in srgb,var(--surface) 72%,#3c4043); color:var(--text); box-sizing:border-box; }
    .ai-sent-attachment-type { position:absolute; top:.62rem; left:.62rem; font-size:.66rem; font-weight:850; color:color-mix(in srgb,var(--text) 78%,transparent); }
    .ai-sent-attachment-name { position:relative; z-index:1; display:-webkit-box; overflow:hidden; -webkit-line-clamp:2; -webkit-box-orient:vertical; font-size:.68rem; line-height:1.25; font-weight:800; overflow-wrap:anywhere; }
    .ai-sent-attachment.is-image { background-position:center; background-size:cover; }
    .ai-sent-attachment.is-image::after { content:''; position:absolute; inset:0; background:linear-gradient(transparent 42%,rgba(0,0,0,.7)); }
    .ai-sent-attachment.is-image .ai-sent-attachment-type { display:none; }
    .ai-sent-attachment.is-image .ai-sent-attachment-name { color:#fff; text-shadow:0 1px 3px rgba(0,0,0,.75); }
    .ai-format-pill { max-width:185px; overflow:hidden; text-overflow:ellipsis; }
    .ai-format-pill { min-height:38px; display:inline-flex; align-items:center; gap:.42rem; padding:.48rem .75rem; border:0; border-radius:999px; background:#2b2b2b; color:#ededed; }
    .ai-format-pill::before { display:none; }
    .ai-format-pill:hover,.ai-format-pill[aria-expanded="true"] { border-color:transparent; background:#343434; color:#fff; }
    body:not([data-theme="dark"]) .ai-format-pill { background:color-mix(in srgb,var(--se-primary-soft) 55%,#f5f1ed); color:var(--text); }
    body:not([data-theme="dark"]) .ai-format-pill:hover,
    body:not([data-theme="dark"]) .ai-format-pill[aria-expanded="true"] { background:var(--se-primary-soft); color:var(--se-primary-strong); }
    .ai-format-menu { position:absolute; right:48px; bottom:62px; width:235px; display:none; gap:.25rem; padding:.55rem; border:1px solid color-mix(in srgb,var(--se-primary) 30%,var(--border)); border-radius:16px; background:color-mix(in srgb,var(--surface) 96%,transparent); box-shadow:0 20px 50px rgba(0,0,0,.28); backdrop-filter:blur(20px); }
    .ai-format-menu.is-open { display:grid; }
    .ai-format-option { display:flex; justify-content:space-between; align-items:center; gap:.5rem; width:100%; padding:.65rem .72rem; border:0; border-radius:10px; background:transparent; color:var(--text); font:inherit; font-size:.76rem; font-weight:750; text-align:left; cursor:pointer; }
    .ai-format-option:hover,.ai-format-option.is-selected { background:var(--se-primary-soft); color:var(--se-primary-strong); }
    .ai-format-option.is-selected::after { content:'✓'; }
    .ai-hint { margin:.42rem 0 0; opacity:.78; }
    .ai-tools-backdrop { z-index:11990 !important; }
    .ai-admin > aside.ai-panel { z-index:12000 !important; top:88px !important; right:-460px !important; bottom:18px !important; width:min(420px,calc(100vw - 28px)) !important; max-height:none !important; position:fixed !important; visibility:hidden; pointer-events:none; transform:none !important; transition:right .24s ease,visibility .24s ease !important; box-shadow:-24px 0 70px rgba(0,0,0,.3); }
    .ai-admin > aside.ai-panel { scrollbar-width:none; -ms-overflow-style:none; }
    .ai-admin > aside.ai-panel::-webkit-scrollbar { display:none; width:0; }
    .ai-admin > aside.ai-panel.is-open { right:18px !important; visibility:visible; pointer-events:auto; }
    .ai-admin > aside.ai-panel .ai-head { padding:.9rem 1rem; background:color-mix(in srgb,var(--surface) 92%,var(--se-primary-soft)); }
    .ai-admin > aside.ai-panel .ai-sub { display:block; }

    /* Keep the admin tools drawer visually tied to the System Admin accent. */
    .ai-admin--admin > aside.ai-panel {
        border-color:color-mix(in srgb,var(--se-primary) 48%,var(--border)) !important;
        background:
            radial-gradient(420px circle at 100% 0%,color-mix(in srgb,var(--se-primary) 24%,transparent),transparent 68%),
            linear-gradient(165deg,color-mix(in srgb,var(--surface) 88%,var(--se-primary-soft)),var(--surface)) !important;
        box-shadow:-24px 0 70px rgba(0,0,0,.3),0 0 0 1px color-mix(in srgb,var(--se-primary) 12%,transparent) !important;
    }
    .ai-admin--admin > aside.ai-panel .ai-head {
        border-color:color-mix(in srgb,var(--se-primary) 32%,var(--border)) !important;
        background:linear-gradient(145deg,color-mix(in srgb,var(--surface) 74%,var(--se-primary-soft)),color-mix(in srgb,var(--surface) 94%,var(--se-primary))) !important;
    }
    .ai-admin--admin #aiToolsClose {
        border-color:color-mix(in srgb,var(--se-primary) 62%,var(--border));
        background:var(--se-primary-soft);
        color:var(--se-primary-strong);
    }
    .ai-admin--admin #aiToolsClose:hover {
        background:linear-gradient(135deg,var(--se-primary-button-start),var(--se-primary-button-end));
        color:var(--se-primary-button-text);
        box-shadow:0 10px 24px color-mix(in srgb,var(--se-primary) 28%,transparent);
    }
    .ai-admin--admin .ops-card {
        border-color:color-mix(in srgb,var(--se-primary) 34%,var(--border)) !important;
        background:linear-gradient(145deg,color-mix(in srgb,var(--surface) 82%,var(--se-primary-soft)),color-mix(in srgb,var(--surface) 96%,var(--se-primary))) !important;
        box-shadow:inset 3px 0 0 color-mix(in srgb,var(--se-primary) 78%,transparent),0 12px 28px color-mix(in srgb,var(--se-primary) 10%,transparent) !important;
    }
    .ai-admin--admin .ops-title { color:var(--se-primary-muted) !important; }
    .ai-admin--admin .task-btn {
        border-color:color-mix(in srgb,var(--se-primary) 36%,var(--border)) !important;
        background:color-mix(in srgb,var(--surface) 78%,var(--se-primary-soft)) !important;
    }
    .ai-admin--admin .task-btn:hover,
    .ai-admin--admin .task-btn:focus-visible {
        border-color:var(--se-primary) !important;
        background:var(--se-primary-soft) !important;
        color:var(--se-primary-strong) !important;
        box-shadow:0 10px 22px color-mix(in srgb,var(--se-primary) 20%,transparent) !important;
        outline:none;
    }
    .ai-admin--admin .ops-field input,
    .ai-admin--admin .ops-field select {
        border-color:color-mix(in srgb,var(--se-primary) 30%,var(--border)) !important;
        background:color-mix(in srgb,var(--surface) 92%,var(--se-primary-soft)) !important;
    }
    .ai-admin--admin .ops-field input:focus,
    .ai-admin--admin .ops-field select:focus {
        border-color:var(--se-primary) !important;
        box-shadow:0 0 0 3px color-mix(in srgb,var(--se-primary) 20%,transparent) !important;
    }
    .ai-admin--admin .ai-upload-drop {
        border-color:color-mix(in srgb,var(--se-primary) 62%,var(--border)) !important;
        background:linear-gradient(145deg,var(--se-primary-soft),color-mix(in srgb,var(--surface) 82%,var(--se-primary-soft))) !important;
    }
    .ai-admin--admin .ai-upload-drop:hover {
        box-shadow:0 12px 26px color-mix(in srgb,var(--se-primary) 18%,transparent);
    }
    .ai-admin .msg.user {
        padding:0 !important;
        border:0 !important;
        border-radius:0 !important;
        background:transparent !important;
        color:var(--text) !important;
        box-shadow:none !important;
    }
    .ai-admin .msg.ai {
        background:linear-gradient(145deg,color-mix(in srgb,var(--surface) 82%,var(--se-primary-soft)),var(--surface)) !important;
        border-color:color-mix(in srgb,var(--se-primary) 34%,var(--border)) !important;
        color:var(--text) !important;
        box-shadow:0 12px 28px color-mix(in srgb,var(--se-primary) 10%,transparent) !important;
    }
    .ai-admin .msg.ai.is-conversation { padding:.2rem 0 !important; border:0 !important; border-radius:0 !important; background:transparent !important; box-shadow:none !important; }
    .ai-admin .msg.ai.is-report { width:min(88%,720px); max-width:min(88%,720px); padding:1rem 1.05rem !important; border-radius:17px !important; }
    .ai-report-kicker { display:flex; align-items:center; gap:.42rem; margin:0 0 .7rem; color:var(--se-primary-strong); font-size:.63rem; font-weight:800; letter-spacing:.075em; text-transform:uppercase; }
    .ai-report-kicker::before { content:''; width:7px; height:7px; border-radius:50%; background:var(--se-primary); box-shadow:0 0 0 4px color-mix(in srgb,var(--se-primary) 14%,transparent); }
    .ai-report-content { position:relative; }
    .ai-admin .msg.ai.is-report.is-collapsed .ai-report-content { max-height:420px; overflow:hidden; }
    .ai-admin .msg.ai.is-report.is-collapsed .ai-report-content::after { content:''; position:absolute; right:0; bottom:0; left:0; height:72px; background:linear-gradient(transparent,color-mix(in srgb,var(--surface) 96%,var(--se-primary-soft))); pointer-events:none; }
    .ai-report-toggle { margin:.55rem 0 0; padding:.4rem .62rem; border:1px solid color-mix(in srgb,var(--se-primary) 26%,var(--border)); border-radius:9px; background:transparent; color:var(--se-primary-strong); font:inherit; font-size:.69rem; font-weight:750; cursor:pointer; }
    .ai-admin .msg-meta { color:var(--text-muted) !important; }
    .ai-admin .ai-toolbar {
        width:max-content;
        max-width:100%;
        margin:.35rem auto .75rem;
        padding:.3rem !important;
        gap:.2rem;
        border:1px solid color-mix(in srgb,var(--se-primary) 30%,var(--border)) !important;
        border-radius:999px;
        background:color-mix(in srgb,var(--surface) 88%,var(--se-primary-soft)) !important;
        box-shadow:0 12px 30px color-mix(in srgb,var(--se-primary) 12%,transparent);
    }
    .ai-admin .ai-toolbar .ai-btn {
        min-height:36px;
        gap:.4rem;
        padding:.48rem .68rem;
        border:0 !important;
        border-radius:999px;
        background:transparent !important;
        color:var(--text-muted) !important;
        box-shadow:none !important;
    }
    .ai-admin .ai-toolbar .ai-btn svg { width:15px; height:15px; flex:0 0 15px; }
    .ai-admin .ai-toolbar .ai-btn:hover,
    .ai-admin .ai-toolbar .ai-btn:focus-visible {
        transform:none;
        background:var(--se-primary-soft) !important;
        color:var(--se-primary-strong) !important;
        outline:none;
        box-shadow:0 5px 14px color-mix(in srgb,var(--se-primary) 14%,transparent) !important;
    }
    .ai-top-actions { position:relative; z-index:8; justify-self:center; width:min(1180px,calc(100% - 32px)); display:flex; align-items:stretch; justify-content:center; flex-wrap:wrap; gap:.5rem; margin:14px auto 0; padding:.55rem; overflow:visible; border:1px solid color-mix(in srgb,var(--se-primary) 20%,var(--border)); border-radius:16px; background:color-mix(in srgb,var(--surface) 94%,var(--se-primary-soft)); box-shadow:0 8px 24px rgba(30,38,43,.06); }
    .ai-history-trigger,.ai-new-chat-trigger,.ai-quick-action { position:relative; flex:0 0 auto; display:inline-flex; align-items:center; justify-content:center; gap:.5rem; min-height:42px; padding:.55rem .82rem; border:1px solid color-mix(in srgb,var(--se-primary) 28%,var(--border)); border-radius:10px; background:var(--surface); color:var(--se-primary-strong); font:inherit; font-size:.74rem; font-weight:800; line-height:1.2; cursor:pointer; white-space:nowrap; box-shadow:0 1px 2px rgba(30,38,43,.04); }
    .ai-history-trigger svg { width:17px; height:17px; }
    .ai-new-chat-trigger svg { width:16px; height:16px; }
    .ai-history-trigger:hover,.ai-new-chat-trigger:hover,.ai-quick-action:hover { background:var(--se-primary-soft); border-color:var(--se-primary); }
    .ai-new-chat-trigger,.ai-history-trigger { min-width:108px; border-color:color-mix(in srgb,var(--se-primary) 48%,var(--border)); }
    .ai-new-chat-trigger { background:var(--se-primary); border-color:var(--se-primary); color:#fff; box-shadow:0 5px 14px color-mix(in srgb,var(--se-primary) 24%,transparent); }
    .ai-new-chat-trigger:hover { background:var(--se-primary-strong); border-color:var(--se-primary-strong); color:#fff; }
    .ai-history-trigger { background:color-mix(in srgb,var(--surface) 78%,var(--se-primary-soft)); }
    .ai-top-actions::after { content:''; align-self:stretch; width:1px; margin:2px .12rem; background:color-mix(in srgb,var(--se-primary) 20%,var(--border)); order:-1; }
    .ai-new-chat-trigger,.ai-history-trigger { order:-2; }
    .ai-quick-action-icon { width:20px; height:20px; flex:0 0 20px; display:grid; place-items:center; border-radius:6px; background:var(--se-primary-soft); color:var(--se-primary-strong); font-size:.82rem; }
    body[data-theme="dark"] .ai-top-actions { background:color-mix(in srgb,var(--surface) 90%,var(--se-primary-soft)); box-shadow:0 10px 28px rgba(0,0,0,.2); }
    .ai-history-backdrop { position:absolute; inset:0; z-index:11990; border:0; border-radius:inherit; background:transparent; opacity:0; visibility:hidden; transition:opacity .18s ease,visibility 0s linear .18s; }
    .ai-history-backdrop.is-open { opacity:1; visibility:visible; }
    .ai-history-panel { position:fixed; z-index:12000; top:88px; bottom:18px; left:-390px; width:min(360px,calc(100vw - 28px)); display:grid; grid-template-rows:auto minmax(0,1fr) auto; border:1px solid color-mix(in srgb,var(--se-primary) 42%,var(--border)); border-radius:22px; overflow:hidden; background:linear-gradient(165deg,color-mix(in srgb,var(--surface) 88%,var(--se-primary-soft)),var(--surface)); box-shadow:24px 0 70px rgba(0,0,0,.3); transition:left .24s ease; }
    .ai-history-panel.is-open { left:18px; }
    .ai-history-head { display:flex; align-items:center; justify-content:space-between; gap:.65rem; padding:1rem; border-bottom:1px solid color-mix(in srgb,var(--se-primary) 26%,var(--border)); }
    .ai-history-head h3 { margin:0; color:var(--text); font-size:1rem; }
    .ai-history-actions { display:flex; gap:.4rem; }
    .ai-history-icon-btn { width:36px; height:36px; display:grid; place-items:center; border:1px solid color-mix(in srgb,var(--se-primary) 34%,var(--border)); border-radius:11px; background:var(--se-primary-soft); color:var(--se-primary-strong); cursor:pointer; }
    .ai-history-list { min-height:0; overflow:auto; display:grid; align-content:start; gap:.45rem; padding:.75rem; }
    .ai-history-empty { padding:1.5rem .75rem; text-align:center; color:var(--text-muted); font-size:.8rem; line-height:1.5; }
    .ai-history-item { position:relative; display:grid; gap:.25rem; padding:.72rem 4.8rem .72rem .78rem; border:1px solid color-mix(in srgb,var(--se-primary) 24%,var(--border)); border-radius:13px; background:color-mix(in srgb,var(--surface) 88%,var(--se-primary-soft)); color:var(--text); cursor:pointer; }
    .ai-history-item:hover,.ai-history-item.is-active { border-color:var(--se-primary); background:var(--se-primary-soft); box-shadow:inset 3px 0 0 var(--se-primary); }
    .ai-history-title { overflow:hidden; text-overflow:ellipsis; white-space:nowrap; font-size:.78rem; font-weight:800; }
    .ai-history-time { color:var(--text-muted); font-size:.67rem; }
    .ai-history-item-actions { position:absolute; top:50%; right:.45rem; transform:translateY(-50%); display:flex; gap:.2rem; }
    .ai-history-item-actions button { width:30px; height:30px; border:0; border-radius:9px; background:transparent; color:var(--text-muted); cursor:pointer; }
    .ai-history-item-actions button:hover { background:color-mix(in srgb,var(--se-primary) 18%,transparent); color:var(--se-primary-strong); }
    .ai-history-foot { padding:.75rem; border-top:1px solid color-mix(in srgb,var(--se-primary) 24%,var(--border)); }
    .ai-history-retention { margin:0 0 .6rem; color:var(--text-muted); font-size:.67rem; line-height:1.45; text-align:center; }
    .ai-history-delete-all { width:100%; padding:.65rem; border:1px solid color-mix(in srgb,#dc2626 45%,var(--border)); border-radius:11px; background:color-mix(in srgb,#dc2626 9%,var(--surface)); color:#ef4444; font:inherit; font-size:.74rem; font-weight:800; cursor:pointer; }
    .ai-history-panel { position:absolute; z-index:12000; top:16px; right:16px; bottom:auto; left:auto; width:min(320px,calc(100% - 32px)); height:min(720px,calc(100% - 32px)); display:flex; flex-direction:column; overflow:hidden; border:1px solid rgba(255,255,255,.1); border-radius:18px; background:#1e1e1e; box-shadow:0 18px 50px rgba(0,0,0,.24); color:#f3f3f3; opacity:0; visibility:hidden; pointer-events:none; transform:translateX(calc(100% + 32px)); transition:transform .24s ease,opacity .18s ease,visibility 0s linear .24s; }
    .ai-history-panel.is-open { right:16px; left:auto; opacity:1; visibility:visible; pointer-events:auto; transform:translateX(0); transition-delay:0s; }
    .ai-history-head { width:100%; display:block; padding:.78rem .75rem; border-color:rgba(255,255,255,.08); box-sizing:border-box; }
    .ai-history-brand { width:100%; display:flex; align-items:center; justify-content:space-between; gap:.6rem; box-sizing:border-box; }
    .ai-history-brand strong { display:flex; align-items:center; gap:.55rem; font-size:1rem; }
    .ai-history-brand svg { width:22px; height:22px; color:var(--se-primary); }
    .ai-history-brand .ai-history-icon-btn { width:34px; height:34px; flex:0 0 34px; border-color:rgba(255,255,255,.12); background:#292526; color:#ddd; }
    .ai-history-brand .ai-history-icon-btn:hover { border-color:var(--se-primary); background:#343031; color:#fff; }
    .ai-history-tabs { display:grid; grid-template-columns:1fr 1fr; padding:3px; border-radius:999px; background:#171717; }
    .ai-history-tab { min-height:30px; border:0; border-radius:999px; background:transparent; color:#bdbdbd; font:inherit; font-size:.72rem; font-weight:750; }
    .ai-history-tab.is-active { background:#252525; color:#fff; }
    .ai-history-primary-actions { display:grid; gap:.08rem; padding:.42rem .48rem .3rem; border-bottom:1px solid rgba(255,255,255,.06); }
    .ai-history-primary-action { width:100%; min-height:38px; display:flex; align-items:center; gap:.7rem; padding:.5rem .55rem; border:0; border-radius:10px; background:transparent; color:#f2f2f2; font:inherit; font-size:.77rem; font-weight:700; text-align:left; cursor:pointer; }
    .ai-history-primary-action:hover { background:#292929; }
    .ai-history-primary-action svg { width:18px; height:18px; flex:0 0 18px; color:#d8c4ff; }
    .ai-history-search { display:none; margin:.1rem .55rem .45rem; }
    .ai-history-search.is-visible { display:block; }
    .ai-history-search input { width:100%; min-height:38px; padding:.55rem .7rem; border:1px solid rgba(255,255,255,.14); border-radius:10px; outline:0; background:#151515; color:#fff; font:inherit; font-size:.76rem; box-sizing:border-box; }
    .ai-history-search input:focus { border-color:var(--se-primary); box-shadow:0 0 0 2px color-mix(in srgb,var(--se-primary) 20%,transparent); }
    .ai-history-section-label { padding:.55rem .72rem .28rem; color:#999; font-size:.64rem; font-weight:750; text-transform:uppercase; letter-spacing:.045em; }
    .ai-history-list { flex:1 1 auto; gap:.06rem; padding:.08rem .42rem .65rem; scrollbar-width:thin; }
    .ai-history-item { min-height:32px; display:block; padding:.47rem 4rem .47rem .55rem; border:0; border-radius:9px; background:transparent; color:#f3f3f3; box-sizing:border-box; }
    .ai-history-item:hover,.ai-history-item.is-active { border:0; background:#292929; box-shadow:none; }
    .ai-history-title { display:block; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; font-size:.7rem; line-height:1.35; font-weight:650; }
    .ai-history-time { display:none; }
    .ai-history-item-actions { opacity:0; transition:opacity .15s ease; }
    .ai-history-item:hover .ai-history-item-actions,.ai-history-item:focus-within .ai-history-item-actions { opacity:1; }
    .ai-history-item-actions button { color:#c8c8c8; }
    .ai-history-item-actions button:hover { background:#3a3a3a; color:#fff; }
    .ai-history-empty { color:#aaa; }
    .ai-history-foot { padding:.58rem .72rem .7rem; border-color:rgba(255,255,255,.08); background:#1e1e1e; }
    .ai-history-retention { margin:0 0 .48rem; color:#999; font-size:.62rem; }
    .ai-history-delete-all { min-height:38px; padding:.5rem; font-size:.68rem; }
    body:not([data-theme="dark"]) .ai-history-panel { background:#f7f4f0; color:#241f1b; border-color:rgba(70,50,35,.12); }
    body:not([data-theme="dark"]) .ai-history-tabs,
    body:not([data-theme="dark"]) .ai-history-search input { background:#ebe6e0; color:#241f1b; }
    body:not([data-theme="dark"]) .ai-history-tab.is-active,
    body:not([data-theme="dark"]) .ai-history-item:hover,
    body:not([data-theme="dark"]) .ai-history-item.is-active,
    body:not([data-theme="dark"]) .ai-history-primary-action:hover { background:#e5ded7; color:#241f1b; }
    body:not([data-theme="dark"]) .ai-history-primary-action,
    body:not([data-theme="dark"]) .ai-history-item { color:#241f1b; }
    body:not([data-theme="dark"]) .ai-history-foot { background:#f7f4f0; border-color:rgba(70,50,35,.12); }
    .ai-confirm { position:fixed; inset:0; z-index:14000; display:grid; place-items:center; padding:1rem; opacity:0; visibility:hidden; pointer-events:none; transition:opacity .18s ease,visibility 0s linear .18s; }
    .ai-confirm.is-open { opacity:1; visibility:visible; pointer-events:auto; transition-delay:0s; }
    .ai-confirm-backdrop { position:absolute; inset:0; border:0; background:rgba(0,0,0,.66); backdrop-filter:blur(5px); cursor:default; }
    .ai-confirm-card { position:relative; width:min(400px,100%); padding:1.25rem; border:1px solid color-mix(in srgb,var(--se-primary) 32%,var(--border)); border-radius:20px; background:color-mix(in srgb,var(--surface) 96%,var(--se-primary-soft)); box-shadow:0 24px 70px rgba(0,0,0,.45); color:var(--text); transform:translateY(8px) scale(.98); transition:transform .18s ease; }
    .ai-confirm.is-open .ai-confirm-card { transform:translateY(0) scale(1); }
    .ai-confirm-icon { width:42px; height:42px; display:grid; place-items:center; margin-bottom:.9rem; border-radius:13px; background:color-mix(in srgb,#dc2626 12%,var(--surface)); color:#ef4444; }
    .ai-confirm-icon svg { width:20px; height:20px; }
    .ai-confirm-card h3 { margin:0 0 .4rem; font-size:1rem; line-height:1.35; }
    .ai-confirm-card p { margin:0; color:var(--text-muted); font-size:.8rem; line-height:1.55; }
    #programReportDialog { align-items:center; overflow:hidden; }
    #programReportDialog .ai-confirm-card { max-height:calc(100dvh - 2rem); overflow-y:auto; overscroll-behavior:contain; scrollbar-gutter:stable; -webkit-overflow-scrolling:touch; }
    #programReportDialog .ai-confirm-actions { position:sticky; bottom:-1.25rem; z-index:3; margin-inline:-1.25rem; padding:.8rem 1.25rem 1.25rem; background:linear-gradient(to bottom,transparent,color-mix(in srgb,var(--surface) 98%,var(--se-primary-soft)) 24%); }
    .program-report-form { display:grid; gap:.9rem; margin-top:1rem; }
    .program-report-upload { display:grid; gap:.48rem; }
    .program-report-upload-head { display:flex; align-items:center; justify-content:space-between; gap:.75rem; }
    .program-report-upload-head label { margin:0; }
    .program-report-file-input { position:absolute; width:1px; height:1px; padding:0; margin:-1px; overflow:hidden; clip:rect(0,0,0,0); white-space:nowrap; border:0; }
    .program-report-add-file { min-height:38px; display:inline-flex; align-items:center; justify-content:center; gap:.45rem; padding:.52rem .72rem; border:1px solid color-mix(in srgb,var(--se-primary) 42%,var(--border)); border-radius:10px; background:color-mix(in srgb,var(--surface) 82%,var(--se-primary-soft)); color:var(--se-primary-strong); font-size:.73rem; font-weight:800; cursor:pointer; transition:border-color .16s ease,background .16s ease,box-shadow .16s ease; }
    .program-report-add-file:hover,.program-report-add-file:focus-visible { border-color:var(--se-primary); background:var(--se-primary-soft); outline:none; box-shadow:0 5px 14px color-mix(in srgb,var(--se-primary) 15%,transparent); }
    .program-report-add-file svg { width:16px; height:16px; }
    .program-report-attachments { min-height:76px; display:flex; flex-wrap:wrap; align-items:stretch; gap:.55rem; padding:.55rem; border:1px dashed color-mix(in srgb,var(--se-primary) 30%,var(--border)); border-radius:13px; background:color-mix(in srgb,var(--surface) 92%,var(--se-primary-soft)); }
    .program-report-attachments.is-empty { align-items:center; }
    .program-report-attachments[data-drop-zone] { cursor:pointer; transition:border-color .16s ease,background .16s ease,box-shadow .16s ease,transform .16s ease; }
    .program-report-attachments[data-drop-zone]:hover { border-color:color-mix(in srgb,var(--se-primary) 62%,var(--border)); background:color-mix(in srgb,var(--surface) 84%,var(--se-primary-soft)); }
    .program-report-attachments.is-dragging { border-color:var(--se-primary); border-style:solid; background:var(--se-primary-soft); box-shadow:0 0 0 3px color-mix(in srgb,var(--se-primary) 15%,transparent),0 12px 24px color-mix(in srgb,var(--se-primary) 12%,transparent); transform:translateY(-1px); }
    .program-report-attachments-empty { width:100%; margin:0; color:var(--text-muted); font-size:.72rem; text-align:center; }
    .program-report-attachments-empty.is-error { color:#dc2626; font-weight:700; }
    .program-report-attachment { position:relative; width:126px; min-height:94px; display:grid; grid-template-rows:42px auto; gap:.42rem; padding:.62rem 2rem .58rem .62rem; overflow:hidden; border:1px solid color-mix(in srgb,var(--se-primary) 28%,var(--border)); border-radius:13px; background:var(--surface); box-shadow:0 5px 14px rgba(30,38,43,.07); }
    .program-report-attachment-preview { width:42px; height:42px; display:grid; place-items:center; overflow:hidden; border-radius:9px; background:var(--se-primary-soft); color:var(--se-primary-strong); font-size:.59rem; font-weight:850; letter-spacing:.035em; }
    .program-report-attachment-preview img { width:100%; height:100%; object-fit:cover; }
    .program-report-attachment-name { min-width:0; overflow:hidden; color:var(--text); font-size:.68rem; font-weight:750; line-height:1.35; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; }
    .program-report-attachment-size { display:block; margin-top:.1rem; color:var(--text-muted); font-size:.6rem; font-weight:600; }
    .program-report-attachment-remove { appearance:none !important; position:absolute; top:.48rem; right:.48rem; width:24px !important; min-width:24px !important; max-width:24px !important; height:24px !important; min-height:24px !important; max-height:24px !important; display:grid !important; place-items:center; margin:0 !important; padding:0 !important; border:1px solid color-mix(in srgb,var(--text) 14%,var(--border)) !important; border-radius:50% !important; background:color-mix(in srgb,var(--surface) 92%,var(--se-primary-soft)) !important; color:var(--text-muted) !important; line-height:1 !important; cursor:pointer; box-shadow:0 2px 7px rgba(30,38,43,.1) !important; transform:none !important; }
    .program-report-attachment-remove:hover,.program-report-attachment-remove:focus-visible { border-color:#dc2626 !important; background:#dc2626 !important; color:#fff !important; outline:none; box-shadow:0 4px 10px color-mix(in srgb,#dc2626 24%,transparent) !important; }
    .program-report-attachment-remove svg { display:block; width:12px !important; height:12px !important; pointer-events:none; }
    .program-report-upload-status { min-height:1em; margin:0; color:var(--text-muted); font-size:.67rem; }
    .program-report-upload-status.is-error { color:#dc2626; font-weight:700; }
    @media (max-width:560px) {
        .program-report-upload-head { align-items:flex-start; flex-direction:column; }
        .program-report-add-file { width:100%; }
        .program-report-attachment { width:calc(50% - .3rem); box-sizing:border-box; }
    }
    .program-report-progress { display:none; margin-top:1rem; padding:.9rem; border:1px solid var(--border); border-radius:14px; background:color-mix(in srgb,var(--primary) 5%,var(--surface)); }
    .program-report-progress.is-active { display:block; }
    .program-report-progress strong { display:block; margin-bottom:.65rem; font-size:.86rem; }
    .program-report-progress ol { display:grid; gap:.45rem; margin:0; padding:0; list-style:none; }
    .program-report-progress li { display:flex; align-items:center; gap:.55rem; color:var(--text-muted); font-size:.8rem; }
    .program-report-progress li::before { content:''; width:9px; height:9px; border:2px solid var(--border); border-radius:50%; background:var(--surface); }
    .program-report-progress li.is-active { color:var(--text); font-weight:800; }
    .program-report-progress li.is-active::before { border-color:var(--primary); box-shadow:0 0 0 3px color-mix(in srgb,var(--primary) 14%,transparent); }
    .program-report-progress li.is-done::before { border-color:#21835a; background:#21835a; }
    .ai-confirm-actions { display:flex; justify-content:flex-end; gap:.55rem; margin-top:1.2rem; }
    .ai-confirm-button { min-height:40px; padding:.55rem 1rem; border:1px solid color-mix(in srgb,var(--text) 16%,transparent); border-radius:12px; background:transparent; color:var(--text); font:inherit; font-size:.76rem; font-weight:750; cursor:pointer; }
    .ai-confirm-button:hover { background:color-mix(in srgb,var(--text) 7%,transparent); }
    .ai-confirm-button--danger { border-color:color-mix(in srgb,#dc2626 60%,transparent); background:#b4232d; color:#fff; }
    .ai-confirm-button--danger:hover { background:#991f27; }
    .ai-confirm-button:focus-visible { outline:2px solid var(--se-primary); outline-offset:2px; }
    .program-report-complete-card { width:min(520px,100%); text-align:left; }
    .program-report-complete-icon { width:52px; height:52px; display:grid; place-items:center; margin-bottom:1rem; border-radius:16px; background:color-mix(in srgb,#21835a 14%,var(--surface)); color:#21835a; box-shadow:0 8px 22px color-mix(in srgb,#21835a 18%,transparent); }
    .program-report-complete-icon svg { width:27px; height:27px; }
    .program-report-complete-card h3 { font-size:1.15rem; }
    .program-report-complete-program { display:flex; align-items:center; gap:.55rem; margin-top:1rem; padding:.75rem .85rem; border:1px solid color-mix(in srgb,var(--se-primary) 22%,var(--border)); border-radius:12px; background:color-mix(in srgb,var(--surface) 90%,var(--se-primary-soft)); color:var(--text); font-size:.8rem; font-weight:800; }
    .program-report-complete-files { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:.55rem; margin-top:.8rem; }
    .program-report-complete-files a { min-height:44px; display:flex; align-items:center; justify-content:center; gap:.45rem; padding:.65rem .8rem; border:1px solid color-mix(in srgb,var(--se-primary) 42%,var(--border)); border-radius:11px; background:color-mix(in srgb,var(--surface) 86%,var(--se-primary-soft)); color:var(--se-primary-strong); font-size:.76rem; font-weight:850; text-decoration:none; }
    .program-report-complete-files a:hover,.program-report-complete-files a:focus-visible { border-color:var(--se-primary); background:var(--se-primary-soft); outline:none; }
    .program-report-complete-note { margin-top:.8rem !important; padding:.72rem .8rem; border-left:3px solid var(--se-primary); border-radius:8px; background:color-mix(in srgb,var(--surface) 91%,var(--se-primary-soft)); }
    .program-report-complete-card .ai-confirm-actions a { display:inline-flex; align-items:center; justify-content:center; text-decoration:none; }
    .program-report-complete-card .ai-confirm-actions .is-primary { border-color:var(--se-primary); background:var(--se-primary); color:#fff; box-shadow:0 10px 24px color-mix(in srgb,var(--se-primary) 24%,transparent); }
    @media(max-width:520px){ .program-report-complete-files { grid-template-columns:1fr; } }
    body.admin-ai-helper-page { overflow:hidden !important; }
    body.admin-ai-helper-page .main-scroll-viewport { overflow:hidden !important; }
    body.admin-ai-helper-page .main-scroll-inner { height:100%; min-height:0; overflow:hidden; }
    body.admin-ai-helper-page .page-header { flex:0 0 auto; }
    body.admin-ai-helper-page .page-body { flex:1 1 auto; height:auto !important; min-height:0; overflow:hidden !important; padding-bottom:0 !important; box-sizing:border-box; }
    body.admin-ai-helper-page .app-footer { display:none !important; }
    body.admin-ai-helper-page .ai-admin,body.admin-ai-helper-page .ai-admin>.ai-panel:first-child { min-height:0 !important; height:100% !important; }
    @media(max-width:640px){
        .ai-history-backdrop { background:rgba(0,0,0,.16); }
        body.admin-ai-helper-page .page-body { padding:.4rem .55rem .7rem !important; }
        .ai-admin > .ai-panel:first-child { min-height:0 !important; height:100% !important; grid-template-rows:auto minmax(0,1fr) auto auto !important; }
        .ai-chat-log { min-height:0 !important; height:auto !important; overflow-y:auto !important; padding:.7rem .2rem .55rem !important; overscroll-behavior:contain; }
        .ai-empty-state { min-height:0 !important; padding:2rem .45rem 1rem; align-content:start; }
        .ai-empty-state h3 { font-size:clamp(1.55rem,7vw,2rem); line-height:1.12; }
        .ai-empty-state p { max-width:330px; font-size:.9rem; line-height:1.55; }
        .ai-empty-chips { width:100%; gap:.42rem; }
        .ai-empty-chip { flex:0 1 auto; min-height:42px; padding:.58rem .78rem; font-size:.7rem; }
        .ai-compose { position:relative !important; bottom:auto !important; width:100% !important; margin:0 auto !important; padding:0 !important; }
        .ai-compose-frame { width:100%; border-radius:24px; }
        .ai-compose-frame .ai-compose-row { min-height:54px; padding:4px 6px 4px 8px; }
        .ai-input { height:42px !important; min-height:42px !important; max-height:42px !important; font-size:.82rem !important; }
        .ai-compose-icon,.ai-send { width:40px !important; height:40px !important; }
        .ai-admin--admin .ai-compose-row { grid-template-columns:minmax(0,1fr) 42px !important; }
        .ai-admin--lecturer .ai-compose-row { grid-template-columns:minmax(0,1fr) 42px !important; }
        .ai-format-pill { display:none !important; }
        .ai-admin--student .ai-compose-row { grid-template-columns:minmax(0,1fr) 44px !important; padding-left:10px; }
        .ai-admin--student .ai-input { padding-left:12px !important; }
        .ai-hint { display:none; }
        .ai-admin .ai-toolbar { display:none !important; }
        .ai-admin .ai-toolbar .ai-btn { width:38px; height:38px; min-height:38px; padding:0; justify-content:center; }
        .ai-admin .ai-toolbar .ai-btn span { display:none; }
        .ai-message-action span { display:none; }
        .ai-message-action { width:32px; height:32px; padding:0; justify-content:center; }
        .msg.ai,.msg.user { max-width:92% !important; }
        .ai-admin .msg.ai.is-report { width:96%; max-width:96% !important; padding:.8rem !important; }
        .ai-top-actions { width:calc(100% - 16px); justify-content:flex-start; flex-wrap:nowrap; gap:.35rem; margin:8px 8px 0; padding:.4rem; overflow-x:auto; scrollbar-width:none; }
        .ai-top-actions::-webkit-scrollbar { display:none; }
        .ai-history-trigger,.ai-new-chat-trigger { width:auto; min-width:max-content; padding:.48rem .7rem; }
        .ai-history-trigger span,.ai-new-chat-trigger span { display:inline; }
        .ai-top-actions::after { flex:0 0 1px; }
        .ai-quick-action { min-height:40px; padding:.48rem .62rem; }
        .ai-history-panel { top:8px; right:8px; bottom:8px; left:auto; width:calc(100% - 16px); height:auto; border-radius:18px; }
        .ai-history-panel.is-open { right:8px; left:auto; transform:translateX(0); }
        .ai-admin > aside.ai-panel { top:72px !important; right:-105vw !important; bottom:8px !important; width:calc(100vw - 16px) !important; }
        .ai-admin > aside.ai-panel.is-open { right:8px !important; }
    }
    @media (max-width:767px) and (display-mode:standalone),
           (max-width:767px) and (display-mode:fullscreen),
           (max-width:767px) and (display-mode:minimal-ui),
           (max-width:767px) and (display-mode:window-controls-overlay) {
        body.admin-ai-helper-page .page-body { padding-bottom:calc(4.7rem + env(safe-area-inset-bottom,0px)) !important; }
        body.admin-ai-helper-page .ai-compose {
            position:fixed !important;
            z-index:1080 !important;
            left:max(.55rem,env(safe-area-inset-left,0px));
            right:max(.55rem,env(safe-area-inset-right,0px));
            bottom:calc(.8rem + env(safe-area-inset-bottom,0px)) !important;
            width:auto !important;
        }
        body.student-bottom-nav-eligible.admin-ai-helper-page .page-body { padding-bottom:calc(10.8rem + env(safe-area-inset-bottom,0px)) !important; }
        body.student-bottom-nav-eligible.admin-ai-helper-page .ai-compose { bottom:calc(6.65rem + env(safe-area-inset-bottom,0px)) !important; }
        body.student-bottom-nav-eligible.admin-ai-helper-page .ai-chat-log { padding-bottom:4.5rem !important; }
    }
    @media(max-width:767px){
        body.student-mobile-shell.admin-ai-helper-page .page-header { display:none !important; }
        body.student-mobile-shell.admin-ai-helper-page .page-body {
            padding:.35rem .55rem calc(6.3rem + env(safe-area-inset-bottom,0px)) !important;
        }
        body.student-mobile-shell.admin-ai-helper-page .ai-admin > .ai-panel:first-child {
            grid-template-rows:auto minmax(0,1fr) auto !important;
        }
        body.student-mobile-shell.admin-ai-helper-page .ai-top-actions {
            width:100%;
            min-height:42px;
            margin:0;
            padding:.15rem 0 .35rem;
            justify-content:flex-end;
        }
        body.student-mobile-shell.admin-ai-helper-page .ai-chat-log {
            padding:.55rem .2rem .7rem !important;
            scrollbar-width:none;
        }
        body.student-mobile-shell.admin-ai-helper-page .ai-chat-log::-webkit-scrollbar { display:none; }
        body.student-mobile-shell.admin-ai-helper-page .ai-empty-state {
            min-height:100% !important;
            padding:1.25rem .6rem;
            align-content:center;
        }
        body.student-mobile-shell.admin-ai-helper-page .ai-empty-state h3 { font-size:1.45rem; }
        body.student-mobile-shell.admin-ai-helper-page .ai-empty-state p { font-size:.82rem; line-height:1.45; }
        body.student-mobile-shell.admin-ai-helper-page .ai-compose-frame {
            border-radius:16px;
            box-shadow:0 6px 18px rgba(45,31,20,.1);
        }
        body.student-mobile-shell.admin-ai-helper-page .ai-compose-frame .ai-compose-row { min-height:48px; }
        body.student-mobile-shell.admin-ai-helper-page .ai-input { height:38px !important; min-height:38px !important; max-height:92px !important; }
        body.student-mobile-shell.admin-ai-helper-page .ai-send { width:36px !important; height:36px !important; }
    }

</style>
@endpush

@section('header')
    <h2 style="margin:0;font-size:1.1rem;font-weight:700;color:var(--text);">{{ $aiPageTitle }}</h2>
@endsection

@section('content')
<div class="ai-admin {{ $studentAiMode ? 'ai-admin--student' : ($lecturerAiMode ? 'ai-admin--lecturer' : 'ai-admin--admin') }}"
     data-ai-url="{{ route($studentAiMode ? 'student.ai-helper.ask' : ($lecturerAiMode ? 'lecturer.ai-helper.ask' : 'admin.ai-helper.ask')) }}"
     data-ai-enabled="{{ $aiEnabled ? '1' : '0' }}"
     data-ai-provider="{{ $aiProvider }}"
     data-ai-model="{{ $aiModel }}"
     data-can-edit-ai="{{ $studentAiMode ? '0' : '1' }}"
     data-ai-session-key="myhep.ai.active.{{ $studentAiMode ? 'student' : ($lecturerAiMode ? 'lecturer' : 'admin') }}.{{ (int) session('auth_user.id') }}"
     data-conversations-url="{{ url($studentAiMode ? '/student/ai-helper/conversations' : ($lecturerAiMode ? '/lecturer/ai-helper/conversations' : '/admin/ai-helper/conversations')) }}">
    <section class="ai-panel">
        <div class="ai-top-actions">
            <button type="button" class="ai-new-chat-trigger" id="aiNewConversation" aria-label="{{ __('New chat') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 5v14M5 12h14" stroke-linecap="round"/></svg><span>{{ __('New chat') }}</span>
            </button>
            <button type="button" class="ai-history-trigger" id="aiHistoryTrigger" aria-expanded="false" aria-controls="aiHistoryPanel" aria-label="{{ __('Conversation history') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5M12 7v5l3 2"/></svg><span>{{ __('History') }}</span>
            </button>
            @if($canUploadAiFiles)
            <button type="button" class="ai-quick-action" id="aiUploadShortcut" aria-label="{{ __('Upload PDF or image') }}"><span class="ai-quick-action-icon">↥</span><span class="ai-quick-label">{{ __('Upload PDF or image') }}</span></button>
            @if(!$studentAiMode)<button type="button" class="ai-quick-action" id="programReportTemplateShortcut"><span class="ai-quick-action-icon">▤</span><span class="ai-quick-label">{{ __('Program Report Template') }}</span></button>@endif
            @unless($lecturerAiMode)
            <button type="button" class="ai-quick-action" id="aiReportToolsShortcut"><span class="ai-quick-action-icon">☷</span><span class="ai-quick-label">{{ __('Report tools and filters') }}</span></button>
            <button type="button" class="ai-quick-action" data-template="{{ __('Generate Monthly Report') }}"><span class="ai-quick-action-icon">▤</span><span class="ai-quick-label">{{ __('Generate monthly report') }}</span></button>
            <button type="button" class="ai-quick-action" data-template="{{ __('Review Pending Fine Applications') }}"><span class="ai-quick-action-icon">!</span><span class="ai-quick-label">{{ __('Review pending fines') }}</span></button>
            @endunless
            @endif
        </div>
        <div class="ai-head">
            <div>
                <h3>{{ __('MyHEP AI') }}</h3>
                <p class="ai-sub">{{ $studentAiMode ? __('Ask about your scholarship, offenses, payments, rules, or student portal steps.') : ($lecturerAiMode ? __('Get text guidance and summaries from records available to your lecturer category.') : __('Use templates and filters to generate actionable admin outputs quickly.')) }}</p>
            </div>
            <div class="ai-badges">
                <span class="ai-badge">BETA</span>
                <span class="ai-badge">{{ strtoupper($aiProvider) }}</span>
                <span class="ai-badge" id="aiClock">--:--</span>
            </div>
        </div>

        <div class="ai-chat-log" id="aiChatLog" aria-live="polite">
            <div class="ai-empty-state" id="aiEmptyState">
                <span class="ai-empty-orb">@include('partials.ai_helper_icon')</span>
                <h3>{{ __('What should we focus on?') }}</h3>
                <p>{{ $studentAiMode ? __('Ask about your own scholarship, offenses, payments, rules, or MyHEP portal steps.') : ($lecturerAiMode ? __('Research any topic, analyze attached documents, or summarize the anonymized records available to your lecturer category.') : __('Research any topic, analyze attached documents, or work with authorized MyHEP records.')) }}</p>
                <div class="ai-empty-chips">
                    @if($studentAiMode)
                    <button type="button" class="ai-empty-chip" data-template="{{ __('What can the AI Helper help me with?') }}">{{ __('Overview') }}</button>
                    <button type="button" class="ai-empty-chip" data-template="{{ __('What is my current scholarship status?') }}">{{ __('My scholarship') }}</button>
                    <button type="button" class="ai-empty-chip" data-template="{{ __('Explain my offense and payment records.') }}">{{ __('Payments') }}</button>
                    @elseif($lecturerAiMode)
                    <button type="button" class="ai-empty-chip" data-template="{{ __('Summarize the records available to my lecturer category.') }}">{{ __('Category summary') }}</button>
                    <button type="button" class="ai-empty-chip" data-template="{{ __('What lecturer tasks can you help me with?') }}">{{ __('Lecturer guidance') }}</button>
                    <button type="button" class="ai-empty-chip" data-template="{{ __('Prepare an anonymized monthly summary.') }}">{{ __('Monthly summary') }}</button>
                    @else
                    <button type="button" class="ai-empty-chip" data-template="{{ __('Generate Monthly Report') }}">{{ __('Monthly report') }}</button>
                    <button type="button" class="ai-empty-chip" data-template="{{ __('Review Pending Fine Applications') }}">{{ __('Pending fines') }}</button>
                    <button type="button" class="ai-empty-chip" data-template="{{ __('Summarize Scholarship Status') }}">{{ __('Scholarship summary') }}</button>
                    @endif
                </div>
            </div>
        </div>

        <div class="ai-compose">
            <div class="ai-compose-frame">
            <div class="ai-compose-context" id="aiComposeContext" hidden><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 12h12m-4-4 4 4-4 4" stroke-linecap="round" stroke-linejoin="round"/></svg><blockquote id="aiComposeContextText"></blockquote><button type="button" id="aiComposeContextClear" aria-label="{{ __('Remove selected context') }}">&times;</button></div>
            @if($canUploadAiFiles)
            <input id="reportAttachment" type="file" accept="application/pdf,.docx,application/vnd.openxmlformats-officedocument.wordprocessingml.document,image/jpeg,image/png,image/webp,text/csv,.csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,.xlsx" multiple hidden>
            <div class="ai-compose-attachments" id="attachmentPreview" aria-live="polite"></div>
            @endif
            <div class="ai-compose-row">
                <textarea class="ai-input" id="aiInput" rows="1" placeholder="{{ __('Ask MyHEP AI anything') }}"></textarea>
                @unless($textOnlyAiMode)
                @endunless
                <button type="button" class="ai-send" id="aiSendBtn" aria-label="{{ __('Send') }}" @disabled(!$aiEnabled)>↑</button>
            </div>
            </div>
            <p class="ai-hint">{{ __('Enter sends. Shift+Enter adds newline. Independently verify AI-generated conclusions.') }}</p>
        </div>
    </section>

    <button type="button" class="ai-history-backdrop" id="aiHistoryBackdrop" aria-label="{{ __('Close conversation history') }}"></button>
    <aside class="ai-history-panel" id="aiHistoryPanel" aria-hidden="true">
        <div class="ai-history-head">
            <div class="ai-history-brand">
                <strong>@include('partials.ai_helper_icon') <span>{{ __('MyHEP AI') }}</span></strong>
                <button type="button" class="ai-history-icon-btn" id="aiHistoryClose" aria-label="{{ __('Close') }}">×</button>
            </div>
        </div>
        <div class="ai-history-primary-actions">
            <button type="button" class="ai-history-primary-action" id="aiHistorySearchButton"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4" stroke-linecap="round"/></svg><span>{{ __('Search chats') }}</span></button>
        </div>
        <label class="ai-history-search" id="aiHistorySearch"><input id="aiHistorySearchInput" type="search" placeholder="{{ __('Search conversations') }}" autocomplete="off"></label>
        <div class="ai-history-section-label">{{ __('Recents') }}</div>
        <div class="ai-history-list" id="aiHistoryList">
            @forelse($aiConversations ?? [] as $conversation)
            <div class="ai-history-item" data-conversation-id="{{ $conversation['id'] }}" data-title="{{ $conversation['title'] }}">
                <span class="ai-history-title">{{ $conversation['title'] }}</span>
                <span class="ai-history-time">{{ $conversation['last_message_at'] ?? $conversation['updated_at'] }}</span>
                <span class="ai-history-item-actions"><button type="button" data-history-rename aria-label="{{ __('Rename') }}">✎</button><button type="button" data-history-delete aria-label="{{ __('Delete') }}">×</button></span>
            </div>
            @empty
            <p class="ai-history-empty">{{ __('Your AI conversations will appear here after you send a message.') }}</p>
            @endforelse
        </div>
        <div class="ai-history-foot">
            <p class="ai-history-retention">{{ __('Inactive conversations are automatically deleted after :days days.', ['days' => config('ai.conversation_retention_days', 30)]) }}</p>
            <button type="button" class="ai-history-delete-all" id="aiDeleteAllHistory">{{ __('Delete all AI history') }}</button>
        </div>
    </aside>

    <div class="ai-selection-tools" id="aiSelectionTools" role="toolbar" aria-label="{{ __('AI text actions') }}" aria-hidden="true">
        <button type="button" id="aiAskSelection">{{ __('Ask MyHEP AI') }}</button>
        <button type="button" id="aiWriteSelection">{{ __('Start writing') }}</button>
    </div>

    <div class="ai-confirm" id="aiConfirmDialog" role="dialog" aria-modal="true" aria-labelledby="aiConfirmTitle" aria-describedby="aiConfirmMessage" aria-hidden="true">
        <button type="button" class="ai-confirm-backdrop" data-confirm-cancel tabindex="-1" aria-label="{{ __('Cancel') }}"></button>
        <div class="ai-confirm-card">
            <div class="ai-confirm-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16M9 7V4h6v3m-9 0 1 13h10l1-13M10 11v5m4-5v5" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
            <h3 id="aiConfirmTitle">{{ __('Delete conversation?') }}</h3>
            <p id="aiConfirmMessage"></p>
            <div class="ai-confirm-actions">
                <button type="button" class="ai-confirm-button" data-confirm-cancel>{{ __('Cancel') }}</button>
                <button type="button" class="ai-confirm-button ai-confirm-button--danger" id="aiConfirmAccept">{{ __('Delete') }}</button>
            </div>
        </div>
    </div>

    @if(!$studentAiMode)
    <div class="ai-confirm" id="programReportDialog" role="dialog" aria-modal="true" aria-labelledby="programReportDialogTitle" aria-hidden="true">
        <button type="button" class="ai-confirm-backdrop" data-program-report-close tabindex="-1" aria-label="{{ __('Close') }}"></button>
        <div class="ai-confirm-card" data-lenis-prevent style="width:min(620px,calc(100vw - 2rem));text-align:left;">
            <h3 id="programReportDialogTitle">{{ __('Program Report Template') }}</h3>
            <p>{{ __('Choose your program and source files. MyHEP adds its attendance and questionnaire records automatically and saves the generated report under the selected program.') }}</p>
            <form id="programReportForm" class="program-report-form" method="post" enctype="multipart/form-data">@csrf
                <div class="ops-field"><label for="programReportProgram">{{ __('My program') }}</label><select id="programReportProgram" required><option value="">{{ __('Choose a program') }}</option>@foreach($ownedPrograms as $ownedProgram)<option value="{{ $ownedProgram->id }}" data-action="{{ route('admin.programs.report.generate', $ownedProgram->id) }}" data-requires-paperwork="{{ ($ownedProgram->registration_type ?? 'approved_program') === 'attendance_only_activity' ? '0' : '1' }}">{{ $ownedProgram->title }}</option>@endforeach</select></div>
                <div class="ops-field program-report-upload" id="programReportPaperworkField">
                    <div class="program-report-upload-head"><label for="programReportPaperwork">{{ __('Approved paperwork') }}</label><label class="program-report-add-file" for="programReportPaperwork"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 16V4m0 0L7.5 8.5M12 4l4.5 4.5M5 14v4a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-4" stroke-linecap="round" stroke-linejoin="round"/></svg>{{ __('Add paperwork') }}</label></div>
                    <input class="program-report-file-input" id="programReportPaperwork" name="paperwork_file" type="file" accept=".pdf,.docx,application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document">
                    <div class="program-report-attachments is-empty" id="programReportPaperworkPreview" data-drop-zone="paperwork" role="button" tabindex="0" aria-label="{{ __('Add or drop approved paperwork') }}" aria-live="polite"></div>
                    <small>{{ __('Required for approved programs. Attendance-only activities do not require paperwork.') }}</small>
                </div>
                <div class="ops-field program-report-upload">
                    <div class="program-report-upload-head"><label for="programReportImages">{{ __('Images after the program') }}</label><label class="program-report-add-file" for="programReportImages"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 5v14M5 12h14" stroke-linecap="round"/><rect x="3" y="3" width="18" height="18" rx="4"/></svg>{{ __('Add images') }}</label></div>
                    <input class="program-report-file-input" id="programReportImages" name="program_images[]" type="file" accept="image/jpeg,image/png,image/webp" multiple required>
                    <div class="program-report-attachments is-empty" id="programReportImagesPreview" data-drop-zone="images" role="button" tabindex="0" aria-label="{{ __('Add or drop activity images') }}" aria-live="polite"></div>
                    <small class="program-report-upload-status" id="programReportImagesStatus">{{ __('Add up to 8 activity images. You can choose more images again without replacing the current selection.') }}</small>
                </div>
                <div class="ops-field"><label for="programReportOutput">{{ __('Create report as') }}</label><select id="programReportOutput" name="output_format" required><option value="docx">DOCX</option><option value="pdf">PDF</option><option value="both">{{ __('DOCX and PDF') }}</option></select></div>
                <div class="program-report-progress" id="programReportProgress" role="status" aria-live="polite">
                    <strong>{{ __('Generating your official report') }}</strong>
                    <ol>
                        <li data-report-progress-step>{{ __('Uploading source files') }}</li>
                        <li data-report-progress-step>{{ __('Analysing program records') }}</li>
                        <li data-report-progress-step>{{ __('Creating the official report') }}</li>
                        <li data-report-progress-step>{{ __('Saving report files') }}</li>
                    </ol>
                </div>
                <div class="ai-confirm-actions"><button type="button" class="ai-confirm-button" data-program-report-close>{{ __('Cancel') }}</button><button id="programReportSubmit" type="submit" class="ai-confirm-button">{{ __('Generate Program Report') }}</button></div>
            </form>
        </div>
    </div>
    @endif

    @if(session('generated_report'))
    @php
        $generatedReport = session('generated_report');
    @endphp
    <div class="ai-confirm is-open" id="programReportCompleteDialog" role="dialog" aria-modal="true" aria-labelledby="programReportCompleteTitle" aria-describedby="programReportCompleteDescription" aria-hidden="false">
        <button type="button" class="ai-confirm-backdrop" data-program-report-complete-close tabindex="-1" aria-label="{{ __('Close') }}"></button>
        <div class="ai-confirm-card program-report-complete-card" data-lenis-prevent>
            <div class="program-report-complete-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12.5l4.2 4.2L19 7" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
            <h3 id="programReportCompleteTitle">{{ __('Program report generated successfully') }}</h3>
            <p id="programReportCompleteDescription">{{ __('Your report files were saved successfully. Download and review them before submitting the final report for approval.') }}</p>
            <div class="program-report-complete-program"><span>{{ __('Program') }}</span><span aria-hidden="true">&middot;</span><strong>{{ $generatedReport['program_title'] }}</strong></div>
            <div class="program-report-complete-files">
                @if($generatedReport['docx_url'])<a href="{{ $generatedReport['docx_url'] }}">{{ __('Download editable DOCX') }}</a>@endif
                @if($generatedReport['pdf_url'])<a href="{{ $generatedReport['pdf_url'] }}">{{ __('Download review PDF') }}</a>@endif
            </div>
            <p class="program-report-complete-note">{{ __('Generation creates a draft. Check the content, formatting, blank pages, names, dates, images, and signatures before submission.') }}</p>
            <div class="ai-confirm-actions">
                <button type="button" class="ai-confirm-button" data-program-report-complete-close>{{ __('Close') }}</button>
                <a class="ai-confirm-button is-primary" href="{{ $generatedReport['operations_url'] }}">{{ __('Review report workflow') }}</a>
                <a class="ai-confirm-button" href="{{ $generatedReport['details_url'] }}">{{ __('View program') }}</a>
            </div>
        </div>
    </div>
    @endif

    @unless($textOnlyAiMode)
    <button type="button" class="ai-tools-backdrop" id="aiToolsBackdrop" aria-label="{{ __('Close report tools') }}"></button>
    <aside class="ai-panel" id="aiToolsPanel" aria-hidden="true">
        <div class="ai-head">
            <div>
                <h3>{{ __('Quick Actions & Filters') }}</h3>
                <p class="ai-sub">{{ __('Pre-structured prompts for common admin workflows.') }}</p>
            </div>
            <button type="button" class="ai-btn" id="aiToolsClose">{{ __('Close') }}</button>
        </div>

        <div class="ops-body">
            <section class="ops-card">
                <h4 class="ops-title">{{ __('Task Templates') }}</h4>
                <div class="task-list">
                    <button type="button" class="task-btn" data-template="{{ __('Generate Monthly Report') }}">{{ __('Generate Monthly Report') }}</button>
                    <button type="button" class="task-btn" data-template="{{ __('Review Pending Fine Applications') }}">{{ __('Review Pending Fine Applications') }}</button>
                    <button type="button" class="task-btn" data-template="{{ __('Find Student by Matric No') }}">{{ __('Find Student by Matric No') }}</button>
                    <button type="button" class="task-btn" data-template="{{ __('Summarize Scholarship Status') }}">{{ __('Summarize Scholarship Status') }}</button>
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
                        <option value="all">{{ __('All') }}</option>
                        <option value="pending">{{ __('Pending') }}</option>
                        <option value="approved">{{ __('Approved') }}</option>
                        <option value="rejected">{{ __('Rejected') }}</option>
                        <option value="unpaid">{{ __('Unpaid') }}</option>
                        <option value="applied">{{ __('Applied') }}</option>
                        <option value="paid">{{ __('Paid') }}</option>
                    </select>
                </div>
                <div class="ops-field">
                    <label for="matricFilter">{{ __('Matric Number') }}</label>
                    <input id="matricFilter" type="text" placeholder="{{ __('23DIB23F1001') }}">
                </div>
                <div class="ops-field">
                    <label for="outputFormat">{{ __('Report Format') }}</label>
                    <select id="outputFormat">
                        <option value="auto">{{ __('Follow my request automatically') }}</option>
                        <option value="formal_report">{{ __('Formal report') }}</option>
                        <option value="executive_summary">{{ __('Executive summary') }}</option>
                        <option value="table">{{ __('Table format') }}</option>
                        <option value="csv">CSV</option>
                        <option value="json">JSON</option>
                    </select>
                </div>
            </section>

            <section class="ops-card">
                <h4 class="ops-title">{{ __('Report Source') }}</h4>
                <label class="ai-upload-drop" for="reportAttachment">
                    <span class="ai-upload-title">＋ {{ __('Attach a document or image') }}</span>
                    <span class="ai-upload-note">{{ __('Up to 10 PDF, CSV, XLSX, JPG, PNG or WebP files · Maximum 10 MB each. Gemini will inspect every attachment.') }}</span>
                </label>
            </section>

            <section class="ops-card">
                <h4 class="ops-title">{{ __('Data Sources') }}</h4>
                <ul class="ops-kv">
                    <li><span>students</span><strong>table</strong></li>
                    <li><span>scholarships</span><strong>table</strong></li>
                    <li><span>offenses</span><strong>table</strong></li>
                    <li><span>fine_payment_applications</span><strong>table</strong></li>
                </ul>
            </section>
        </div>
    </aside>
    @endunless
</div>
@endsection

@push('scripts')
@php
    $aiScriptText = [
        'ready' => __('Ready. Choose a task template or enter a custom request.'),
        'missingKey' => __('AI API key is not configured. Add an API key in .env, then clear config cache if needed.'),
        'scope' => __('Scope: students, scholarships, offenses, applications'),
        'thinking' => __('Thinking...'),
        'failed' => __('AI request failed.'),
        'empty' => __('No answer was returned.'),
        'unreachable' => __('AI service could not be reached.'),
        'draftPrompt' => __('Draft a short announcement for students based on the latest issue or pending action. Include title and body.'),
    ];
@endphp
<script>
(() => {
    const root = document.querySelector('.ai-admin');
    const clockNode = document.getElementById('aiClock');
    const stampNode = document.getElementById('aiStamp');
    const chatLog = document.getElementById('aiChatLog');
    const input = document.getElementById('aiInput');
    const sendBtn = document.getElementById('aiSendBtn');
    const reportMonth = document.getElementById('reportMonth');
    const statusFilter = document.getElementById('statusFilter');
    const matricFilter = document.getElementById('matricFilter');
    const outputFormat = document.getElementById('outputFormat');
    const attachmentInput = document.getElementById('reportAttachment');
    const attachmentPreview = document.getElementById('attachmentPreview');
    let selectedAttachments = [];
    let attachmentObjectUrls = [];
    const uploadShortcut = document.getElementById('aiUploadShortcut');
    const programReportShortcut = document.getElementById('programReportTemplateShortcut');
    const programReportDialog = document.getElementById('programReportDialog');
    const programReportProgram = document.getElementById('programReportProgram');
    const programReportForm = document.getElementById('programReportForm');
    const programReportPaperwork = document.getElementById('programReportPaperwork');
    const programReportPaperworkField = document.getElementById('programReportPaperworkField');
    const programReportPaperworkPreview = document.getElementById('programReportPaperworkPreview');
    const programReportImages = document.getElementById('programReportImages');
    const programReportImagesPreview = document.getElementById('programReportImagesPreview');
    const programReportImagesStatus = document.getElementById('programReportImagesStatus');
    const programReportProgress = document.getElementById('programReportProgress');
    const programReportSubmit = document.getElementById('programReportSubmit');
    let programReportSelectedImages = [];
    let programReportImageUrls = [];
    const reportToolsShortcut = document.getElementById('aiReportToolsShortcut');
    const formatBtn = document.getElementById('aiFormatBtn');
    const toolsPanel = document.getElementById('aiToolsPanel');
    const toolsBackdrop = document.getElementById('aiToolsBackdrop');
    const toolsClose = document.getElementById('aiToolsClose');
    const historyTrigger = document.getElementById('aiHistoryTrigger');
    const historyPanel = document.getElementById('aiHistoryPanel');
    const historyBackdrop = document.getElementById('aiHistoryBackdrop');
    const historyClose = document.getElementById('aiHistoryClose');
    const historyList = document.getElementById('aiHistoryList');
    const newConversationBtn = document.getElementById('aiNewConversation');
    const deleteAllHistoryBtn = document.getElementById('aiDeleteAllHistory');
    const historySearchButton = document.getElementById('aiHistorySearchButton');
    const historySearch = document.getElementById('aiHistorySearch');
    const historySearchInput = document.getElementById('aiHistorySearchInput');
    const confirmDialog = document.getElementById('aiConfirmDialog');
    const confirmTitle = document.getElementById('aiConfirmTitle');
    const confirmMessage = document.getElementById('aiConfirmMessage');
    const confirmAccept = document.getElementById('aiConfirmAccept');
    const selectionTools = document.getElementById('aiSelectionTools');
    const askSelection = document.getElementById('aiAskSelection');
    const writeSelection = document.getElementById('aiWriteSelection');
    const composeContext = document.getElementById('aiComposeContext');
    const composeContextText = document.getElementById('aiComposeContextText');
    const composeContextClear = document.getElementById('aiComposeContextClear');
    const locale = @json(app()->getLocale() === 'ms' ? 'ms-MY' : 'en-GB');
    let lastRequest = null;
    let lastAnswer = '';
    let currentConversationId = null;
    let requestInFlight = false;
    let selectedAiText = '';
    let selectedAiArticle = null;
    let composerSelectionContext = '';
    const emptyStateMarkup = chatLog?.innerHTML || '';
    let confirmResolver = null;

    const closeConfirm = (accepted = false) => {
        if (!confirmDialog?.classList.contains('is-open')) return;
        confirmDialog.classList.remove('is-open');
        confirmDialog.setAttribute('aria-hidden', 'true');
        const resolve = confirmResolver;
        confirmResolver = null;
        resolve?.(accepted);
    };
    const requestConfirmation = ({title, message, acceptLabel}) => new Promise(resolve => {
        if (!confirmDialog) return resolve(false);
        confirmResolver = resolve;
        confirmTitle.textContent = title;
        confirmMessage.textContent = message;
        confirmAccept.textContent = acceptLabel;
        confirmDialog.classList.add('is-open');
        confirmDialog.setAttribute('aria-hidden', 'false');
        requestAnimationFrame(() => confirmAccept.focus());
    });
    confirmDialog?.querySelectorAll('[data-confirm-cancel]').forEach(button => button.addEventListener('click', () => closeConfirm(false)));
    confirmAccept?.addEventListener('click', () => closeConfirm(true));
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && confirmDialog?.classList.contains('is-open')) {
            event.preventDefault();
            closeConfirm(false);
        }
    });

    const tick = () => {
        const now = new Date();
        if (clockNode) clockNode.textContent = now.toLocaleTimeString(locale, {
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

    if (!root || !chatLog || !input || !sendBtn) return;

    const aiText = {!! json_encode($aiScriptText, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};
    const initialMessage = root.dataset.aiEnabled === '1'
        ? aiText.ready
        : aiText.missingKey;
    const scopeText = aiText.scope;
    const providerText = root.dataset.aiProvider?.toUpperCase() || 'AI';
    const modelText = root.dataset.aiModel || '-';
    const metaText = `${scopeText} · ${providerText} / ${modelText}`;

    const syncChatFades = () => {
        const maxScroll = Math.max(0, chatLog.scrollHeight - chatLog.clientHeight);
        chatLog.classList.toggle('can-scroll-up', chatLog.scrollTop > 6);
        chatLog.classList.toggle('can-scroll-down', chatLog.scrollTop < maxScroll - 6);
    };
    const scrollChat = () => {
        chatLog.scrollTop = chatLog.scrollHeight;
        requestAnimationFrame(syncChatFades);
    };
    chatLog.addEventListener('scroll', syncChatFades, { passive:true });
    window.addEventListener('resize', syncChatFades, { passive:true });
    const attachmentTypeLabel = file => {
        const extension = file.name.split('.').pop()?.toLowerCase();
        if (extension === 'pdf') return 'PDF';
        if (extension === 'csv') return 'CSV';
        if (extension === 'xlsx') return 'XLSX';
        return file.type.startsWith('image/') ? (file.type.split('/')[1] || 'IMAGE').toUpperCase() : 'FILE';
    };

    const addMessage = (type, text, meta = '', attachments = [], messageId = null) => {
        const article = document.createElement('article');
        article.className = `msg ${type}`;
        const messageText = String(text || '');
        const isReport = type === 'ai' && (messageText.length > 650 || /(?:report|summary|findings|recommendations|laporan|ringkasan|cadangan)/iu.test(messageText));
        if (messageId) article.dataset.messageId = String(messageId);
        if (type === 'ai') {
            article.dataset.rawContent = messageText;
            article.classList.add(isReport ? 'is-report' : 'is-conversation');
        }
        if (type === 'ai') {
            article.classList.add('msg-rich');
            const lines = String(text).replace(/\r\n?/g, '\n').split('\n');
            let list = null;
            let metaGrid = null;
            let hasContent = false;
            const appendInline = (node, value) => {
                String(value).split(/(`[^`]+`|\*\*[^*]+\*\*|\*[^*]+\*)/g).filter(Boolean).forEach(part => {
                    if (part.startsWith('`') && part.endsWith('`')) {
                        const code = document.createElement('code');
                        code.textContent = part.slice(1, -1);
                        node.appendChild(code);
                    } else if (part.startsWith('**') && part.endsWith('**')) {
                        const strong = document.createElement('strong');
                        strong.textContent = part.slice(2, -2);
                        node.appendChild(strong);
                    } else if (part.startsWith('*') && part.endsWith('*')) {
                        const em = document.createElement('em');
                        em.textContent = part.slice(1, -1);
                        node.appendChild(em);
                    } else node.appendChild(document.createTextNode(part));
                });
            };
            const appendTable = (start) => {
                const rows = [];
                let cursor = start;
                while (cursor < lines.length && /^\s*\|.+\|\s*$/.test(lines[cursor])) {
                    rows.push(lines[cursor].trim().slice(1, -1).split('|').map(cell => cell.trim()));
                    cursor++;
                }
                if (rows.length < 2 || !rows[1].every(cell => /^:?-{3,}:?$/.test(cell))) return start;
                const wrap = document.createElement('div'); wrap.className = 'report-table-wrap';
                const table = document.createElement('table');
                const thead = document.createElement('thead'); const headRow = document.createElement('tr');
                rows[0].forEach(value => { const th = document.createElement('th'); appendInline(th, value); headRow.appendChild(th); });
                thead.appendChild(headRow); table.appendChild(thead);
                const tbody = document.createElement('tbody');
                rows.slice(2).forEach(row => { const tr = document.createElement('tr'); row.forEach(value => { const td = document.createElement('td'); appendInline(td, value); tr.appendChild(td); }); tbody.appendChild(tr); });
                table.appendChild(tbody); wrap.appendChild(table); article.appendChild(wrap); hasContent = true;
                return cursor - 1;
            };
            lines.forEach((raw, index) => {
                const line = raw.trim();
                if (!line) { list = null; metaGrid = null; return; }
                if (/^\s*\|.+\|\s*$/.test(raw)) { if (index > 0 && /^\s*\|.+\|\s*$/.test(lines[index - 1])) return; appendTable(index); list = null; metaGrid = null; return; }
                if (/^(?:-{3,}|_{3,}|\*{3,})$/.test(line)) { article.appendChild(document.createElement('hr')); list = null; metaGrid = null; hasContent = true; return; }
                const markdownHeading = line.match(/^(#{1,4})\s+(.+)$/);
                if (markdownHeading) { const h = document.createElement(markdownHeading[1].length === 1 ? 'h3' : 'h4'); appendInline(h, markdownHeading[2]); article.appendChild(h); list = null; metaGrid = null; hasContent = true; return; }
                const boldHeading = line.match(/^\*\*(.+)\*\*$/);
                if (boldHeading) { const h = document.createElement(hasContent ? 'h4' : 'h3'); h.textContent = boldHeading[1]; article.appendChild(h); list = null; metaGrid = null; hasContent = true; return; }
                const metadata = line.match(/^\*{1,2}([^:*]{2,40}):\*{1,2}\s*(.+)$/) || line.match(/^\*\*([^:*]{2,40}):\*\*\s*(.+)$/);
                if (metadata) {
                    if (!metaGrid) { metaGrid = document.createElement('div'); metaGrid.className = 'report-meta'; article.appendChild(metaGrid); }
                    const label = document.createElement('span'); label.className = 'report-meta-label'; label.textContent = metadata[1];
                    const value = document.createElement('span'); value.className = 'report-meta-value'; appendInline(value, metadata[2]);
                    metaGrid.append(label, value); list = null; hasContent = true; return;
                }
                const bullet = line.match(/^[-*]\s*(.+)$/);
                const numbered = line.match(/^\d+[.)]\s+(.+)$/);
                if (bullet || numbered) { const tag = numbered ? 'ol' : 'ul'; if (!list || list.tagName.toLowerCase() !== tag) { list = document.createElement(tag); article.appendChild(list); } const li = document.createElement('li'); appendInline(li, (bullet || numbered)[1]); list.appendChild(li); metaGrid = null; hasContent = true; return; }
                const clean = line.replace(/^\*([^*].*):\*\*\s*/, '$1: ');
                const isTitle = !hasContent && clean.length <= 100 && !/[.!?]$/.test(clean);
                const node = document.createElement(isTitle ? 'h3' : 'p'); appendInline(node, clean); article.appendChild(node); list = null; metaGrid = null; hasContent = true;
            });
        } else {
            if (type === 'user' && attachments.length) {
                article.classList.add('has-attachments');
                const attachmentList = document.createElement('div');
                attachmentList.className = 'ai-sent-attachments';
                attachments.forEach(file => {
                    const card = document.createElement('div');
                    card.className = 'ai-sent-attachment';
                    const fileType = attachmentTypeLabel(file);
                    const typeNode = document.createElement('span'); typeNode.className = 'ai-sent-attachment-type'; typeNode.textContent = fileType;
                    const nameNode = document.createElement('span'); nameNode.className = 'ai-sent-attachment-name'; nameNode.textContent = file.name;
                    if (file.type.startsWith('image/')) {
                        card.classList.add('is-image');
                        card.style.backgroundImage = `url(${URL.createObjectURL(file)})`;
                    }
                    card.append(typeNode, nameNode);
                    attachmentList.appendChild(card);
                });
                article.appendChild(attachmentList);
            }
            const pre = document.createElement('pre');
            pre.textContent = text;
            article.appendChild(pre);
        }
        if (meta) {
            const metaNode = document.createElement('span');
            metaNode.className = 'msg-meta';
            metaNode.textContent = meta;
            article.appendChild(metaNode);
        }
        if (isReport) {
            const content = document.createElement('div');
            content.className = 'ai-report-content';
            while (article.firstChild) content.appendChild(article.firstChild);
            const kicker = document.createElement('div');
            kicker.className = 'ai-report-kicker';
            kicker.textContent = @json(__('MyHEP AI report'));
            article.append(kicker, content);
            if (messageText.length > 900) {
                article.classList.add('is-collapsed');
                const toggle = document.createElement('button');
                toggle.type = 'button';
                toggle.className = 'ai-report-toggle';
                toggle.dataset.toggleReport = '';
                toggle.textContent = @json(__('Show full report'));
                article.appendChild(toggle);
            }
        }
        if (type === 'ai') {
            chatLog.querySelectorAll('.ai-message-actions').forEach(actions => { actions.hidden = true; });
            const actions = document.createElement('div');
            actions.className = 'ai-message-actions';
            actions.innerHTML = `<button type="button" class="ai-message-action" data-copy-ai-message><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="8" y="8" width="11" height="11" rx="2"/><path d="M16 8V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h2"/></svg><span>${@json(__('Copy'))}</span></button>${root.dataset.canEditAi === '1' && messageId ? `<button type="button" class="ai-message-action" data-edit-ai-message><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 20h4L19 9l-4-4L4 16v4Z"/><path d="m13.5 6.5 4 4"/></svg><span>${@json(__('Edit'))}</span></button>` : ''}<button type="button" class="ai-message-action" data-regenerate-ai-message><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 11a8 8 0 1 0-2.34 5.66L20 14"/><path d="M20 7v4h-4"/></svg><span>${@json(__('Regenerate'))}</span></button>`;
            article.appendChild(actions);
        }
        chatLog.appendChild(article);
        scrollChat();
        return article;
    };

    const activeConversationStorageKey = root.dataset.aiSessionKey;
    const rememberActiveConversation = id => {
        try {
            if (id) sessionStorage.setItem(activeConversationStorageKey, String(id));
            else sessionStorage.removeItem(activeConversationStorageKey);
        } catch (_) {}
    };
    const rememberedConversation = () => {
        try { return sessionStorage.getItem(activeConversationStorageKey); }
        catch (_) { return null; }
    };

    const resetChat = (forgetActive = true) => {
        closeExpandedEditor();
        currentConversationId = null;
        lastRequest = null;
        lastAnswer = '';
        if (forgetActive) rememberActiveConversation(null);
        historyList?.querySelectorAll('.ai-history-item').forEach(item => item.classList.remove('is-active'));
        root.classList.remove('has-chat');
        chatLog.innerHTML = root.dataset.aiEnabled === '1' ? emptyStateMarkup : '';
        syncChatFades();
        if (root.dataset.aiEnabled !== '1') addMessage('error', initialMessage, metaText);
    };

    const setHistoryOpen = (open) => {
        historyPanel?.classList.toggle('is-open', open);
        historyBackdrop?.classList.toggle('is-open', open);
        historyPanel?.setAttribute('aria-hidden', open ? 'false' : 'true');
        historyTrigger?.setAttribute('aria-expanded', open ? 'true' : 'false');
    };
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const conversationUrl = id => `${root.dataset.conversationsUrl}/${id}`;
    const upsertConversation = conversation => {
        if (!historyList || !conversation) return;
        historyList.querySelector('.ai-history-empty')?.remove();
        let item = historyList.querySelector(`[data-conversation-id="${conversation.id}"]`);
        if (!item) {
            item = document.createElement('div');
            item.className = 'ai-history-item';
            item.dataset.conversationId = conversation.id;
            item.innerHTML = '<span class="ai-history-title"></span><span class="ai-history-time"></span><span class="ai-history-item-actions"><button type="button" data-history-rename aria-label="Rename">✎</button><button type="button" data-history-delete aria-label="Delete">×</button></span>';
        }
        item.dataset.title = conversation.title || '';
        item.querySelector('.ai-history-title').textContent = conversation.title || @json(__('Untitled conversation'));
        item.querySelector('.ai-history-time').textContent = conversation.last_message_at || conversation.updated_at || '';
        historyList.prepend(item);
        historyList.querySelectorAll('.ai-history-item').forEach(node => node.classList.toggle('is-active', String(node.dataset.conversationId) === String(currentConversationId)));
    };
    const loadConversation = async id => {
        closeExpandedEditor();
        const response = await fetch(conversationUrl(id), {headers:{'Accept':'application/json'},credentials:'same-origin'});
        const payload = await response.json().catch(() => ({}));
        if (!response.ok) throw new Error(payload.message || @json(__('Conversation could not be loaded.')));
        currentConversationId = payload.conversation.id;
        rememberActiveConversation(currentConversationId);
        root.classList.add('has-chat');
        chatLog.innerHTML = '';
        lastAnswer = '';
        (payload.messages || []).forEach(message => {
            const type = message.role === 'assistant' ? 'ai' : 'user';
            addMessage(type, message.content, '', [], message.id);
            if (type === 'ai') lastAnswer = message.content;
            if (type === 'user') lastRequest = {message: message.content, template: null, filters: filters()};
        });
        upsertConversation(payload.conversation);
        setHistoryOpen(false);
        input.focus();
    };
    historyTrigger?.addEventListener('click', () => setHistoryOpen(true));
    historyClose?.addEventListener('click', () => setHistoryOpen(false));
    historyBackdrop?.addEventListener('click', () => setHistoryOpen(false));
    newConversationBtn?.addEventListener('click', () => { resetChat(); setHistoryOpen(false); input.focus(); });
    historySearchButton?.addEventListener('click', () => {
        historySearch?.classList.toggle('is-visible');
        if (historySearch?.classList.contains('is-visible')) historySearchInput?.focus();
    });
    historySearchInput?.addEventListener('input', () => {
        const query = historySearchInput.value.trim().toLocaleLowerCase();
        historyList?.querySelectorAll('.ai-history-item').forEach(item => {
            item.hidden = query !== '' && !String(item.dataset.title || '').toLocaleLowerCase().includes(query);
        });
    });
    const hideSelectionTools = () => {
        selectionTools?.classList.remove('is-open');
        selectionTools?.setAttribute('aria-hidden', 'true');
    };
    const openMessageEditor = (article, selectedText = '') => {
        const raw = article.dataset.rawContent || '';
        hideSelectionTools();
        window.getSelection()?.removeAllRanges();
        article.classList.add('is-writing');
        article.innerHTML = `<div class="ai-writing-head"><span class="ai-writing-label"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 20h4L19 9l-4-4L4 16v4Z"/><path d="m13.5 6.5 4 4"/></svg>${@json(__('Edit'))}</span><div class="ai-writing-tools"><button type="button" data-copy-writing aria-label="${@json(__('Copy'))}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="8" y="8" width="11" height="11" rx="2"/><path d="M16 8V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h2"/></svg></button><button type="button" data-expand-writing aria-label="${@json(__('Open editor'))}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 4h6v6M20 4l-7 7M10 20H4v-6M4 20l7-7"/></svg></button></div></div><div class="ai-writing-body"><div class="ai-edit-prompt"><input type="text" placeholder="${@json(__('Ask for changes'))}" aria-label="${@json(__('Ask for changes'))}"><button type="button" data-request-ai-edit aria-label="${@json(__('Apply AI changes'))}">↑</button></div><textarea class="ai-message-editor" aria-label="${@json(__('Edit AI response'))}"></textarea><div class="ai-edit-actions"><button type="button" data-cancel-ai-edit>${@json(__('Cancel'))}</button><button type="button" class="is-primary" data-save-ai-edit>${@json(__('Save changes'))}</button></div></div>`;
        const editor = article.querySelector('.ai-message-editor');
        editor.value = raw;
        editor.focus();
        const start = selectedText ? raw.indexOf(selectedText) : -1;
        if (start >= 0) editor.setSelectionRange(start, start + selectedText.length);
    };
    const closeExpandedEditor = () => {
        chatLog?.querySelectorAll('.msg.ai.is-writing.is-expanded').forEach(article => article.classList.remove('is-expanded'));
        document.body.classList.remove('ai-writing-expanded');
    };
    chatLog?.addEventListener('mouseup', () => {
        if (root.dataset.canEditAi !== '1') return;
        requestAnimationFrame(() => {
            const selection = window.getSelection();
            const text = selection?.toString().trim() || '';
            const range = selection?.rangeCount ? selection.getRangeAt(0) : null;
            const article = range?.commonAncestorContainer?.nodeType === Node.ELEMENT_NODE
                ? range.commonAncestorContainer.closest?.('.msg.ai[data-message-id]')
                : range?.commonAncestorContainer?.parentElement?.closest('.msg.ai[data-message-id]');
            if (!text || !range || !article || article.querySelector('.ai-message-editor')) return hideSelectionTools();
            selectedAiText = text;
            selectedAiArticle = article;
            const rect = range.getBoundingClientRect();
            selectionTools.style.left = `${Math.max(8, Math.min(window.innerWidth - 250, rect.left + (rect.width / 2) - 120))}px`;
            selectionTools.style.top = `${Math.max(8, rect.top - 48)}px`;
            selectionTools.classList.add('is-open');
            selectionTools.setAttribute('aria-hidden', 'false');
        });
    });
    askSelection?.addEventListener('click', () => {
        if (!selectedAiText) return;
        composerSelectionContext = selectedAiText;
        composeContextText.textContent = `“${selectedAiText}”`;
        composeContext.hidden = false;
        composeContext.classList.add('is-visible');
        hideSelectionTools();
        window.getSelection()?.removeAllRanges();
        input.value = '';
        input.placeholder = @json(__('Ask MyHEP AI about the selected text'));
        input.focus();
    });
    composeContextClear?.addEventListener('click', () => {
        composerSelectionContext = '';
        composeContext.classList.remove('is-visible');
        composeContext.hidden = true;
        composeContextText.textContent = '';
        input.placeholder = @json(__('Ask MyHEP AI anything'));
        input.focus();
    });
    writeSelection?.addEventListener('click', () => {
        if (selectedAiArticle) openMessageEditor(selectedAiArticle, selectedAiText);
    });
    chatLog?.addEventListener('scroll', hideSelectionTools, {passive:true});
    document.addEventListener('mousedown', event => {
        if (!selectionTools?.contains(event.target) && !event.target.closest?.('.msg.ai[data-message-id]')) hideSelectionTools();
    });
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && document.body.classList.contains('ai-writing-expanded')) closeExpandedEditor();
    });
    chatLog?.addEventListener('click', async event => {
        const article = event.target.closest('.msg.ai');
        if (!article) return;
        if (event.target.closest('[data-toggle-report]')) {
            const collapsed = article.classList.toggle('is-collapsed');
            event.target.closest('[data-toggle-report]').textContent = collapsed ? @json(__('Show full report')) : @json(__('Show less'));
            syncChatFades();
            return;
        }
        if (event.target.closest('[data-copy-ai-message]')) {
            await navigator.clipboard?.writeText(article.dataset.rawContent || '').catch(() => {});
            return;
        }
        if (event.target.closest('[data-regenerate-ai-message]')) {
            if (!requestInFlight && lastRequest) send(lastRequest.message, lastRequest.template, lastRequest.selectedContext || '');
            return;
        }
        if (event.target.closest('[data-edit-ai-message]')) {
            if (root.dataset.canEditAi === '1' && article.dataset.messageId) openMessageEditor(article);
            return;
        }
        if (root.dataset.canEditAi !== '1') return;
        if (event.target.closest('[data-copy-writing]')) {
            await navigator.clipboard?.writeText(article.querySelector('.ai-message-editor')?.value || '').catch(() => {});
            return;
        }
        if (event.target.closest('[data-expand-writing]')) {
            const expanded = !article.classList.contains('is-expanded');
            closeExpandedEditor();
            article.classList.toggle('is-expanded', expanded);
            document.body.classList.toggle('ai-writing-expanded', expanded);
            return;
        }
        if (event.target.closest('[data-cancel-ai-edit]')) {
            if (currentConversationId) await loadConversation(currentConversationId);
            return;
        }
        if (event.target.closest('[data-request-ai-edit]')) {
            const instruction = article.querySelector('.ai-edit-prompt input')?.value.trim();
            const context = article.querySelector('.ai-message-editor')?.value.trim();
            if (!instruction || !context || !currentConversationId || requestInFlight) return;
            await loadConversation(currentConversationId);
            send(instruction, null, context);
            return;
        }
        if (event.target.closest('[data-save-ai-edit]')) {
            const editor = article.querySelector('.ai-message-editor');
            const content = editor?.value.trim();
            if (!content || !currentConversationId) return;
            const save = event.target.closest('[data-save-ai-edit]');
            save.disabled = true;
            const response = await fetch(`${conversationUrl(currentConversationId)}/messages/${article.dataset.messageId}`, {method:'PATCH',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrfToken},credentials:'same-origin',body:JSON.stringify({content})});
            const payload = await response.json().catch(() => ({}));
            if (!response.ok) { save.disabled = false; return addMessage('error', payload.message || @json(__('AI response could not be updated.'))); }
            await loadConversation(currentConversationId);
        }
    });
    historyList?.addEventListener('click', async event => {
        const item = event.target.closest('.ai-history-item');
        if (!item) return;
        const id = item.dataset.conversationId;
        try {
            if (event.target.closest('[data-history-delete]')) {
                event.stopPropagation();
                if (!await requestConfirmation({
                    title: @json(__('Delete conversation?')),
                    message: @json(__('This conversation and all of its messages will be permanently deleted.')),
                    acceptLabel: @json(__('Delete conversation')),
                })) return;
                const response = await fetch(conversationUrl(id), {method:'DELETE',headers:{'Accept':'application/json','X-CSRF-TOKEN':csrfToken},credentials:'same-origin'});
                if (!response.ok) throw new Error(@json(__('Conversation could not be deleted.')));
                item.remove();
                if (String(currentConversationId) === String(id)) resetChat();
                if (!historyList.querySelector('.ai-history-item')) historyList.innerHTML = `<p class="ai-history-empty">${@json(__('Your AI conversations will appear here after you send a message.'))}</p>`;
                return;
            }
            if (event.target.closest('[data-history-rename]')) {
                event.stopPropagation();
                const title = prompt(@json(__('Rename conversation')), item.dataset.title || '');
                if (!title?.trim()) return;
                const response = await fetch(conversationUrl(id), {method:'PATCH',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrfToken},credentials:'same-origin',body:JSON.stringify({title:title.trim()})});
                const payload = await response.json().catch(() => ({}));
                if (!response.ok) throw new Error(payload.message || @json(__('Conversation could not be renamed.')));
                upsertConversation(payload.conversation);
                return;
            }
            await loadConversation(id);
        } catch (error) { addMessage('error', error.message || aiText.failed); }
    });
    deleteAllHistoryBtn?.addEventListener('click', async () => {
        if (!await requestConfirmation({
            title: @json(__('Delete all AI history?')),
            message: @json(__('This permanently deletes every AI conversation in your history. This action cannot be undone.')),
            acceptLabel: @json(__('Delete all history')),
        })) return;
        const response = await fetch(root.dataset.conversationsUrl, {method:'DELETE',headers:{'Accept':'application/json','X-CSRF-TOKEN':csrfToken},credentials:'same-origin'});
        if (!response.ok) return addMessage('error', @json(__('AI history could not be deleted.')));
        historyList.innerHTML = `<p class="ai-history-empty">${@json(__('Your AI conversations will appear here after you send a message.'))}</p>`;
        resetChat();
        setHistoryOpen(false);
    });

    const setToolsOpen = (open) => {
        toolsPanel?.classList.toggle('is-open', open);
        toolsBackdrop?.classList.toggle('is-open', open);
        toolsPanel?.setAttribute('aria-hidden', open ? 'false' : 'true');
        document.body.classList.toggle('ai-tools-open', open);
        if (open) reportMonth?.focus();
    };
    uploadShortcut?.addEventListener('click', () => { if (attachmentInput) attachmentInput.value = ''; attachmentInput?.click(); });
    reportToolsShortcut?.addEventListener('click', () => setToolsOpen(true));
    formatBtn?.addEventListener('click', () => setToolsOpen(true));
    toolsClose?.addEventListener('click', () => setToolsOpen(false));
    toolsBackdrop?.addEventListener('click', () => setToolsOpen(false));
    document.addEventListener('keydown', event => { if (event.key === 'Escape') setToolsOpen(false); });
    outputFormat?.addEventListener('change', () => {
        if (formatBtn) formatBtn.textContent = `${outputFormat.options[outputFormat.selectedIndex]?.text || @json(__('Auto format'))}⌄`;
    });

    const filters = () => ({
        report_month: reportMonth?.value || '',
        status: statusFilter?.value || 'all',
        matric_no: matricFilter?.value || '',
        output_format: outputFormat?.value || 'auto',
    });

    const setAttachmentFiles = files => {
        if (!attachmentInput || typeof DataTransfer === 'undefined') return;
        const transfer = new DataTransfer();
        files.forEach(file => transfer.items.add(file));
        attachmentInput.files = transfer.files;
    };
    const showAttachments = (limitReached = false) => {
        if (!attachmentPreview) return;
        attachmentObjectUrls.forEach(url => URL.revokeObjectURL(url));
        attachmentObjectUrls = [];
        attachmentPreview.replaceChildren();
        const files = selectedAttachments;
        attachmentPreview.classList.toggle('is-visible', files.length > 0 || limitReached);
        files.forEach((file, index) => {
            const card = document.createElement('article'); card.className = 'ai-compose-attachment';
            const thumb = document.createElement('div'); thumb.className = 'ai-compose-attachment-thumb';
            const fileType = attachmentTypeLabel(file);
            thumb.textContent = fileType;
            if (file.type.startsWith('image/')) {
                card.classList.add('is-image');
                const objectUrl = URL.createObjectURL(file); attachmentObjectUrls.push(objectUrl);
                thumb.textContent = ''; thumb.style.backgroundImage = `url(${objectUrl})`;
            }
            const copy = document.createElement('div'); copy.className = 'ai-compose-attachment-copy';
            const name = document.createElement('span'); name.className = 'ai-compose-attachment-name'; name.textContent = file.name;
            const details = document.createElement('span'); details.className = 'ai-compose-attachment-meta'; details.textContent = `${fileType} · ${(file.size / 1024 / 1024).toFixed(2)} MB`;
            copy.append(name, details);
            const remove = document.createElement('button'); remove.type = 'button'; remove.className = 'ai-compose-attachment-remove'; remove.setAttribute('aria-label', @json(__('Remove attachment'))); remove.textContent = '×';
            remove.addEventListener('click', () => {
                selectedAttachments = selectedAttachments.filter((_, fileIndex) => fileIndex !== index);
                setAttachmentFiles(selectedAttachments);
                showAttachments();
            });
            card.append(thumb, copy, remove); attachmentPreview.appendChild(card);
        });
        if (limitReached) {
            const notice = document.createElement('span'); notice.className = 'ai-compose-attachment-limit'; notice.textContent = @json(__('Only the first 10 files were selected.'));
            attachmentPreview.appendChild(notice);
        }
    };
    const clearComposerAttachments = () => {
        selectedAttachments = [];
        attachmentObjectUrls.forEach(url => URL.revokeObjectURL(url));
        attachmentObjectUrls = [];
        if (attachmentInput) attachmentInput.value = '';
        attachmentPreview?.replaceChildren();
        attachmentPreview?.classList.remove('is-visible');
    };
    attachmentInput?.addEventListener('change', () => {
        const incomingFiles = Array.from(attachmentInput.files || []);
        const seen = new Set(selectedAttachments.map(file => `${file.name}:${file.size}:${file.lastModified}:${file.type}`));
        incomingFiles.forEach(file => {
            const key = `${file.name}:${file.size}:${file.lastModified}:${file.type}`;
            if (!seen.has(key)) { selectedAttachments.push(file); seen.add(key); }
        });
        const limitReached = selectedAttachments.length > 10;
        selectedAttachments = selectedAttachments.slice(0, 10);
        setAttachmentFiles(selectedAttachments);
        showAttachments(limitReached);
    });
    const programReportFileKey = file => `${file.name}:${file.size}:${file.lastModified}:${file.type}`;
    const programReportFileSize = bytes => {
        if (bytes < 1024) return `${bytes} B`;
        if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
        return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
    };
    const setProgramReportInputFiles = (inputNode, files) => {
        if (!inputNode || typeof DataTransfer === 'undefined') return;
        const transfer = new DataTransfer();
        files.forEach(file => transfer.items.add(file));
        inputNode.files = transfer.files;
    };
    const createProgramReportAttachment = (file, index, kind) => {
        const card = document.createElement('article');
        card.className = 'program-report-attachment';

        const preview = document.createElement('span');
        preview.className = 'program-report-attachment-preview';
        if (kind === 'image') {
            const imageUrl = URL.createObjectURL(file);
            programReportImageUrls.push(imageUrl);
            const image = document.createElement('img');
            image.src = imageUrl;
            image.alt = '';
            preview.appendChild(image);
        } else {
            preview.textContent = (file.name.split('.').pop() || 'FILE').slice(0, 5).toUpperCase();
        }

        const details = document.createElement('span');
        const name = document.createElement('span');
        name.className = 'program-report-attachment-name';
        name.textContent = file.name;
        const size = document.createElement('span');
        size.className = 'program-report-attachment-size';
        size.textContent = programReportFileSize(file.size);
        details.append(name, size);

        const remove = document.createElement('button');
        remove.type = 'button';
        remove.className = 'program-report-attachment-remove';
        remove.setAttribute('aria-label', `${@json(__('Remove'))} ${file.name}`);
        remove.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" stroke-linecap="round"/></svg>';
        remove.addEventListener('click', () => {
            if (kind === 'image') {
                programReportSelectedImages.splice(index, 1);
                setProgramReportInputFiles(programReportImages, programReportSelectedImages);
                renderProgramReportImages();
            } else {
                programReportPaperwork.value = '';
                renderProgramReportPaperwork();
            }
        });

        card.append(preview, details, remove);
        return card;
    };
    const renderProgramReportPaperwork = () => {
        if (!programReportPaperworkPreview) return;
        programReportPaperworkPreview.replaceChildren();
        const file = programReportPaperwork?.files?.[0];
        programReportPaperworkPreview.classList.toggle('is-empty', !file);
        if (file) {
            programReportPaperworkPreview.appendChild(createProgramReportAttachment(file, 0, 'paperwork'));
            return;
        }
        const empty = document.createElement('p');
        empty.className = 'program-report-attachments-empty';
        empty.textContent = @json(__('Drop a PDF or DOCX here, or click Add paperwork.'));
        programReportPaperworkPreview.appendChild(empty);
    };
    const renderProgramReportImages = () => {
        if (!programReportImagesPreview) return;
        programReportImageUrls.forEach(url => URL.revokeObjectURL(url));
        programReportImageUrls = [];
        programReportImagesPreview.replaceChildren();
        programReportImagesPreview.classList.toggle('is-empty', programReportSelectedImages.length === 0);
        if (programReportSelectedImages.length) {
            programReportSelectedImages.forEach((file, index) => programReportImagesPreview.appendChild(createProgramReportAttachment(file, index, 'image')));
        } else {
            const empty = document.createElement('p');
            empty.className = 'program-report-attachments-empty';
            empty.textContent = @json(__('Drop activity images here, or click Add images.'));
            programReportImagesPreview.appendChild(empty);
        }
        if (programReportImagesStatus) {
            programReportImagesStatus.classList.remove('is-error');
            programReportImagesStatus.textContent = programReportSelectedImages.length
                ? `${programReportSelectedImages.length} / 8 ${@json(__('images selected'))}`
                : @json(__('Add up to 8 activity images. You can choose more images again without replacing the current selection.'));
        }
    };
    const addProgramReportImages = incoming => {
        const seen = new Set(programReportSelectedImages.map(programReportFileKey));
        let rejectedForLimit = 0;
        let rejectedForType = 0;
        incoming.forEach(file => {
            if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
                rejectedForType += 1;
                return;
            }
            const key = programReportFileKey(file);
            if (!seen.has(key) && programReportSelectedImages.length < 8) {
                programReportSelectedImages.push(file);
                seen.add(key);
            } else if (!seen.has(key)) {
                rejectedForLimit += 1;
            }
        });
        setProgramReportInputFiles(programReportImages, programReportSelectedImages);
        renderProgramReportImages();
        if ((rejectedForLimit > 0 || rejectedForType > 0) && programReportImagesStatus) {
            programReportImagesStatus.classList.add('is-error');
            programReportImagesStatus.textContent = rejectedForType > 0
                ? @json(__('Only JPG, PNG, and WEBP images are accepted. Unsupported files were not added.'))
                : @json(__('Maximum 8 activity images. Extra files were not added.'));
        }
    };
    programReportPaperwork?.addEventListener('change', renderProgramReportPaperwork);
    programReportImages?.addEventListener('change', () => {
        addProgramReportImages(Array.from(programReportImages.files || []));
    });
    const bindProgramReportDropZone = (zone, inputNode, kind) => {
        if (!zone || !inputNode) return;
        const stopDragEvent = event => {
            event.preventDefault();
            event.stopPropagation();
        };
        ['dragenter', 'dragover'].forEach(type => zone.addEventListener(type, event => {
            stopDragEvent(event);
            zone.classList.add('is-dragging');
            if (event.dataTransfer) event.dataTransfer.dropEffect = 'copy';
        }));
        ['dragleave', 'drop'].forEach(type => zone.addEventListener(type, event => {
            stopDragEvent(event);
            zone.classList.remove('is-dragging');
        }));
        zone.addEventListener('drop', event => {
            const files = Array.from(event.dataTransfer?.files || []);
            if (!files.length) return;
            if (kind === 'images') {
                addProgramReportImages(files);
                return;
            }
            const file = files[0];
            const isPaperwork = file.type === 'application/pdf'
                || file.type === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                || /\.(pdf|docx)$/i.test(file.name);
            if (!isPaperwork) {
                renderProgramReportPaperwork();
                const error = document.createElement('p');
                error.className = 'program-report-attachments-empty is-error';
                error.textContent = @json(__('Only PDF or DOCX paperwork is accepted.'));
                programReportPaperworkPreview.appendChild(error);
                return;
            }
            setProgramReportInputFiles(programReportPaperwork, [file]);
            renderProgramReportPaperwork();
        });
        zone.addEventListener('click', event => {
            if (!event.target.closest('.program-report-attachment-remove')) inputNode.click();
        });
        zone.addEventListener('keydown', event => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                inputNode.click();
            }
        });
    };
    bindProgramReportDropZone(programReportPaperworkPreview, programReportPaperwork, 'paperwork');
    bindProgramReportDropZone(programReportImagesPreview, programReportImages, 'images');
    renderProgramReportPaperwork();
    renderProgramReportImages();
    const setProgramReportOpen = open => {
        programReportDialog?.classList.toggle('is-open', open);
        programReportDialog?.setAttribute('aria-hidden', open ? 'false' : 'true');
    };
    const syncProgramReportRequirements = () => {
        const requiresPaperwork = programReportProgram?.selectedOptions?.[0]?.dataset.requiresPaperwork === '1';
        if (programReportPaperwork) programReportPaperwork.required = requiresPaperwork;
        if (programReportPaperworkField) programReportPaperworkField.hidden = !requiresPaperwork && Boolean(programReportProgram?.value);
    };
    programReportProgram?.addEventListener('change', syncProgramReportRequirements);
    programReportShortcut?.addEventListener('click', () => setProgramReportOpen(true));
    document.querySelectorAll('[data-program-report-close]').forEach(button => button.addEventListener('click', () => setProgramReportOpen(false)));
    const programReportCompleteDialog = document.getElementById('programReportCompleteDialog');
    document.querySelectorAll('[data-program-report-complete-close]').forEach(button => button.addEventListener('click', () => {
        programReportCompleteDialog?.classList.remove('is-open');
        programReportCompleteDialog?.setAttribute('aria-hidden', 'true');
    }));
    programReportForm?.addEventListener('submit', event => {
        const action = programReportProgram?.selectedOptions?.[0]?.dataset.action;
        if (!action) { event.preventDefault(); return; }
        programReportForm.action = action;
        programReportSubmit.disabled = true;
        programReportSubmit.textContent = @json(__('Generating...'));
        programReportProgress?.classList.add('is-active');
        const progressSteps = Array.from(programReportProgress?.querySelectorAll('[data-report-progress-step]') || []);
        progressSteps.forEach(step => step.classList.remove('is-active', 'is-done'));
        progressSteps[0]?.classList.add('is-active');
        [900, 2200, 4200].forEach((delay, index) => window.setTimeout(() => {
            progressSteps[index]?.classList.remove('is-active');
            progressSteps[index]?.classList.add('is-done');
            progressSteps[index + 1]?.classList.add('is-active');
        }, delay));
    });
    const requestedProgramReport = new URLSearchParams(window.location.search).get('program_report');
    if (requestedProgramReport && programReportProgram) {
        const option = Array.from(programReportProgram.options).find(item => item.value === requestedProgramReport);
        if (option) {
            programReportProgram.value = requestedProgramReport;
            syncProgramReportRequirements();
            setProgramReportOpen(true);
        }
    }

    const setBusy = (busy) => {
        requestInFlight = busy;
        sendBtn.disabled = busy || root.dataset.aiEnabled !== '1';
        document.querySelectorAll('.task-btn').forEach((button) => button.disabled = busy);
        input.disabled = busy;
        chatLog.querySelectorAll('[data-regenerate-ai-message]').forEach(button => {
            button.disabled = busy || !lastRequest;
            button.setAttribute('aria-busy', busy ? 'true' : 'false');
        });
    };

    const send = async (message = input.value.trim(), template = null, context = composerSelectionContext) => {
        if (requestInFlight || !message || root.dataset.aiEnabled !== '1') return;

        const selectedContext = context;
        lastRequest = { message, template, filters: filters(), selectedContext };
        if (!root.classList.contains('has-chat')) {
            root.classList.add('has-chat');
            chatLog.innerHTML = '';
        }
        // Snapshot the File objects for the request and move their visual cards
        // out of the composer before waiting for the AI response.
        const sentAttachments = [...selectedAttachments];
        clearComposerAttachments();
        addMessage('user', message, '', sentAttachments);
        input.value = '';
        setBusy(true);
        const loading = addMessage('ai loading', aiText.thinking);

        try {
            const requestBody = new FormData();
            requestBody.append('message', lastRequest.message);
            if (selectedContext) requestBody.append('selected_context', selectedContext);
            if (currentConversationId) requestBody.append('conversation_id', currentConversationId);
            if (lastRequest.template) requestBody.append('template', lastRequest.template);
            Object.entries(lastRequest.filters).forEach(([key, value]) => {
                if (value) requestBody.append(`filters[${key}]`, value);
            });
            sentAttachments.forEach(attachment => requestBody.append('attachments[]', attachment));
            if (selectedContext) composeContextClear?.click();

            const response = await fetch(root.dataset.aiUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                credentials: 'same-origin',
                body: requestBody,
            });

            const payload = await response.json().catch(() => ({}));
            loading.remove();

            if (!response.ok) {
                throw new Error(payload.message || aiText.failed);
            }

            lastAnswer = payload.answer || '';
            if (payload.conversation) {
                currentConversationId = payload.conversation.id;
                rememberActiveConversation(currentConversationId);
                upsertConversation(payload.conversation);
            }
            addMessage('ai', lastAnswer || aiText.empty, '', [], payload.assistant_message_id);
        } catch (error) {
            loading.remove();
            addMessage('error', error.message || aiText.unreachable);
        } finally {
            setBusy(false);
            input.focus();
        }
    };

    resetChat(false);
    const activeConversationId = rememberedConversation();
    if (activeConversationId) {
        loadConversation(activeConversationId).catch(() => resetChat());
    }

    sendBtn.addEventListener('click', () => send());
    input.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            send();
        }
    });

    root.addEventListener('click', event => {
        const button = event.target.closest('[data-template]');
        if (!button || !root.contains(button)) return;
        const template = button.dataset.template;
        setToolsOpen(false);
        input.value = template;
        send(template, template);
    });

})();
</script>
@endpush
