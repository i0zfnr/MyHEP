@push('styles')
<style>
    .analytics-dashboard {
        display: none;
        grid-template-columns: minmax(0, 1fr);
        gap: 24px;
        transform: translateZ(0);
    }
    .adash[data-dashboard-mode="graphs"] .analytics-dashboard { display: grid; }
    .adash[data-dashboard-mode="graphs"] .stats-grid { display: none !important; }

    /* Screen Reader Accessibility Text */
    .an-sr {
        position: absolute !important;
        width: 1px !important;
        height: 1px !important;
        padding: 0 !important;
        margin: -1px !important;
        overflow: hidden !important;
        clip: rect(0, 0, 0, 0) !important;
        white-space: nowrap !important;
        border: 0 !important;
    }

    /* Executive Overview Header */
    .an-overview-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 2px 0 6px;
    }
    .an-overview-title h2 {
        margin: 0;
        font-size: 1.35rem;
        font-weight: 800;
        letter-spacing: -0.03em;
        color: var(--c-text-primary, #171310);
    }
    body[data-theme="dark"] .an-overview-title h2 { color: #fdf8f3; }
    .an-overview-title p {
        margin: 4px 0 0;
        font-size: 0.82rem;
        color: var(--c-text-secondary, #7f7165);
    }
    .an-overview-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 999px;
        background: rgba(196, 142, 66, 0.12);
        border: 1px solid rgba(196, 142, 66, 0.24);
        color: #c48e42;
        font-size: 0.74rem;
        font-weight: 800;
    }

    /* KPI Summary Cards */
    .an-kpis {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
    }
    .an-kpi {
        position: relative;
        min-width: 0;
        padding: 18px 20px;
        border: 1px solid var(--c-border, #eadfd2);
        border-radius: 18px;
        background: var(--c-surface, #ffffff);
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.03);
        overflow: hidden;
        contain: paint;
        transform: translateZ(0);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .an-kpi:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 22px rgba(0, 0, 0, 0.06);
    }
    body[data-theme="dark"] .an-kpi {
        background: #171310;
        border-color: rgba(226, 209, 192, 0.14);
    }
    .an-kpi::before {
        content: '';
        position: absolute;
        left: 0; right: 0; top: 0;
        height: 3px;
        background: var(--kpi-accent, #c48e42);
    }
    .an-kpi-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
    }
    .an-kpi-heading {
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .an-kpi-icon {
        width: 32px;
        height: 32px;
        border-radius: 9px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(196, 142, 66, 0.12);
        color: var(--kpi-accent, #c48e42);
        flex-shrink: 0;
    }
    .an-kpi-icon svg {
        width: 17px;
        height: 17px;
        fill: none;
        stroke: currentColor;
        stroke-width: 1.8;
        stroke-linecap: round;
        stroke-linejoin: round;
        display: block;
    }
    .an-kpi-label {
        color: var(--c-text-secondary, #7f7165);
        font-size: 0.74rem;
        font-weight: 750;
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }
    body[data-theme="dark"] .an-kpi-label { color: #b8a899; }
    .an-kpi-value {
        display: block;
        margin-top: 10px;
        color: var(--c-text-primary, #171310);
        font-size: clamp(1.6rem, 2.5vw, 2.1rem);
        font-weight: 850;
        line-height: 1.1;
        letter-spacing: -0.04em;
        font-variant-numeric: tabular-nums;
    }
    body[data-theme="dark"] .an-kpi-value { color: #fdf8f3; }
    .an-kpi-bottom {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 10px;
        margin-top: 10px;
    }
    .an-kpi-sub {
        color: var(--c-text-secondary, #7f7165);
        font-size: 0.72rem;
    }
    body[data-theme="dark"] .an-kpi-sub { color: #8e8072; }
    .an-delta {
        padding: 3px 8px;
        border-radius: 999px;
        font-size: 0.68rem;
        font-weight: 800;
        white-space: nowrap;
    }
    .an-delta.up { background: rgba(16, 185, 129, 0.12); color: #059669; }
    .an-delta.down { background: rgba(239, 68, 68, 0.12); color: #dc2626; }
    .an-delta.flat { background: rgba(148, 163, 184, 0.12); color: #64748b; }
    .an-spark { width: 64px; height: 24px; flex-shrink: 0; }

    /* KPI Tone Palettes */
    .tone-slate { --kpi-accent: #64748b; }
    .tone-slate .an-kpi-icon { background: rgba(100, 116, 139, 0.12); color: #64748b; }
    .tone-gold { --kpi-accent: #c48e42; }
    .tone-gold .an-kpi-icon { background: rgba(196, 142, 66, 0.12); color: #c48e42; }
    .tone-blue { --kpi-accent: #3b82f6; }
    .tone-blue .an-kpi-icon { background: rgba(59, 130, 246, 0.12); color: #3b82f6; }
    .tone-green { --kpi-accent: #10b981; }
    .tone-green .an-kpi-icon { background: rgba(16, 185, 129, 0.12); color: #10b981; }
    .tone-violet { --kpi-accent: #8b5cf6; }
    .tone-violet .an-kpi-icon { background: rgba(139, 92, 246, 0.12); color: #8b5cf6; }
    .tone-red { --kpi-accent: #ef4444; }
    .tone-red .an-kpi-icon { background: rgba(239, 68, 68, 0.12); color: #ef4444; }

    /* Master Executive Studio Card */
    .an-studio-card {
        border: 1px solid var(--c-border, #eadfd2);
        border-radius: 24px;
        background: var(--c-surface, #ffffff);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.03);
        overflow: hidden;
        contain: paint;
        transform: translateZ(0);
    }
    body[data-theme="dark"] .an-studio-card {
        background: #171310;
        border-color: rgba(226, 209, 192, 0.14);
    }
    .an-studio-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
        padding: 22px 24px 18px;
        border-bottom: 1px solid var(--c-border, #eadfd2);
    }
    body[data-theme="dark"] .an-studio-header { border-color: rgba(226, 209, 192, 0.12); }
    .an-studio-title h3 {
        margin: 0;
        font-size: 1.15rem;
        font-weight: 800;
        letter-spacing: -0.02em;
        color: var(--c-text-primary, #171310);
    }
    body[data-theme="dark"] .an-studio-title h3 { color: #fdf8f3; }
    .an-studio-title p {
        margin: 3px 0 0;
        font-size: 0.78rem;
        color: var(--c-text-secondary, #7f7165);
    }

    /* Studio Controls */
    .an-studio-controls {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
    }
    .an-pills {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px;
        border-radius: 12px;
        background: rgba(0, 0, 0, 0.03);
        border: 1px solid var(--c-border, #eadfd2);
    }
    body[data-theme="dark"] .an-pills {
        background: rgba(255, 255, 255, 0.03);
        border-color: rgba(226, 209, 192, 0.12);
    }
    .an-pill {
        min-height: 30px;
        padding: 0 12px;
        border: 0;
        border-radius: 8px;
        background: transparent;
        color: var(--c-text-secondary, #7f7165);
        font: inherit;
        font-size: 0.74rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    body[data-theme="dark"] .an-pill { color: #b8a899; }
    .an-pill.active {
        background: linear-gradient(135deg, #f3d49b 0%, #c48e42 100%);
        color: #17120c;
        box-shadow: 0 2px 8px rgba(196, 142, 66, 0.22);
    }

    /* Metric Key Summary Bar */
    .an-studio-metrics {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 14px;
        padding: 16px 24px;
        background: rgba(0, 0, 0, 0.015);
        border-bottom: 1px solid var(--c-border, #eadfd2);
    }
    body[data-theme="dark"] .an-studio-metrics {
        background: rgba(255, 255, 255, 0.02);
        border-color: rgba(226, 209, 192, 0.12);
    }
    .an-sm-item {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
    .an-sm-label {
        font-size: 0.7rem;
        font-weight: 750;
        text-transform: uppercase;
        color: var(--c-text-secondary, #7f7165);
        letter-spacing: 0.03em;
    }
    .an-sm-val {
        font-size: 1.35rem;
        font-weight: 850;
        color: var(--c-text-primary, #171310);
        font-variant-numeric: tabular-nums;
    }
    body[data-theme="dark"] .an-sm-val { color: #fdf8f3; }
    .an-sm-sub {
        font-size: 0.72rem;
        color: var(--c-text-secondary, #7f7165);
    }

    /* Interactive Studio Chart Area */
    .an-studio-body { padding: 20px 24px 24px; }
    .an-chart-panel[hidden] { display: none !important; }
    .an-studio-svg-wrap {
        position: relative;
        width: 100%;
        height: 240px;
    }
    .an-studio-svg {
        width: 100%;
        height: 100%;
        display: block;
    }
    .an-gridline {
        stroke: rgba(0, 0, 0, 0.06);
        stroke-width: 0.35;
        stroke-dasharray: 3 3;
    }
    body[data-theme="dark"] .an-gridline { stroke: rgba(255, 255, 255, 0.06); }
    .an-studio-line {
        fill: none !important;
        stroke: #c48e42 !important;
        stroke-width: 0.8 !important;
        stroke-linecap: round;
        stroke-linejoin: round;
        vector-effect: non-scaling-stroke;
    }
    .an-studio-area {
        vector-effect: non-scaling-stroke;
        opacity: 0.85;
    }
    .an-studio-dot {
        fill: var(--c-surface, #ffffff) !important;
        stroke: #c48e42 !important;
        stroke-width: 0.45 !important;
        vector-effect: non-scaling-stroke;
        cursor: crosshair;
        transition: transform 0.15s ease;
    }
    body[data-theme="dark"] .an-studio-dot { fill: #171310 !important; }
    .an-studio-dot:hover { transform: scale(1.4); transform-origin: center; }
    .an-chart-x-labels {
        display: flex;
        justify-content: space-between;
        margin-top: 10px;
        font-size: 0.72rem;
        font-weight: 750;
        color: var(--c-text-secondary, #7f7165);
    }

    /* 2-Column Grid Layouts */
    .an-grid-2 {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(min(450px, 100%), 1fr));
        gap: 20px;
    }
    .an-card {
        min-width: 0;
        border: 1px solid var(--c-border, #eadfd2);
        border-radius: 22px;
        background: var(--c-surface, #ffffff);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.03);
        overflow: hidden;
        contain: paint;
        transform: translateZ(0);
    }
    body[data-theme="dark"] .an-card {
        background: #171310;
        border-color: rgba(226, 209, 192, 0.14);
    }
    .an-card-head { padding: 20px 22px 0; }
    .an-card-kicker {
        color: var(--c-accent, #c48e42);
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        display: block;
        margin-bottom: 2px;
    }
    .an-card h3 {
        margin: 0;
        color: var(--c-text-primary, #171310);
        font-size: 1.08rem;
        font-weight: 800;
        letter-spacing: -0.02em;
    }
    body[data-theme="dark"] .an-card h3 { color: #fdf8f3; }
    .an-card-copy {
        margin: 4px 0 0;
        color: var(--c-text-secondary, #7f7165);
        font-size: 0.78rem;
    }
    .an-card-body { padding: 18px 22px 22px; }

    /* Program Stack & Progress Distribution */
    .an-stack { display: grid; gap: 14px; }
    .an-stack-row {
        display: grid;
        grid-template-columns: minmax(80px, auto) 1fr auto;
        align-items: center;
        gap: 14px;
        color: var(--c-text-secondary, #7f7165);
        font-size: 0.78rem;
    }
    body[data-theme="dark"] .an-stack-row { color: #b8a899; }
    .an-stack-track {
        height: 12px;
        display: flex;
        overflow: hidden;
        border-radius: 999px;
        background: rgba(0, 0, 0, 0.04);
    }
    body[data-theme="dark"] .an-stack-track { background: rgba(255, 255, 255, 0.05); }
    .an-stack-seg { height: 100%; transition: width 0.3s ease; }
    .an-stack-total {
        color: var(--c-text-primary, #171310);
        font-weight: 800;
        font-variant-numeric: tabular-nums;
    }
    body[data-theme="dark"] .an-stack-total { color: #fdf8f3; }
    .an-stack-legend {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 14px;
        font-size: 0.72rem;
        color: var(--c-text-secondary, #7f7165);
    }
    .an-stack-legend span { display: inline-flex; align-items: center; gap: 6px; }
    .an-stack-legend i {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--legend-color, #c48e42);
    }

    /* Ranked Categories */
    .an-ranked { display: grid; gap: 8px; margin-top: 14px; }
    .an-ranked-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 9px 12px;
        border-radius: 10px;
        background: rgba(0, 0, 0, 0.02);
        border: 1px solid var(--c-border, #eadfd2);
        font-size: 0.78rem;
        color: var(--c-text-secondary, #7f7165);
    }
    body[data-theme="dark"] .an-ranked-row {
        background: rgba(255, 255, 255, 0.03);
        border-color: rgba(226, 209, 192, 0.12);
        color: #b8a899;
    }
    .an-ranked-row strong {
        color: var(--c-text-primary, #171310);
        font-weight: 750;
    }
    body[data-theme="dark"] .an-ranked-row strong { color: #fdf8f3; }

    /* Donut Charts */
    .an-donut-layout {
        display: grid;
        grid-template-columns: minmax(140px, 1fr) minmax(160px, 1.2fr);
        align-items: center;
        gap: 20px;
    }
    .an-donut {
        width: 150px;
        aspect-ratio: 1;
        margin: auto;
        display: grid;
        place-items: center;
        border-radius: 50%;
        background: var(--donut);
        position: relative;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
        contain: paint;
    }
    .an-donut::after {
        content: '';
        position: absolute;
        inset: 24px;
        border-radius: 50%;
        background: var(--c-surface, #ffffff);
        box-shadow: inset 0 0 0 1px var(--c-border, #eadfd2);
    }
    body[data-theme="dark"] .an-donut::after {
        background: #171310;
        box-shadow: inset 0 0 0 1px rgba(226, 209, 192, 0.14);
    }
    .an-donut-centre { position: relative; z-index: 1; text-align: center; }
    .an-donut-centre strong {
        display: block;
        color: var(--c-text-primary, #171310);
        font-size: 1.5rem;
        font-weight: 850;
        line-height: 1;
    }
    body[data-theme="dark"] .an-donut-centre strong { color: #fdf8f3; }
    .an-donut-centre span {
        display: block;
        margin-top: 2px;
        color: var(--c-text-secondary, #7f7165);
        font-size: 0.68rem;
    }
    .an-legend { display: grid; gap: 8px; }
    .an-legend-row {
        display: grid;
        grid-template-columns: auto 1fr auto;
        align-items: center;
        gap: 8px;
        color: var(--c-text-secondary, #7f7165);
        font-size: 0.76rem;
    }
    body[data-theme="dark"] .an-legend-row { color: #b8a899; }
    .an-legend-row i {
        width: 9px;
        height: 9px;
        border-radius: 50%;
        background: var(--legend-color);
    }
    .an-legend-row strong {
        color: var(--c-text-primary, #171310);
        font-weight: 700;
        font-variant-numeric: tabular-nums;
    }
    body[data-theme="dark"] .an-legend-row strong { color: #fdf8f3; }

    /* Heatmap Grid */
    .an-heat-shell {
        max-width: 100%;
        overflow-x: auto;
        padding: 6px 0;
    }
    .an-heat {
        width: max-content;
        margin: 0 auto;
        display: grid;
        grid-template-columns: 36px repeat(8, 44px);
        grid-template-rows: 20px repeat(7, 22px);
        gap: 5px;
        align-items: center;
    }
    .an-heat-dow {
        font-size: 0.68rem;
        font-weight: 800;
        color: var(--c-text-secondary, #7f7165);
        text-align: center;
        text-transform: uppercase;
    }
    .an-heat-week {
        font-size: 0.64rem;
        font-weight: 750;
        color: var(--c-text-secondary, #7f7165);
        text-align: center;
    }
    .an-heat-cell {
        width: 22px;
        height: 22px;
        justify-self: center;
        border-radius: 5px;
        background: rgba(0, 0, 0, 0.04);
        border: 1px solid var(--c-border, #eadfd2);
        cursor: default;
        transition: transform 0.15s ease;
    }
    body[data-theme="dark"] .an-heat-cell {
        background: rgba(255, 255, 255, 0.05);
        border-color: rgba(226, 209, 192, 0.10);
    }
    .an-heat-cell:hover { transform: scale(1.15); }
    .an-heat-cell[data-lvl="1"] { background: rgba(196, 142, 66, 0.25); border-color: rgba(196, 142, 66, 0.35); }
    .an-heat-cell[data-lvl="2"] { background: rgba(196, 142, 66, 0.50); border-color: rgba(196, 142, 66, 0.60); }
    .an-heat-cell[data-lvl="3"] { background: rgba(196, 142, 66, 0.75); border-color: rgba(196, 142, 66, 0.85); }
    .an-heat-cell[data-lvl="4"] { background: #c48e42; border-color: #9a6d2c; }
    .an-heat-foot {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-top: 12px;
        font-size: 0.72rem;
        color: var(--c-text-secondary, #7f7165);
    }
    .an-heat-legend {
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .an-heat-legend i {
        width: 10px;
        height: 10px;
        display: inline-block;
        border-radius: 2px;
    }

    /* Month-over-Month Comparison */
    .an-grouped { display: grid; gap: 10px; }
    .an-group {
        display: grid;
        grid-template-columns: minmax(80px, auto) 1fr auto;
        align-items: center;
        gap: 12px;
        font-size: 0.78rem;
        color: var(--c-text-secondary, #7f7165);
    }
    .an-group-pair {
        height: 14px;
        display: flex;
        gap: 3px;
        align-items: flex-end;
    }
    .an-group-bar {
        width: 14px;
        border-radius: 3px 3px 0 0;
        background: #c48e42;
    }
    .an-group-bar.last { background: rgba(196, 142, 66, 0.35); }
    .an-group-value {
        color: var(--c-text-primary, #171310);
        font-weight: 750;
    }
    body[data-theme="dark"] .an-group-value { color: #fdf8f3; }
    .an-group-key {
        display: flex;
        gap: 14px;
        margin-top: 10px;
        font-size: 0.72rem;
        color: var(--c-text-secondary, #7f7165);
    }
    .an-group-key span { display: inline-flex; align-items: center; gap: 6px; }
    .an-group-key i {
        width: 10px;
        height: 10px;
        border-radius: 2px;
        background: #c48e42;
    }
    .an-group-key i.last { background: rgba(196, 142, 66, 0.35); }

    /* Empty State */
    .an-empty {
        min-height: 140px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 20px;
        color: var(--c-text-secondary, #7f7165);
    }
    .an-empty-mark {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        margin: 0 auto 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(196, 142, 66, 0.12);
        color: #c48e42;
        font-weight: 800;
        font-size: 0.85rem;
    }
    .an-empty strong {
        display: block;
        font-size: 0.92rem;
        color: var(--c-text-primary, #171310);
    }
    body[data-theme="dark"] .an-empty strong { color: #fdf8f3; }
    .an-empty span {
        display: block;
        margin-top: 2px;
        font-size: 0.74rem;
    }

    /* Tooltip */
    .an-tooltip {
        position: fixed;
        z-index: 9999;
        padding: 8px 12px;
        border-radius: 10px;
        background: rgba(23, 19, 16, 0.94);
        color: #ffffff;
        font-size: 0.72rem;
        line-height: 1.4;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.22);
        pointer-events: none;
        backdrop-filter: blur(8px);
        opacity: 0;
        transform: translate(-50%, calc(-100% - 8px));
        transition: opacity 0.15s ease;
    }
    .an-tooltip.is-visible { opacity: 1; }

    /* Unified analytics system */
    .analytics-dashboard :is(.an-card,.an-kpi,.an-featured,.an-studio-card) {
        background:var(--se-surface);
    }

    /* Warm monitoring visual language shared with System Performance. */
    .analytics-dashboard {
        border-color: color-mix(in srgb,var(--se-primary) 30%,var(--se-border));
    }
    @media (prefers-reduced-motion: reduce) {
        .analytics-dashboard > *, .an-studio-dot, .an-studio-line, .an-donut {
            animation:none!important;
        }
    }

    @media (max-width: 1024px) {
        .an-kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .an-donut-layout { grid-template-columns: 1fr; }
    }
    @media (max-width: 640px) {
        .an-kpis { grid-template-columns: 1fr; }
        .an-overview-head { flex-direction: column; align-items: flex-start; }
        .an-studio-header { flex-direction: column; align-items: flex-start; }
    }
</style>
@endpush

@if (!empty($analytics['domains']))
@php
    $anTileColors = ['#c48e42', '#3b82f6', '#10b981', '#8b5cf6', '#ef4444', '#8c8175', '#6f8d78', '#9a6a3f'];

    $anComputePoints = function (array $values, int $width = 100, int $height = 40, int $pad = 4): array {
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

    $anRankedCategories = array_slice($analytics['hbar']['rows'] ?? ($analytics['donuts'][0]['segments'] ?? []), 0, 5);
    $anActivitySegments = collect($analytics['trends'])->map(function ($trend, $index) use ($anTileColors) {
        return ['label' => $trend['title'], 'value' => (int) array_sum($trend['six']['values']), 'color' => $anTileColors[$index % count($anTileColors)]];
    })->filter(fn ($segment) => $segment['value'] > 0)->values()->all();
    $anActivityTotal = (int) array_sum(array_column($anActivitySegments, 'value'));
    $primaryDonut = $analytics['donuts'][0] ?? null;
@endphp
<section class="analytics-dashboard" data-dashboard-analytics aria-label="{{ __('Executive Analytics Studio') }}">
    <header class="an-overview-head">
        <div class="an-overview-title">
            <h2>{{ __('Executive Analytics Studio') }}</h2>
            <p>{{ __('Unified operational performance across discipline, campus movements, scholarships, and student enrollment.') }}</p>
        </div>
        <div class="an-overview-badge">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 6v6l4 2"/></svg>
            <span>{{ __('Live System Insights') }}</span>
        </div>
    </header>

    {{-- Top Executive KPI Summary Row --}}
    @if ($analytics['kpis'] !== [])
    <div class="an-kpis">
        @foreach ($analytics['kpis'] as $kpi)
        <article class="an-kpi tone-{{ $kpi['tone'] }}">
            <div class="an-kpi-top">
                <span class="an-kpi-heading">
                    <span class="an-kpi-icon" aria-hidden="true">
                    @switch($kpi['icon'] ?? 'announcement')
                        @case('offense')<svg viewBox="0 0 24 24"><path d="M12 3 3.5 7.5v5c0 4.7 3.6 7.3 8.5 8.5 4.9-1.2 8.5-3.8 8.5-8.5v-5L12 3Z"/><path d="M12 8v5m0 3h.01"/></svg>@break
                        @case('payment')<svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18m-5 5h2"/></svg>@break
                        @case('review')<svg viewBox="0 0 24 24"><path d="M8 4h8l3 3v13H5V4h3Z"/><path d="M9 12h6m-6 4h4M14 4v4h4"/></svg>@break
                        @case('students')<svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3"/><path d="M3.5 19c.5-4 2.3-6 5.5-6s5 2 5.5 6M16 7.5a2.5 2.5 0 0 1 0 5M16.5 14c2.4.5 3.7 2.2 4 5"/></svg>@break
                        @case('outside')<svg viewBox="0 0 24 24"><path d="M10 4H5v16h5M14 8l4 4-4 4m4-4H9"/></svg>@break
                        @case('movement')<svg viewBox="0 0 24 24"><path d="M4 7h13m0 0-3-3m3 3-3 3M20 17H7m0 0 3 3m-3-3 3-3"/></svg>@break
                        @case('late')<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v6l4 2"/></svg>@break
                        @case('records')<svg viewBox="0 0 24 24"><path d="M6 3h12v18H6zM9 7h6m-6 4h6m-6 4h4"/></svg>@break
                        @case('aid')<svg viewBox="0 0 24 24"><path d="M12 20s-7-4.4-7-10a4 4 0 0 1 7-2.6A4 4 0 0 1 19 10c0 5.6-7 10-7 10Z"/><path d="M9 12h6m-3-3v6"/></svg>@break
                        @case('pending')<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>@break
                        @default<svg viewBox="0 0 24 24"><path d="M5 18h14V6H5zM8 3v3m8-3v3M8 10h8m-8 4h5"/></svg>
                    @endswitch
                    </span>
                    <span class="an-kpi-label">{{ $kpi['label'] }}</span>
                </span>
                @if ($kpi['delta'])
                <span class="an-delta {{ $kpi['delta']['dir'] }}" aria-label="{{ $kpi['delta']['text'] }}">{{ $kpi['delta']['text'] }}</span>
                @endif
            </div>
            <strong class="an-kpi-value">{{ $kpi['value'] }}</strong>
            <div class="an-kpi-bottom">
                <span class="an-kpi-sub">{{ $kpi['sub'] }}</span>
                @if ($kpi['spark'] !== [])
                <svg class="an-spark" viewBox="0 0 100 24" preserveAspectRatio="none" aria-hidden="true">
                    <polyline points="{{ $anPointsToLine($anComputePoints($kpi['spark'], 100, 24, 2)) }}" fill="none" stroke="var(--kpi-accent, #c48e42)" stroke-width="2.5" stroke-linejoin="round" stroke-linecap="round"/>
                </svg>
                @endif
            </div>
            <span class="an-sr">{{ $kpi['label'] }}: {{ $kpi['value'] }}. {{ $kpi['sub'] }}. Last six months: {{ implode(', ', $kpi['spark']) }}.</span>
        </article>
        @endforeach
    </div>
    @endif

    {{-- Main Executive Interactive Studio --}}
    @if (!empty($analytics['trends']))
    <article class="an-studio-card" data-an-studio>
        <div class="an-studio-header">
            <div class="an-studio-title">
                <h3>{{ __('Domain Performance Studio') }}</h3>
                <p>{{ __('Interactive multi-metric trends and activity trajectory across modules.') }}</p>
            </div>
            <div class="an-studio-controls">
                <div class="an-pills" role="tablist" aria-label="{{ __('Select Domain') }}">
                    @foreach ($analytics['trends'] as $trendIndex => $trend)
                    <button type="button" class="an-pill {{ $trendIndex === 0 ? 'active' : '' }}" data-studio-tab="{{ $trendIndex }}" role="tab" aria-selected="{{ $trendIndex === 0 ? 'true' : 'false' }}">
                        {{ $trend['title'] }}
                    </button>
                    @endforeach
                </div>
                <div class="an-pills" role="radiogroup" aria-label="{{ __('Timeframe') }}">
                    <button type="button" class="an-pill active" data-studio-range="6">6 {{ __('Months') }}</button>
                    <button type="button" class="an-pill" data-studio-range="12">12 {{ __('Months') }}</button>
                </div>
            </div>
        </div>

        @foreach ($analytics['trends'] as $trendIndex => $trend)
        @php
            $sixValues = $trend['six']['values'] ?? [];
            $sixLabels = $trend['six']['labels'] ?? [];
            $twelveValues = $trend['twelve']['values'] ?? [];
            $twelveLabels = $trend['twelve']['labels'] ?? [];
            $areaValues = $trend['area'] ?? [];
            $sixTotal = (int) array_sum($sixValues);
            $twelveTotal = (int) array_sum($twelveValues);
            $currentMonth = (int) ($sixValues[count($sixValues) - 1] ?? 0);
            $prevMonth = (int) ($sixValues[count($sixValues) - 2] ?? 0);
            $deltaPercent = $prevMonth > 0 ? round((($currentMonth - $prevMonth) / $prevMonth) * 100, 1) : ($currentMonth > 0 ? 100 : 0);
            $sixPoints = $anComputePoints($sixValues, 100, 24, 2);
            $areaPoints = $anComputePoints($areaValues, 100, 24, 2);
            $twelvePoints = $anComputePoints($twelveValues, 100, 24, 2);
        @endphp
        <div class="an-chart-panel {{ $trendIndex === 0 ? 'active' : '' }}" data-studio-panel="{{ $trendIndex }}" {{ $trendIndex > 0 ? 'hidden' : '' }}>
            <div class="an-studio-metrics">
                <div class="an-sm-item">
                    <span class="an-sm-label">{{ __('Current Month Volume') }}</span>
                    <strong class="an-sm-val">{{ number_format($currentMonth) }}</strong>
                    <span class="an-sm-sub">
                        <span class="an-delta {{ $deltaPercent < 0 ? 'down' : 'up' }}">{{ $deltaPercent > 0 ? '+' : '' }}{{ $deltaPercent }}%</span>
                        {{ __('vs last month') }}
                    </span>
                </div>
                <div class="an-sm-item">
                    <span class="an-sm-label">{{ __('6-Month Total') }}</span>
                    <strong class="an-sm-val">{{ number_format($sixTotal) }}</strong>
                    <span class="an-sm-sub">{{ __('Total recorded volume') }}</span>
                </div>
                <div class="an-sm-item">
                    <span class="an-sm-label">{{ __('12-Month Total') }}</span>
                    <strong class="an-sm-val">{{ number_format($twelveTotal) }}</strong>
                    <span class="an-sm-sub">{{ __('Annual cumulative trajectory') }}</span>
                </div>
                <div class="an-sm-item">
                    <span class="an-sm-label">{{ __('Peak Record') }}</span>
                    <strong class="an-sm-val">{{ number_format(max(1, ...$twelveValues)) }}</strong>
                    <span class="an-sm-sub">{{ __('Highest monthly volume') }}</span>
                </div>
            </div>

            <div class="an-studio-body">
                @if ($sixTotal > 0 || $twelveTotal > 0)
                {{-- 6-Month View --}}
                <div class="an-studio-range-view active" data-studio-range-view="6">
                    <div class="an-studio-svg-wrap">
                        <svg class="an-studio-svg" data-an-chart viewBox="0 0 100 24" preserveAspectRatio="none" role="img" aria-label="{{ $trend['title'] }} 6-month curve">
                            <defs>
                                <linearGradient id="anGradSix{{ $trendIndex }}" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="#c48e42" stop-opacity="0.30"/>
                                    <stop offset="100%" stop-color="#c48e42" stop-opacity="0.02"/>
                                </linearGradient>
                            </defs>
                            <line class="an-gridline" x1="0" y1="6" x2="100" y2="6"/>
                            <line class="an-gridline" x1="0" y1="12" x2="100" y2="12"/>
                            <line class="an-gridline" x1="0" y1="18" x2="100" y2="18"/>
                            <path class="an-studio-area" d="{{ $anPointsToSmoothPath($sixPoints) }} L{{ $sixPoints[count($sixPoints)-1]['x'] }},24 L{{ $sixPoints[0]['x'] }},24 Z" fill="url(#anGradSix{{ $trendIndex }})"/>
                            <path class="an-studio-line" fill="none" stroke="#c48e42" stroke-width="0.8" stroke-linecap="round" stroke-linejoin="round" vector-effect="non-scaling-stroke" d="{{ $anPointsToSmoothPath($sixPoints) }}"/>
                            @foreach ($sixPoints as $pointIndex => $point)
                            <circle class="an-studio-dot" fill="var(--c-surface, #ffffff)" stroke="#c48e42" stroke-width="0.45" vector-effect="non-scaling-stroke" data-an-point data-an-tooltip="{{ $sixLabels[$pointIndex] ?? ($pointIndex + 1) }}|{{ number_format($sixValues[$pointIndex] ?? 0) }} {{ Str::lower($trend['kicker']) }}" cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="1.3"/>
                            @endforeach
                        </svg>
                    </div>
                    <div class="an-chart-x-labels">
                        @foreach ($sixLabels as $label)<span>{{ $label }}</span>@endforeach
                    </div>
                </div>

                {{-- 12-Month View --}}
                <div class="an-studio-range-view" data-studio-range-view="12" hidden>
                    <div class="an-studio-svg-wrap">
                        <svg class="an-studio-svg" data-an-chart viewBox="0 0 100 24" preserveAspectRatio="none" role="img" aria-label="{{ $trend['title'] }} 12-month curve">
                            <defs>
                                <linearGradient id="anGradTwelve{{ $trendIndex }}" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="#3b82f6" stop-opacity="0.30"/>
                                    <stop offset="100%" stop-color="#3b82f6" stop-opacity="0.02"/>
                                </linearGradient>
                            </defs>
                            <line class="an-gridline" x1="0" y1="6" x2="100" y2="6"/>
                            <line class="an-gridline" x1="0" y1="12" x2="100" y2="12"/>
                            <line class="an-gridline" x1="0" y1="18" x2="100" y2="18"/>
                            <path class="an-studio-area" d="{{ $anPointsToSmoothPath($twelvePoints) }} L{{ $twelvePoints[count($twelvePoints)-1]['x'] }},24 L{{ $twelvePoints[0]['x'] }},24 Z" fill="url(#anGradTwelve{{ $trendIndex }})"/>
                            <path class="an-studio-line" fill="none" stroke="#3b82f6" stroke-width="0.8" stroke-linecap="round" stroke-linejoin="round" vector-effect="non-scaling-stroke" d="{{ $anPointsToSmoothPath($twelvePoints) }}"/>
                            @foreach ($twelvePoints as $pointIndex => $point)
                            <circle class="an-studio-dot" fill="var(--c-surface, #ffffff)" stroke="#3b82f6" stroke-width="0.45" vector-effect="non-scaling-stroke" data-an-point data-an-tooltip="{{ $twelveLabels[$pointIndex] ?? ($pointIndex + 1) }}|{{ number_format($twelveValues[$pointIndex] ?? 0) }} {{ Str::lower($trend['kicker']) }}" cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="1.2"/>
                            @endforeach
                        </svg>
                    </div>
                    <div class="an-chart-x-labels">
                        @foreach ($twelveLabels as $label)<span>{{ $label }}</span>@endforeach
                    </div>
                </div>
                @else
                <div class="an-empty">
                    <div>
                        <div class="an-empty-mark">0</div>
                        <strong>{{ $trend['title'] }}</strong>
                        <span>{{ __('No operational activity recorded in this period yet.') }}</span>
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </article>
    @endif

    {{-- Secondary Executive Insights Grid (2 Columns) --}}
    <div class="an-grid-2">
        {{-- Academic Programs & Enrollment Breakdown --}}
        @if ($analytics['stacked'] && !empty($analytics['stacked']['series']))
        <article class="an-card">
            <div class="an-card-head">
                <span class="an-card-kicker">{{ $analytics['stacked']['kicker'] ?? __('Academic') }}</span>
                <h3>{{ $analytics['stacked']['title'] ?? __('Students by Program & Semester') }}</h3>
                <p class="an-card-copy">{{ __('Distribution of enrolled student cohorts across active programs.') }}</p>
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

                @if ($anRankedCategories !== [])
                <div class="an-ranked">
                    @foreach ($anRankedCategories as $ranked)
                    <div class="an-ranked-row" data-an-point data-an-tooltip="{{ $ranked['label'] }}|{{ number_format($ranked['value']) }} records">
                        <span>{{ $ranked['label'] }}</span>
                        <strong>{{ number_format($ranked['value']) }}</strong>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </article>
        @endif

        {{-- Operational Distribution Pipeline --}}
        @if ($primaryDonut)
        <article class="an-card">
            <div class="an-card-head">
                <span class="an-card-kicker">{{ $primaryDonut['kicker'] ?? __('Operations') }}</span>
                <h3>{{ $primaryDonut['title'] ?? __('Status & Pipeline Distribution') }}</h3>
                <p class="an-card-copy">{{ __('Active operational workflow and verification queue share.') }}</p>
            </div>
            <div class="an-card-body">
                @if ($primaryDonut['total'] > 0)
                <div class="an-donut-layout" data-an-chart>
                    <div class="an-donut" style="--donut:{{ $anDonutGradient($primaryDonut['segments'], $primaryDonut['total']) }};" role="img" aria-label="{{ $primaryDonut['title'] }}. Total {{ $primaryDonut['total'] }}">
                        <div class="an-donut-centre">
                            <strong>{{ number_format($primaryDonut['total']) }}</strong>
                            <span>{{ __('Total Records') }}</span>
                        </div>
                    </div>
                    <div class="an-legend">
                        @foreach ($primaryDonut['segments'] as $segment)
                        <div class="an-legend-row" data-an-point data-an-tooltip="{{ $segment['label'] }}|{{ number_format($segment['value']) }} ({{ $primaryDonut['total'] > 0 ? round($segment['value'] / $primaryDonut['total'] * 100) : 0 }}%)" style="--legend-color:{{ $segment['color'] }}">
                            <i></i><span>{{ $segment['label'] }}</span>
                            <strong>{{ number_format($segment['value']) }} <em>({{ $primaryDonut['total'] > 0 ? round($segment['value'] / $primaryDonut['total'] * 100) : 0 }}%)</em></strong>
                        </div>
                        @endforeach
                    </div>
                </div>
                @else
                <div class="an-empty">
                    <div>
                        <div class="an-empty-mark">0</div>
                        <strong>{{ $primaryDonut['title'] }}</strong>
                        <span>{{ __('No records available for this distribution yet.') }}</span>
                    </div>
                </div>
                @endif
            </div>
        </article>
        @endif
    </div>

    {{-- Bottom Grid: Heatmap & Month-over-Month Comparison --}}
    @if (!empty($analytics['trends'][0]['heat']))
    @php
        $featuredHeat = $analytics['trends'][0]['heat'];
        $heatLabels = array_column(array_slice($featuredHeat['cells'], 0, 7), 'weekday');
    @endphp
    <div class="an-grid-2">
        <article class="an-card">
            <div class="an-card-head">
                <span class="an-card-kicker">{{ __('Activity Matrix') }}</span>
                <h3>{{ __('Peak Activity Heatmap – 8 Weeks') }}</h3>
                <p class="an-card-copy">{{ __('Distribution of high-activity days across the latest eight weeks.') }}</p>
            </div>
            <div class="an-card-body">
                @if ($featuredHeat['total'] > 0)
                <div class="an-heat-shell">
                    <div class="an-heat" data-an-chart role="img" aria-label="Peak activity days">
                        <span aria-hidden="true"></span>
                        @foreach (range(0, 7) as $weekIndex)
                        <span class="an-heat-week">{{ $featuredHeat['cells'][$weekIndex * 7]['date'] }}</span>
                        @endforeach
                        @foreach ($heatLabels as $dayIndex => $heatLabel)
                        <span class="an-heat-dow">{{ $heatLabel }}</span>
                            @foreach (range(0, 7) as $weekIndex)
                                @php
                                    $cellIndex = $weekIndex * 7 + $dayIndex;
                                    $heatCell = $featuredHeat['cells'][$cellIndex] ?? null;
                                @endphp
                                @if ($heatCell)
                                <span class="an-heat-cell" data-an-point data-an-tooltip="{{ $heatCell['date'] }}|{{ number_format($heatCell['count']) }} records" data-lvl="{{ $heatCell['level'] }}" aria-label="{{ $heatCell['date'] }}: {{ $heatCell['count'] }}"></span>
                                @else
                                <span class="an-heat-cell" data-lvl="0"></span>
                                @endif
                            @endforeach
                        @endforeach
                    </div>
                </div>
                <div class="an-heat-foot">
                    <span>{{ $featuredHeat['cells'][0]['date'] }} – {{ $featuredHeat['cells'][count($featuredHeat['cells']) - 1]['date'] }}</span>
                    <span class="an-heat-legend">{{ __('Less') }} <i style="background:rgba(0,0,0,0.04);border:1px solid var(--c-border);"></i><i style="background:rgba(196,142,66,0.35);"></i><i style="background:#c48e42;"></i> {{ __('More') }}</span>
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

        @if ($analytics['grouped'])
        @php
            $groupedTotal = collect($analytics['grouped']['this'])->sum('value') + collect($analytics['grouped']['last'])->sum('value');
        @endphp
        <article class="an-card">
            <div class="an-card-head">
                <span class="an-card-kicker">{{ $analytics['grouped']['kicker'] ?? __('Comparative') }}</span>
                <h3>{{ $analytics['grouped']['title'] ?? __('Offenses This Month vs Last Month') }}</h3>
                <p class="an-card-copy">{{ __('Side-by-side comparison of offenses and disciplinary actions.') }}</p>
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
                <div class="an-group-key">
                    <span><i></i>{{ __('This month') }}</span>
                    <span><i class="last"></i>{{ __('Last month') }}</span>
                </div>
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

    // Studio Domain Tabs
    const studioTabs = [...root.querySelectorAll('[data-studio-tab]')];
    const studioPanels = [...root.querySelectorAll('[data-studio-panel]')];
    studioTabs.forEach((tab) => tab.addEventListener('click', () => {
        const targetIndex = tab.dataset.studioTab;
        studioTabs.forEach((t) => {
            const isMatch = t === tab;
            t.classList.toggle('active', isMatch);
            t.setAttribute('aria-selected', isMatch ? 'true' : 'false');
        });
        studioPanels.forEach((panel) => {
            panel.hidden = panel.dataset.studioPanel !== targetIndex;
            panel.classList.toggle('active', panel.dataset.studioPanel === targetIndex);
        });
        hideTooltip();
    }));

    // Studio Range Switcher (6M vs 12M)
    const rangeButtons = [...root.querySelectorAll('[data-studio-range]')];
    rangeButtons.forEach((btn) => btn.addEventListener('click', () => {
        const range = btn.dataset.studioRange;
        rangeButtons.forEach((b) => b.classList.toggle('active', b === btn));
        root.querySelectorAll('[data-studio-range-view]').forEach((view) => {
            const isMatch = view.dataset.studioRangeView === range;
            view.hidden = !isMatch;
            view.classList.toggle('active', isMatch);
        });
        hideTooltip();
    }));

    // Tooltip functionality
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
    };

    const hideTooltip = () => {
        tooltip.classList.remove('is-visible');
        activePoint = null;
    };

    root.addEventListener('pointerover', (event) => {
        const point = event.target.closest?.('[data-an-point][data-an-tooltip]');
        if (point) showTooltip(point, event.clientX, event.clientY);
    });
    root.addEventListener('pointermove', (event) => {
        const point = event.target.closest?.('[data-an-point][data-an-tooltip]');
        if (point) showTooltip(point, event.clientX, event.clientY);
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
