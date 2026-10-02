@php
    $isScholarshipModule = ($staffModuleDashboard['module'] ?? null) === 'scholarship';
    $moduleName = $isScholarshipModule ? __('Scholarship') : __('Discipline');
    $overviewTitle = $isScholarshipModule ? __('Scholarship Overview') : __('Discipline Overview');
    $activityTitle = $isScholarshipModule ? __('Scholarship Record Activity') : __('Discipline Case Activity');
    $activityDescription = $isScholarshipModule
        ? __('New scholarship records created each month.')
        : __('New discipline cases recorded each month.');
    $trend = $staffModuleDashboard['trend'] ?? [];
    $statuses = $staffModuleDashboard['statuses'] ?? [];
    $trendMax = max(1, ...array_column($trend, 'value'));
    $statusMax = max(1, ...array_column($statuses, 'value'));
    $sixMonthTotal = (int) array_sum(array_column($trend, 'value'));
@endphp

<section class="staff-module-overview {{ $isScholarshipModule ? 'is-scholarship' : 'is-discipline' }}" aria-labelledby="staffModuleOverviewTitle">
    <header class="smd-heading">
        <div>
            <span class="smd-eyebrow">{{ $moduleName }}</span>
            <h2 id="staffModuleOverviewTitle">{{ $overviewTitle }}</h2>
            <p>{{ $isScholarshipModule ? __('Overview of scholarship records, award statuses, and recent activity.') : __('Overview of student discipline cases, case statuses, and recent activity.') }}</p>
        </div>
    </header>

    <div class="smd-metrics">
        <article class="smd-metric">
            <span>{{ $isScholarshipModule ? __('Total Scholarship Records') : __('Total Discipline Cases') }}</span>
            <strong>{{ number_format($staffModuleDashboard['total'] ?? 0) }}</strong>
        </article>
        <article class="smd-metric">
            <span>{{ __('Records in the last six months') }}</span>
            <strong>{{ number_format($sixMonthTotal) }}</strong>
        </article>
        <article class="smd-metric">
            <span>{{ __('Statuses tracked') }}</span>
            <strong>{{ count($statuses) }}</strong>
        </article>
    </div>

    <div class="smd-chart-grid">
        <article class="smd-card smd-activity-card">
            <div class="smd-card-head">
                <div>
                    <span class="smd-card-kicker">{{ __('Last six months') }}</span>
                    <h3>{{ $activityTitle }}</h3>
                    <p>{{ $activityDescription }}</p>
                </div>
                <strong class="smd-total-badge">{{ number_format($sixMonthTotal) }}</strong>
            </div>

            @if($trend !== [])
                <div class="smd-trend-chart" role="list" aria-label="{{ $activityTitle }}">
                    @foreach($trend as $point)
                        @php $height = $point['value'] > 0 ? max(5, round(($point['value'] / $trendMax) * 100)) : 0; @endphp
                        <div class="smd-trend-column" role="listitem" aria-label="{{ $point['label'] }}: {{ number_format($point['value']) }}">
                            <span class="smd-trend-value">{{ number_format($point['value']) }}</span>
                            <div class="smd-trend-track">
                                <div class="smd-trend-bar" style="height: {{ $height }}%;"></div>
                            </div>
                            <span class="smd-trend-label">{{ $point['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="smd-empty">{{ __('No records available for this period.') }}</div>
            @endif
        </article>

        <article class="smd-card">
            <div class="smd-card-head">
                <div>
                    <span class="smd-card-kicker">{{ __('Current records') }}</span>
                    <h3>{{ __('Records by status') }}</h3>
                    <p>{{ __('Distribution of records across their current statuses.') }}</p>
                </div>
                <strong class="smd-total-badge">{{ number_format($staffModuleDashboard['total'] ?? 0) }}</strong>
            </div>

            @if($statuses !== [])
                <div class="smd-status-list">
                    @foreach($statuses as $status)
                        <div class="smd-status-row">
                            <div class="smd-status-meta">
                                <span>{{ __($status['label']) }}</span>
                                <strong>{{ number_format($status['value']) }}</strong>
                            </div>
                            <div class="smd-status-track" aria-hidden="true">
                                <span style="width: {{ round(($status['value'] / $statusMax) * 100, 1) }}%;"></span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="smd-empty">{{ __('No status records available yet.') }}</div>
            @endif
        </article>
    </div>
</section>

<style>
    .staff-module-overview {
        --smd-accent: #b88943;
        margin: 1.1rem 0 1.35rem;
    }
    .staff-module-overview.is-discipline { --smd-accent: #bd6a52; }
    .smd-heading { margin-bottom: .9rem; }
    .smd-eyebrow, .smd-card-kicker {
        color: var(--smd-accent);
        font-size: .7rem;
        font-weight: 750;
        letter-spacing: .1em;
        text-transform: uppercase;
    }
    .smd-heading h2 { margin: .22rem 0; color: var(--c-text-primary, #241c16); font-size: 1.3rem; }
    .smd-heading p, .smd-card-head p { margin: .25rem 0 0; color: var(--c-text-secondary, #766656); font-size: .84rem; line-height: 1.5; }
    .smd-metrics { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: .8rem; margin-bottom: .85rem; }
    .smd-metric, .smd-card {
        min-width: 0;
        border: 1px solid var(--se-border, #eadbc5);
        border-radius: 14px;
        background: var(--se-surface, #fff);
        box-shadow: 0 5px 16px rgba(60, 42, 23, .045);
    }
    .smd-metric { display: grid; gap: .3rem; padding: .9rem 1rem; border-top: 3px solid var(--smd-accent); }
    .smd-metric span { color: var(--c-text-secondary, #766656); font-size: .75rem; font-weight: 600; }
    .smd-metric strong { color: var(--c-text-primary, #241c16); font-size: 1.4rem; }
    .smd-chart-grid { display: grid; grid-template-columns: minmax(0, 1.35fr) minmax(270px, 1fr); gap: .85rem; }
    .smd-card { padding: 1rem 1.05rem; }
    .smd-card-head { display: flex; align-items: flex-start; justify-content: space-between; gap: .8rem; min-height: 64px; }
    .smd-card-head h3 { margin: .2rem 0 0; color: var(--c-text-primary, #241c16); font-size: .98rem; }
    .smd-card-head p { font-size: .76rem; }
    .smd-total-badge { flex: none; padding: .33rem .55rem; border-radius: 999px; color: var(--smd-accent); background: color-mix(in srgb, var(--smd-accent) 12%, white); font-size: .75rem; }
    .smd-trend-chart { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); align-items: end; gap: .55rem; min-height: 178px; padding: .75rem .1rem 0; }
    .smd-trend-column { display: flex; min-width: 0; height: 158px; flex-direction: column; align-items: center; justify-content: flex-end; gap: .35rem; }
    .smd-trend-value { color: var(--c-text-secondary, #766656); font-size: .68rem; font-weight: 650; }
    .smd-trend-track { display: flex; width: min(100%, 38px); height: 112px; align-items: flex-end; overflow: hidden; border-radius: 8px 8px 3px 3px; background: color-mix(in srgb, var(--smd-accent) 9%, white); }
    .smd-trend-bar { width: 100%; min-height: 2px; border-radius: 7px 7px 2px 2px; background: linear-gradient(180deg, color-mix(in srgb, var(--smd-accent) 68%, white), var(--smd-accent)); }
    .smd-trend-label { color: var(--c-text-secondary, #766656); font-size: .7rem; }
    .smd-status-list { display: grid; gap: .9rem; padding: 1rem 0 .2rem; }
    .smd-status-meta { display: flex; align-items: center; justify-content: space-between; gap: .75rem; margin-bottom: .38rem; color: var(--c-text-primary, #241c16); font-size: .8rem; }
    .smd-status-meta strong { font-size: .78rem; }
    .smd-status-track { height: 8px; overflow: hidden; border-radius: 999px; background: color-mix(in srgb, var(--smd-accent) 10%, white); }
    .smd-status-track span { display: block; height: 100%; border-radius: inherit; background: var(--smd-accent); }
    .smd-empty { display: grid; min-height: 130px; place-items: center; color: var(--c-text-secondary, #766656); font-size: .85rem; text-align: center; }
    @media (max-width: 760px) {
        .smd-metrics { grid-template-columns: 1fr; gap: .55rem; }
        .smd-chart-grid { grid-template-columns: 1fr; }
        .smd-metric { grid-template-columns: 1fr auto; align-items: center; }
        .smd-metric strong { font-size: 1.15rem; }
    }
</style>
