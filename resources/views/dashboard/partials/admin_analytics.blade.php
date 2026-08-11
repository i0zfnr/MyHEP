@push('styles')
<style>
    .analytics-dashboard { display:none; grid-template-columns:minmax(0,1fr); gap:20px; }
    .adash[data-dashboard-mode="graphs"] .analytics-dashboard { display:grid; }
    .adash[data-dashboard-mode="graphs"] .stats-grid { display:none !important; }

    .an-kpis { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:14px; }
    .an-kpi { position:relative; min-width:0; padding:18px; border:1px solid var(--border); border-left:4px solid var(--kpi-accent,var(--primary)); border-radius:14px; background:linear-gradient(135deg,var(--surface),color-mix(in srgb,var(--surface-soft) 68%,var(--surface))); box-shadow:0 8px 20px rgba(48,34,22,.07); overflow:hidden; }
    .an-kpi::after { content:''; position:absolute; width:92px; height:92px; top:-58px; right:-42px; border-radius:50%; background:color-mix(in srgb,var(--kpi-accent,var(--primary)) 10%,transparent); pointer-events:none; }
    .an-kpi-top { display:flex; align-items:flex-start; justify-content:space-between; gap:8px; }
    .an-kpi-label { color:var(--text-muted); font-size:.7rem; font-weight:750; line-height:1.35; }
    .an-kpi-value { display:block; margin-top:8px; color:var(--text); font-size:clamp(1.5rem,2.6vw,2rem); font-weight:850; line-height:1; letter-spacing:-.045em; font-variant-numeric:tabular-nums; }
    .an-kpi-bottom { display:flex; align-items:flex-end; justify-content:space-between; gap:10px; margin-top:10px; }
    .an-kpi-sub { color:var(--text-muted); font-size:.66rem; line-height:1.4; }
    .an-delta { flex-shrink:0; display:inline-flex; align-items:center; padding:3px 8px; border-radius:999px; font-size:.64rem; font-weight:850; white-space:nowrap; }
    .an-delta.up { background:rgba(63,143,105,.14); color:#1d7a4f; }
    .an-delta.down { background:rgba(193,79,92,.14); color:#b0343f; }
    .an-delta.flat { background:var(--surface-soft); color:var(--text-muted); }
    .an-spark { width:62px; height:24px; flex-shrink:0; }

    .an-grid { display:grid; gap:18px; }
    .an-grid-3 { grid-template-columns:repeat(auto-fit,minmax(min(290px,100%),1fr)); }
    .an-grid-2 { grid-template-columns:repeat(auto-fit,minmax(min(420px,100%),1fr)); }
    .an-card { min-width:0; border:1px solid var(--border); border-radius:14px; background:var(--surface); box-shadow:0 10px 24px rgba(48,34,22,.07); overflow:hidden; }
    .an-card-head { padding:20px 22px 0; }
    .an-card-kicker { color:var(--primary-dark); font-size:.66rem; font-weight:850; letter-spacing:.13em; text-transform:uppercase; }
    .an-card h3 { margin:5px 0 0; color:var(--text); font-size:1rem; letter-spacing:-.018em; }
    .an-card-copy { margin:5px 0 0; color:var(--text-muted); font-size:.74rem; line-height:1.5; }
    .an-card-body { padding:20px 22px 22px; }
    .an-empty { min-height:150px; display:grid; place-items:center; padding:20px; text-align:center; }
    .an-empty-mark { width:42px; height:42px; margin:0 auto 10px; display:grid; place-items:center; border-radius:14px; background:var(--surface-soft); border:1px solid var(--border); color:var(--primary-dark); font-size:1.15rem; font-weight:850; }
    .an-empty strong { display:block; color:var(--text); font-size:.9rem; }
    .an-empty span { display:block; max-width:320px; margin:5px auto 0; color:var(--text-muted); font-size:.72rem; line-height:1.5; }
    .an-sr { position:absolute; width:1px; height:1px; padding:0; margin:-1px; overflow:hidden; clip:rect(0,0,0,0); white-space:nowrap; border:0; }

    .an-donut-layout { display:grid; grid-template-columns:minmax(140px,1fr) minmax(150px,.9fr); align-items:center; gap:18px; }
    .an-donut { width:150px; aspect-ratio:1; margin:auto; display:grid; place-items:center; border-radius:50%; background:var(--donut); position:relative; box-shadow:0 10px 20px rgba(48,34,22,.12),inset 0 0 0 1px rgba(255,255,255,.5); }
    .an-donut::after { content:''; position:absolute; inset:26px; border-radius:50%; background:var(--surface); box-shadow:inset 0 0 0 1px var(--border); }
    .an-donut-centre { position:relative; z-index:1; text-align:center; }
    .an-donut-centre strong { display:block; color:var(--text); font-size:1.4rem; letter-spacing:-.04em; }
    .an-donut-centre span { display:block; margin-top:2px; color:var(--text-muted); font-size:.6rem; }
    .an-legend { display:grid; gap:10px; }
    .an-legend-row { display:grid; grid-template-columns:auto 1fr auto; align-items:center; gap:8px; color:var(--text-muted); font-size:.68rem; }
    .an-legend-row i { width:9px; height:9px; border-radius:50%; background:var(--legend-color); }
    .an-legend-row strong { color:var(--text); font-variant-numeric:tabular-nums; white-space:nowrap; }
    .an-legend-row em { font-style:normal; opacity:.75; }

    .an-gauge-wrap { display:flex; flex-direction:column; align-items:center; gap:14px; }
    .an-gauge { width:150px; aspect-ratio:1; display:grid; place-items:center; border-radius:50%; background:var(--gauge); position:relative; box-shadow:0 10px 20px rgba(48,34,22,.12),inset 0 0 0 1px rgba(255,255,255,.5); }
    .an-gauge::after { content:''; position:absolute; inset:26px; border-radius:50%; background:var(--surface); box-shadow:inset 0 0 0 1px var(--border); }
    .an-gauge-centre { position:relative; z-index:1; text-align:center; }
    .an-gauge-centre strong { display:block; color:var(--text); font-size:1.5rem; letter-spacing:-.04em; }
    .an-gauge-centre span { display:block; margin-top:2px; color:var(--text-muted); font-size:.6rem; }
    .an-gauge-note { margin:0; color:var(--text-muted); font-size:.7rem; line-height:1.5; text-align:center; }

    .an-stack { display:grid; gap:12px; }
    .an-stack-row { display:grid; grid-template-columns:minmax(84px,auto) 1fr auto; align-items:center; gap:10px; color:var(--text-muted); font-size:.72rem; }
    .an-stack-track { height:14px; display:flex; overflow:hidden; border-radius:999px; background:var(--surface-soft); box-shadow:inset 0 1px 2px rgba(0,0,0,.08); }
    .an-stack-seg { height:100%; }
    .an-stack-total { color:var(--text); font-weight:800; font-variant-numeric:tabular-nums; white-space:nowrap; }
    .an-stack-legend { display:flex; flex-wrap:wrap; gap:12px; margin-top:6px; color:var(--text-muted); font-size:.66rem; }
    .an-stack-legend span { display:inline-flex; align-items:center; gap:6px; }
    .an-stack-legend i { width:8px; height:8px; border-radius:2px; background:var(--legend-color); }

    .an-treemap { display:flex; flex-wrap:wrap; gap:6px; align-content:flex-start; }
    .an-tile { position:relative; min-width:0; flex-basis:110px; flex-grow:1; padding:10px 12px; border:1px solid color-mix(in srgb,var(--tile,#3f8f69) 32%,var(--border)); border-radius:10px; background:color-mix(in srgb,var(--tile,#3f8f69) 10%,var(--surface)); overflow:hidden; }
    .an-tile strong { display:block; color:var(--text); font-size:.95rem; letter-spacing:-.02em; font-variant-numeric:tabular-nums; }
    .an-tile span { display:block; margin-top:2px; color:var(--text-muted); font-size:.62rem; line-height:1.35; }

    .an-hbar { display:grid; gap:10px; }
    .an-hbar-row { display:grid; grid-template-columns:minmax(140px,1.1fr) minmax(120px,2fr) auto; align-items:center; gap:10px; color:var(--text-muted); font-size:.68rem; }
    .an-hbar-label { line-height:1.35; }
    .an-hbar-track { height:9px; border-radius:999px; background:var(--surface-soft); overflow:hidden; box-shadow:inset 0 1px 2px rgba(0,0,0,.08); }
    .an-hbar-fill { height:100%; border-radius:inherit; background:linear-gradient(90deg,var(--primary-dark),var(--primary)); }
    .an-hbar-value { color:var(--text); font-weight:800; font-variant-numeric:tabular-nums; text-align:right; }

    .an-grouped { display:grid; gap:12px; }
    .an-group { display:grid; grid-template-columns:minmax(90px,auto) 1fr auto; align-items:center; gap:10px; color:var(--text-muted); font-size:.7rem; }
    .an-group-pair { display:flex; align-items:flex-end; gap:6px; height:34px; }
    .an-group-bar { width:min(26px,40%); border-radius:4px 4px 2px 2px; background:linear-gradient(180deg,var(--primary),var(--primary-dark)); }
    .an-group-bar.last { background:var(--surface-muted); }
    .an-group-value { color:var(--text); font-weight:800; font-variant-numeric:tabular-nums; white-space:nowrap; }
    .an-group-key { display:flex; gap:12px; margin-top:6px; color:var(--text-muted); font-size:.66rem; }
    .an-group-key span { display:inline-flex; align-items:center; gap:6px; }
    .an-group-key i { width:9px; height:9px; border-radius:3px; background:var(--primary); }
    .an-group-key i.last { background:var(--surface-muted); }

    .an-columns { display:flex; align-items:flex-end; gap:6px; height:170px; padding:6px 2px 0; border-bottom:1px solid var(--border); background:repeating-linear-gradient(to top,transparent 0,transparent 42px,color-mix(in srgb,var(--border) 60%,transparent) 43px); }
    .an-col { flex:1; min-width:0; display:flex; flex-direction:column; align-items:center; gap:4px; height:100%; }
    .an-col-stage { width:100%; height:140px; display:flex; align-items:flex-end; justify-content:center; }
    .an-col-bar { width:min(22px,60%); min-height:3px; border-radius:5px 5px 2px 2px; background:linear-gradient(180deg,color-mix(in srgb,var(--primary) 72%,white),var(--primary)); box-shadow:0 4px 10px color-mix(in srgb,var(--primary) 16%,transparent); }
    .an-col[data-zero="true"] .an-col-bar { opacity:.25; }
    .an-col-label { color:var(--text-muted); font-size:.6rem; font-weight:700; white-space:nowrap; }

    .an-trend-svg { width:100%; height:170px; display:block; }
    .an-trend-svg .an-gridline { stroke:color-mix(in srgb,var(--border) 62%,transparent); stroke-width:1; }
    .an-trend-line { fill:none; stroke:var(--primary); stroke-width:2.5; stroke-linejoin:round; stroke-linecap:round; }
    .an-trend-area { fill:url(#anAreaGrad); }
    .an-trend-dot { fill:var(--surface); stroke:var(--primary); stroke-width:2; }
    .an-trend-labels { display:flex; justify-content:space-between; margin-top:4px; color:var(--text-muted); font-size:.6rem; }

    .an-heat { display:grid; grid-template-columns:26px repeat(7,1fr); gap:5px; align-items:center; }
    .an-heat-dow { color:var(--text-muted); font-size:.58rem; font-weight:800; text-align:center; text-transform:uppercase; }
    .an-heat-cell { aspect-ratio:1; border-radius:5px; background:var(--surface-soft); border:1px solid var(--border); }
    .an-heat-cell[data-lvl="1"] { background:color-mix(in srgb,var(--primary) 22%,var(--surface)); border-color:color-mix(in srgb,var(--primary) 30%,var(--border)); }
    .an-heat-cell[data-lvl="2"] { background:color-mix(in srgb,var(--primary) 42%,var(--surface)); border-color:color-mix(in srgb,var(--primary) 46%,var(--border)); }
    .an-heat-cell[data-lvl="3"] { background:color-mix(in srgb,var(--primary) 64%,var(--surface)); border-color:color-mix(in srgb,var(--primary) 66%,var(--border)); }
    .an-heat-cell[data-lvl="4"] { background:var(--primary); border-color:var(--primary-dark); }
    .an-heat-foot { display:flex; justify-content:space-between; align-items:center; margin-top:10px; color:var(--text-muted); font-size:.66rem; }
    .an-heat-legend { display:inline-flex; align-items:center; gap:5px; }
    .an-heat-legend i { width:11px; height:11px; border-radius:4px; }

    .tone-slate { --kpi-accent:#64748b; }
    .tone-gold { --kpi-accent:#c48628; }
    .tone-blue { --kpi-accent:#28686c; }
    .tone-green { --kpi-accent:#3f8f69; }
    .tone-violet { --kpi-accent:#7258bd; }
    .tone-red { --kpi-accent:#c14f5c; }

    html[data-theme="dark"] .an-kpi,
    html[data-theme="dark"] .an-card,
    body[data-theme="dark"] .an-kpi,
    body[data-theme="dark"] .an-card { background:linear-gradient(145deg,var(--se-surface),var(--se-surface-soft)); }
    html[data-theme="dark"] .an-donut::after,
    html[data-theme="dark"] .an-gauge::after,
    body[data-theme="dark"] .an-donut::after,
    body[data-theme="dark"] .an-gauge::after { background:var(--se-surface); }
    html[data-theme="dark"] .an-trend-dot,
    body[data-theme="dark"] .an-trend-dot { fill:var(--se-surface); }
    html[data-theme="dark"] .an-tile,
    body[data-theme="dark"] .an-tile { background:color-mix(in srgb,var(--tile,#3f8f69) 16%,var(--se-surface)); }

    /* Reporting canvas inspired by modern analytics dashboards: quiet panels,
       a single data accent, and a clear primary-chart hierarchy. */
    .analytics-dashboard { --an-blue:var(--se-primary); --an-blue-soft:var(--se-primary-soft); --an-ink:var(--se-text); --an-muted:var(--se-text-muted); gap:24px; }
    .an-kpis { gap:18px; }
    .an-kpi { min-height:132px; padding:18px 20px; border:1px solid #e4e7ec; border-left:0; border-radius:16px; background:#fff; box-shadow:none; }
    .an-kpi::before { content:''; position:absolute; left:0; right:0; top:0; height:3px; background:var(--kpi-accent,var(--an-blue)); }
    .an-kpi::after { width:74px; height:74px; top:-45px; right:-34px; background:color-mix(in srgb,var(--kpi-accent,var(--an-blue)) 8%,transparent); }
    .an-kpi-label, .an-kpi-sub, .an-card-copy { color:var(--an-muted); }
    .an-kpi-value { color:var(--an-ink); font-size:clamp(1.65rem,2.7vw,2.15rem); }
    .an-delta { padding:4px 9px; font-size:.62rem; }
    .an-delta.up { background:#ecfdf3; color:#027a48; }
    .an-delta.down { background:#fef3f2; color:#d92d20; }
    .an-card { border-color:#e4e7ec; border-radius:16px; background:#fff; box-shadow:none; }
    .an-card-head { padding:20px 22px 0; }
    .an-card-head h3 { color:var(--an-ink); font-size:1.05rem; }
    .an-card-kicker { color:var(--an-blue); letter-spacing:.1em; }
    .an-card-body { padding:20px 22px 22px; }
    .an-stack-track, .an-hbar-track { background:#f2f4f7; box-shadow:none; }
    .an-hbar-fill, .an-group-bar, .an-col-bar { background:var(--an-blue); box-shadow:none; }
    .an-group-bar.last { background:#c7d2fe; }
    .an-group-key i { background:var(--an-blue); }
    .an-group-key i.last { background:#c7d2fe; }
    .an-columns { height:214px; padding:12px 6px 0; border-bottom-color:#eaecf0; background:repeating-linear-gradient(to top,transparent 0,transparent 52px,#f2f4f7 53px); }
    .an-col-stage { height:180px; }
    .an-col-label, .an-trend-labels { color:#98a2b3; }
    .an-trend-svg { height:190px; }
    .an-trend-svg .an-gridline { stroke:#eaecf0; stroke-dasharray:2 3; }
    .an-trend-line { stroke:var(--an-blue); stroke-width:2.2; }
    .an-trend-dot { fill:#fff; stroke:var(--an-blue); stroke-width:2; }
    .an-legend-row strong, .an-stack-total, .an-hbar-value, .an-group-value { color:var(--an-ink); }
    .an-trend-grid { display:grid; grid-template-columns:minmax(0,1.75fr) minmax(280px,.85fr); gap:18px; }
    .an-trend-grid.has-featured > .an-card:first-child { grid-row:span 2; }
    .an-trend-grid.has-featured > .an-card:first-child .an-columns { height:292px; }
    .an-trend-grid.has-featured > .an-card:first-child .an-col-stage { height:252px; }
    .an-trend-grid.has-featured > .an-card:first-child .an-card-body { padding-bottom:26px; }
    .an-trend-grid-featured { grid-template-columns:repeat(2,minmax(0,1fr)); }
    .an-trend-grid-featured .an-monthly-support { display:none; }
    .an-donut::after, .an-gauge::after { background:#fff; border:1px solid #f2f4f7; box-shadow:none; }
    .an-heat-cell { border-color:#eaecf0; border-radius:4px; }
    .an-heat-cell[data-lvl="4"] { background:var(--an-blue); border-color:var(--an-blue); }
    .an-overview-head { display:flex; align-items:flex-start; justify-content:space-between; gap:18px; padding:2px 0; }
    .an-overview-title { display:grid; gap:4px; }
    .an-overview-title h2 { margin:0; color:var(--an-ink); font-size:1.35rem; line-height:1.3; letter-spacing:-.025em; }
    .an-overview-title p { margin:0; color:var(--an-muted); font-size:.78rem; line-height:1.5; }
    .an-range { display:inline-flex; align-items:center; gap:3px; padding:4px; border-radius:10px; background:#f2f4f7; }
    .an-range button { min-height:34px; display:inline-flex; align-items:center; padding:0 12px; border:0; border-radius:7px; background:transparent; color:#667085; font:inherit; font-size:.7rem; font-weight:700; white-space:nowrap; cursor:pointer; transition:background .2s ease,color .2s ease,box-shadow .2s ease,transform .2s ease; }
    .an-range button:hover { color:#344054; transform:translateY(-1px); }
    .an-range button.active { background:#fff; color:#1d2939; box-shadow:0 1px 3px rgba(16,24,40,.1); }
    [data-an-period-panel][hidden] { display:none!important; }
    .an-featured { border:1px solid #e4e7ec; border-radius:16px; background:#fff; overflow:hidden; }
    .an-featured-head { display:flex; align-items:flex-start; justify-content:space-between; gap:18px; padding:22px 24px 8px; }
    .an-featured-copy { display:grid; gap:4px; }
    .an-featured-copy h3 { margin:0; color:var(--an-ink); font-size:1.08rem; line-height:1.35; }
    .an-featured-copy p { margin:0; color:var(--an-muted); font-size:.75rem; }
    .an-featured-total { text-align:right; }
    .an-featured-total strong { display:block; color:var(--an-ink); font-size:1.55rem; line-height:1.2; font-variant-numeric:tabular-nums; }
    .an-featured-total span { color:var(--an-muted); font-size:.68rem; }
    .an-featured-chart { overflow-x:auto; padding:8px 20px 20px; scrollbar-width:thin; scrollbar-color:#d0d5dd transparent; }
    .an-featured-plot { min-width:720px; height:310px; display:grid; grid-template-columns:42px 1fr; grid-template-rows:1fr 24px; }
    .an-featured-axis { grid-row:1; display:flex; flex-direction:column; justify-content:space-between; padding:3px 8px 4px 0; color:#98a2b3; font-size:.62rem; text-align:right; }
    .an-featured-bars { grid-column:2; grid-row:1; display:grid; grid-template-columns:repeat(12,minmax(28px,1fr)); align-items:end; gap:12px; padding:0 8px 0; border-bottom:1px solid #eaecf0; background:repeating-linear-gradient(to top,transparent 0,transparent calc(25% - 1px),#f2f4f7 25%); }
    .an-featured-bar-slot { height:100%; display:flex; align-items:flex-end; justify-content:center; }
    .an-featured-bar { width:min(32px,65%); min-height:4px; border-radius:6px 6px 2px 2px; background:var(--an-blue); transition:filter .15s ease,transform .15s ease; }
    .an-featured-bar:hover { filter:brightness(.9); transform:translateY(-2px); }
    .an-featured-labels { grid-column:2; grid-row:2; display:grid; grid-template-columns:repeat(12,minmax(28px,1fr)); gap:12px; padding:7px 8px 0; color:#667085; font-size:.62rem; text-align:center; }
    .an-support-grid { display:grid; grid-template-columns:minmax(0,7fr) minmax(300px,5fr); gap:18px; }
    .an-support-grid > .an-card { height:100%; }
    .an-daily-chart { min-height:390px; }
    .an-daily-plot { min-width:980px; height:300px; display:grid; grid-template-columns:repeat(30,minmax(16px,1fr)); align-items:end; gap:8px; padding:20px 14px 0; border-bottom:1px solid #eaecf0; background:repeating-linear-gradient(to top,transparent 0,transparent calc(25% - 1px),#f2f4f7 25%); position:relative; }
    .an-daily-slot { height:100%; display:flex; align-items:flex-end; justify-content:center; position:relative; }
    .an-daily-bar { width:min(18px,78%); min-height:3px; border-radius:5px 5px 1px 1px; background:var(--an-blue); transform-origin:bottom; animation:anBarRise .62s cubic-bezier(.2,.75,.3,1) both; animation-delay:calc(var(--bar-index) * 18ms); cursor:crosshair; }
    .an-daily-labels { min-width:980px; display:grid; grid-template-columns:repeat(30,minmax(16px,1fr)); gap:8px; padding:8px 14px 0; color:#98a2b3; font-size:.58rem; text-align:center; }
    .an-insights-grid { display:grid; grid-template-columns:1fr; gap:18px; }
    .an-ranked-pair { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:18px; }
    .an-ranked { display:grid; }
    .an-ranked-row { display:grid; grid-template-columns:minmax(0,1fr) auto; gap:14px; align-items:center; min-height:48px; border-bottom:1px solid #f2f4f7; color:var(--an-muted); font-size:.72rem; }
    .an-ranked-row:last-child { border-bottom:0; }
    .an-ranked-row span { overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .an-ranked-row strong { color:var(--an-ink); font-variant-numeric:tabular-nums; }
    .an-report-link { min-height:40px; margin-top:14px; display:flex; align-items:center; justify-content:center; border:1px solid #d0d5dd; border-radius:8px; color:var(--an-ink); font-size:.7rem; font-weight:700; text-decoration:none; }
    .an-active-heading { display:flex; align-items:baseline; gap:7px; }
    .an-active-heading strong { color:var(--an-ink); font-size:1.8rem; line-height:1; }
    .an-active-heading span { color:var(--an-muted); font-size:.72rem; }
    .an-live-dot { width:9px; height:9px; border-radius:50%; background:#12b76a; box-shadow:0 0 0 5px rgba(18,183,106,.12); animation:anPulse 1.7s ease-out infinite; }
    .an-active-chart { margin-top:18px; padding:16px 10px 8px; border-radius:12px; background:#f9fafb; position:relative; overflow:hidden; }
    .an-active-svg { width:100%; height:130px; display:block; }
    .an-active-svg .an-gridline { stroke:#eaecf0; stroke-width:.5; stroke-dasharray:2 2; }
    .an-active-line { fill:none; stroke:var(--an-blue); stroke-width:2; stroke-linecap:round; stroke-linejoin:round; }
    .an-active-area { fill:url(#anActiveGradient); }
    .an-active-guide { stroke:#98a2b3; stroke-width:.7; stroke-dasharray:2 2; opacity:0; }
    .an-active-node { fill:#fff; stroke:var(--an-blue); stroke-width:1.8; cursor:crosshair; }
    .an-active-stats { display:grid; grid-template-columns:repeat(3,1fr); margin-top:16px; }
    .an-active-stat { padding:0 10px; border-right:1px solid #eaecf0; text-align:center; }
    .an-active-stat:last-child { border-right:0; }
    .an-active-stat strong { display:block; color:var(--an-ink); font-size:.9rem; }
    .an-active-stat span { color:var(--an-muted); font-size:.58rem; }
    .an-statistics-head { display:flex; align-items:center; justify-content:space-between; gap:18px; }
    .an-statistics-title { display:flex; align-items:center; gap:12px; }
    .an-statistics-icon { width:42px; height:42px; display:grid; place-items:center; flex:0 0 42px; border:1px solid color-mix(in srgb,var(--an-blue) 22%,transparent); border-radius:13px; background:linear-gradient(145deg,color-mix(in srgb,var(--an-blue) 14%,#fff),color-mix(in srgb,var(--an-blue) 5%,#fff)); color:var(--an-blue); box-shadow:inset 0 1px 0 rgba(255,255,255,.72),0 5px 14px color-mix(in srgb,var(--an-blue) 10%,transparent); }
    .an-statistics-icon svg { width:22px; height:22px; overflow:visible; }
    .an-statistics-period { padding:9px 12px; border-radius:9px; background:#f2f4f7; color:#344054; font-size:.68rem; font-weight:750; white-space:nowrap; }
    .an-statistics-metrics { display:grid; grid-template-columns:repeat(2,minmax(0,220px)); gap:12px; margin-top:22px; }
    .an-statistics-metric { padding:14px 16px; border:1px solid #e4e7ec; border-radius:12px; background:#f8fafc; }
    .an-statistics-metric strong { color:var(--an-ink); font-size:1.45rem; font-variant-numeric:tabular-nums; }
    .an-statistics-metric p { margin:6px 0 0; color:var(--an-muted); font-size:.68rem; }
    .an-statistics-badge { display:inline-flex; margin-left:8px; padding:3px 7px; border-radius:999px; background:#ecfdf3; color:#027a48; font-size:.6rem; font-weight:800; vertical-align:middle; }
    .an-statistics-badge.down { background:#fef3f2; color:#d92d20; }
    .an-statistics-chart { margin-top:22px; padding:12px 8px 0; border-radius:0; background:transparent; }
    .an-statistics-chart .an-active-svg { height:220px; }
    .an-active-line-secondary { fill:none; stroke:var(--se-success); stroke-width:1.7; stroke-linecap:round; stroke-linejoin:round; path-length:1; stroke-dasharray:1; stroke-dashoffset:1; animation:anLineDraw .9s .28s ease-out forwards; }
    .an-active-node.secondary { stroke:var(--se-success); }
    .an-statistics-labels { display:flex; justify-content:space-between; padding:7px 3px 0; color:#667085; font-size:.62rem; }
    .an-statistics-legend { display:flex; gap:16px; margin-top:14px; color:var(--an-muted); font-size:.64rem; }
    .an-statistics-legend span { display:inline-flex; align-items:center; gap:6px; }
    .an-statistics-legend i { width:8px; height:8px; border-radius:50%; background:var(--an-blue); }
    .an-statistics-legend i.secondary { background:var(--se-success); }
    .an-statistics-empty { min-height:170px; margin-top:20px; display:grid; place-items:center; padding:24px; border:1px dashed #d0d5dd; border-radius:14px; background:linear-gradient(180deg,#fbfdff,#f8fafc); text-align:center; }
    .an-statistics-empty .an-statistics-empty-icon { width:48px; height:48px; max-width:none; margin:0 auto 11px; display:grid; place-items:center; border:1px solid color-mix(in srgb,var(--an-blue) 18%,transparent); border-radius:50%; background:color-mix(in srgb,var(--an-blue) 8%,#fff); color:var(--an-blue); box-shadow:0 0 0 5px color-mix(in srgb,var(--an-blue) 4%,transparent); line-height:0; }
    .an-statistics-empty-icon svg { display:block; width:22px; height:22px; overflow:visible; vertical-align:middle; }
    .an-statistics-empty strong { display:block; color:var(--an-ink); font-size:.9rem; }
    .an-statistics-empty span { display:block; max-width:420px; margin:5px auto 0; color:var(--an-muted); font-size:.68rem; line-height:1.5; }
    .an-statistics-pies { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:18px; margin-top:24px; padding-top:24px; border-top:1px solid #eaecf0; }
    .an-statistics-pie { min-width:0; padding:18px; border:1px solid #e4e7ec; border-radius:14px; background:#fff; }
    .an-statistics-pie h4 { margin:0; color:var(--an-ink); font-size:.9rem; }
    .an-statistics-pie p { margin:4px 0 0; color:var(--an-muted); font-size:.65rem; }
    .an-statistics-pie .an-donut-layout { margin-top:15px; }
    .an-tooltip { position:fixed; z-index:1600; min-width:110px; max-width:220px; padding:9px 11px; border:1px solid rgba(255,255,255,.12); border-radius:8px; background:rgba(17,24,39,.94); color:#fff; box-shadow:0 10px 30px rgba(15,23,42,.22); backdrop-filter:blur(10px); font-size:.68rem; line-height:1.45; pointer-events:none; opacity:0; transform:translate(-50%,calc(-100% - 10px)) scale(.96); transition:opacity .12s ease,transform .12s ease; }
    .an-tooltip.is-visible { opacity:1; transform:translate(-50%,calc(-100% - 10px)) scale(1); }
    [data-an-chart]:has([data-an-point]:hover) [data-an-point]:not(:hover) { opacity:.32; }
    [data-an-point] { transition:opacity .16s ease,filter .16s ease,transform .16s ease; }
    [data-an-point]:hover { filter:saturate(1.2) brightness(.96); }
    @keyframes anCardEnter { from { opacity:0; transform:translateY(16px); } to { opacity:1; transform:translateY(0); } }
    @keyframes anBarRise { from { transform:scaleY(0); opacity:.2; } to { transform:scaleY(1); opacity:1; } }
    @keyframes anLineDraw { from { stroke-dashoffset:1; } to { stroke-dashoffset:0; } }
    @keyframes anDonutSweep { from { --an-sweep:0%; } to { --an-sweep:100%; } }
    @keyframes anPulse { 0% { box-shadow:0 0 0 0 rgba(18,183,106,.34); } 70%,100% { box-shadow:0 0 0 9px rgba(18,183,106,0); } }
    @property --an-sweep { syntax:'<percentage>'; inherits:false; initial-value:0%; }
    .analytics-dashboard > * { animation:anCardEnter .42s ease-out both; animation-delay:calc(var(--an-order,0) * 55ms); }
    .analytics-dashboard > :nth-child(1) { --an-order:0; }
    .analytics-dashboard > :nth-child(2) { --an-order:1; }
    .analytics-dashboard > :nth-child(3) { --an-order:2; }
    .analytics-dashboard > :nth-child(4) { --an-order:3; }
    .analytics-dashboard > :nth-child(n+5) { --an-order:4; }
    .an-card, .an-kpi, .an-featured { transition:transform .28s ease,box-shadow .28s ease,border-color .28s ease; }
    .an-card:hover, .an-kpi:hover, .an-featured:hover { transform:translateY(-4px); border-color:#cbd5e1; box-shadow:0 12px 28px rgba(15,23,42,.09); }
    .an-trend-line, .an-active-line { path-length:1; stroke-dasharray:1; stroke-dashoffset:1; animation:anLineDraw .85s .18s ease-out forwards; }
    .an-donut, .an-gauge { --an-sweep:100%; -webkit-mask:conic-gradient(#000 0 var(--an-sweep),transparent var(--an-sweep) 100%); mask:conic-gradient(#000 0 var(--an-sweep),transparent var(--an-sweep) 100%); animation:anDonutSweep .8s .14s ease-out both; }
    .an-donut-layout:hover .an-donut { transform:scale(1.04); box-shadow:0 15px 32px rgba(15,23,42,.16); }
    .an-donut { transition:transform .3s ease,box-shadow .3s ease,filter .3s ease; }
    .an-legend-row { padding:5px 7px; margin-inline:-7px; border-radius:7px; cursor:default; transition:transform .22s ease,background .22s ease,opacity .22s ease; }
    .an-legend-row:hover { transform:translateX(4px); background:#f8fafc; }
    .an-legend:has(.an-legend-row:hover) .an-legend-row:not(:hover) { opacity:.42; }
    .an-daily-chart.is-seven .an-daily-plot,
    .an-daily-chart.is-seven .an-daily-labels { min-width:560px; grid-template-columns:repeat(7,minmax(36px,1fr)); }
    .an-daily-chart.is-seven .an-daily-slot:nth-child(-n+23),
    .an-daily-chart.is-seven .an-daily-labels span:nth-child(-n+23) { display:none; }
    html[data-theme="dark"] .analytics-dashboard,
    body[data-theme="dark"] .analytics-dashboard { --an-blue:var(--se-primary); --an-blue-soft:var(--se-primary-soft); --an-ink:var(--se-text); --an-muted:var(--se-text-soft); }
    html[data-theme="dark"] .an-kpi,
    html[data-theme="dark"] .an-card,
    html[data-theme="dark"] .an-featured,
    body[data-theme="dark"] .an-kpi,
    body[data-theme="dark"] .an-card,
    body[data-theme="dark"] .an-featured { background:var(--se-surface); border-color:var(--se-border); }
    html[data-theme="dark"] .an-range,
    body[data-theme="dark"] .an-range { background:var(--se-surface-soft); }
    html[data-theme="dark"] .an-range button.active,
    body[data-theme="dark"] .an-range button.active { background:var(--se-surface-muted); color:var(--se-text); }
    html[data-theme="dark"] .an-legend-row:hover,
    body[data-theme="dark"] .an-legend-row:hover { background:var(--se-surface-soft); }
    html[data-theme="dark"] .an-featured-bars,
    body[data-theme="dark"] .an-featured-bars { border-bottom-color:var(--se-border); background:repeating-linear-gradient(to top,transparent 0,transparent calc(25% - 1px),color-mix(in srgb,var(--se-border) 48%,transparent) 25%); }
    html[data-theme="dark"] .an-active-chart,
    body[data-theme="dark"] .an-active-chart { background:var(--se-surface-soft); }
    html[data-theme="dark"] .an-statistics-chart,
    body[data-theme="dark"] .an-statistics-chart { background:transparent; }
    html[data-theme="dark"] .an-statistics-period,
    body[data-theme="dark"] .an-statistics-period { background:var(--se-surface-soft); color:var(--se-text); }
    html[data-theme="dark"] .an-statistics-icon,
    html[data-theme="dark"] .an-statistics-empty-icon,
    body[data-theme="dark"] .an-statistics-icon,
    body[data-theme="dark"] .an-statistics-empty-icon { background:var(--se-primary-soft); border-color:color-mix(in srgb,var(--se-primary) 28%,var(--se-border)); box-shadow:0 0 0 5px color-mix(in srgb,var(--se-primary) 7%,transparent); }
    html[data-theme="dark"] .an-statistics-icon,
    body[data-theme="dark"] .an-statistics-icon { background:linear-gradient(145deg,color-mix(in srgb,var(--se-primary) 16%,var(--se-surface-soft)),var(--se-surface-soft)); border-color:color-mix(in srgb,var(--se-primary) 28%,var(--se-border)); box-shadow:inset 0 1px 0 color-mix(in srgb,#fff 5%,transparent),0 5px 14px rgba(0,0,0,.14); }
    html[data-theme="dark"] .an-statistics-metric,
    html[data-theme="dark"] .an-statistics-empty,
    body[data-theme="dark"] .an-statistics-metric,
    body[data-theme="dark"] .an-statistics-empty { background:var(--se-surface-soft); border-color:var(--se-border); }
    html[data-theme="dark"] .an-statistics-pies,
    body[data-theme="dark"] .an-statistics-pies { border-color:var(--se-border); }
    html[data-theme="dark"] .an-statistics-pie,
    body[data-theme="dark"] .an-statistics-pie { background:var(--se-surface-soft); border-color:var(--se-border); }
    html[data-theme="dark"] .an-ranked-row,
    html[data-theme="dark"] .an-active-stat,
    body[data-theme="dark"] .an-ranked-row,
    body[data-theme="dark"] .an-active-stat { border-color:var(--se-border); }
    html[data-theme="dark"] .an-daily-plot,
    body[data-theme="dark"] .an-daily-plot { border-bottom-color:var(--se-border); background:repeating-linear-gradient(to top,transparent 0,transparent calc(25% - 1px),color-mix(in srgb,var(--se-border) 48%,transparent) 25%); }
    html[data-theme="dark"] .an-active-svg .an-gridline,
    body[data-theme="dark"] .an-active-svg .an-gridline { stroke:color-mix(in srgb,var(--se-border) 70%,transparent); }

    @media (max-width:1100px) {
        .an-kpis { grid-template-columns:repeat(2,minmax(0,1fr)); }
        .an-grid-3, .an-grid-2, .an-trend-grid { grid-template-columns:1fr; }
        .an-support-grid { grid-template-columns:1fr; }
        .an-insights-grid { grid-template-columns:1fr; }
        .an-trend-grid.has-featured > .an-card:first-child { grid-row:auto; }
        .an-trend-grid.has-featured > .an-card:first-child .an-columns { height:214px; }
        .an-trend-grid.has-featured > .an-card:first-child .an-col-stage { height:180px; }
    }
    @media (max-width:640px) {
        .an-kpis { grid-template-columns:1fr; }
        .an-donut-layout { grid-template-columns:1fr; }
        .an-donut { width:130px; }
        .an-hbar-row { grid-template-columns:1fr; gap:4px; }
        .an-group { grid-template-columns:1fr; gap:4px; }
        .an-stack-row { grid-template-columns:64px 1fr auto; }
        .an-kpi, .an-donut, .an-gauge { transform:none; }
        .an-overview-head, .an-featured-head { align-items:stretch; flex-direction:column; }
        .an-range { width:100%; overflow-x:auto; }
        .an-range button { flex:1; justify-content:center; }
        .an-featured-total { text-align:left; }
        .an-featured-head { padding:18px 18px 6px; }
        .an-featured-chart { padding-inline:10px; }
        .an-ranked-pair { grid-template-columns:1fr; }
        .an-statistics-head { align-items:flex-start; flex-direction:column; }
        .an-statistics-metrics { grid-template-columns:1fr; }
        .an-statistics-pies { grid-template-columns:1fr; }
    }
    @media (prefers-reduced-motion:reduce) { .analytics-dashboard > *, .an-daily-bar, .an-trend-line, .an-active-line, .an-donut, .an-gauge, .an-live-dot { animation:none!important; } .an-kpi, .an-donut, .an-gauge { transform:none; } }
</style>
@endpush

@if (!empty($analytics['domains']))
@php
    $anTileColors = ['#c8a96a', '#28686c', '#8c8175', '#7d8055', '#a65f4f', '#64706a', '#6f8d78', '#9a6a3f'];

    $anComputePoints = function (array $values, int $width = 100, int $height = 44, int $pad = 4): array {
        $max = max(1, ...$values);
        $count = count($values);
        $points = [];
        foreach ($values as $index => $value) {
            $x = $count <= 1 ? $width / 2 : round($index / ($count - 1) * ($width - $pad * 2) + $pad, 2);
            $y = $count <= 1 ? $height - $pad : round($height - $pad - ($value / $max) * ($height - $pad * 2), 2);
            $points[] = ['x' => $x, 'y' => $y];
        }
        return $points;
    };

    $anPointsToLine = function (array $points): string {
        return implode(' ', array_map(fn ($point) => $point['x'] . ',' . $point['y'], $points));
    };

    $anPointsToArea = function (array $points, int $height = 44): string {
        if ($points === []) {
            return '';
        }
        $first = $points[0];
        $last = $points[count($points) - 1];
        return implode(' ', array_map(fn ($point) => $point['x'] . ',' . $point['y'], $points))
            . " L{$last['x']},{$height} L{$first['x']},{$height} Z";
    };

    $anPointsToSmoothPath = function (array $points): string {
        if ($points === []) return '';
        $path = 'M' . $points[0]['x'] . ',' . $points[0]['y'];
        for ($index = 1; $index < count($points); $index++) {
            $previous = $points[$index - 1];
            $current = $points[$index];
            $midX = round(($previous['x'] + $current['x']) / 2, 2);
            $path .= ' C' . $midX . ',' . $previous['y'] . ' ' . $midX . ',' . $current['y'] . ' ' . $current['x'] . ',' . $current['y'];
        }
        return $path;
    };

    $anDonutGradient = function (array $segments, int $total): string {
        if ($total <= 0) {
            return 'conic-gradient(var(--surface-muted) 0 100%)';
        }
        $cumulative = 0;
        $stops = [];
        foreach ($segments as $segment) {
            $percent = $segment['value'] / $total * 100;
            $stops[] = $segment['color'] . ' ' . round($cumulative, 2) . '% ' . round($cumulative + $percent, 2) . '%';
            $cumulative += $percent;
        }
        return 'conic-gradient(' . implode(',', $stops) . ')';
    };

    $anGaugeGradient = function (int $value): string {
        $clamped = max(0, min(100, $value));
        return "conic-gradient(var(--primary) 0 {$clamped}%, var(--surface-muted) {$clamped}% 100%)";
    };

    $anFeaturedTrend = $analytics['trends'][0] ?? null;
    $anDailyCells = array_slice($anFeaturedTrend['heat']['cells'] ?? [], -30);
    $anDailyMax = max(1, ...array_column($anDailyCells, 'count'));
    $anRankedCategories = array_slice($analytics['hbar']['rows'] ?? ($analytics['donuts'][0]['segments'] ?? []), 0, 4);
    $anRankedPrograms = array_slice($analytics['treemap']['tiles'] ?? array_map(fn ($row) => ['label' => $row['label'], 'value' => $row['total']], $analytics['stacked']['series'] ?? []), 0, 4);
    $anActiveValues = $anFeaturedTrend['six']['values'] ?? [];
    $anActiveLabels = $anFeaturedTrend['six']['labels'] ?? [];
    $anActivePoints = $anComputePoints($anActiveValues, 100, 50, 4);
    $anActiveSecondaryValues = $anFeaturedTrend['area'] ?? [];
    $anActiveSecondaryPoints = $anComputePoints($anActiveSecondaryValues, 100, 50, 4);
    $anActiveAreaPath = $anActiveSecondaryPoints === [] ? '' : $anPointsToSmoothPath($anActiveSecondaryPoints) . ' L' . $anActiveSecondaryPoints[count($anActiveSecondaryPoints) - 1]['x'] . ',50 L' . $anActiveSecondaryPoints[0]['x'] . ',50 Z';
    $anActiveCurrent = (int) ($anActiveValues[count($anActiveValues) - 1] ?? 0);
    $anActivePrevious = (int) ($anActiveValues[count($anActiveValues) - 2] ?? 0);
    $anActiveDelta = $anActivePrevious > 0 ? round((($anActiveCurrent - $anActivePrevious) / $anActivePrevious) * 100, 1) : ($anActiveCurrent > 0 ? 100 : 0);
    $anActiveSixTotal = (int) array_sum($anActiveValues);
    $anActiveDaily = count($anDailyCells) > 0 ? (int) round(array_sum(array_column($anDailyCells, 'count')) / count($anDailyCells)) : 0;
    $anActiveWeekly = $anActiveDaily * 7;
    $anActiveMonthly = (int) array_sum(array_column($anDailyCells, 'count'));
    $anActivitySegments = collect($analytics['trends'])->map(function ($trend, $index) use ($anTileColors) {
        return ['label' => $trend['title'], 'value' => (int) array_sum($trend['six']['values']), 'color' => $anTileColors[$index % count($anTileColors)]];
    })->filter(fn ($segment) => $segment['value'] > 0)->values()->all();
    $anActivityTotal = (int) array_sum(array_column($anActivitySegments, 'value'));
    $anProgramSegments = collect($analytics['stacked']['series'] ?? [])->sortByDesc('total')->take(5)->values()->map(function ($row, $index) use ($anTileColors) {
        return ['label' => $row['label'], 'value' => (int) $row['total'], 'color' => $anTileColors[$index % count($anTileColors)]];
    })->filter(fn ($segment) => $segment['value'] > 0)->all();
    $anProgramTotal = (int) array_sum(array_column($anProgramSegments, 'value'));
@endphp
<section class="analytics-dashboard" data-dashboard-analytics aria-label="{{ __('Analytics dashboard') }}">
    <header class="an-overview-head">
        <div class="an-overview-title">
            <h2>{{ __('Analytics Overview') }}</h2>
            <p>{{ __('Operational performance across students, discipline, movement, and scholarships.') }}</p>
        </div>
        <div class="an-range" aria-label="{{ __('Reporting period') }}">
            <button type="button" data-an-period="12" aria-pressed="false">12 {{ __('Months') }}</button>
            <button class="active" type="button" data-an-period="30" aria-pressed="true">30 {{ __('Days') }}</button>
            <button type="button" data-an-period="7" aria-pressed="false">7 {{ __('Days') }}</button>
        </div>
    </header>

    @if ($analytics['kpis'] !== [])
    <div class="an-kpis">
        @foreach ($analytics['kpis'] as $kpi)
        <article class="an-kpi tone-{{ $kpi['tone'] }}">
            <div class="an-kpi-top">
                <span class="an-kpi-label">{{ $kpi['label'] }}</span>
                @if ($kpi['delta'])
                <span class="an-delta {{ $kpi['delta']['dir'] }}" aria-label="{{ $kpi['delta']['text'] }}">{{ $kpi['delta']['text'] }}</span>
                @endif
            </div>
            <strong class="an-kpi-value">{{ $kpi['value'] }}</strong>
            <div class="an-kpi-bottom">
                <span class="an-kpi-sub">{{ $kpi['sub'] }}</span>
                @if ($kpi['spark'] !== [])
                <svg class="an-spark" viewBox="0 0 100 24" preserveAspectRatio="none" aria-hidden="true">
                    <polyline points="{{ $anPointsToLine($anComputePoints($kpi['spark'], 100, 24, 2)) }}" fill="none" stroke="var(--kpi-accent,var(--primary))" stroke-width="2.5" stroke-linejoin="round" stroke-linecap="round"/>
                </svg>
                @endif
            </div>
            <span class="an-sr">{{ $kpi['label'] }}: {{ $kpi['value'] }}. {{ $kpi['sub'] }}. Last six months: {{ implode(', ', $kpi['spark']) }}.</span>
        </article>
        @endforeach
    </div>
    @endif

    @if ($anFeaturedTrend)
    @php
        $anFeaturedTotal = (int) array_sum($anFeaturedTrend['twelve']['values']);
        $anFeaturedMax = max(1, ...$anFeaturedTrend['twelve']['values']);
    @endphp
    <article class="an-featured" data-an-period-panel="12" hidden>
        <div class="an-featured-head">
            <div class="an-featured-copy">
                <h3>{{ $anFeaturedTrend['title'] }}</h3>
                <p>{{ __('Monthly activity across the last twelve months.') }}</p>
            </div>
            <div class="an-featured-total">
                <strong>{{ number_format($anFeaturedTotal) }}</strong>
                <span>{{ __('Total activity') }}</span>
            </div>
        </div>
        @if ($anFeaturedTotal > 0)
        <div class="an-featured-chart" data-an-chart role="img" aria-label="{{ $anFeaturedTrend['title'] }}: {{ implode(', ', $anFeaturedTrend['twelve']['values']) }}">
            <div class="an-featured-plot">
                <div class="an-featured-axis"><span>{{ number_format($anFeaturedMax) }}</span><span>{{ number_format((int) round($anFeaturedMax * .66)) }}</span><span>{{ number_format((int) round($anFeaturedMax * .33)) }}</span><span>0</span></div>
                <div class="an-featured-bars">
                    @foreach ($anFeaturedTrend['twelve']['values'] as $valueIndex => $value)
                    <div class="an-featured-bar-slot"><span class="an-featured-bar" data-an-point data-an-tooltip="{{ $anFeaturedTrend['twelve']['labels'][$valueIndex] }}|{{ number_format($value) }} activity" style="height:{{ max(2, round($value / $anFeaturedMax * 100, 1)) }}%"></span></div>
                    @endforeach
                </div>
                <div class="an-featured-labels">@foreach ($anFeaturedTrend['twelve']['labels'] as $label)<span>{{ $label }}</span>@endforeach</div>
            </div>
        </div>
        @else
        <div class="an-empty"><div><div class="an-empty-mark">0</div><strong>{{ $anFeaturedTrend['title'] }}</strong><span>{{ __('No activity recorded in the last twelve months.') }}</span></div></div>
        @endif
    </article>
    @endif

    @if ($anDailyCells !== [])
    <article class="an-card an-daily-chart" data-an-period-panel="30,7">
        <div class="an-card-head">
            <span class="an-card-kicker">{{ __('Operational Analytics') }}</span>
            <h3 data-an-daily-title>{{ __('Daily Activity') }} - 30 {{ __('Days') }}</h3>
            <p class="an-card-copy">{{ __('Recorded activity across the latest thirty days.') }}</p>
        </div>
        <div class="an-card-body">
            <div class="an-featured-chart" data-an-chart>
                <div class="an-daily-plot">
                    @foreach ($anDailyCells as $dailyIndex => $dailyCell)
                    <div class="an-daily-slot">
                        <span class="an-daily-bar" data-an-point data-an-tooltip="{{ $dailyCell['date'] }}|{{ number_format($dailyCell['count']) }} {{ Str::lower($anFeaturedTrend['kicker']) }}" style="height:{{ max(2, round($dailyCell['count'] / $anDailyMax * 100, 1)) }}%;--bar-index:{{ $dailyIndex }}"></span>
                    </div>
                    @endforeach
                </div>
                <div class="an-daily-labels">@foreach ($anDailyCells as $dailyIndex => $dailyCell)<span>{{ $dailyIndex % 3 === 0 || $dailyIndex === count($anDailyCells) - 1 ? \Illuminate\Support\Carbon::parse($dailyCell['date'])->format('j') : '' }}</span>@endforeach</div>
            </div>
        </div>
    </article>
    @endif

    <div class="an-insights-grid">
        <div class="an-ranked-pair">
            <article class="an-card">
                <div class="an-card-head"><span class="an-card-kicker">{{ __('Insights') }}</span><h3>{{ __('Top Activity Categories') }}</h3><p class="an-card-copy">{{ __('Highest-volume categories in the current records.') }}</p></div>
                <div class="an-card-body">
                    <div class="an-ranked">
                        @forelse ($anRankedCategories as $ranked)
                        <div class="an-ranked-row" data-an-point data-an-tooltip="{{ $ranked['label'] }}|{{ number_format($ranked['value']) }} records"><span>{{ $ranked['label'] }}</span><strong>{{ number_format($ranked['value']) }}</strong></div>
                        @empty
                        <div class="an-ranked-row"><span>{{ __('No category data') }}</span><strong>0</strong></div>
                        @endforelse
                    </div>
                    <a class="an-report-link" href="{{ route('admin.reports.monthly') }}">{{ __('Monthly Report') }} &rarr;</a>
                </div>
            </article>
            <article class="an-card">
                <div class="an-card-head"><span class="an-card-kicker">{{ __('Insights') }}</span><h3>{{ __('Top Programs') }}</h3><p class="an-card-copy">{{ __('Largest student program groups in the current dataset.') }}</p></div>
                <div class="an-card-body">
                    <div class="an-ranked">
                        @forelse ($anRankedPrograms as $ranked)
                        <div class="an-ranked-row" data-an-point data-an-tooltip="{{ $ranked['label'] }}|{{ number_format($ranked['value']) }} students"><span>{{ $ranked['label'] }}</span><strong>{{ number_format($ranked['value']) }}</strong></div>
                        @empty
                        <div class="an-ranked-row"><span>{{ __('No program data') }}</span><strong>0</strong></div>
                        @endforelse
                    </div>
                    <a class="an-report-link" href="{{ route('admin.students.index') }}">{{ __('Student List') }} &rarr;</a>
                </div>
            </article>
        </div>
        <article class="an-card">
            <div class="an-card-body">
                <div class="an-statistics-head">
                    <div class="an-statistics-title"><span class="an-statistics-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13h4l2.2-5 4.1 9 2.2-5H21"/><circle cx="3" cy="13" r="1.2" fill="currentColor" stroke="none"/><circle cx="21" cy="12" r="1.2" fill="currentColor" stroke="none"/></svg></span><div><h3 style="margin:0;color:var(--an-ink);font-size:1.08rem;">{{ __('Statistics') }}</h3><p class="an-card-copy">{{ __('Operational activity across the last six months.') }}</p></div></div>
                    <span class="an-statistics-period">{{ __('Monthly') }}</span>
                </div>
                <div class="an-statistics-metrics">
                    <div class="an-statistics-metric"><strong>{{ number_format($anActiveCurrent) }}</strong><span class="an-statistics-badge {{ $anActiveDelta < 0 ? 'down' : '' }}">{{ $anActiveDelta > 0 ? '+' : '' }}{{ number_format($anActiveDelta,1) }}%</span><p>{{ __('Monthly Activity') }}</p></div>
                    <div class="an-statistics-metric"><strong>{{ number_format($anActiveSixTotal) }}</strong><p>{{ __('Cumulative Activity') }}</p></div>
                </div>
                @if ($anActiveSixTotal > 0 || $anActiveMonthly > 0)
                <div class="an-active-chart an-statistics-chart" data-an-chart>
                    @if ($anActivePoints !== [])
                    <svg class="an-active-svg" viewBox="0 0 100 50" preserveAspectRatio="none" role="img" aria-label="{{ __('Monthly and cumulative activity') }}: {{ implode(', ', $anActiveValues) }}">
                        <defs><linearGradient id="anActiveGradient" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="var(--se-primary)" stop-opacity=".20"/><stop offset="100%" stop-color="var(--se-primary)" stop-opacity="0"/></linearGradient></defs>
                        <line class="an-gridline" x1="0" y1="12.5" x2="100" y2="12.5"/><line class="an-gridline" x1="0" y1="25" x2="100" y2="25"/><line class="an-gridline" x1="0" y1="37.5" x2="100" y2="37.5"/>
                        <path class="an-active-area" d="{{ $anActiveAreaPath }}"/>
                        <line class="an-active-guide" data-an-guide x1="0" y1="0" x2="0" y2="50"/>
                        <path class="an-active-line-secondary" pathLength="1" d="{{ $anPointsToSmoothPath($anActiveSecondaryPoints) }}"/>
                        <path class="an-active-line" pathLength="1" d="{{ $anPointsToSmoothPath($anActivePoints) }}"/>
                        @foreach ($anActivePoints as $pointIndex => $point)
                        <circle class="an-active-node" data-an-point data-an-x="{{ $point['x'] }}" data-an-tooltip="{{ $anActiveLabels[$pointIndex] ?? ($pointIndex + 1) }}|Monthly: {{ number_format($anActiveValues[$pointIndex] ?? 0) }} · Cumulative: {{ number_format($anActiveSecondaryValues[$pointIndex] ?? 0) }}" cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="1.5"/>
                        @endforeach
                    </svg>
                    @endif
                </div>
                <div class="an-statistics-labels">@foreach ($anActiveLabels as $label)<span>{{ $label }}</span>@endforeach</div>
                <div class="an-statistics-legend"><span><i></i>{{ __('Monthly activity') }}</span><span><i class="secondary"></i>{{ __('Cumulative activity') }}</span></div>
                @else
                <div class="an-statistics-empty">
                    <div><span class="an-statistics-empty-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v17.25h16.5M7.5 15l3-3 2.25 2.25L16.5 9"/></svg></span><strong>{{ __('No operational activity recorded') }}</strong><span>{{ __('The six-month trend will appear automatically when discipline, movement, or scholarship activity is recorded.') }}</span></div>
                </div>
                @endif
                <div class="an-statistics-pies">
                    <section class="an-statistics-pie">
                        <h4>{{ __('Activity by Type') }}</h4><p>{{ __('Operational activity grouped by module.') }}</p>
                        <div class="an-donut-layout" data-an-chart>
                            <div class="an-donut" style="--donut:{{ $anDonutGradient($anActivitySegments, $anActivityTotal) }}"><div class="an-donut-centre"><strong>{{ number_format($anActivityTotal) }}</strong><span>{{ __('Activity') }}</span></div></div>
                            <div class="an-legend">@forelse($anActivitySegments as $segment)<div class="an-legend-row" data-an-point data-an-tooltip="{{ $segment['label'] }}|{{ number_format($segment['value']) }} ({{ $anActivityTotal > 0 ? round($segment['value'] / $anActivityTotal * 100) : 0 }}%)" style="--legend-color:{{ $segment['color'] }}"><i></i><span>{{ $segment['label'] }}</span><strong>{{ number_format($segment['value']) }}</strong></div>@empty<div class="an-legend-row" style="--legend-color:#e2e8f0"><i></i><span>{{ __('No activity') }}</span><strong>0</strong></div>@endforelse</div>
                        </div>
                    </section>
                    <section class="an-statistics-pie">
                        <h4>{{ __('Top Programs') }}</h4><p>{{ __('Largest enrolled program groups.') }}</p>
                        <div class="an-donut-layout" data-an-chart>
                            <div class="an-donut" style="--donut:{{ $anDonutGradient($anProgramSegments, $anProgramTotal) }}"><div class="an-donut-centre"><strong>{{ number_format($anProgramTotal) }}</strong><span>{{ __('Student Total') }}</span></div></div>
                            <div class="an-legend">@forelse($anProgramSegments as $segment)<div class="an-legend-row" data-an-point data-an-tooltip="{{ $segment['label'] }}|{{ number_format($segment['value']) }} ({{ $anProgramTotal > 0 ? round($segment['value'] / $anProgramTotal * 100) : 0 }}%)" style="--legend-color:{{ $segment['color'] }}"><i></i><span>{{ $segment['label'] }}</span><strong>{{ number_format($segment['value']) }}</strong></div>@empty<div class="an-legend-row" style="--legend-color:#e2e8f0"><i></i><span>{{ __('No program data') }}</span><strong>0</strong></div>@endforelse</div>
                        </div>
                    </section>
                </div>
            </div>
        </article>
    </div>

    @if ($analytics['gauges'] !== [] || $analytics['donuts'] !== [])
    <div class="an-grid an-grid-3">
        @foreach ($analytics['gauges'] as $gauge)
        <article class="an-card">
            <div class="an-card-head">
                <span class="an-card-kicker">{{ $gauge['kicker'] }}</span>
                <h3>{{ $gauge['title'] }}</h3>
                <p class="an-card-copy">{{ $gauge['copy'] }}</p>
            </div>
            <div class="an-card-body">
                @if ($gauge['active'])
                <div class="an-gauge-wrap">
                    <div class="an-gauge" style="--gauge:{{ $anGaugeGradient($gauge['value']) }};" role="img" aria-label="{{ $gauge['title'] }} {{ $gauge['display'] }}">
                        <div class="an-gauge-centre"><strong>{{ $gauge['display'] }}</strong><span>{{ __('Progress') }}</span></div>
                    </div>
                    <p class="an-gauge-note">{{ $gauge['note'] }}</p>
                </div>
                @else
                <div class="an-empty">
                    <div>
                        <div class="an-empty-mark">0</div>
                        <strong>{{ $gauge['title'] }}</strong>
                        <span>{{ $gauge['note'] }}</span>
                    </div>
                </div>
                @endif
            </div>
        </article>
        @endforeach

        @foreach ($analytics['donuts'] as $donut)
        <article class="an-card">
            <div class="an-card-head">
                <span class="an-card-kicker">{{ $donut['kicker'] }}</span>
                <h3>{{ $donut['title'] }}</h3>
                <p class="an-card-copy">{{ $donut['copy'] }}</p>
            </div>
            <div class="an-card-body">
                @if ($donut['total'] > 0)
                <div class="an-donut-layout" data-an-chart>
                    <div class="an-donut" style="--donut:{{ $anDonutGradient($donut['segments'], $donut['total']) }};" role="img" aria-label="{{ $donut['title'] }}. Total {{ $donut['total'] }}">
                        <div class="an-donut-centre"><strong>{{ number_format($donut['total']) }}</strong><span>{{ __('Total') }}</span></div>
                    </div>
                    <div class="an-legend">
                        @foreach ($donut['segments'] as $segment)
                        <div class="an-legend-row" data-an-point data-an-tooltip="{{ $segment['label'] }}|{{ number_format($segment['value']) }} ({{ $donut['total'] > 0 ? round($segment['value'] / $donut['total'] * 100) : 0 }}%)" style="--legend-color:{{ $segment['color'] }}">
                            <i></i><span>{{ $segment['label'] }}</span>
                            <strong>{{ number_format($segment['value']) }} <em>({{ $donut['total'] > 0 ? round($segment['value'] / $donut['total'] * 100) : 0 }}%)</em></strong>
                        </div>
                        @endforeach
                    </div>
                </div>
                <span class="an-sr">{{ $donut['title'] }}: total {{ $donut['total'] }}. @foreach ($donut['segments'] as $segment){{ $segment['label'] }} {{ $segment['value'] }}; @endforeach</span>
                @else
                <div class="an-empty">
                    <div>
                        <div class="an-empty-mark">0</div>
                        <strong>{{ $donut['title'] }}</strong>
                        <span>{{ __('No records available for this distribution yet.') }}</span>
                    </div>
                </div>
                @endif
            </div>
        </article>
        @endforeach
    </div>
    @endif

    @if ($analytics['stacked'] && $analytics['stacked']['series'] !== [])
    <div class="an-grid an-grid-2">
        <article class="an-card">
            <div class="an-card-head">
                <span class="an-card-kicker">{{ $analytics['stacked']['kicker'] }}</span>
                <h3>{{ $analytics['stacked']['title'] }}</h3>
                <p class="an-card-copy">{{ $analytics['stacked']['copy'] }}</p>
            </div>
            <div class="an-card-body">
                <div class="an-stack">
                    @foreach ($analytics['stacked']['series'] as $stackRow)
                    <div class="an-stack-row">
                        <span>{{ $stackRow['label'] }}</span>
                        <div class="an-stack-track" aria-hidden="true">
                            @foreach ($stackRow['segments'] as $segment)
                            <span class="an-stack-seg" style="width:{{ $stackRow['total'] > 0 ? round($segment['value'] / $stackRow['total'] * 100, 2) : 0 }}%; background:{{ $segment['color'] }};" title="{{ $segment['label'] }}: {{ $segment['value'] }}"></span>
                            @endforeach
                        </div>
                        <span class="an-stack-total">{{ number_format($stackRow['total']) }}</span>
                    </div>
                    @endforeach
                </div>
                <div class="an-stack-legend">
                    @foreach (($analytics['stacked']['series'][0]['segments'] ?? []) as $segment)
                    <span><i style="--legend-color:{{ $segment['color'] }}"></i>{{ $segment['label'] }}</span>
                    @endforeach
                </div>
                <span class="an-sr">@foreach ($analytics['stacked']['series'] as $stackRow){{ $stackRow['label'] }}: total {{ $stackRow['total'] }}; @endforeach</span>
            </div>
        </article>

        @if ($analytics['treemap'])
        <article class="an-card">
            <div class="an-card-head">
                <span class="an-card-kicker">{{ $analytics['treemap']['kicker'] }}</span>
                <h3>{{ $analytics['treemap']['title'] }}</h3>
                <p class="an-card-copy">{{ $analytics['treemap']['copy'] }}</p>
            </div>
            <div class="an-card-body">
                <div class="an-treemap">
                    @foreach ($analytics['treemap']['tiles'] as $tileIndex => $tile)
                    <div class="an-tile" style="--tile:{{ $anTileColors[$tileIndex % count($anTileColors)] }}; flex-basis:{{ max(90, min(260, round($tile['value'] / $analytics['treemap']['max'] * 240))) }}px;" title="{{ $tile['label'] }}: {{ $tile['value'] }}">
                        <strong>{{ number_format($tile['value']) }}</strong>
                        <span>{{ $tile['label'] }}</span>
                    </div>
                    @endforeach
                </div>
                <span class="an-sr">@foreach ($analytics['treemap']['tiles'] as $tile){{ $tile['label'] }}: {{ $tile['value'] }}; @endforeach Total {{ $analytics['treemap']['total'] }}.</span>
            </div>
        </article>
        @endif
    </div>
    @endif

    @if ($analytics['hbar'] || $analytics['grouped'])
    <div class="an-grid an-grid-2">
        @if ($analytics['hbar'])
        <article class="an-card">
            <div class="an-card-head">
                <span class="an-card-kicker">{{ $analytics['hbar']['kicker'] }}</span>
                <h3>{{ $analytics['hbar']['title'] }}</h3>
                <p class="an-card-copy">{{ $analytics['hbar']['copy'] }}</p>
            </div>
            <div class="an-card-body">
                <div class="an-hbar">
                    @foreach ($analytics['hbar']['rows'] as $row)
                    <div class="an-hbar-row">
                        <span class="an-hbar-label">{{ $row['label'] }}</span>
                        <div class="an-hbar-track" aria-hidden="true"><span class="an-hbar-fill" style="width:{{ max(2, round($row['value'] / $analytics['hbar']['max'] * 100, 1)) }}%"></span></div>
                        <span class="an-hbar-value">{{ number_format($row['value']) }}</span>
                    </div>
                    @endforeach
                </div>
                <span class="an-sr">@foreach ($analytics['hbar']['rows'] as $row){{ $row['label'] }}: {{ $row['value'] }}; @endforeach</span>
            </div>
        </article>
        @endif

        @if ($analytics['grouped'])
        @php
            $groupedTotal = collect($analytics['grouped']['this'])->sum('value') + collect($analytics['grouped']['last'])->sum('value');
        @endphp
        <article class="an-card">
            <div class="an-card-head">
                <span class="an-card-kicker">{{ $analytics['grouped']['kicker'] }}</span>
                <h3>{{ $analytics['grouped']['title'] }}</h3>
                <p class="an-card-copy">{{ $analytics['grouped']['copy'] }}</p>
            </div>
            <div class="an-card-body">
                @if ($groupedTotal > 0)
                <div class="an-grouped">
                    @foreach ($analytics['grouped']['this'] as $groupIndex => $group)
                    @php
                        $lastValue = collect($analytics['grouped']['last'])->firstWhere('label', $group['label'])['value'] ?? 0;
                        $thisHeight = $analytics['grouped']['max'] > 0 ? max(4, round($group['value'] / $analytics['grouped']['max'] * 100)) : 0;
                        $lastHeight = $analytics['grouped']['max'] > 0 ? max(4, round($lastValue / $analytics['grouped']['max'] * 100)) : 0;
                    @endphp
                    <div class="an-group" style="--group-index:{{ $groupIndex }}">
                        <span>{{ __($group['label']) }}</span>
                        <div class="an-group-pair" aria-hidden="true">
                            <span class="an-group-bar" style="height:{{ max(4, $thisHeight) }}%;" title="{{ __('This month') }}"></span>
                            <span class="an-group-bar last" style="height:{{ max(4, $lastHeight) }}%;" title="{{ __('Last month') }}"></span>
                        </div>
                        <span class="an-group-value">{{ number_format($group['value']) }} <em style="font-style:normal;opacity:.7;font-size:.62rem;">/ {{ number_format($lastValue) }}</em></span>
                    </div>
                    @endforeach
                </div>
                <div class="an-group-key"><span><i></i>{{ __('This month') }}</span><span><i class="last"></i>{{ __('Last month') }}</span></div>
                @else
                <div class="an-empty">
                    <div>
                        <div class="an-empty-mark">0</div>
                        <strong>{{ $analytics['grouped']['title'] }}</strong>
                        <span>{{ __('No activity recorded in the current or previous month.') }}</span>
                    </div>
                </div>
                @endif
            </div>
        </article>
        @endif
    </div>
    @endif

    @foreach ($analytics['trends'] as $trendIndex => $trend)
        @php
            $linePoints = $anComputePoints($trend['six']['values'], 100, 44, 5);
            $areaPoints = $anComputePoints($trend['area'], 100, 44, 5);
            $twelveTotal = (int) array_sum($trend['twelve']['values']);
            $twelveMax = max(1, ...$trend['twelve']['values']);
            $heatWeekdays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            $heatFirst = $trend['heat']['cells'][0]['weekday'] ?? 'Mon';
            $heatStartIndex = array_search($heatFirst, $heatWeekdays, true);
            $heatLabels = array_slice($heatWeekdays, $heatStartIndex === false ? 0 : $heatStartIndex, 7);
        @endphp
        <div class="an-trend-grid {{ $trendIndex === 0 ? 'an-trend-grid-featured' : '' }}">
            <article class="an-card an-monthly-support">
                <div class="an-card-head">
                    <span class="an-card-kicker">{{ $trend['kicker'] }}</span>
                    <h3>{{ $trend['title'] }} – 12 {{ __('Months') }}</h3>
                    <p class="an-card-copy">{{ __('Monthly volume across the last twelve months.') }}</p>
                </div>
                <div class="an-card-body">
                    @if ($twelveTotal > 0)
                    <div class="an-columns">
                        @foreach ($trend['twelve']['values'] as $colIndex => $colValue)
                        <div class="an-col" data-zero="{{ $colValue === 0 ? 'true' : 'false' }}">
                            <div class="an-col-stage">
                                <span class="an-col-bar" style="height:{{ max(3, round($colValue / $twelveMax * 100)) }}%;" title="{{ $trend['twelve']['labels'][$colIndex] }}: {{ $colValue }}"></span>
                            </div>
                            <span class="an-col-label">{{ $colIndex === count($trend['twelve']['labels']) - 1 ? $trend['twelve']['labels'][$colIndex] : '' }}</span>
                        </div>
                        @endforeach
                    </div>
                    <span class="an-sr">{{ $trend['title'] }} by month: @foreach ($trend['twelve']['labels'] as $labelIndex => $label){{ $label }} {{ $trend['twelve']['values'][$labelIndex] }}; @endforeach</span>
                    @else
                    <div class="an-empty">
                        <div>
                            <div class="an-empty-mark">0</div>
                            <strong>{{ $trend['title'] }}</strong>
                            <span>{{ __('No activity recorded in the last twelve months.') }}</span>
                        </div>
                    </div>
                    @endif
                </div>
            </article>

            <article class="an-card">
                <div class="an-card-head">
                    <span class="an-card-kicker">{{ $trend['kicker'] }}</span>
                    <h3>{{ $trend['title'] }} – 6 {{ __('Months') }}</h3>
                    <p class="an-card-copy">{{ __('Monthly trajectory with micro-fluctuations.') }}</p>
                </div>
                <div class="an-card-body">
                    @if ((int) array_sum($trend['six']['values']) > 0)
                    <svg class="an-trend-svg" data-an-chart viewBox="0 0 100 44" preserveAspectRatio="none" role="img" aria-label="{{ $trend['title'] }} last six months: {{ implode(', ', $trend['six']['values']) }}">
                        <line class="an-gridline" x1="0" y1="11" x2="100" y2="11"/>
                        <line class="an-gridline" x1="0" y1="22" x2="100" y2="22"/>
                        <line class="an-gridline" x1="0" y1="33" x2="100" y2="33"/>
                        <polyline class="an-trend-line" pathLength="1" points="{{ $anPointsToLine($linePoints) }}"/>
                        @foreach ($linePoints as $pointIndex => $point)
                        <circle class="an-trend-dot" data-an-point data-an-tooltip="{{ $trend['six']['labels'][$pointIndex] ?? ($pointIndex + 1) }}|{{ number_format($trend['six']['values'][$pointIndex] ?? 0) }} activity" cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="2.4"/>
                        @endforeach
                    </svg>
                    <div class="an-trend-labels">
                        @foreach ($trend['six']['labels'] as $label)
                        <span>{{ $label }}</span>
                        @endforeach
                    </div>
                    @else
                    <div class="an-empty">
                        <div>
                            <div class="an-empty-mark">0</div>
                            <strong>{{ $trend['title'] }}</strong>
                            <span>{{ __('No activity recorded in the last six months.') }}</span>
                        </div>
                    </div>
                    @endif
                </div>
            </article>

            <article class="an-card">
                <div class="an-card-head">
                    <span class="an-card-kicker">{{ $trend['kicker'] }}</span>
                    <h3>{{ __('Cumulative Volume') }}</h3>
                    <p class="an-card-copy">{{ __('Running total across the last six months.') }}</p>
                </div>
                <div class="an-card-body">
                    @if ((int) array_sum($trend['six']['values']) > 0)
                    <svg class="an-trend-svg" data-an-chart viewBox="0 0 100 44" preserveAspectRatio="none" role="img" aria-label="Cumulative {{ $trend['title'] }}: {{ implode(', ', $trend['area']) }}">
                        <defs>
                            <linearGradient id="anAreaGrad{{ $trendIndex }}" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="var(--primary)" stop-opacity="0.30"/>
                                <stop offset="100%" stop-color="var(--primary)" stop-opacity="0.03"/>
                            </linearGradient>
                        </defs>
                        <line class="an-gridline" x1="0" y1="11" x2="100" y2="11"/>
                        <line class="an-gridline" x1="0" y1="22" x2="100" y2="22"/>
                        <line class="an-gridline" x1="0" y1="33" x2="100" y2="33"/>
                        <path class="an-trend-area" d="M{{ $anPointsToArea($areaPoints, 44) }}" style="fill:url(#anAreaGrad{{ $trendIndex }})"/>
                        <polyline class="an-trend-line" pathLength="1" points="{{ $anPointsToLine($areaPoints) }}"/>
                        @foreach ($areaPoints as $pointIndex => $point)
                        <circle class="an-trend-dot" data-an-point data-an-tooltip="{{ $trend['six']['labels'][$pointIndex] ?? ($pointIndex + 1) }}|{{ number_format($trend['area'][$pointIndex] ?? 0) }} cumulative" cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="2.4"/>
                        @endforeach
                    </svg>
                    <div class="an-trend-labels">
                        @foreach ($trend['six']['labels'] as $label)
                        <span>{{ $label }}</span>
                        @endforeach
                    </div>
                    @else
                    <div class="an-empty">
                        <div>
                            <div class="an-empty-mark">0</div>
                            <strong>{{ __('Cumulative Volume') }}</strong>
                            <span>{{ __('No activity recorded in the last six months.') }}</span>
                        </div>
                    </div>
                    @endif
                </div>
            </article>
        </div>

        <article class="an-card">
            <div class="an-card-head">
                <span class="an-card-kicker">{{ $trend['kicker'] }}</span>
                <h3>{{ __('Activity Heatmap') }} – 8 {{ __('Weeks') }}</h3>
                <p class="an-card-copy">{{ __('Peak activity days across the last eight weeks.') }}</p>
            </div>
            <div class="an-card-body">
                @if ($trend['heat']['total'] > 0)
                <div class="an-heat" role="img" aria-label="{{ $trend['title'] }} by day, {{ $trend['heat']['total'] }} total. {{ $trend['heat']['cells'][0]['date'] }} to {{ $trend['heat']['cells'][count($trend['heat']['cells']) - 1]['date'] }}">
                    <span class="an-heat-dow"></span>
                    @foreach ($heatLabels as $heatLabel)
                    <span class="an-heat-dow">{{ $heatLabel }}</span>
                    @endforeach
                    @foreach ($trend['heat']['cells'] as $cellIndex => $cell)
                    @if ($cellIndex % 7 === 0)
                    <span class="an-heat-dow">{{ $cell['weekday'] }}</span>
                    @endif
                    <span class="an-heat-cell" data-lvl="{{ $cell['level'] }}" title="{{ $cell['date'] }}: {{ $cell['count'] }} {{ Str::lower($trend['kicker']) }}"></span>
                    @endforeach
                </div>
                <div class="an-heat-foot">
                    <span>{{ $trend['heat']['cells'][0]['date'] }} – {{ $trend['heat']['cells'][count($trend['heat']['cells']) - 1]['date'] }} · {{ number_format($trend['heat']['total']) }} {{ Str::lower($trend['kicker']) }}</span>
                    <span class="an-heat-legend">{{ __('Less') }} <i style="background:var(--surface-soft);border:1px solid var(--border);"></i><i style="background:color-mix(in srgb,var(--primary) 42%,var(--surface));border-radius:4px;"></i><i style="background:var(--primary);border-radius:4px;"></i> {{ __('More') }}</span>
                </div>
                @else
                <div class="an-empty">
                    <div>
                        <div class="an-empty-mark">0</div>
                        <strong>{{ __('Activity Heatmap') }}</strong>
                        <span>{{ __('No activity recorded in the last eight weeks.') }}</span>
                    </div>
                </div>
                @endif
            </div>
        </article>
    @endforeach
</section>
<div class="an-tooltip" data-an-tooltip-surface role="status" aria-live="polite"></div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('[data-dashboard-analytics]');
    const tooltip = document.querySelector('[data-an-tooltip-surface]');
    if (!root || !tooltip || root.dataset.interactionsReady === 'true') return;
    root.dataset.interactionsReady = 'true';

    let activePoint = null;
    const periodButtons = [...root.querySelectorAll('[data-an-period]')];
    const periodPanels = [...root.querySelectorAll('[data-an-period-panel]')];
    const dailyPanel = root.querySelector('.an-daily-chart');
    const dailyTitle = root.querySelector('[data-an-daily-title]');
    periodButtons.forEach((button) => button.addEventListener('click', () => {
        const period = button.dataset.anPeriod;
        periodButtons.forEach((item) => {
            const selected = item === button;
            item.classList.toggle('active', selected);
            item.setAttribute('aria-pressed', selected ? 'true' : 'false');
        });
        periodPanels.forEach((panel) => {
            panel.hidden = !panel.dataset.anPeriodPanel.split(',').includes(period);
        });
        dailyPanel?.classList.toggle('is-seven', period === '7');
        if (dailyTitle && period !== '12') dailyTitle.textContent = `Daily Activity - ${period} Days`;
        hideTooltip();
    }));

    const showTooltip = (point, clientX, clientY) => {
        if (activePoint === point) {
            tooltip.style.left = `${Math.max(70, Math.min(window.innerWidth - 70, clientX))}px`;
            tooltip.style.top = `${Math.max(70, clientY)}px`;
            return;
        }
        const [label, value = ''] = (point.dataset.anTooltip || '').split('|');
        if (!label) return;
        tooltip.replaceChildren();
        const title = document.createElement('strong');
        const detail = document.createElement('span');
        title.textContent = label;
        detail.textContent = value;
        title.style.display = 'block';
        detail.style.display = 'block';
        detail.style.marginTop = '2px';
        detail.style.color = 'rgba(255,255,255,.78)';
        tooltip.append(title, detail);
        tooltip.style.left = `${Math.max(70, Math.min(window.innerWidth - 70, clientX))}px`;
        tooltip.style.top = `${Math.max(70, clientY)}px`;
        tooltip.classList.add('is-visible');
        activePoint = point;

        const chart = point.closest('[data-an-chart]');
        const guide = chart?.querySelector('[data-an-guide]');
        if (guide && point.dataset.anX) {
            guide.setAttribute('x1', point.dataset.anX);
            guide.setAttribute('x2', point.dataset.anX);
            guide.style.opacity = '1';
        }
    };
    const hideTooltip = () => {
        tooltip.classList.remove('is-visible');
        activePoint?.closest('[data-an-chart]')?.querySelector('[data-an-guide]')?.style.removeProperty('opacity');
        activePoint = null;
    };

    root.addEventListener('pointerover', (event) => {
        const point = event.target.closest?.('[data-an-point][data-an-tooltip]');
        if (point) showTooltip(point, event.clientX, event.clientY);
    });
    root.addEventListener('pointermove', (event) => {
        const directPoint = event.target.closest?.('[data-an-point][data-an-tooltip]');
        if (directPoint) {
            showTooltip(directPoint, event.clientX, event.clientY);
            return;
        }

        const chart = event.target.closest?.('[data-an-chart]');
        const points = chart ? [...chart.querySelectorAll('[data-an-x][data-an-tooltip]')] : [];
        if (points.length === 0) return;
        const nearest = points.reduce((best, point) => {
            const rect = point.getBoundingClientRect();
            const distance = Math.abs(event.clientX - (rect.left + rect.width / 2));
            return !best || distance < best.distance ? { point, distance } : best;
        }, null);
        if (nearest) showTooltip(nearest.point, event.clientX, event.clientY);
    });
    root.addEventListener('pointerout', (event) => {
        const point = event.target.closest?.('[data-an-point][data-an-tooltip]');
        if (point && (!event.relatedTarget || !point.contains(event.relatedTarget))) hideTooltip();
        else if (!event.relatedTarget || !root.contains(event.relatedTarget)) hideTooltip();
    });
    root.querySelectorAll('[data-an-chart]').forEach((chart) => chart.addEventListener('pointerleave', hideTooltip));
    window.addEventListener('scroll', hideTooltip, { passive: true });
});
</script>
@endpush
@endif
