@php
    $studentAiMode = $studentAiMode ?? false;
@endphp
@extends('layouts.app')

@section('title', $studentAiMode ? __('AI Helper (Student)') : __('AI Helper (Admin)'))

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
    .ai-add-menu { position:absolute; left:0; bottom:58px; width:245px; padding:.55rem; display:none; gap:.25rem; border:1px solid color-mix(in srgb,var(--se-primary) 26%,var(--border)); border-radius:17px; background:color-mix(in srgb,var(--surface) 96%,transparent); box-shadow:0 20px 50px rgba(0,0,0,.24); backdrop-filter:blur(20px); }
    .ai-add-menu.is-open { display:grid; }
    .ai-add-action { display:flex; align-items:center; gap:.65rem; width:100%; padding:.68rem .7rem; border:0; border-radius:11px; background:transparent; color:var(--text); font:inherit; font-size:.78rem; font-weight:750; text-align:left; cursor:pointer; }
    .ai-add-action:hover { background:var(--se-primary-soft); color:var(--se-primary-strong); }
    .ai-add-action span { width:28px; height:28px; display:grid; place-items:center; border-radius:9px; background:color-mix(in srgb,var(--se-primary-soft) 80%,var(--surface)); }
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
    .msg-rich { white-space:normal; }
    .msg-rich h4 { margin:.1rem 0 .65rem; font-size:1rem; line-height:1.35; color:inherit; }
    .msg-rich p { margin:.35rem 0; }
    .msg-rich ul { margin:.35rem 0 .7rem; padding-left:1.25rem; }
    .msg-rich li { margin:.22rem 0; }
    .msg-rich strong { font-weight:850; }
    .ai-input:focus,.ops-field input:focus,.ops-field select:focus { outline:none; border-color:var(--primary); box-shadow:0 0 0 3px color-mix(in srgb,var(--primary) 18%,transparent); }

    /* Final layout safeguards against global admin form and panel rules. */
    .ai-admin > .ai-panel:first-child > .ai-head { display:none !important; }
    .ai-admin > .ai-panel:first-child { min-height:calc(100vh - 160px) !important; grid-template-rows:minmax(390px,1fr) auto auto !important; }
    .ai-chat-log { min-height:420px !important; max-height:none !important; }
    .ai-compose { bottom:18px; padding:0 10px !important; }
    .ai-compose-row { min-height:60px; grid-template-columns:46px minmax(0,1fr) auto 46px; align-items:center; padding:6px 7px; border-radius:30px; }
    .ai-input { width:100% !important; height:46px !important; min-height:46px !important; max-height:46px !important; padding:12px 6px !important; overflow-y:auto; resize:none !important; line-height:22px; border:0 !important; border-radius:0 !important; background:transparent !important; scrollbar-width:none; -ms-overflow-style:none; }
    .ai-input::-webkit-scrollbar { display:none; width:0; height:0; }
    .ai-compose-icon,.ai-send { align-self:center; flex:none; }
    .ai-compose-row .ai-send { background:linear-gradient(135deg,var(--se-primary-button-start),var(--se-primary-button-end)) !important; border-color:color-mix(in srgb,var(--se-primary-strong) 72%,var(--border)) !important; color:var(--se-primary-button-text) !important; box-shadow:0 10px 24px color-mix(in srgb,var(--se-primary) 28%,transparent) !important; }
    .ai-compose-row .ai-send:hover:not(:disabled) { transform:translateY(-1px); box-shadow:0 14px 30px color-mix(in srgb,var(--se-primary) 38%,transparent) !important; }
    .ai-admin--student .ai-compose-row { grid-template-columns:minmax(0,1fr) 46px !important; gap:.45rem; padding-left:18px; }
    .ai-admin--student .ai-input { min-width:0 !important; padding-left:14px !important; padding-right:10px !important; }
    .ai-admin--student .ai-send { justify-self:end; }
    .ai-format-pill { max-width:185px; overflow:hidden; text-overflow:ellipsis; }
    .ai-hint { margin:.42rem 0 0; opacity:.78; }
    .ai-tools-backdrop { z-index:11990 !important; }
    .ai-admin > aside.ai-panel { z-index:12000 !important; top:88px !important; right:-460px !important; bottom:18px !important; width:min(420px,calc(100vw - 28px)) !important; max-height:none !important; position:fixed !important; visibility:hidden; pointer-events:none; transform:none !important; transition:right .24s ease,visibility .24s ease !important; box-shadow:-24px 0 70px rgba(0,0,0,.3); }
    .ai-admin > aside.ai-panel.is-open { right:18px !important; visibility:visible; pointer-events:auto; }
    .ai-admin > aside.ai-panel .ai-head { padding:.9rem 1rem; background:color-mix(in srgb,var(--surface) 92%,var(--se-primary-soft)); }
    .ai-admin > aside.ai-panel .ai-sub { display:block; }
    @media(max-width:640px){
        .ai-admin > .ai-panel:first-child { min-height:calc(100vh - 132px) !important; grid-template-rows:minmax(350px,1fr) auto auto !important; }
        .ai-chat-log { min-height:360px !important; }
        .ai-compose { bottom:78px; padding:0 2px !important; }
        .ai-compose-row { grid-template-columns:44px minmax(0,1fr) 44px; }
        .ai-admin--student .ai-compose-row { grid-template-columns:minmax(0,1fr) 44px !important; padding-left:10px; }
        .ai-admin--student .ai-input { padding-left:12px !important; }
        .ai-admin > aside.ai-panel { top:72px !important; right:-105vw !important; bottom:8px !important; width:calc(100vw - 16px) !important; }
        .ai-admin > aside.ai-panel.is-open { right:8px !important; }
    }

</style>
@endpush

@section('header')
    <h2 style="margin:0;font-size:1.1rem;font-weight:700;color:var(--text);">{{ $studentAiMode ? __('AI Helper (Student)') : __('AI Helper (Admin)') }}</h2>
@endsection

@section('content')
<div class="ai-admin {{ $studentAiMode ? 'ai-admin--student' : 'ai-admin--admin' }}"
     data-ai-url="{{ route($studentAiMode ? 'student.ai-helper.ask' : 'admin.ai-helper.ask') }}"
     data-ai-enabled="{{ $aiEnabled ? '1' : '0' }}"
     data-ai-provider="{{ $aiProvider }}"
     data-ai-model="{{ $aiModel }}">
    <section class="ai-panel">
        <div class="ai-head">
            <div>
                <h3>{{ __('StudentEdge AI') }}</h3>
                <p class="ai-sub">{{ $studentAiMode ? __('Ask about your scholarship, offenses, payments, rules, or student portal steps.') : __('Use templates and filters to generate actionable admin outputs quickly.') }}</p>
            </div>
            <div class="ai-badges">
                <span class="ai-badge">BETA</span>
                <span class="ai-badge">{{ strtoupper($aiProvider) }}</span>
                <span class="ai-badge" id="aiClock">--:--</span>
            </div>
        </div>

        <div class="ai-chat-log" id="aiChatLog" aria-live="polite">
            <div class="ai-empty-state" id="aiEmptyState">
                <span class="ai-empty-orb" aria-hidden="true">✦</span>
                <h3>{{ __('What should we focus on?') }}</h3>
                <p>{{ $studentAiMode ? __('Ask about your own StudentEdge records or get general student portal guidance.') : __('Ask about StudentEdge records, create a written report, or attach a document or image as report evidence.') }}</p>
                <div class="ai-empty-chips">
                    @if($studentAiMode)
                    <button type="button" class="ai-empty-chip" data-template="{{ __('What can the AI Helper help me with?') }}">{{ __('Overview') }}</button>
                    <button type="button" class="ai-empty-chip" data-template="{{ __('What is my current scholarship status?') }}">{{ __('My scholarship') }}</button>
                    <button type="button" class="ai-empty-chip" data-template="{{ __('Explain my offense and payment records.') }}">{{ __('Payments') }}</button>
                    @else
                    <button type="button" class="ai-empty-chip" data-template="{{ __('Generate Monthly Report') }}">{{ __('Monthly report') }}</button>
                    <button type="button" class="ai-empty-chip" data-template="{{ __('Review Pending Fine Applications') }}">{{ __('Pending fines') }}</button>
                    <button type="button" class="ai-empty-chip" data-template="{{ __('Summarize Scholarship Status') }}">{{ __('Scholarship summary') }}</button>
                    @endif
                </div>
            </div>
        </div>

        <div class="ai-toolbar">
            <button type="button" class="ai-btn" id="aiCopyBtn">{{ __('Copy') }}</button>
            <button type="button" class="ai-btn" id="aiClearBtn">{{ __('Clear') }}</button>
            @unless($studentAiMode)<button type="button" class="ai-btn" id="aiDraftAnnouncementBtn">{{ __('Create Draft Announcement') }}</button>@endunless
            <button type="button" class="ai-btn" id="aiRegenerateBtn">{{ __('Regenerate') }}</button>
        </div>

        <div class="ai-compose">
            <div class="ai-compose-row">
                @unless($studentAiMode)
                <button type="button" class="ai-compose-icon" id="aiAddBtn" aria-expanded="false" aria-controls="aiAddMenu" aria-label="{{ __('Add report source') }}">＋</button>
                <div class="ai-add-menu" id="aiAddMenu">
                    <button type="button" class="ai-add-action" id="aiUploadShortcut"><span>↥</span>{{ __('Upload PDF or image') }}</button>
                    <button type="button" class="ai-add-action" id="aiReportToolsShortcut"><span>☷</span>{{ __('Report tools and filters') }}</button>
                    <button type="button" class="ai-add-action" data-template="{{ __('Generate Monthly Report') }}"><span>▤</span>{{ __('Generate monthly report') }}</button>
                    <button type="button" class="ai-add-action" data-template="{{ __('Review Pending Fine Applications') }}"><span>!</span>{{ __('Review pending fines') }}</button>
                </div>
                @endunless
                <textarea class="ai-input" id="aiInput" rows="1" placeholder="{{ __('Ask StudentEdge AI') }}"></textarea>
                @unless($studentAiMode)
                <button type="button" class="ai-format-pill" id="aiFormatBtn">{{ __('Auto format') }}⌄</button>
                @endunless
                <button type="button" class="ai-send" id="aiSendBtn" aria-label="{{ __('Send') }}" @disabled(!$aiEnabled)>↑</button>
            </div>
            <p class="ai-hint">{{ __('Enter sends. Shift+Enter adds newline. Independently verify AI-generated conclusions.') }}</p>
        </div>
    </section>

    @unless($studentAiMode)
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
                    <input id="matricFilter" type="text" placeholder="23DIB23F1001">
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
                    <span class="ai-upload-note">{{ __('PDF, JPG, PNG or WebP · Maximum 10 MB. Gemini will inspect the attachment when generating the report.') }}</span>
                    <input id="reportAttachment" type="file" accept="application/pdf,image/jpeg,image/png,image/webp">
                </label>
                <div class="ai-upload-preview" id="attachmentPreview">
                    <div class="ai-upload-thumb" id="attachmentThumb">PDF</div>
                    <div class="ai-upload-copy"><span class="ai-upload-name" id="attachmentName"></span><span class="ai-upload-size" id="attachmentSize"></span></div>
                    <button type="button" class="ai-upload-remove" id="attachmentRemove" aria-label="{{ __('Remove attachment') }}">×</button>
                </div>
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
    const copyBtn = document.getElementById('aiCopyBtn');
    const clearBtn = document.getElementById('aiClearBtn');
    const regenerateBtn = document.getElementById('aiRegenerateBtn');
    const draftBtn = document.getElementById('aiDraftAnnouncementBtn');
    const reportMonth = document.getElementById('reportMonth');
    const statusFilter = document.getElementById('statusFilter');
    const matricFilter = document.getElementById('matricFilter');
    const outputFormat = document.getElementById('outputFormat');
    const attachmentInput = document.getElementById('reportAttachment');
    const attachmentPreview = document.getElementById('attachmentPreview');
    const attachmentThumb = document.getElementById('attachmentThumb');
    const attachmentName = document.getElementById('attachmentName');
    const attachmentSize = document.getElementById('attachmentSize');
    const attachmentRemove = document.getElementById('attachmentRemove');
    const addBtn = document.getElementById('aiAddBtn');
    const addMenu = document.getElementById('aiAddMenu');
    const uploadShortcut = document.getElementById('aiUploadShortcut');
    const reportToolsShortcut = document.getElementById('aiReportToolsShortcut');
    const formatBtn = document.getElementById('aiFormatBtn');
    const toolsPanel = document.getElementById('aiToolsPanel');
    const toolsBackdrop = document.getElementById('aiToolsBackdrop');
    const toolsClose = document.getElementById('aiToolsClose');
    const locale = @json(app()->getLocale() === 'ms' ? 'ms-MY' : 'en-GB');
    let lastRequest = null;
    let lastAnswer = '';
    const emptyStateMarkup = chatLog?.innerHTML || '';

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

    const scrollChat = () => {
        chatLog.scrollTop = chatLog.scrollHeight;
    };

    const addMessage = (type, text, meta = '') => {
        const article = document.createElement('article');
        article.className = `msg ${type}`;
        if (type === 'ai') {
            article.classList.add('msg-rich');
            const lines = String(text).split(/\r?\n/);
            let list = null;
            const appendInline = (node, value) => {
                String(value).split(/(\*\*[^*]+\*\*)/g).filter(Boolean).forEach(part => {
                    if (part.startsWith('**') && part.endsWith('**')) {
                        const strong = document.createElement('strong');
                        strong.textContent = part.slice(2, -2);
                        node.appendChild(strong);
                    } else node.appendChild(document.createTextNode(part));
                });
            };
            lines.forEach(raw => {
                const line = raw.trim();
                if (!line) { list = null; return; }
                const heading = line.match(/^\*\*(.+)\*\*$/);
                if (heading) { const h = document.createElement('h4'); h.textContent = heading[1]; article.appendChild(h); list = null; return; }
                const bullet = line.match(/^[-*]\s*(.+)$/);
                if (bullet) { if (!list) { list = document.createElement('ul'); article.appendChild(list); } const li = document.createElement('li'); appendInline(li, bullet[1]); list.appendChild(li); return; }
                const p = document.createElement('p'); appendInline(p, line.replace(/^\*|\*$/g, '')); article.appendChild(p); list = null;
            });
        } else {
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
        chatLog.appendChild(article);
        scrollChat();
        return article;
    };

    const resetChat = () => {
        root.classList.remove('has-chat');
        chatLog.innerHTML = root.dataset.aiEnabled === '1' ? emptyStateMarkup : '';
        if (root.dataset.aiEnabled !== '1') addMessage('error', initialMessage, metaText);
    };

    const setToolsOpen = (open) => {
        toolsPanel?.classList.toggle('is-open', open);
        toolsBackdrop?.classList.toggle('is-open', open);
        toolsPanel?.setAttribute('aria-hidden', open ? 'false' : 'true');
        document.body.classList.toggle('ai-tools-open', open);
        if (open) reportMonth?.focus();
    };
    const setAddOpen = (open) => {
        addMenu?.classList.toggle('is-open', open);
        addBtn?.setAttribute('aria-expanded', open ? 'true' : 'false');
    };
    addBtn?.addEventListener('click', event => { event.stopPropagation(); setAddOpen(!addMenu?.classList.contains('is-open')); });
    document.addEventListener('click', event => { if (!addMenu?.contains(event.target) && event.target !== addBtn) setAddOpen(false); });
    uploadShortcut?.addEventListener('click', () => { setAddOpen(false); attachmentInput?.click(); });
    reportToolsShortcut?.addEventListener('click', () => { setAddOpen(false); setToolsOpen(true); });
    formatBtn?.addEventListener('click', () => setToolsOpen(true));
    toolsClose?.addEventListener('click', () => setToolsOpen(false));
    toolsBackdrop?.addEventListener('click', () => setToolsOpen(false));
    document.addEventListener('keydown', event => { if (event.key === 'Escape') { setAddOpen(false); setToolsOpen(false); } });
    outputFormat?.addEventListener('change', () => {
        if (formatBtn) formatBtn.textContent = `${outputFormat.options[outputFormat.selectedIndex]?.text || @json(__('Auto format'))}⌄`;
    });

    const filters = () => ({
        report_month: reportMonth?.value || '',
        status: statusFilter?.value || 'all',
        matric_no: matricFilter?.value || '',
        output_format: outputFormat?.value || 'auto',
    });

    const showAttachment = () => {
        const file = attachmentInput?.files?.[0];
        attachmentPreview?.classList.toggle('is-visible', Boolean(file));
        if (!file) return;
        attachmentName.textContent = file.name;
        attachmentSize.textContent = `${(file.size / 1024 / 1024).toFixed(2)} MB`;
        if (file.type.startsWith('image/')) {
            attachmentThumb.textContent = '';
            attachmentThumb.style.backgroundImage = `url(${URL.createObjectURL(file)})`;
            attachmentThumb.style.backgroundSize = 'cover';
        } else {
            attachmentThumb.style.backgroundImage = '';
            attachmentThumb.textContent = 'PDF';
        }
    };
    attachmentInput?.addEventListener('change', showAttachment);
    attachmentRemove?.addEventListener('click', () => {
        attachmentInput.value = '';
        showAttachment();
    });

    const setBusy = (busy) => {
        sendBtn.disabled = busy || root.dataset.aiEnabled !== '1';
        document.querySelectorAll('.task-btn').forEach((button) => button.disabled = busy);
        input.disabled = busy;
    };

    const send = async (message = input.value.trim(), template = null) => {
        if (!message || root.dataset.aiEnabled !== '1') return;

        lastRequest = { message, template, filters: filters() };
        if (!root.classList.contains('has-chat')) {
            root.classList.add('has-chat');
            chatLog.innerHTML = '';
        }
        addMessage('user', message);
        input.value = '';
        setBusy(true);
        const loading = addMessage('ai loading', aiText.thinking);

        try {
            const requestBody = new FormData();
            requestBody.append('message', lastRequest.message);
            if (lastRequest.template) requestBody.append('template', lastRequest.template);
            Object.entries(lastRequest.filters).forEach(([key, value]) => {
                if (value) requestBody.append(`filters[${key}]`, value);
            });
            const attachment = attachmentInput?.files?.[0];
            if (attachment) requestBody.append('attachment', attachment);

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
            addMessage('ai', lastAnswer || aiText.empty, `${(payload.provider || 'ai').toUpperCase()} / ${payload.model || ''} - ${payload.generated_at || ''}`);
        } catch (error) {
            loading.remove();
            addMessage('error', error.message || aiText.unreachable);
        } finally {
            setBusy(false);
            input.focus();
        }
    };

    resetChat();

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
        setAddOpen(false);
        setToolsOpen(false);
        input.value = template;
        send(template, template);
    });

    copyBtn?.addEventListener('click', async () => {
        if (!lastAnswer) return;
        await navigator.clipboard?.writeText(lastAnswer).catch(() => {});
    });

    clearBtn?.addEventListener('click', resetChat);

    regenerateBtn?.addEventListener('click', () => {
        if (lastRequest) send(lastRequest.message, lastRequest.template);
    });

    draftBtn?.addEventListener('click', () => {
        input.value = aiText.draftPrompt;
        input.focus();
    });
})();
</script>
@endpush
