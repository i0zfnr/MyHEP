@push('styles')
<style>
    .analytics-dashboard { display:none; grid-template-columns:minmax(0,1fr); gap:18px; }
    .adash[data-dashboard-mode="graphs"] .analytics-dashboard { display:grid; }
    .adash[data-dashboard-mode="graphs"] .stats-grid { display:none !important; }

    .an-kpis { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:12px; perspective:1300px; }
    .an-kpi { position:relative; min-width:0; padding:16px 18px; border:1px solid var(--border); border-top:3px solid var(--kpi-accent,var(--primary)); border-radius:16px; background:linear-gradient(145deg,var(--surface),var(--surface-soft)); box-shadow:10px 13px 24px rgba(48,34,22,.11),inset 0 1px 0 rgba(255,255,255,.82); overflow:hidden; transform:rotateX(2deg) rotateY(-1deg); transform-style:preserve-3d; }
    .an-kpi::after { content:''; position:absolute; width:84px; height:84px; top:-46px; right:-38px; border-radius:50%; background:color-mix(in srgb,var(--kpi-accent,var(--primary)) 13%,transparent); pointer-events:none; }
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

    .an-grid { display:grid; gap:16px; }
    .an-grid-3 { grid-template-columns:repeat(3,minmax(0,1fr)); }
    .an-grid-2 { grid-template-columns:repeat(2,minmax(0,1fr)); }
    .an-card { min-width:0; border:1px solid var(--border); border-radius:18px; background:linear-gradient(145deg,var(--surface),var(--surface-soft)); box-shadow:14px 18px 34px rgba(48,34,22,.13),inset 0 1px 0 rgba(255,255,255,.84); overflow:hidden; }
    .an-card-head { padding:18px 20px 0; }
    .an-card-kicker { color:var(--primary-dark); font-size:.66rem; font-weight:850; letter-spacing:.13em; text-transform:uppercase; }
    .an-card h3 { margin:5px 0 0; color:var(--text); font-size:1rem; letter-spacing:-.018em; }
    .an-card-copy { margin:5px 0 0; color:var(--text-muted); font-size:.74rem; line-height:1.5; }
    .an-card-body { padding:18px 20px 20px; }
    .an-empty { min-height:150px; display:grid; place-items:center; padding:20px; text-align:center; }
    .an-empty-mark { width:42px; height:42px; margin:0 auto 10px; display:grid; place-items:center; border-radius:14px; background:var(--surface-soft); border:1px solid var(--border); color:var(--primary-dark); font-size:1.15rem; font-weight:850; }
    .an-empty strong { display:block; color:var(--text); font-size:.9rem; }
    .an-empty span { display:block; max-width:320px; margin:5px auto 0; color:var(--text-muted); font-size:.72rem; line-height:1.5; }
    .an-sr { position:absolute; width:1px; height:1px; padding:0; margin:-1px; overflow:hidden; clip:rect(0,0,0,0); white-space:nowrap; border:0; }

    .an-donut-layout { display:grid; grid-template-columns:minmax(140px,1fr) minmax(150px,.9fr); align-items:center; gap:18px; }
    .an-donut { width:150px; aspect-ratio:1; margin:auto; display:grid; place-items:center; border-radius:50%; background:var(--donut); position:relative; transform:rotateX(12deg) rotateZ(-2deg); transform-style:preserve-3d; box-shadow:0 20px 26px rgba(48,34,22,.18),inset 0 2px 0 rgba(255,255,255,.45); }
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
    .an-gauge { width:150px; aspect-ratio:1; display:grid; place-items:center; border-radius:50%; background:var(--gauge); position:relative; transform:rotateX(10deg); transform-style:preserve-3d; box-shadow:0 20px 26px rgba(48,34,22,.18),inset 0 2px 0 rgba(255,255,255,.45); }
    .an-gauge::after { content:''; position:absolute; inset:26px; border-radius:50%; background:var(--surface); box-shadow:inset 0 0 0 1px var(--border); }
    .an-gauge-centre { position:relative; z-index:1; text-align:center; }
    .an-gauge-centre strong { display:block; color:var(--text); font-size:1.5rem; letter-spacing:-.04em; }
    .an-gauge-centre span { display:block; margin-top:2px; color:var(--text-muted); font-size:.6rem; }
    .an-gauge-note { margin:0; color:var(--text-muted); font-size:.7rem; line-height:1.5; text-align:center; }

    .an-stack { display:grid; gap:12px; }
    .an-stack-row { display:grid; grid-template-columns:minmax(84px,auto) 1fr auto; align-items:center; gap:10px; color:var(--text-muted); font-size:.72rem; }
    .an-stack-track { height:16px; display:flex; overflow:hidden; border-radius:5px; background:var(--surface-soft); box-shadow:inset 0 1px 2px rgba(0,0,0,.08); }
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
    .an-hbar-track { height:10px; border-radius:5px; background:var(--surface-soft); overflow:hidden; box-shadow:inset 0 1px 2px rgba(0,0,0,.08); }
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
    .an-col-bar { width:min(22px,60%); min-height:3px; border-radius:4px 4px 2px 2px; background:linear-gradient(180deg,color-mix(in srgb,var(--primary) 70%,white),var(--primary)); box-shadow:0 8px 14px color-mix(in srgb,var(--primary) 22%,transparent); }
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
    .tone-blue { --kpi-accent:#5375c5; }
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

    @media (max-width:1100px) {
        .an-kpis { grid-template-columns:repeat(2,minmax(0,1fr)); }
        .an-grid-3, .an-grid-2 { grid-template-columns:1fr; }
    }
    @media (max-width:640px) {
        .an-kpis { grid-template-columns:1fr; }
        .an-donut-layout { grid-template-columns:1fr; }
        .an-donut { width:130px; }
        .an-hbar-row { grid-template-columns:1fr; gap:4px; }
        .an-group { grid-template-columns:1fr; gap:4px; }
        .an-stack-row { grid-template-columns:64px 1fr auto; }
        .an-kpi, .an-donut, .an-gauge { transform:none; }
    }
    @media (prefers-reduced-motion:reduce) { .an-kpi, .an-donut, .an-gauge { transform:none; } }
</style>
@endpush

@if (!empty($analytics['domains']))
@php
    $anTileColors = ['#3f8f69', '#c48628', '#5375c5', '#7258bd', '#c14f5c', '#64748b', '#2d7d8a', '#9a6a3f'];

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
@endphp
<section class="analytics-dashboard" data-dashboard-analytics aria-label="{{ __('Analytics dashboard') }}">
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
                <div class="an-donut-layout">
                    <div class="an-donut" style="--donut:{{ $anDonutGradient($donut['segments'], $donut['total']) }};" role="img" aria-label="{{ $donut['title'] }}. Total {{ $donut['total'] }}">
                        <div class="an-donut-centre"><strong>{{ number_format($donut['total']) }}</strong><span>{{ __('Total') }}</span></div>
                    </div>
                    <div class="an-legend">
                        @foreach ($donut['segments'] as $segment)
                        <div class="an-legend-row" style="--legend-color:{{ $segment['color'] }}">
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
        <div class="an-grid an-grid-3">
            <article class="an-card">
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
                    <svg class="an-trend-svg" viewBox="0 0 100 44" preserveAspectRatio="none" role="img" aria-label="{{ $trend['title'] }} last six months: {{ implode(', ', $trend['six']['values']) }}">
                        <line class="an-gridline" x1="0" y1="11" x2="100" y2="11"/>
                        <line class="an-gridline" x1="0" y1="22" x2="100" y2="22"/>
                        <line class="an-gridline" x1="0" y1="33" x2="100" y2="33"/>
                        <polyline class="an-trend-line" points="{{ $anPointsToLine($linePoints) }}"/>
                        @foreach ($linePoints as $point)
                        <circle class="an-trend-dot" cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="2.4"/>
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
                    <svg class="an-trend-svg" viewBox="0 0 100 44" preserveAspectRatio="none" role="img" aria-label="Cumulative {{ $trend['title'] }}: {{ implode(', ', $trend['area']) }}">
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
                        <polyline class="an-trend-line" points="{{ $anPointsToLine($areaPoints) }}"/>
                        @foreach ($areaPoints as $point)
                        <circle class="an-trend-dot" cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="2.4"/>
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
@endif