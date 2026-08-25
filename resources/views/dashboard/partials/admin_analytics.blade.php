

<style>
/* Unified analytics system */
/* Warm monitoring visual language shared with System Performance. */
.an-card { background:var(--se-surface); border-color: color-mix(in srgb,var(--se-primary) 30%,var(--se-border)); }
@media (prefers-reduced-motion: reduce) { .an-card, .an-group-bar { animation:none!important; } }
</style>

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
                {{-- 6-Month Pillar Volume View --}}
                <div class="an-studio-range-view active" data-studio-range-view="6">
                    <div class="an-pillar-chart">
                        <div class="an-pillar-stage">
                            <div class="an-pillar-gridlines">
                                <span class="an-pillar-gl"></span>
                                <span class="an-pillar-gl"></span>
                                <span class="an-pillar-gl"></span>
                                <span class="an-pillar-gl"></span>
                            </div>
                            @php $sixMax = max(1, ...$sixValues); @endphp
                            @foreach ($sixValues as $idx => $val)
                                @php
                                    $hPercent = $val > 0 ? max(8, round(($val / $sixMax) * 100)) : 0;
                                    $isLast = $idx === count($sixValues) - 1;
                                @endphp
                                <div class="an-pillar-col {{ $isLast ? 'is-active' : '' }}" data-an-point data-an-tooltip="{{ $sixLabels[$idx] ?? ($idx + 1) }}|{{ number_format($val) }} {{ Str::lower($trend['kicker']) }}">
                                    @if ($val > 0)
                                        <span class="an-pillar-val-badge">{{ number_format($val) }}</span>
                                    @endif
                                    <div class="an-pillar-track">
                                        <div class="an-pillar-fill {{ $val === 0 ? 'is-zero' : '' }}" style="height: {{ $val > 0 ? $hPercent : 2 }}%;"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="an-pillar-labels">
                            @foreach ($sixLabels as $label)
                                <span class="an-pillar-label">{{ $label }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- 12-Month Pillar Volume View --}}
                <div class="an-studio-range-view" data-studio-range-view="12" hidden>
                    <div class="an-pillar-chart">
                        <div class="an-pillar-stage">
                            <div class="an-pillar-gridlines">
                                <span class="an-pillar-gl"></span>
                                <span class="an-pillar-gl"></span>
                                <span class="an-pillar-gl"></span>
                                <span class="an-pillar-gl"></span>
                            </div>
                            @php $twelveMax = max(1, ...$twelveValues); @endphp
                            @foreach ($twelveValues as $idx => $val)
                                @php
                                    $hPercent = $val > 0 ? max(8, round(($val / $twelveMax) * 100)) : 0;
                                    $isLast = $idx === count($twelveValues) - 1;
                                @endphp
                                <div class="an-pillar-col {{ $isLast ? 'is-active' : '' }}" data-an-point data-an-tooltip="{{ $twelveLabels[$idx] ?? ($idx + 1) }}|{{ number_format($val) }} {{ Str::lower($trend['kicker']) }}">
                                    @if ($val > 0)
                                        <span class="an-pillar-val-badge">{{ number_format($val) }}</span>
                                    @endif
                                    <div class="an-pillar-track">
                                        <div class="an-pillar-fill theme-blue {{ $val === 0 ? 'is-zero' : '' }}" style="height: {{ $val > 0 ? $hPercent : 2 }}%;"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="an-pillar-labels">
                            @foreach ($twelveLabels as $label)
                                <span class="an-pillar-label">{{ $label }}</span>
                            @endforeach
                        </div>
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
