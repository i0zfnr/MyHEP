<style>
/* Program Management module — aligned with Document Context/Design.md. */
.pmr {
    --pm-accent: {{ $canUseAccent ? 'var(--se-primary, #C8A96A)' : '#C8A96A' }};
    --pm-accent-strong: {{ $canUseAccent ? 'var(--se-primary-strong, #8B6A34)' : '#8B6A34' }};
    --pm-success: #28686C;
    --pm-success-strong: #1F5559;
    --pm-card-radius: 8px;
    gap: 16px !important;
    width: min(100%, 1360px);
    padding: 20px 16px 32px !important;
}

.pmr-hero {
    position: relative;
    overflow: hidden;
    min-height: 132px;
    padding: 24px 28px !important;
    border: 1px solid color-mix(in srgb, var(--pm-accent) 24%, var(--border, #e5d8c8)) !important;
    border-radius: var(--pm-card-radius) !important;
    background:
        linear-gradient(180deg, color-mix(in srgb, white 48%, transparent), transparent 1px),
        linear-gradient(135deg, color-mix(in srgb, var(--surface, #fff) 88%, transparent), color-mix(in srgb, var(--pm-accent) 9%, var(--surface, #fff))) !important;
    box-shadow: 0 1px 2px rgba(45, 32, 18, .08), 0 10px 28px rgba(45, 32, 18, .07) !important;
    backdrop-filter: saturate(145%) blur(var(--glass-blur, 16px));
    -webkit-backdrop-filter: saturate(145%) blur(var(--glass-blur, 16px));
}
.pmr-hero::after {
    content: '';
    position: absolute;
    inset: auto -48px -72px auto;
    width: 210px;
    height: 150px;
    border-radius: 50%;
    background: color-mix(in srgb, var(--pm-accent) 11%, transparent);
    filter: blur(4px);
    pointer-events: none;
}
.pmr-hero > * { position: relative; z-index: 1; }

.pmr h1 {
    margin: 5px 0 6px !important;
    font-size: clamp(1.55rem, 2vw, 1.95rem) !important;
    line-height: 1.15;
    letter-spacing: -.035em;
}
.pmr-eyebrow {
    color: var(--pm-accent-strong) !important;
    font-size: .67rem !important;
    letter-spacing: .105em !important;
}
.pmr p { max-width: 74ch; line-height: 1.55; }

.pmr-card,
.pmr-kpi {
    border: 1px solid var(--border, #e5d8c8) !important;
    border-radius: var(--pm-card-radius) !important;
    background: var(--surface, #fff) !important;
    box-shadow: 0 1px 2px rgba(45, 32, 18, .06), 0 6px 18px rgba(45, 32, 18, .045) !important;
    backdrop-filter: none !important;
}
.pmr-card { padding: 20px !important; }
.pmr-card h2,
.pmr-card h3 {
    margin: 0 0 5px !important;
    font-size: 1.08rem !important;
    line-height: 1.3;
    letter-spacing: -.015em;
}

.pmr-btn {
    min-width: 44px;
    min-height: 44px;
    border-radius: 7px !important;
    padding: 9px 14px !important;
    transition: color var(--motion-fast, 160ms) ease, background var(--motion-fast, 160ms) ease, border-color var(--motion-fast, 160ms) ease, box-shadow var(--motion-fast, 160ms) ease, transform var(--motion-fast, 160ms) cubic-bezier(.2,.8,.2,1) !important;
}
.pmr-btn:focus-visible,
.pmr input:focus-visible,
.pmr textarea:focus-visible,
.pmr select:focus-visible,
.pmr-tab-link:focus-visible,
.pmr-method:focus-within {
    outline: 3px solid color-mix(in srgb, var(--pm-accent) 32%, transparent) !important;
    outline-offset: 2px;
    border-color: var(--pm-accent) !important;
}
.pmr-btn.primary {
    background: var(--pm-accent-strong) !important;
    border-color: var(--pm-accent-strong) !important;
    color: #fff !important;
    box-shadow: 0 5px 14px color-mix(in srgb, var(--pm-accent-strong) 22%, transparent) !important;
}
@media (hover: hover) and (pointer: fine) {
    .pmr-btn:hover { border-color: color-mix(in srgb, var(--pm-accent) 55%, var(--border)); }
    .pmr-btn.primary:hover { transform: translateY(-1px); background: color-mix(in srgb, var(--pm-accent-strong) 88%, #000) !important; }
}
.pmr-btn:active { transform: translateY(1px) !important; }

.pmr-kpis { gap: 10px !important; }
.pmr-kpi { min-height: 104px; padding: 16px !important; }
.pmr-kpi::after { display: none !important; }
.pmr-kpi span { font-size: .66rem !important; letter-spacing: .055em !important; }
.pmr-kpi strong { margin-top: 8px !important; font-size: 1.55rem !important; line-height: 1; }

.pmr-filter-card { padding: 14px !important; }
.pmr-filter-form { gap: 8px !important; }
.pmr-filter-form input,
.pmr-filter-form select,
.pmr-field input,
.pmr-field textarea,
.pmr-field select,
.pmr-q-item input,
.pmr-q-item select,
.pmr input,
.pmr textarea,
.pmr select {
    border-radius: 6px !important;
    border-color: var(--border, #e5d8c8) !important;
    background: var(--surface, #fff);
    color: var(--text, #241d16);
}
.pmr-quick-tabs { padding: 0; gap: 10px !important; }
.pmr-tab-link { min-height: 38px; align-items: center; border-radius: 6px !important; padding: 7px 11px !important; }
.pmr-tab-link.active { background: color-mix(in srgb, var(--pm-accent) 18%, var(--surface, #fff)) !important; color: var(--pm-accent-strong) !important; }

.pmr-card-body { padding: 0 !important; overflow: hidden; }
.pmr-table th,
.pmr-table td { padding: 12px 14px !important; }
.pmr-table th { background: color-mix(in srgb, var(--pm-accent) 5%, var(--surface, #fff)) !important; }
.pmr-table tbody tr { transition: background var(--motion-fast, 160ms) ease; }
@media (hover: hover) and (pointer: fine) {
    .pmr-table tbody tr:hover { background: color-mix(in srgb, var(--pm-accent) 4%, var(--surface, #fff)); }
}

.pmr-badge {
    border-radius: 999px !important;
    padding: 4px 8px !important;
    letter-spacing: .025em;
}
.pmr-badge.active,
.pmr-badge.archived,
.pmr-badge.completed { background: color-mix(in srgb, var(--pm-success) 13%, var(--surface, #fff)) !important; color: var(--pm-success-strong) !important; }
.pmr-tone-success { color: var(--pm-success-strong) !important; }
.pmr-tone-accent { color: var(--pm-accent-strong) !important; }
.pmr-tone-muted { color: var(--text-muted, #746b62) !important; }
.pmr-action-danger { color: var(--se-danger, #b42318) !important; border-color: color-mix(in srgb, var(--se-danger, #b42318) 42%, var(--border)) !important; background: color-mix(in srgb, var(--se-danger, #b42318) 5%, var(--surface, #fff)) !important; }
.pmr-control-panel { padding: 14px 0; margin: 12px 0 16px; border-block: 1px solid var(--border, #e5d8c8); }
.pmr-published { margin-top: 10px; padding: 10px 12px; border-left: 3px solid var(--pm-success); border-radius: 0 6px 6px 0; background: color-mix(in srgb, var(--pm-success) 8%, var(--surface, #fff)); color: var(--pm-success-strong); font-size: .84rem; font-weight: 800; }
.pmr-qr-image { display: inline-block; padding: 12px; border: 1px solid var(--border, #e5d8c8); border-radius: 7px; background: white; box-shadow: 0 4px 12px rgba(45,32,18,.07); }
.pmr-remove { min-width: 44px; min-height: 44px; border: 0; background: transparent; color: var(--se-danger, #b42318); font: inherit; font-weight: 800; cursor: pointer; }

.pmr-methods { gap: 8px !important; }
.pmr-methods { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; }
.pmr-method {
    min-height: 116px;
    border-radius: 7px !important;
    padding: 14px !important;
    box-shadow: none !important;
}
.pmr-method:has(input:checked) { background: color-mix(in srgb, var(--pm-accent) 8%, var(--surface, #fff)) !important; box-shadow: inset 3px 0 var(--pm-accent) !important; }
.pmr-grid { gap: 14px !important; }
.pmr-field { gap: 5px !important; }
.pmr-field textarea { min-height: 96px !important; }
.pmr-actions-row { position: sticky; bottom: 12px; z-index: 5; width: fit-content; margin-left: auto !important; padding: 6px; border: 1px solid color-mix(in srgb, var(--pm-accent) 22%, var(--border)); border-radius: 8px; background: color-mix(in srgb, var(--surface, #fff) 84%, transparent); box-shadow: 0 8px 24px rgba(45,32,18,.11); backdrop-filter: blur(14px) saturate(140%); }

/* Inner data is content, not another decorative card. */
.pmr-data {
    padding: 13px 0 !important;
    border: 0 !important;
    border-bottom: 1px solid var(--border, #e5d8c8) !important;
    border-radius: 0 !important;
    background: transparent !important;
}
.pmr-grid-3 .pmr-data:nth-last-child(-n+3) { border-bottom-color: transparent !important; }
.pmr-data strong { font-size: .9rem !important; overflow-wrap: anywhere; }
.pmr-version { padding: 14px 0 !important; }

.pmr-grid-2 { gap: 16px !important; align-items: start; }
.pmr-q-item { border: 0 !important; border-top: 1px solid var(--border, #e5d8c8) !important; border-radius: 0 !important; padding: 14px 0 0 !important; background: transparent !important; }
.pmr-qr-box { border-radius: 7px !important; padding: 18px !important; background: color-mix(in srgb, var(--pm-accent) 5%, var(--surface, #fff)) !important; }
#programReport > .pmr-grid-2 { grid-template-columns: repeat(4, minmax(0, 1fr)) !important; gap: 0 !important; margin: 14px 0 !important; border-block: 1px solid var(--border, #e5d8c8); }
#programReport > .pmr-grid-2 .pmr-data { padding: 12px !important; border-bottom: 0 !important; border-right: 1px solid var(--border, #e5d8c8) !important; }
#programReport > .pmr-grid-2 .pmr-data:last-child { border-right: 0 !important; }
#programReport textarea[name="content"] { min-height: 420px; font-family: inherit; line-height: 1.65 !important; }

body[data-theme="dark"] .pmr-hero {
    background:
        linear-gradient(180deg, color-mix(in srgb, white 10%, transparent), transparent 1px),
        linear-gradient(135deg, color-mix(in srgb, var(--surface, #17130f) 91%, transparent), color-mix(in srgb, var(--pm-accent) 8%, var(--surface, #17130f))) !important;
}
body[data-theme="dark"] .pmr-btn.primary { color: #17130f !important; background: var(--pm-accent) !important; border-color: var(--pm-accent) !important; }
body[data-theme="dark"] .pmr-tone-success { color: #9bd4d7 !important; }
body[data-theme="dark"] .pmr-badge.active,
body[data-theme="dark"] .pmr-badge.archived,
body[data-theme="dark"] .pmr-badge.completed { background: color-mix(in srgb, var(--pm-success) 34%, transparent) !important; color: #9bd4d7 !important; }

@media (max-width: 1050px) {
    .pmr-kpis { grid-template-columns: repeat(3, minmax(0, 1fr)) !important; }
    #programReport > .pmr-grid-2 { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; }
    #programReport > .pmr-grid-2 .pmr-data:nth-child(2) { border-right: 0 !important; }
}
@media (max-width: 760px) {
    .pmr { padding: 12px 10px 88px !important; gap: 12px !important; }
    .pmr-hero { min-height: 0; padding: 18px !important; gap: 14px !important; }
    .pmr-hero .pmr-actions { width: 100%; }
    .pmr-hero .pmr-actions .pmr-btn { flex: 1 1 auto; }
    .pmr-card { padding: 16px !important; }
    .pmr-kpis { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; }
    .pmr-kpi { min-height: 92px; }
    .pmr-filter-form input { min-width: 0 !important; width: 100%; }
    .pmr-quick-tabs { width: 100%; }
    .pmr-card-body { overflow: visible; background: transparent !important; border: 0 !important; box-shadow: none !important; }
    .pmr-table, .pmr-table tbody, .pmr-table tr, .pmr-table td { display: block; width: 100%; }
    .pmr-table thead { display: none; }
    .pmr-table tr { margin-bottom: 10px; padding: 12px; border: 1px solid var(--border, #e5d8c8); border-radius: 8px; background: var(--surface, #fff); }
    .pmr-table td { padding: 5px 0 !important; border: 0 !important; text-align: left !important; }
    .pmr-table td:last-child { margin-top: 7px; }
    .pmr-table td:last-child .pmr-btn { width: 100%; }
    .pmr-methods { grid-template-columns: 1fr !important; }
    .pmr-method { min-height: 0; }
    .pmr-grid-3 { grid-template-columns: 1fr !important; }
    .pmr-grid-3 .pmr-data { border-bottom-color: var(--border, #e5d8c8) !important; }
    #programReport > .pmr-grid-2 { grid-template-columns: 1fr !important; }
    #programReport > .pmr-grid-2 .pmr-data { border-right: 0 !important; border-bottom: 1px solid var(--border, #e5d8c8) !important; }
    #programReport > .pmr-grid-2 .pmr-data:last-child { border-bottom: 0 !important; }
    #programReport textarea[name="content"] { min-height: 340px; }
}
@media (max-width: 420px) {
    .pmr-kpis { grid-template-columns: 1fr 1fr !important; }
    .pmr-actions-row { width: 100%; bottom: 8px; }
    .pmr-actions-row .pmr-btn { flex: 1; }
}
@media (prefers-reduced-transparency: reduce) {
    .pmr-hero, .pmr-actions-row { backdrop-filter: none !important; -webkit-backdrop-filter: none !important; background: var(--surface, #fff) !important; }
}
@media (prefers-reduced-motion: reduce) {
    .pmr *, .pmr *::before, .pmr *::after { scroll-behavior: auto !important; transition-duration: .01ms !important; animation-duration: .01ms !important; }
}
@media (prefers-contrast: more) {
    .pmr-hero, .pmr-card, .pmr-kpi, .pmr-btn, .pmr input, .pmr textarea, .pmr select { border-color: currentColor !important; }
}

/* Match the established Admin Profile surface and action language. */
.pmr {
    width: 100% !important;
    max-width: none !important;
    padding: 28px 26px 40px !important;
    gap: 16px !important;
}
.pmr-hero,
.pmr-card,
.pmr-kpi {
    border: 1px solid var(--se-border, var(--border, #e5d8c8)) !important;
    border-radius: 14px !important;
    background: var(--se-surface, var(--surface, #fff)) !important;
    box-shadow: var(--se-shadow-md, 0 12px 30px rgba(45,32,18,.08)) !important;
}
.pmr-hero {
    padding: 22px 24px !important;
    min-height: 0;
    background: var(--se-surface, var(--surface, #fff)) !important;
    backdrop-filter: none;
    -webkit-backdrop-filter: none;
}
.pmr-hero::after { display: none; }
.pmr-card { overflow: hidden; padding: 20px !important; }
.pmr-card > h2:first-child,
.pmr-card > .pmr-eyebrow:first-child {
    display: block;
    margin: -20px -20px 16px !important;
    padding: 15px 18px !important;
    border-bottom: 1px solid var(--se-border, var(--border, #e5d8c8));
    background: var(--se-surface-soft, color-mix(in srgb, var(--pm-accent) 4%, var(--surface, #fff)));
    color: var(--se-text, var(--text, #241d16)) !important;
    font-size: 1rem !important;
    font-weight: 700 !important;
    letter-spacing: 0 !important;
    text-transform: none !important;
}
.pmr-card > .pmr-eyebrow:first-child + h2 { margin-top: 0 !important; }
.pmr-kpi {
    min-height: 106px;
    padding: 16px 18px !important;
    background: var(--se-surface-soft, var(--surface, #fff)) !important;
}
.pmr-data,
.pmr-method {
    border: 1px solid var(--se-border, var(--border, #e5d8c8)) !important;
    border-radius: 10px !important;
    background: var(--se-surface-soft, var(--surface, #fff)) !important;
    padding: 14px !important;
}
.pmr-grid-3 .pmr-data:nth-last-child(-n+3) { border-bottom-color: var(--se-border, var(--border, #e5d8c8)) !important; }
.pmr-method:has(input:checked) {
    border-color: var(--pm-accent) !important;
    background: color-mix(in srgb, var(--pm-accent) 9%, var(--se-surface, #fff)) !important;
    box-shadow: 0 0 0 2px color-mix(in srgb, var(--pm-accent) 20%, transparent) !important;
}
.pmr-field input,
.pmr-field textarea,
.pmr-field select,
.pmr-filter-form input,
.pmr-filter-form select,
.pmr-q-item input,
.pmr-q-item select,
.pmr input,
.pmr textarea,
.pmr select {
    border-radius: 9px !important;
    border-color: var(--se-border, var(--border, #e5d8c8)) !important;
}
.pmr-btn {
    min-height: 44px;
    border: 1px solid color-mix(in srgb, var(--pm-accent) 46%, var(--se-border, var(--border, #e5d8c8))) !important;
    border-radius: 9px !important;
    padding: .62rem .9rem !important;
    background: color-mix(in srgb, var(--pm-accent) 6%, var(--se-surface, var(--surface, #fff))) !important;
    color: var(--pm-accent-strong) !important;
    font-size: .82rem !important;
    font-weight: 700 !important;
    box-shadow: none !important;
}
.pmr-btn:hover {
    background: color-mix(in srgb, var(--pm-accent) 14%, var(--se-surface, var(--surface, #fff))) !important;
    border-color: var(--pm-accent) !important;
    color: var(--pm-accent-strong) !important;
    box-shadow: 0 8px 16px color-mix(in srgb, var(--pm-accent) 18%, transparent) !important;
}
.pmr-btn.primary {
    border-color: var(--pm-accent-strong) !important;
    background: linear-gradient(135deg, var(--pm-accent-strong), var(--pm-accent)) !important;
    color: #fff !important;
    box-shadow: 0 10px 18px color-mix(in srgb, var(--pm-accent-strong) 22%, transparent) !important;
}
.pmr-btn.primary:hover {
    background: linear-gradient(135deg, color-mix(in srgb, var(--pm-accent-strong) 88%, #000), var(--pm-accent-strong)) !important;
    border-color: var(--pm-accent-strong) !important;
    color: #fff !important;
}
.pmr-actions-row {
    position: static;
    width: auto;
    margin-top: 4px !important;
    padding: 0;
    border: 0;
    border-radius: 0;
    background: transparent;
    box-shadow: none;
    backdrop-filter: none;
}
.pmr-filter-card { padding: 16px 18px !important; }
.pmr-card-body { padding: 0 !important; }
.pmr-q-item { border-radius: 10px !important; border: 1px solid var(--se-border, var(--border, #e5d8c8)) !important; padding: 14px !important; background: var(--se-surface-soft, var(--surface, #fff)) !important; }
.pmr-qr-box { border-radius: 10px !important; }
#programReport > .pmr-grid-2 { border-radius: 10px; overflow: hidden; border: 1px solid var(--se-border, var(--border, #e5d8c8)); }
#programReport > .pmr-grid-2 .pmr-data { border: 0 !important; border-right: 1px solid var(--se-border, var(--border, #e5d8c8)) !important; border-radius: 0 !important; }
.pmr-report-flow {
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    margin: 16px 0 0;
    overflow: hidden;
    border: 1px solid var(--se-border, var(--border, #e5d8c8));
    border-radius: 10px;
    background: var(--se-surface-soft, var(--surface, #fff));
}
.pmr-report-step {
    position: relative;
    min-width: 0;
    padding: 13px 14px;
    border-right: 1px solid var(--se-border, var(--border, #e5d8c8));
}
.pmr-report-step:last-child { border-right: 0; }
.pmr-report-step span,
.pmr-report-step strong { display: block; overflow-wrap: anywhere; }
.pmr-report-step span { color: var(--se-text-muted, var(--text-muted, #746b62)); font-size: .67rem; font-weight: 800; letter-spacing: .045em; text-transform: uppercase; }
.pmr-report-step strong { margin-top: 5px; color: var(--se-text, var(--text, #241d16)); font-size: .82rem; }
.pmr-report-step.is-complete { background: color-mix(in srgb, var(--pm-success) 8%, var(--se-surface, #fff)); }
.pmr-report-step.is-complete strong { color: var(--pm-success-strong); }
.pmr-report-step.is-current { background: color-mix(in srgb, var(--pm-accent) 14%, var(--se-surface, #fff)); box-shadow: inset 0 3px var(--pm-accent); }
.pmr-report-step.is-current strong { color: var(--pm-accent-strong); }

body[data-theme="dark"] .pmr-btn {
    border-color: var(--se-border, #4d4034) !important;
    background: var(--se-surface-soft, #211b16) !important;
    color: var(--se-text, #f4ede5) !important;
}
body[data-theme="dark"] .pmr-btn:hover {
    background: color-mix(in srgb, var(--pm-accent) 18%, var(--se-surface-soft, #211b16)) !important;
    border-color: var(--pm-accent) !important;
    color: var(--pm-accent-strong) !important;
}
body[data-theme="dark"] .pmr-btn.primary {
    border-color: var(--pm-accent) !important;
    background: linear-gradient(135deg, var(--pm-accent-strong), var(--pm-accent)) !important;
    color: #17130f !important;
}

@media (max-width: 760px) {
    .pmr { padding: 14px 10px 88px !important; }
    .pmr-hero, .pmr-card, .pmr-kpi { border-radius: 12px !important; }
    .pmr-card > h2:first-child,
    .pmr-card > .pmr-eyebrow:first-child { margin-inline: -16px !important; }
    .pmr-data { border-radius: 10px !important; }
    .pmr-report-flow { grid-template-columns: 1fr; }
    .pmr-report-step { border-right: 0; border-bottom: 1px solid var(--se-border, var(--border, #e5d8c8)); }
    .pmr-report-step:last-child { border-bottom: 0; }
}
</style>
