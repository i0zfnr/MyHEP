@extends('layouts.app')

@section('title', __('Monthly Operations Report'))

@push('styles')
<style>
    .monthly-report { width:min(1320px,100%); margin:0 auto; display:grid; gap:18px; color:var(--text); }
    .report-hero { display:flex; align-items:center; justify-content:space-between; gap:24px; padding:22px 24px; border:1px solid var(--border); border-radius:20px; background:linear-gradient(135deg,var(--surface),var(--surface-soft)); box-shadow:var(--se-shadow-sm); }
    .report-hero-copy { min-width:0; }
    .report-eyebrow { display:block; margin-bottom:7px; color:var(--primary-dark); font-size:.7rem; font-weight:850; letter-spacing:.14em; text-transform:uppercase; }
    .report-hero h1 { margin:0; color:var(--text); font-size:clamp(1.65rem,3vw,2.25rem); line-height:1.08; letter-spacing:-.045em; }
    .report-hero p { max-width:680px; margin:9px 0 0; color:var(--text-muted); font-size:.88rem; line-height:1.6; }
    .report-actions { display:flex; align-items:flex-end; justify-content:flex-end; gap:10px; flex-wrap:wrap; }
    .report-actions form { display:flex; gap:8px; align-items:flex-end; }
    .report-month-field { display:grid; gap:6px; }
    .report-month-field label { color:var(--text-muted); font-size:.68rem; font-weight:800; letter-spacing:.07em; text-transform:uppercase; }
    .report-month-field input { min-width:185px; }
    .report-scope { display:flex; align-items:center; gap:8px; margin-top:13px; color:var(--text-muted); font-size:.76rem; }
    .report-scope strong { color:var(--text); }
    .report-scope span { padding:5px 10px; border:1px solid var(--border); border-radius:999px; background:var(--surface); }

    .report-module { display:grid; gap:16px; scroll-margin-top:90px; }
    .report-jump-nav { display:flex; gap:8px; flex-wrap:wrap; }
    .report-jump-nav a { display:inline-flex; align-items:center; padding:7px 11px; border:1px solid var(--border); border-radius:999px; background:var(--surface); color:var(--text-muted); text-decoration:none; font-size:.72rem; font-weight:800; }
    .report-jump-nav a:hover { border-color:var(--primary); color:var(--primary-dark); }
    .report-module-head { display:flex; justify-content:space-between; align-items:flex-end; gap:18px; }
    .report-module-head h2 { margin:3px 0 0; color:var(--text); font-size:1.3rem; letter-spacing:-.025em; }
    .report-module-head p { margin:5px 0 0; color:var(--text-muted); font-size:.8rem; }
    .report-module-badge { display:inline-flex; align-items:center; gap:7px; padding:7px 11px; border:1px solid var(--border); border-radius:999px; background:var(--surface); color:var(--text-muted); font-size:.7rem; font-weight:800; white-space:nowrap; }
    .report-module-badge::before { content:''; width:7px; height:7px; border-radius:50%; background:var(--se-success); box-shadow:0 0 0 4px var(--se-success-soft); }

    .report-kpis { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:12px; perspective:1300px; }
    .report-kpi { position:relative; min-width:0; padding:18px; border:1px solid var(--border); border-top:3px solid var(--kpi-accent,var(--primary)); border-radius:16px; background:linear-gradient(145deg,var(--surface),var(--surface-soft)); box-shadow:10px 13px 24px rgba(48,34,22,.11),inset 0 1px 0 rgba(255,255,255,.82); overflow:hidden; transform:rotateX(2deg) rotateY(-1deg); transform-style:preserve-3d; }
    .report-kpi::after { content:''; position:absolute; width:90px; height:90px; top:-52px; right:-42px; border-radius:50%; background:color-mix(in srgb,var(--kpi-accent,var(--primary)) 13%,transparent); }
    .report-kpi-label { display:block; min-height:30px; color:var(--text-muted); font-size:.72rem; font-weight:750; line-height:1.35; }
    .report-kpi-value { display:block; margin-top:8px; color:var(--text); font-size:clamp(1.65rem,3vw,2.2rem); font-weight:850; line-height:1; letter-spacing:-.045em; font-variant-numeric:tabular-nums; }
    .report-kpi-note { display:block; margin-top:9px; color:var(--text-muted); font-size:.67rem; line-height:1.4; }
    .tone-slate { --kpi-accent:#64748b; }
    .tone-gold { --kpi-accent:#c48628; }
    .tone-blue { --kpi-accent:#4f7f68; }
    .tone-green { --kpi-accent:#3f8f69; }
    .tone-violet { --kpi-accent:#7258bd; }
    .tone-red { --kpi-accent:#c14f5c; }

    .report-grid { display:grid; grid-template-columns:minmax(0,1.45fr) minmax(340px,.85fr); gap:16px; }
    .report-card { min-width:0; border:1px solid var(--border); border-radius:18px; background:linear-gradient(145deg,var(--surface),var(--surface-soft)); box-shadow:14px 18px 34px rgba(48,34,22,.13),inset 0 1px 0 rgba(255,255,255,.84); overflow:hidden; transform-style:preserve-3d; }
    .report-card-head { padding:19px 21px 0; }
    .report-card-kicker { color:var(--primary-dark); font-size:.67rem; font-weight:850; letter-spacing:.13em; text-transform:uppercase; }
    .report-card h3 { margin:5px 0 0; color:var(--text); font-size:1.05rem; letter-spacing:-.018em; }
    .report-card-copy { margin:5px 0 0; color:var(--text-muted); font-size:.75rem; line-height:1.5; }
    .report-card-body { padding:20px 21px 22px; }
    .report-empty { min-height:190px; display:grid; place-items:center; padding:24px; text-align:center; }
    .report-empty-mark { width:46px; height:46px; margin:0 auto 11px; display:grid; place-items:center; border-radius:14px; background:var(--surface-soft); border:1px solid var(--border); color:var(--primary-dark); font-size:1.25rem; font-weight:850; }
    .report-empty strong { display:block; color:var(--text); font-size:.92rem; }
    .report-empty span { display:block; max-width:350px; margin:5px auto 0; color:var(--text-muted); font-size:.74rem; line-height:1.5; }

    .report-bars { min-height:260px; display:flex; align-items:stretch; gap:12px; padding:12px 8px 0; perspective:900px; transform:rotateX(3deg); transform-origin:bottom; }
    .report-bar-group { flex:1; min-width:0; display:grid; grid-template-rows:1fr auto; gap:10px; }
    .report-bar-stage { min-height:205px; display:flex; align-items:flex-end; justify-content:center; gap:6px; padding:0 3px; border-bottom:1px solid var(--border); background:repeating-linear-gradient(to top,transparent 0,transparent 50px,color-mix(in srgb,var(--border) 65%,transparent) 51px); }
    .report-bar { position:relative; width:min(24px,38%); min-height:4px; height:max(4px,var(--bar-height)); border-radius:4px 4px 1px 1px; background:linear-gradient(90deg,#9a641f,#d99c45 52%,#b67826); box-shadow:8px 9px 14px rgba(91,56,20,.22); transform:translateZ(14px); transform-style:preserve-3d; }
    .report-bar::before { content:''; position:absolute; left:4px; right:-6px; top:-6px; height:6px; background:#efc47f; transform:skewX(-45deg); transform-origin:bottom; }
    .report-bar::after { content:''; position:absolute; top:-3px; right:-6px; width:6px; height:calc(100% + 3px); background:#855419; transform:skewY(-45deg); transform-origin:left; }
    .report-bar.secondary { background:linear-gradient(90deg,#285f46,#64b889 52%,#3d805f); box-shadow:8px 9px 14px rgba(35,91,64,.2); }
    .report-bar.secondary::before { background:#91d4ad; }
    .report-bar.secondary::after { background:#24563f; }
    .report-bar[data-zero="true"] { opacity:.22; }
    .report-bar-value { position:absolute; left:50%; bottom:calc(100% + 5px); transform:translateX(-50%); color:var(--text); font-size:.62rem; font-weight:850; font-variant-numeric:tabular-nums; }
    .report-bar-label { text-align:center; color:var(--text-muted); font-size:.65rem; line-height:1.25; }
    .report-bar-label strong { display:block; color:var(--text); font-size:.7rem; }
    .report-legend { display:flex; justify-content:center; gap:16px; flex-wrap:wrap; margin-top:15px; color:var(--text-muted); font-size:.68rem; }
    .report-legend span { display:inline-flex; align-items:center; gap:6px; }
    .report-legend i { width:8px; height:8px; border-radius:2px; background:#b47a2d; }
    .report-legend i.secondary { background:#3f8f69; }

    .report-donut-layout { min-height:260px; display:grid; grid-template-columns:minmax(160px,1fr) minmax(150px,.9fr); align-items:center; gap:18px; }
    .report-donut { width:170px; aspect-ratio:1; margin:auto; display:grid; place-items:center; border-radius:50%; background:var(--donut); position:relative; transform:rotateX(12deg) rotateZ(-2deg); transform-style:preserve-3d; box-shadow:0 22px 28px rgba(48,34,22,.2),inset 0 2px 0 rgba(255,255,255,.45); }
    .report-donut::before { content:''; position:absolute; inset:7px -3px -10px 7px; z-index:-1; border-radius:50%; background:color-mix(in srgb,var(--primary-dark) 45%,var(--surface-muted)); transform:translateZ(-18px); }
    .report-donut::after { content:''; position:absolute; inset:27px; border-radius:50%; background:var(--surface); box-shadow:inset 0 0 0 1px var(--border); }
    .report-donut-centre { position:relative; z-index:1; text-align:center; }
    .report-donut-centre strong { display:block; color:var(--text); font-size:1.55rem; letter-spacing:-.04em; }
    .report-donut-centre span { display:block; margin-top:2px; color:var(--text-muted); font-size:.62rem; }
    .report-status-list { display:grid; gap:12px; }
    .report-status-row { display:grid; grid-template-columns:auto 1fr auto; align-items:center; gap:8px; color:var(--text-muted); font-size:.7rem; }
    .report-status-row i { width:9px; height:9px; border-radius:50%; background:var(--status-color); }
    .report-status-row strong { color:var(--text); font-variant-numeric:tabular-nums; }

    .report-efficiency { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:12px; }
    .report-efficiency-item { padding:16px; border:1px solid var(--border); border-radius:14px; background:var(--surface-soft); }
    .report-efficiency-item span { display:block; color:var(--text-muted); font-size:.68rem; line-height:1.4; }
    .report-efficiency-item strong { display:block; margin-top:8px; color:var(--text); font-size:1.45rem; letter-spacing:-.03em; }
    .report-progress { height:7px; margin-top:12px; border-radius:999px; background:var(--surface-muted); overflow:hidden; }
    .report-progress span { width:var(--progress); height:100%; border-radius:inherit; background:linear-gradient(90deg,var(--primary-dark),var(--primary)); }
    .report-footer-note { margin:0; padding:2px 2px 8px; color:var(--text-muted); font-size:.68rem; text-align:right; }
    .report-card,.report-kpi,.report-efficiency-item { transition:transform .28s ease,box-shadow .28s ease,border-color .28s ease; }
    .report-card:hover,.report-kpi:hover,.report-efficiency-item:hover { transform:translateY(-4px); border-color:#cbd5e1; box-shadow:0 14px 30px rgba(15,23,42,.1); }
    .report-bar { transform-origin:bottom; animation:reportBarRise .62s cubic-bezier(.2,.75,.3,1) both; animation-delay:calc(var(--report-index,0) * 55ms); cursor:crosshair; transition:filter .18s ease,opacity .18s ease; }
    .report-bar:hover { filter:brightness(1.08) saturate(1.15); }
    .report-bar-stage:has(.report-bar:hover) .report-bar:not(:hover) { opacity:.35; }
    .report-donut { --report-sweep:100%; -webkit-mask:conic-gradient(#000 0 var(--report-sweep),transparent var(--report-sweep)); mask:conic-gradient(#000 0 var(--report-sweep),transparent var(--report-sweep)); animation:reportDonutSweep .8s ease-out both; transition:transform .3s ease,box-shadow .3s ease; cursor:crosshair; }
    .report-donut-layout:hover .report-donut { transform:scale(1.045); box-shadow:0 18px 36px rgba(15,23,42,.17); }
    .report-status-row { padding:6px 8px; margin-inline:-8px; border-radius:8px; transition:transform .2s ease,background .2s ease,opacity .2s ease; cursor:default; }
    .report-status-row:hover { transform:translateX(4px); background:var(--surface-soft); }
    .report-status-list:has(.report-status-row:hover) .report-status-row:not(:hover) { opacity:.4; }
    .report-progress span { display:block; transform-origin:left; animation:reportProgressGrow .7s ease-out both; }
    .monthly-hover-tooltip { position:fixed!important; inset:auto!important; z-index:1600!important; display:block!important; width:max-content!important; min-width:110px!important; max-width:220px!important; height:auto!important; margin:0!important; padding:9px 11px!important; border:1px solid rgba(255,255,255,.12)!important; border-radius:8px!important; background:rgba(17,24,39,.94)!important; color:#fff!important; box-shadow:0 10px 30px rgba(15,23,42,.22)!important; backdrop-filter:blur(10px); font-size:.68rem!important; line-height:1.45!important; pointer-events:none!important; opacity:0; transform:translate(-50%,calc(-100% - 10px)) scale(.96); transition:opacity .12s ease,transform .12s ease; }
    .monthly-hover-tooltip.is-visible { opacity:1; transform:translate(-50%,calc(-100% - 10px)) scale(1); }
    .monthly-report { --report-blue:var(--se-primary); --report-ink:var(--se-text); --report-muted:var(--se-text-muted); gap:24px; }
    .report-hero { padding:26px 28px; border-color:#e4e7ec; border-radius:16px; background:#fff; box-shadow:0 1px 3px rgba(16,24,40,.06); }
    .report-eyebrow,.report-card-kicker { color:var(--report-blue); }
    .report-hero h1,.report-module-head h2,.report-card h3 { color:var(--report-ink); }
    .report-hero p,.report-module-head p,.report-card-copy { color:var(--report-muted); }
    .report-scope span,.report-module-badge,.report-jump-nav a { border-color:#e4e7ec; background:#fff; }
    .report-kpis { gap:14px; perspective:none; }
    .report-kpi { min-height:142px; padding:18px 20px; border:1px solid #e4e7ec; border-top:0; border-left:4px solid var(--kpi-accent,var(--report-blue)); border-radius:14px; background:#fff; box-shadow:none; transform:none; }
    .report-kpi::after { width:72px; height:72px; top:-42px; right:-32px; opacity:.65; }
    .report-kpi-label { color:var(--report-muted); }
    .report-kpi-value { color:var(--report-ink); }
    .report-card { border-color:#e4e7ec; border-radius:16px; background:#fff; box-shadow:none; transform:none; }
    .report-card-head { padding:22px 24px 0; }
    .report-card-body { padding:20px 24px 24px; }
    .report-bars { min-height:250px; perspective:none; transform:none; }
    .report-bar-stage { border-bottom-color:#eaecf0; background:repeating-linear-gradient(to top,transparent 0,transparent 50px,#f2f4f7 51px); }
    .report-bar { border-radius:6px 6px 2px 2px; background:var(--report-blue); box-shadow:none; transform:none; }
    .report-bar::before,.report-bar::after { display:none; }
    .report-bar.secondary { background:var(--se-success); box-shadow:none; }
    .report-legend i { background:var(--report-blue); }
    .report-legend i.secondary { background:var(--se-success); }
    .report-donut { transform:none; box-shadow:0 12px 26px rgba(15,23,42,.12); }
    .report-donut::before { display:none; }
    .report-efficiency-item { border-color:#e4e7ec; background:#f8fafc; }
    .report-progress { background:#e2e8f0; }
    .report-progress span { background:linear-gradient(90deg,var(--se-primary),var(--se-success)); }
    .report-empty { min-height:150px; padding:28px 20px; }
    .report-empty-mark { border-color:#e4e7ec; background:#f8fafc; color:var(--report-blue); }
    .report-kpi::before { content:''; display:block; width:38px; height:38px; margin-bottom:14px; border-radius:11px; background:linear-gradient(to top,var(--kpi-accent,var(--report-blue)) 0 8px,transparent 8px) 9px 20px/4px 10px no-repeat,linear-gradient(to top,var(--kpi-accent,var(--report-blue)) 0 14px,transparent 14px) 17px 14px/4px 16px no-repeat,linear-gradient(to top,var(--kpi-accent,var(--report-blue)) 0 19px,transparent 19px) 25px 9px/4px 21px no-repeat,color-mix(in srgb,var(--kpi-accent,var(--report-blue)) 10%,#fff); box-shadow:inset 0 0 0 1px color-mix(in srgb,var(--kpi-accent,var(--report-blue)) 20%,transparent); }
    .report-workflow-summary { display:flex; align-items:flex-end; justify-content:space-between; gap:14px; margin-bottom:18px; padding:14px 15px; border:1px solid #e4e7ec; border-radius:12px; background:#f8fafc; }
    .report-workflow-summary span { color:var(--report-muted); font-size:.68rem; }
    .report-workflow-summary strong { display:block; margin-top:4px; color:var(--report-ink); font-size:1.7rem; line-height:1; }
    .report-workflow-summary em { padding:5px 9px; border-radius:999px; background:#eef2ff; color:var(--report-blue); font-size:.64rem; font-style:normal; font-weight:800; }
    .report-donut-layout.is-empty { min-height:0; display:block; }
    .report-donut-layout.is-empty .report-status-list { margin-top:0; }
    .report-efficiency { gap:0; border:1px solid #e4e7ec; border-radius:12px; overflow:hidden; }
    .report-efficiency-item { display:grid; grid-template-columns:minmax(180px,1fr) auto minmax(160px,.8fr); align-items:center; gap:18px; padding:17px 18px; border:0; border-bottom:1px solid #e4e7ec; border-radius:0; background:#fff; }
    .report-efficiency-item:last-child { border-bottom:0; }
    .report-efficiency-item strong { margin-top:0; font-size:1.05rem; }
    .report-progress { margin-top:0; }
    html[data-theme="dark"] .monthly-report,
    body[data-theme="dark"] .monthly-report { --report-blue:var(--se-primary); --report-ink:var(--se-text); --report-muted:var(--se-text-muted); }
    html[data-theme="dark"] .report-hero,
    html[data-theme="dark"] .report-kpi,
    html[data-theme="dark"] .report-card,
    body[data-theme="dark"] .report-hero,
    body[data-theme="dark"] .report-kpi,
    body[data-theme="dark"] .report-card { background:var(--se-surface); border-color:var(--se-border); }
    html[data-theme="dark"] .report-efficiency-item,
    body[data-theme="dark"] .report-efficiency-item { background:var(--se-surface-soft); border-color:var(--se-border); }
    html[data-theme="dark"] .report-bar-stage,
    body[data-theme="dark"] .report-bar-stage { border-bottom-color:var(--se-border); background:repeating-linear-gradient(to top,transparent 0,transparent 50px,color-mix(in srgb,var(--se-border) 55%,transparent) 51px); }
    @property --report-sweep { syntax:'<percentage>'; inherits:false; initial-value:0%; }
    @keyframes reportBarRise { from { scale:1 0; opacity:.2; } to { scale:1 1; opacity:1; } }
    @keyframes reportDonutSweep { from { --report-sweep:0%; } to { --report-sweep:100%; } }
    @keyframes reportProgressGrow { from { transform:scaleX(0); } to { transform:scaleX(1); } }

    html[data-theme="dark"] .report-kpi,
    html[data-theme="dark"] .report-card,
    body[data-theme="dark"] .report-kpi,
    body[data-theme="dark"] .report-card { background:linear-gradient(145deg,var(--se-surface),var(--se-surface-soft)); }
    html[data-theme="dark"] .report-hero,
    body[data-theme="dark"] .report-hero { background:linear-gradient(135deg,var(--se-surface),var(--se-surface-soft)); border-color:var(--se-border); box-shadow:var(--se-shadow-sm); }
    html[data-theme="dark"] .report-scope span,
    html[data-theme="dark"] .report-module-badge,
    html[data-theme="dark"] .report-jump-nav a,
    body[data-theme="dark"] .report-scope span,
    body[data-theme="dark"] .report-module-badge,
    body[data-theme="dark"] .report-jump-nav a { background:var(--se-surface-soft); border-color:var(--se-border); color:var(--se-text-muted); }
    html[data-theme="dark"] .report-workflow-summary,
    body[data-theme="dark"] .report-workflow-summary { background:var(--se-surface-soft); border-color:var(--se-border); }
    html[data-theme="dark"] .report-workflow-summary em,
    body[data-theme="dark"] .report-workflow-summary em { background:var(--se-primary-soft); color:var(--se-primary); }
    html[data-theme="dark"] .report-empty-mark,
    body[data-theme="dark"] .report-empty-mark { background:var(--se-primary-soft); border-color:color-mix(in srgb,var(--se-primary) 28%,var(--se-border)); color:var(--se-primary); }
    html[data-theme="dark"] .report-kpi::before,
    body[data-theme="dark"] .report-kpi::before { background:linear-gradient(to top,var(--kpi-accent,var(--report-blue)) 0 8px,transparent 8px) 9px 20px/4px 10px no-repeat,linear-gradient(to top,var(--kpi-accent,var(--report-blue)) 0 14px,transparent 14px) 17px 14px/4px 16px no-repeat,linear-gradient(to top,var(--kpi-accent,var(--report-blue)) 0 19px,transparent 19px) 25px 9px/4px 21px no-repeat,color-mix(in srgb,var(--kpi-accent,var(--report-blue)) 12%,var(--se-surface-soft)); }
    html[data-theme="dark"] .report-efficiency,
    body[data-theme="dark"] .report-efficiency { border-color:var(--se-border); }
    html[data-theme="dark"] .report-progress,
    body[data-theme="dark"] .report-progress { background:var(--se-surface-muted); }
    html[data-theme="dark"] .report-donut::after,
    body[data-theme="dark"] .report-donut::after { background:var(--se-surface); }

    /* Refined analytics hierarchy shared by light and dark themes. */
    .report-module-head {
        align-items:center;
    }
    .report-module-head h2 { font-size:1.2rem; }
    .report-module-head p { max-width:760px; line-height:1.5; }
    .report-module-badge {
        border-color:color-mix(in srgb,var(--se-success) 25%,var(--se-border));
        background:color-mix(in srgb,var(--se-success-soft) 48%,var(--se-surface));
        color:var(--se-text);
    }
    .report-kpi {
        min-height:156px;
        display:flex;
        flex-direction:column;
        box-shadow:0 10px 24px rgba(41,35,29,.08),inset 0 1px 0 rgba(255,255,255,.72);
    }
    .report-kpi::after { display:none; }
    .report-kpi::before { margin-bottom:12px; }
    .report-kpi-label { min-height:auto; color:color-mix(in srgb,var(--kpi-accent) 72%,var(--se-text)); }
    .report-kpi-note { margin-top:auto; padding-top:12px; }
    .report-card {
        border-color:color-mix(in srgb,var(--se-primary) 13%,var(--se-border));
        box-shadow:0 12px 30px rgba(41,35,29,.08);
    }
    .report-empty {
        margin:18px 24px 24px;
        border:1px dashed color-mix(in srgb,var(--se-primary) 24%,var(--se-border));
        border-radius:14px;
        background:color-mix(in srgb,var(--se-primary-soft) 22%,var(--se-surface));
    }
    .report-card:hover,.report-kpi:hover,.report-efficiency-item:hover {
        transform:translateY(-2px);
        border-color:color-mix(in srgb,var(--se-primary) 28%,var(--se-border));
        box-shadow:0 14px 30px color-mix(in srgb,var(--se-primary) 10%,transparent);
    }
    html[data-theme="dark"] .report-kpi,
    html[data-theme="dark"] .report-card,
    body[data-theme="dark"] .report-kpi,
    body[data-theme="dark"] .report-card {
        box-shadow:0 14px 30px rgba(0,0,0,.2),inset 0 1px 0 rgba(255,255,255,.035);
    }
    html[data-theme="dark"] .report-empty,
    body[data-theme="dark"] .report-empty {
        border-color:color-mix(in srgb,var(--se-primary) 25%,var(--se-border));
        background:color-mix(in srgb,var(--se-primary) 5%,var(--se-surface-soft));
    }

    @media(max-width:1100px) {
        .report-kpis { grid-template-columns:repeat(2,minmax(0,1fr)); }
        .report-grid { grid-template-columns:1fr; }
    }
    @media(max-width:720px) {
        .monthly-report { gap:17px; }
        .report-hero { align-items:stretch; flex-direction:column; padding:18px; }
        .report-actions, .report-actions form { width:100%; }
        .report-actions form { display:grid !important; grid-template-columns:1fr auto; align-items:end !important; }
        .report-month-field input { min-width:0; }
        .report-kpis { grid-template-columns:1fr 1fr; }
        .report-module-head { align-items:flex-start; flex-direction:column; }
        .report-module-badge { align-self:flex-start; }
        .report-donut-layout { grid-template-columns:1fr; }
        .report-efficiency { grid-template-columns:1fr; }
        .report-efficiency-item { grid-template-columns:1fr auto; }
        .report-efficiency-item .report-progress { grid-column:1 / -1; }
        .report-kpi,.report-bars,.report-donut { transform:none; }
    }
    @media(max-width:430px) {
        .report-kpis { grid-template-columns:1fr; }
        .report-actions form { grid-template-columns:1fr; }
        .report-actions .ui-btn { width:100%; }
        .report-card-head, .report-card-body { padding-left:16px; padding-right:16px; }
        .report-empty { margin:14px 16px 16px; }
        .report-bars { gap:6px; }
        .report-bar-stage { gap:3px; }
    }
    @media print {
        @page { size:A4 portrait; margin:10mm; }
        html,body { width:auto!important; height:auto!important; min-height:0!important; overflow:visible!important; background:#fff!important; print-color-adjust:exact; -webkit-print-color-adjust:exact; }
        .sidebar,.topbar,.page-header,.mobile-bottom-nav,.app-footer,.report-actions { display:none !important; }
        .app-layout,.main-wrap,.main-scroll-viewport,.main-scroll-inner { position:static!important; display:block!important; width:auto!important; height:auto!important; min-height:0!important; margin:0!important; overflow:visible!important; }
        .page-body { display:block!important; margin:0!important; padding:0!important; background:#fff!important; }
        .monthly-report { width:100%; max-width:none; color:#111; gap:9mm; }
        .report-hero { break-after:avoid-page; padding:0 0 6mm; border:0; border-bottom:1px solid #d0d5dd; border-radius:0; }
        .report-hero h1 { font-size:22pt; }
        .report-hero p { max-width:none; font-size:9pt; }
        .report-scope { margin-top:4mm; }
        .report-module { display:block; break-before:auto; }
        .report-module + .report-module { break-before:page; }
        .report-module-head { margin-bottom:4mm; break-after:avoid-page; }
        .report-module-head h2 { font-size:15pt; }
        .report-module-head p { font-size:8pt; }
        .report-module-badge { padding:4px 7px; font-size:6.5pt; }
        .report-kpis { gap:3mm; margin-bottom:4mm; break-inside:avoid-page; }
        .report-kpi { min-height:0; padding:4mm; box-shadow:none!important; break-inside:avoid; transform:none!important; }
        .report-kpi::before { width:7mm; height:7mm; margin-bottom:3mm; }
        .report-kpi-label { min-height:8mm; font-size:7pt; }
        .report-kpi-value { font-size:18pt; }
        .report-kpi-note { font-size:6.5pt; }
        .report-grid { gap:4mm; break-inside:avoid-page; }
        .report-card { box-shadow:none!important; break-inside:avoid-page; transform:none!important; }
        .report-card-head { padding:4mm 4mm 0; }
        .report-card-body { padding:4mm; }
        .report-card h3 { font-size:10pt; }
        .report-card-copy { font-size:7pt; }
        .report-empty { min-height:42mm; padding:5mm; }
        .report-efficiency { break-inside:avoid-page; }
        .report-efficiency-item { padding:3mm 4mm; }
        .report-footer-note { margin-top:3mm; }
        .monthly-hover-tooltip { display:none!important; }
        .report-bars,.report-donut { transform:none !important; }
        .report-jump-nav { display:none; }
        .report-kpis { grid-template-columns:repeat(4,1fr); }
        .report-grid { grid-template-columns:1.35fr .85fr; }
    }
    @media(prefers-reduced-motion:reduce) { .report-kpi,.report-bars,.report-donut { transform:none; } .report-bar,.report-donut,.report-progress span { animation:none!important; } }
</style>
@endpush

@section('header')
    <h2 style="margin:0;font-size:1rem;font-weight:700;">{{ __('Monthly Report') }}</h2>
@endsection

@section('content')
@php
    $disciplineDecisionTotal = $disciplineSummary ? $disciplineSummary['fine_approved'] + $disciplineSummary['fine_rejected'] : 0;
    $disciplinePendingPct = $disciplineSummary && $disciplineSummary['fine_total'] > 0 ? round(($disciplineSummary['fine_pending'] / $disciplineSummary['fine_total']) * 100, 2) : 0;
    $disciplineApprovedPct = $disciplineSummary && $disciplineSummary['fine_total'] > 0 ? round(($disciplineSummary['fine_status_approved'] / $disciplineSummary['fine_total']) * 100, 2) : 0;
    $scholarshipPendingPct = $scholarshipSummary && $scholarshipSummary['new_records'] > 0 ? round(($scholarshipSummary['pending'] / $scholarshipSummary['new_records']) * 100, 2) : 0;
    $scholarshipConfirmedCurrent = $scholarshipSummary ? $scholarshipSummary['status_confirmed'] : 0;
    $scholarshipConfirmedPct = $scholarshipSummary && $scholarshipSummary['new_records'] > 0 ? round(($scholarshipConfirmedCurrent / $scholarshipSummary['new_records']) * 100, 2) : 0;
    $disciplineTrendTotal = $disciplineSummary ? collect($disciplineSummary['trend'])->sum(fn ($period) => $period['primary'] + $period['secondary']) : 0;
    $scholarshipTrendTotal = $scholarshipSummary ? collect($scholarshipSummary['trend'])->sum(fn ($period) => $period['primary'] + $period['secondary']) : 0;
@endphp
<div class="monthly-report">
    <header class="report-hero">
        <div class="report-hero-copy">
            <span class="report-eyebrow">{{ __('Operations Analytics') }}</span>
            <h1>{{ __('Monthly Performance Report') }}</h1>
            <p>{{ __('A structured overview of discipline and scholarship activity, workflow decisions, current backlogs, and six-month operational trends.') }}</p>
            <div class="report-scope"><strong>{{ __('Report scope:') }}</strong><span>{{ $start->format('1 M Y') }} – {{ $end->format('d M Y') }}</span></div>
        </div>
        <div class="report-actions">
            <button class="ui-btn" type="button" onclick="window.print()">{{ __('Print / Save PDF') }}</button>
            <form method="GET" action="{{ route('admin.reports.monthly') }}">
                <div class="report-month-field">
                    <label for="reportMonth">{{ __('Report month') }}</label>
                    <input id="reportMonth" type="month" name="month" value="{{ $month }}" max="{{ now()->format('Y-m') }}">
                </div>
                <button class="ui-btn primary" type="submit">{{ __('Generate Report') }}</button>
            </form>
        </div>
    </header>

    @if($hasDisciplineAccess && $hasScholarshipAccess)
        <nav class="report-jump-nav" aria-label="{{ __('Report sections') }}">
            <a href="#disciplineReport">{{ __('Discipline Operations') }}</a>
            <a href="#scholarshipReport">{{ __('Scholarship Operations') }}</a>
        </nav>
    @endif

    @if($hasDisciplineAccess && $disciplineSummary)
    <section class="report-module" id="disciplineReport" aria-labelledby="disciplineReportTitle">
        <div class="report-module-head">
            <div><span class="report-eyebrow">{{ __('Discipline Analytics') }}</span><h2 id="disciplineReportTitle">{{ __('Discipline Operations') }}</h2><p>{{ __('Offense records, fine-payment decisions, vehicle stickers, and unresolved workload.') }}</p></div>
            <span class="report-module-badge">{{ __('Live database metrics') }}</span>
        </div>

        <div class="report-kpis">
            <article class="report-kpi tone-slate"><span class="report-kpi-label">{{ __('New Offenses') }}</span><strong class="report-kpi-value">{{ number_format($disciplineSummary['new_offenses']) }}</strong><span class="report-kpi-note">{{ __('Created during this month') }}</span></article>
            <article class="report-kpi tone-green"><span class="report-kpi-label">{{ __('Paid Offenses') }}</span><strong class="report-kpi-value">{{ number_format($disciplineSummary['paid_offenses']) }}</strong><span class="report-kpi-note">{{ __('Payment completed this month') }}</span></article>
            <article class="report-kpi tone-gold"><span class="report-kpi-label">{{ __('Fine Applications Pending') }}</span><strong class="report-kpi-value">{{ number_format($disciplineSummary['fine_pending']) }}</strong><span class="report-kpi-note">{{ __('Submitted within report scope') }}</span></article>
            <article class="report-kpi tone-blue"><span class="report-kpi-label">{{ __('Fine Applications Approved') }}</span><strong class="report-kpi-value">{{ number_format($disciplineSummary['fine_approved']) }}</strong><span class="report-kpi-note">{{ __('Positive decisions this month') }}</span></article>
        </div>

        <div class="report-grid">
            <article class="report-card">
                <div class="report-card-head"><span class="report-card-kicker">{{ __('Six Months') }}</span><h3>{{ __('Discipline Activity Trends') }}</h3><p class="report-card-copy">{{ __('Monthly offenses compared with approved fine-payment applications.') }}</p></div>
                @if($disciplineTrendTotal > 0)
                <div class="report-card-body">
                    <div class="report-bars">
                        @foreach($disciplineSummary['trend'] as $periodIndex => $period)
                        <div class="report-bar-group"><div class="report-bar-stage"><span class="report-bar" style="--bar-height:{{ $period['primary_height'] }}%;--report-index:{{ $periodIndex }}" data-report-tooltip="{{ $period['label'] }} {{ $period['year'] }}|{{ $period['primary'] }} new offenses" data-zero="{{ $period['primary'] === 0 ? 'true' : 'false' }}"><span class="report-bar-value">{{ $period['primary'] }}</span></span><span class="report-bar secondary" style="--bar-height:{{ $period['secondary_height'] }}%;--report-index:{{ $periodIndex + 1 }}" data-report-tooltip="{{ $period['label'] }} {{ $period['year'] }}|{{ $period['secondary'] }} approved payments" data-zero="{{ $period['secondary'] === 0 ? 'true' : 'false' }}"><span class="report-bar-value">{{ $period['secondary'] }}</span></span></div><div class="report-bar-label"><strong>{{ $period['label'] }}</strong>{{ $period['year'] }}</div></div>
                        @endforeach
                    </div>
                    <div class="report-legend"><span><i></i>{{ __('New offenses') }}</span><span><i class="secondary"></i>{{ __('Approved payments') }}</span></div>
                </div>
                @else
                <div class="report-empty"><div><div class="report-empty-mark">0</div><strong>{{ __('No discipline activity in this period') }}</strong><span>{{ __('Choose another report month or return after new offenses and payment decisions are recorded.') }}</span></div></div>
                @endif
            </article>
            <article class="report-card">
                <div class="report-card-head"><span class="report-card-kicker">{{ __('Decision Status') }}</span><h3>{{ __('Fine Application Distribution') }}</h3><p class="report-card-copy">{{ __('Current status of applications submitted this month.') }}</p></div>
                <div class="report-card-body report-donut-layout {{ $disciplineSummary['fine_total'] > 0 ? '' : 'is-empty' }}">
                    @if($disciplineSummary['fine_total'] > 0)
                    <div class="report-donut" style="--donut:{{ $disciplineSummary['fine_total'] > 0 ? 'conic-gradient(#c48628 0 '.$disciplinePendingPct.'%,#3f8f69 '.$disciplinePendingPct.'% '.($disciplinePendingPct+$disciplineApprovedPct).'%,#c14f5c '.($disciplinePendingPct+$disciplineApprovedPct).'% 100%)' : 'conic-gradient(var(--surface-muted) 0 100%)' }};"><div class="report-donut-centre"><strong>{{ number_format($disciplineSummary['fine_total']) }}</strong><span>{{ __('Applications') }}</span></div></div>
                    @else
                    <div class="report-workflow-summary"><div><span>{{ __('Fine approval rate') }}</span><strong>{{ number_format($disciplineSummary['fine_approval_rate'],1) }}%</strong></div><em>{{ __('No applications') }}</em></div>
                    @endif
                    <div class="report-status-list"><div class="report-status-row" style="--status-color:#4f46e5"><i></i><span>{{ __('Approval rate') }}</span><strong>{{ number_format($disciplineSummary['fine_approval_rate'],1) }}%</strong></div><div class="report-status-row" style="--status-color:#c48628"><i></i><span>{{ __('Pending') }}</span><strong>{{ $disciplineSummary['fine_pending'] }}</strong></div><div class="report-status-row" style="--status-color:#3f8f69"><i></i><span>{{ __('Approved') }}</span><strong>{{ $disciplineSummary['fine_status_approved'] }}</strong></div><div class="report-status-row" style="--status-color:#c14f5c"><i></i><span>{{ __('Rejected') }}</span><strong>{{ $disciplineSummary['fine_status_rejected'] }}</strong></div></div>
                </div>
            </article>
        </div>

        <article class="report-card">
            <div class="report-card-head"><span class="report-card-kicker">{{ __('Operational Monitoring') }}</span><h3>{{ __('Processing Efficiency') }}</h3><p class="report-card-copy">{{ __('Decision rates and unresolved workload requiring administrator attention.') }}</p></div>
            <div class="report-card-body report-efficiency">
                <div class="report-efficiency-item"><span>{{ __('Fine decisions completed') }}</span><strong>{{ number_format($disciplineDecisionTotal) }}</strong><div class="report-progress" style="--progress:{{ $disciplineSummary['fine_approval_rate'] }}%"><span></span></div></div>
                <div class="report-efficiency-item"><span>{{ __('Vehicle sticker approval rate') }}</span><strong>{{ number_format($disciplineSummary['sticker_approval_rate'],1) }}%</strong><div class="report-progress" style="--progress:{{ $disciplineSummary['sticker_approval_rate'] }}%"><span></span></div></div>
                <div class="report-efficiency-item"><span>{{ __('Current unresolved workload') }}</span><strong>{{ number_format($disciplineSummary['current_unpaid'] + $disciplineSummary['current_fine_backlog']) }}</strong><div class="report-progress" style="--progress:{{ ($disciplineSummary['current_unpaid'] + $disciplineSummary['current_fine_backlog']) > 0 ? 100 : 0 }}%"><span></span></div></div>
            </div>
        </article>
    </section>
    @endif

    @if($hasScholarshipAccess && $scholarshipSummary)
    <section class="report-module" id="scholarshipReport" aria-labelledby="scholarshipReportTitle">
        <div class="report-module-head">
            <div><span class="report-eyebrow">{{ __('Scholarship Analytics') }}</span><h2 id="scholarshipReportTitle">{{ __('Scholarship Operations') }}</h2><p>{{ __('New aid records, decisions, financial value, announcements, and pending workload.') }}</p></div>
            <span class="report-module-badge">{{ __('Live database metrics') }}</span>
        </div>

        <div class="report-kpis">
            <article class="report-kpi tone-slate"><span class="report-kpi-label">{{ __('New Scholarship Records') }}</span><strong class="report-kpi-value">{{ number_format($scholarshipSummary['new_records']) }}</strong><span class="report-kpi-note">{{ __('Created during this month') }}</span></article>
            <article class="report-kpi tone-green"><span class="report-kpi-label">{{ __('Records Confirmed') }}</span><strong class="report-kpi-value">{{ number_format($scholarshipSummary['confirmed']) }}</strong><span class="report-kpi-note">{{ __('Confirmed during this month') }}</span></article>
            <article class="report-kpi tone-gold"><span class="report-kpi-label">{{ __('Records Pending') }}</span><strong class="report-kpi-value">{{ number_format($scholarshipSummary['pending']) }}</strong><span class="report-kpi-note">{{ __('Created and awaiting decision') }}</span></article>
            <article class="report-kpi tone-blue"><span class="report-kpi-label">{{ __('Confirmed Financial Value') }}</span><strong class="report-kpi-value">RM {{ number_format($scholarshipSummary['confirmed_amount'],0) }}</strong><span class="report-kpi-note">{{ __('Value confirmed this month') }}</span></article>
        </div>

        <div class="report-grid">
            <article class="report-card">
                <div class="report-card-head"><span class="report-card-kicker">{{ __('Six Months') }}</span><h3>{{ __('Scholarship Activity Trends') }}</h3><p class="report-card-copy">{{ __('Monthly scholarship records compared with confirmed decisions.') }}</p></div>
                @if($scholarshipTrendTotal > 0)
                <div class="report-card-body">
                    <div class="report-bars">
                        @foreach($scholarshipSummary['trend'] as $periodIndex => $period)
                        <div class="report-bar-group"><div class="report-bar-stage"><span class="report-bar" style="--bar-height:{{ $period['primary_height'] }}%;--report-index:{{ $periodIndex }}" data-report-tooltip="{{ $period['label'] }} {{ $period['year'] }}|{{ $period['primary'] }} new records" data-zero="{{ $period['primary'] === 0 ? 'true' : 'false' }}"><span class="report-bar-value">{{ $period['primary'] }}</span></span><span class="report-bar secondary" style="--bar-height:{{ $period['secondary_height'] }}%;--report-index:{{ $periodIndex + 1 }}" data-report-tooltip="{{ $period['label'] }} {{ $period['year'] }}|{{ $period['secondary'] }} confirmed records" data-zero="{{ $period['secondary'] === 0 ? 'true' : 'false' }}"><span class="report-bar-value">{{ $period['secondary'] }}</span></span></div><div class="report-bar-label"><strong>{{ $period['label'] }}</strong>{{ $period['year'] }}</div></div>
                        @endforeach
                    </div>
                    <div class="report-legend"><span><i></i>{{ __('New records') }}</span><span><i class="secondary"></i>{{ __('Confirmed records') }}</span></div>
                </div>
                @else
                <div class="report-empty"><div><div class="report-empty-mark">0</div><strong>{{ __('No scholarship activity in this period') }}</strong><span>{{ __('Choose another report month or return after scholarship records and decisions are added.') }}</span></div></div>
                @endif
            </article>
            <article class="report-card">
                <div class="report-card-head"><span class="report-card-kicker">{{ __('Current Status') }}</span><h3>{{ __('Scholarship Status Distribution') }}</h3><p class="report-card-copy">{{ __('Current status of records created this month.') }}</p></div>
                <div class="report-card-body report-donut-layout {{ $scholarshipSummary['new_records'] > 0 ? '' : 'is-empty' }}">
                    @if($scholarshipSummary['new_records'] > 0)
                    <div class="report-donut" style="--donut:{{ $scholarshipSummary['new_records'] > 0 ? 'conic-gradient(#c48628 0 '.$scholarshipPendingPct.'%,#3f8f69 '.$scholarshipPendingPct.'% '.($scholarshipPendingPct+$scholarshipConfirmedPct).'%,#c14f5c '.($scholarshipPendingPct+$scholarshipConfirmedPct).'% 100%)' : 'conic-gradient(var(--surface-muted) 0 100%)' }};"><div class="report-donut-centre"><strong>{{ number_format($scholarshipSummary['new_records']) }}</strong><span>{{ __('Records') }}</span></div></div>
                    @else
                    <div class="report-workflow-summary"><div><span>{{ __('Confirmation rate') }}</span><strong>{{ number_format($scholarshipSummary['confirmation_rate'],1) }}%</strong></div><em>{{ __('No new records') }}</em></div>
                    @endif
                    <div class="report-status-list"><div class="report-status-row" style="--status-color:#4f46e5"><i></i><span>{{ __('Confirmation rate') }}</span><strong>{{ number_format($scholarshipSummary['confirmation_rate'],1) }}%</strong></div><div class="report-status-row" style="--status-color:#c48628"><i></i><span>{{ __('Pending') }}</span><strong>{{ $scholarshipSummary['pending'] }}</strong></div><div class="report-status-row" style="--status-color:#3f8f69"><i></i><span>{{ __('Confirmed') }}</span><strong>{{ $scholarshipConfirmedCurrent }}</strong></div><div class="report-status-row" style="--status-color:#c14f5c"><i></i><span>{{ __('Rejected') }}</span><strong>{{ $scholarshipSummary['rejected'] }}</strong></div></div>
                </div>
            </article>
        </div>

        <article class="report-card">
            <div class="report-card-head"><span class="report-card-kicker">{{ __('Operational Monitoring') }}</span><h3>{{ __('Scholarship Workflow') }}</h3><p class="report-card-copy">{{ __('Decision performance, communication activity, and current pending records.') }}</p></div>
            <div class="report-card-body report-efficiency">
                <div class="report-efficiency-item"><span>{{ __('Confirmation rate') }}</span><strong>{{ number_format($scholarshipSummary['confirmation_rate'],1) }}%</strong><div class="report-progress" style="--progress:{{ $scholarshipSummary['confirmation_rate'] }}%"><span></span></div></div>
                <div class="report-efficiency-item"><span>{{ __('Announcements published') }}</span><strong>{{ number_format($scholarshipSummary['announcements']) }}</strong><div class="report-progress" style="--progress:{{ min(100,$scholarshipSummary['announcements']*10) }}%"><span></span></div></div>
                <div class="report-efficiency-item"><span>{{ __('Current pending backlog') }}</span><strong>{{ number_format($scholarshipSummary['current_pending']) }}</strong><div class="report-progress" style="--progress:{{ $scholarshipSummary['current_pending'] > 0 ? 100 : 0 }}%"><span></span></div></div>
            </div>
        </article>
    </section>
    @endif

    <p class="report-footer-note">{{ __('Generated') }} {{ now()->format('d M Y, H:i') }} · {{ __('StudentEdge monthly operational analytics') }}</p>
</div>
<div class="monthly-hover-tooltip" data-report-tooltip-surface role="status" aria-live="polite"></div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('.monthly-report');
    const tooltip = document.querySelector('[data-report-tooltip-surface]');
    if (!root || !tooltip) return;

    root.querySelectorAll('.report-status-row').forEach((row) => {
        row.dataset.reportTooltip = `${row.querySelector('span')?.textContent.trim() || 'Status'}|${row.querySelector('strong')?.textContent.trim() || '0'}`;
    });
    root.querySelectorAll('.report-kpi').forEach((card) => {
        card.dataset.reportTooltip = `${card.querySelector('.report-kpi-label')?.textContent.trim() || 'Metric'}|${card.querySelector('.report-kpi-value')?.textContent.trim() || '0'}`;
    });
    root.querySelectorAll('.report-donut').forEach((donut) => {
        donut.dataset.reportTooltip = `${donut.querySelector('.report-donut-centre span')?.textContent.trim() || 'Total'}|${donut.querySelector('strong')?.textContent.trim() || '0'}`;
    });

    const show = (target, event) => {
        const [label, value = ''] = target.dataset.reportTooltip.split('|');
        tooltip.innerHTML = '';
        const strong = document.createElement('strong');
        const span = document.createElement('span');
        strong.textContent = label;
        span.textContent = value;
        strong.style.display = span.style.display = 'block';
        span.style.marginTop = '2px';
        span.style.color = 'rgba(255,255,255,.78)';
        tooltip.append(strong, span);
        tooltip.style.left = `${Math.max(70, Math.min(window.innerWidth - 70, event.clientX))}px`;
        tooltip.style.top = `${Math.max(70, event.clientY)}px`;
        tooltip.classList.add('is-visible');
    };
    const hide = () => tooltip.classList.remove('is-visible');
    root.addEventListener('pointermove', (event) => {
        const target = event.target.closest?.('[data-report-tooltip]');
        target ? show(target, event) : hide();
    });
    root.addEventListener('pointerleave', hide);
    window.addEventListener('scroll', hide, { passive:true });
});
</script>
@endpush
