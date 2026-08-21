@push('styles')
<style>
    .analytics-dashboard {
        display: none;
        grid-template-columns: minmax(0, 1fr);
        gap: 20px;
        transform: translateZ(0);
    }
    .adash[data-dashboard-mode="graphs"] .analytics-dashboard { display: grid; }
    .adash[data-dashboard-mode="graphs"] .stats-grid { display: none !important; }

    /* Overview Header & Period Switcher */
    .an-overview-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 4px 0;
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
        margin: 2px 0 0;
        font-size: 0.82rem;
        color: var(--c-text-secondary, #7f7165);
    }
    .an-range {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px;
        border-radius: 12px;
        background: var(--c-surface, #ffffff);
        border: 1px solid var(--c-border, #eadfd2);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    }
    body[data-theme="dark"] .an-range {
        background: #171310;
        border-color: rgba(226, 209, 192, 0.14);
    }
    .an-range button {
        min-height: 32px;
        padding: 0 14px;
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
    .an-range button.active {
        background: linear-gradient(135deg, #f3d49b 0%, #c48e42 100%);
        color: #17120c;
        box-shadow: 0 2px 8px rgba(196, 142, 66, 0.24);
    }

    /* KPI Cards */
    .an-kpis {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
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
    .an-kpi-heading {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .an-kpi-icon {
        width: 32px;
        height: 32px;
        border-radius: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(196, 142, 66, 0.12);
        color: #c48e42;
    }
    .an-kpi-label {
        color: var(--c-text-secondary, #7f7165);
        font-size: 0.74rem;
        font-weight: 750;
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }
    .an-kpi-value {
        display: block;
        margin-top: 8px;
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

    /* Graph Cards */
    .an-grid { display: grid; gap: 16px; }
    .an-grid-3 { grid-template-columns: repeat(auto-fit, minmax(min(320px, 100%), 1fr)); }
    .an-grid-2 { grid-template-columns: repeat(auto-fit, minmax(min(450px, 100%), 1fr)); }
    .an-card {
        min-width: 0;
        border: 1px solid var(--c-border, #eadfd2);
        border-radius: 20px;
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

    /* Donut & Pipeline */
    .an-donut-layout {
        display: grid;
        grid-template-columns: minmax(140px, 1fr) minmax(160px, 1fr);
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
        inset: 26px;
        border-radius: 50%;
        background: var(--c-surface, #ffffff);
        box-shadow: inset 0 0 0 1px var(--c-border, #eadfd2);
    }
    body[data-theme="dark"] .an-donut::after {
        background: #171310;
        box-shadow: inset 0 0 0 1px rgba(226, 209, 192, 0.14);
    }
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

    /* Bars & Columns */
    .an-columns {
        display: flex;
        align-items: flex-end;
        gap: 6px;
        height: 180px;
        padding: 8px 2px 0;
        border-bottom: 1px solid var(--c-border, #eadfd2);
    }
    body[data-theme="dark"] .an-columns { border-color: rgba(226, 209, 192, 0.14); }
    .an-col {
        flex: 1;
        min-width: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 4px;
        height: 100%;
    }
    .an-col-stage {
        width: 100%;
        height: 145px;
        display: flex;
        align-items: flex-end;
        justify-content: center;
    }
    .an-col-bar {
        width: min(22px, 65%);
        min-height: 3px;
        border-radius: 5px 5px 1px 1px;
        background: linear-gradient(180deg, #60a5fa 0%, #3b82f6 100%);
        box-shadow: 0 2px 6px rgba(59, 130, 246, 0.22);
    }
    .an-col-label {
        color: var(--c-text-secondary, #7f7165);
        font-size: 0.66rem;
        font-weight: 700;
    }

    /* SVG Trends */
    .an-trend-svg { width: 100%; height: 175px; display: block; }
    .an-trend-svg .an-gridline { stroke: rgba(0, 0, 0, 0.06); stroke-width: 1; }
    body[data-theme="dark"] .an-trend-svg .an-gridline { stroke: rgba(255, 255, 255, 0.06); }
    .an-trend-line {
        fill: none;
        stroke: var(--c-accent, #c48e42);
        stroke-width: 2.6;
        stroke-linejoin: round;
        stroke-linecap: round;
    }
    .an-trend-area { fill: url(#anAreaGrad); }
    .an-trend-dot {
        fill: var(--c-surface, #ffffff);
        stroke: var(--c-accent, #c48e42);
        stroke-width: 2.4;
    }
    body[data-theme="dark"] .an-trend-dot { fill: #171310; }
    .an-trend-labels {
        display: flex;
        justify-content: space-between;
        margin-top: 6px;
        color: var(--c-text-secondary, #7f7165);
        font-size: 0.68rem;
        font-weight: 600;
    }

    /* Heatmap */
    .an-heat-shell { max-width: 100%; overflow-x: auto; padding: 4px 0 2px; }
    .an-heat {
        width: max-content;
        margin: 0 auto;
        display: grid;
        grid-template-columns: 36px repeat(8, 44px);
        grid-template-rows: 20px repeat(7, 24px);
        gap: 5px;
        align-items: center;
    }
    .an-heat-dow {
        color: var(--c-text-secondary, #7f7165);
        font-size: 0.68rem;
        font-weight: 800;
        text-align: center;
        text-transform: uppercase;
    }
    .an-heat-week {
        color: var(--c-text-secondary, #7f7165);
        font-size: 0.64rem;
        font-weight: 750;
        text-align: center;
    }
    .an-heat-cell {
        width: 24px;
        height: 24px;
        justify-self: center;
        border-radius: 6px;
        background: rgba(0, 0, 0, 0.05);
        border: 1px solid var(--c-border, #eadfd2);
        cursor: default;
    }
    body[data-theme="dark"] .an-heat-cell {
        background: rgba(255, 255, 255, 0.06);
        border-color: rgba(226, 209, 192, 0.10);
    }
    .an-heat-cell[data-lvl="1"] { background: rgba(196, 142, 66, 0.25); border-color: rgba(196, 142, 66, 0.35); }
    .an-heat-cell[data-lvl="2"] { background: rgba(196, 142, 66, 0.50); border-color: rgba(196, 142, 66, 0.60); }
    .an-heat-cell[data-lvl="3"] { background: rgba(196, 142, 66, 0.75); border-color: rgba(196, 142, 66, 0.85); }
    .an-heat-cell[data-lvl="4"] { background: #c48e42; border-color: #9a6d2c; }

    /* Palette Tones */
    .tone-slate { --kpi-accent: #64748b; }
    .tone-gold { --kpi-accent: #c48e42; }
    .tone-blue { --kpi-accent: #3b82f6; }
    .tone-green { --kpi-accent: #10b981; }
    .tone-violet { --kpi-accent: #8b5cf6; }
    .tone-red { --kpi-accent: #ef4444; }

    /* Unified analytics system */
    .analytics-dashboard :is(.an-card,.an-kpi,.an-featured) {
        background:var(--se-surface);
    }

    /* Warm monitoring visual language shared with System Performance. */
    .analytics-dashboard {
        border-color: color-mix(in srgb,var(--se-primary) 30%,var(--se-border));
    }
    @media (prefers-reduced-motion: reduce) {
        .analytics-dashboard > *, .an-daily-bar, .an-trend-line, .an-donut, .an-gauge {
            animation:none!important;
        }
    }
</style>
@endpush

@if (!empty($analytics['domains']))
@php
    $anTileColors = ['var(--se-primary)', 'var(--se-success)', 'var(--se-warning)', 'var(--se-info)', 'var(--se-danger)', '#8c8175', '#6f8d78', '#9a6a3f'];

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
    $anActivePoints = $anComputePoints($anActiveValues, 100, 15, 1);
    $anActiveSecondaryValues = $anFeaturedTrend['area'] ?? [];
    $anActiveSecondaryPoints = $anComputePoints($anActiveSecondaryValues, 100, 15, 1);
    $anActiveAreaPath = $anActiveSecondaryPoints === [] ? '' : $anPointsToSmoothPath($anActiveSecondaryPoints) . ' L' . $anActiveSecondaryPoints[count($anActiveSecondaryPoints) - 1]['x'] . ',15 L' . $anActiveSecondaryPoints[0]['x'] . ',15 Z';
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
                <span class="an-kpi-heading"><span class="an-kpi-icon" aria-hidden="true">
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
                </span><span class="an-kpi-label">{{ $kpi['label'] }}</span></span>
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
                    <svg class="an-active-svg" viewBox="0 0 100 15" preserveAspectRatio="none" role="img" aria-label="{{ __('Monthly and cumulative activity') }}: {{ implode(', ', $anActiveValues) }}">
                        <defs><linearGradient id="anActiveGradient" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="var(--se-primary)" stop-opacity=".20"/><stop offset="100%" stop-color="var(--se-primary)" stop-opacity="0"/></linearGradient></defs>
                        <line class="an-gridline" x1="0" y1="3.75" x2="100" y2="3.75"/><line class="an-gridline" x1="0" y1="7.5" x2="100" y2="7.5"/><line class="an-gridline" x1="0" y1="11.25" x2="100" y2="11.25"/>
                        <path class="an-active-area" d="{{ $anActiveAreaPath }}"/>
                        <line class="an-active-guide" data-an-guide x1="0" y1="0" x2="0" y2="15"/>
                        <path class="an-active-line-secondary" pathLength="1" d="{{ $anPointsToSmoothPath($anActiveSecondaryPoints) }}"/>
                        <path class="an-active-line" pathLength="1" d="{{ $anPointsToSmoothPath($anActivePoints) }}"/>
                        @foreach ($anActivePoints as $pointIndex => $point)
                        <circle class="an-active-node" data-an-point data-an-x="{{ $point['x'] }}" data-an-tooltip="{{ $anActiveLabels[$pointIndex] ?? ($pointIndex + 1) }}|Monthly: {{ number_format($anActiveValues[$pointIndex] ?? 0) }} · Cumulative: {{ number_format($anActiveSecondaryValues[$pointIndex] ?? 0) }}" cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r=".55"/>
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
            $linePoints = $anComputePoints($trend['six']['values'], 100, 25, 2);
            $areaPoints = $anComputePoints($trend['area'], 100, 25, 2);
            $twelveTotal = (int) array_sum($trend['twelve']['values']);
            $twelveMax = max(1, ...$trend['twelve']['values']);
            $heatLabels = array_column(array_slice($trend['heat']['cells'], 0, 7), 'weekday');
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
                    <svg class="an-trend-svg" data-an-chart viewBox="0 0 100 25" preserveAspectRatio="none" role="img" aria-label="{{ $trend['title'] }} last six months: {{ implode(', ', $trend['six']['values']) }}">
                        <line class="an-gridline" x1="0" y1="6.25" x2="100" y2="6.25"/>
                        <line class="an-gridline" x1="0" y1="12.5" x2="100" y2="12.5"/>
                        <line class="an-gridline" x1="0" y1="18.75" x2="100" y2="18.75"/>
                        <path class="an-trend-line" pathLength="1" d="{{ $anPointsToSmoothPath($linePoints) }}"/>
                        @foreach ($linePoints as $pointIndex => $point)
                        <circle class="an-trend-dot" data-an-point data-an-tooltip="{{ $trend['six']['labels'][$pointIndex] ?? ($pointIndex + 1) }}|{{ number_format($trend['six']['values'][$pointIndex] ?? 0) }} activity" cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r=".7"/>
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
                    <svg class="an-trend-svg" data-an-chart viewBox="0 0 100 25" preserveAspectRatio="none" role="img" aria-label="Cumulative {{ $trend['title'] }}: {{ implode(', ', $trend['area']) }}">
                        <defs>
                            <linearGradient id="anAreaGrad{{ $trendIndex }}" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="var(--primary)" stop-opacity="0.30"/>
                                <stop offset="100%" stop-color="var(--primary)" stop-opacity="0.03"/>
                            </linearGradient>
                        </defs>
                        <line class="an-gridline" x1="0" y1="6.25" x2="100" y2="6.25"/>
                        <line class="an-gridline" x1="0" y1="12.5" x2="100" y2="12.5"/>
                        <line class="an-gridline" x1="0" y1="18.75" x2="100" y2="18.75"/>
                        <path class="an-trend-area" d="{{ $anPointsToSmoothPath($areaPoints) }} L{{ $areaPoints[count($areaPoints)-1]['x'] }},25 L{{ $areaPoints[0]['x'] }},25 Z" style="fill:url(#anAreaGrad{{ $trendIndex }})"/>
                        <path class="an-trend-line" pathLength="1" d="{{ $anPointsToSmoothPath($areaPoints) }}"/>
                        @foreach ($areaPoints as $pointIndex => $point)
                        <circle class="an-trend-dot" data-an-point data-an-tooltip="{{ $trend['six']['labels'][$pointIndex] ?? ($pointIndex + 1) }}|{{ number_format($trend['area'][$pointIndex] ?? 0) }} cumulative" cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r=".7"/>
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

        <article class="an-card an-heat-card">
            <div class="an-card-head">
                <span class="an-card-kicker">{{ $trend['kicker'] }}</span>
                <h3>{{ __('Activity Heatmap') }} – 8 {{ __('Weeks') }}</h3>
                <p class="an-card-copy">{{ __('Peak activity days across the last eight weeks.') }}</p>
            </div>
            <div class="an-card-body">
                @if ($trend['heat']['total'] > 0)
                <div class="an-heat-shell">
                <div class="an-heat" data-an-chart role="img" aria-label="{{ $trend['title'] }} by day, {{ $trend['heat']['total'] }} total. {{ $trend['heat']['cells'][0]['date'] }} to {{ $trend['heat']['cells'][count($trend['heat']['cells']) - 1]['date'] }}">
                    <span aria-hidden="true"></span>
                    @foreach (range(0, 7) as $weekIndex)
                    <span class="an-heat-week">{{ $trend['heat']['cells'][$weekIndex * 7]['date'] }}</span>
                    @endforeach
                    @foreach ($heatLabels as $dayIndex => $heatLabel)
                    <span class="an-heat-dow">{{ $heatLabel }}</span>
                        @foreach (range(0, 7) as $weekIndex)
                            @php($heatCell = $trend['heat']['cells'][$weekIndex * 7 + $dayIndex])
                            <span class="an-heat-cell" data-an-point data-an-tooltip="{{ $heatCell['date'] }}|{{ number_format($heatCell['count']) }} {{ Str::lower($trend['kicker']) }}" data-lvl="{{ $heatCell['level'] }}" aria-label="{{ $heatCell['date'] }}: {{ $heatCell['count'] }} {{ Str::lower($trend['kicker']) }}"></span>
                        @endforeach
                    @endforeach
                </div>
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
        </div>
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
