@php
    $programCounts = $programDashboard['counts'] ?? [];
    $programStatuses = collect($programDashboard['status_distribution'] ?? []);
    $totalPrograms = (int) ($programCounts['total'] ?? 0);
    $totalStudents = (int) ($programCounts['total_students'] ?? 0);
    $pendingApprovals = (int) ($programCounts['pending_deputy'] ?? 0) + (int) ($programCounts['pending_director'] ?? 0);
    $reviewTasks = (int) ($programCounts['review_tasks'] ?? 0);
    $completedApproved = (int) ($programCounts['approved'] ?? 0) + (int) ($programCounts['completed'] ?? 0);

    $statusMeta = [
        'draft' => ['label' => __('Draft'), 'color' => '#94a3b8', 'badge' => 'slate'],
        'pending_deputy' => ['label' => __('Deputy Review'), 'color' => '#f59e0b', 'badge' => 'amber'],
        'pending_director' => ['label' => __('Director Approval'), 'color' => '#a855f7', 'badge' => 'purple'],
        'approved' => ['label' => __('Approved'), 'color' => '#10b981', 'badge' => 'emerald'],
        'in_progress' => ['label' => __('In Progress'), 'color' => '#3b82f6', 'badge' => 'blue'],
        'completed' => ['label' => __('Completed'), 'color' => '#c48e42', 'badge' => 'gold'],
        'rejected' => ['label' => __('Rejected'), 'color' => '#ef4444', 'badge' => 'rose'],
    ];

    // Compute SVG Donut Segments
    $circumference = 282.743; // 2 * pi * 45
    $accumulatedPercent = 0;
    $donutSegments = [];
    $totalForDonut = max(1, $totalPrograms);

    foreach ($programStatuses as $item) {
        $statusKey = $item['status'];
        $val = (int) $item['value'];
        $pct = ($val / $totalForDonut);
        $dashArray = ($pct * $circumference) . ' ' . $circumference;
        $dashOffset = -($accumulatedPercent * $circumference);
        $accumulatedPercent += $pct;

        $donutSegments[] = [
            'status' => $statusKey,
            'label' => $statusMeta[$statusKey]['label'] ?? ucfirst($statusKey),
            'color' => $statusMeta[$statusKey]['color'] ?? '#c48e42',
            'value' => $val,
            'percent' => round($pct * 100, 1),
            'dashArray' => $dashArray,
            'dashOffset' => $dashOffset,
        ];
    }
@endphp

<section class="staff-program-board" aria-labelledby="staffProgramTitle">
    <!-- Header -->
    <div class="program-board-head">
        <div>
            <div class="kicker">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5M9 11.25v1.5M12 9v3.75m3-6v6"/>
                </svg>
                <span>{{ __('Program Management') }}</span>
            </div>
            <h2 id="staffProgramTitle">{{ __('My Program Overview') }}</h2>
            <p>{{ __('Live progress for programs you direct and reviews assigned to you.') }}</p>
        </div>
        <a href="{{ route('admin.programs.index') }}" class="btn-primary-action">
            <span>{{ __('Open Program Management') }}</span>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
        </a>
    </div>

    <!-- 4 High-Impact Consolidated KPI Cards -->
    <div class="kpi-grid-4">
        <!-- Card 1: Total Students -->
        <article class="kpi-card tone-gold">
            <div class="kpi-top">
                <div class="kpi-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0z"/>
                    </svg>
                </div>
                <span class="kpi-pill">{{ __('Politeknik Besut') }}</span>
            </div>
            <div class="kpi-value">{{ number_format($totalStudents) }}</div>
            <div class="kpi-label">{{ __('Total Students') }}</div>
            <div class="kpi-subtext">{{ __('Registered in active database') }}</div>
        </article>

        <!-- Card 2: My Programs -->
        <article class="kpi-card tone-blue">
            <div class="kpi-top">
                <div class="kpi-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                    </svg>
                </div>
                <span class="kpi-pill">{{ $programCounts['draft'] ?? 0 }} {{ __('Drafts') }}</span>
            </div>
            <div class="kpi-value">{{ number_format($totalPrograms) }}</div>
            <div class="kpi-label">{{ __('My Programs') }}</div>
            <div class="kpi-subtext">{{ __('Organized by your department') }}</div>
        </article>

        <!-- Card 3: In Approval Pipeline -->
        <article class="kpi-card {{ $pendingApprovals > 0 || $reviewTasks > 0 ? 'tone-amber' : 'tone-slate' }}">
            <div class="kpi-top">
                <div class="kpi-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                @if($reviewTasks > 0)
                    <span class="kpi-pill pill-alert">{{ $reviewTasks }} {{ __('Review Tasks') }}</span>
                @else
                    <span class="kpi-pill">{{ __('TPA / TPSA / Director') }}</span>
                @endif
            </div>
            <div class="kpi-value">{{ number_format($pendingApprovals) }}</div>
            <div class="kpi-label">{{ __('Pending Approvals') }}</div>
            <div class="kpi-subtext">
                {{ $programCounts['pending_deputy'] ?? 0 }} {{ __('Deputy Review') }} &bull; {{ $programCounts['pending_director'] ?? 0 }} {{ __('Director') }}
            </div>
        </article>

        <!-- Card 4: Approved & Delivered -->
        <article class="kpi-card tone-emerald">
            <div class="kpi-top">
                <div class="kpi-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z"/>
                    </svg>
                </div>
                <span class="kpi-pill">{{ $programCounts['completed'] ?? 0 }} {{ __('Completed') }}</span>
            </div>
            <div class="kpi-value">{{ number_format($completedApproved) }}</div>
            <div class="kpi-label">{{ __('Approved & Completed') }}</div>
            <div class="kpi-subtext">{{ __('Programs successfully delivered') }}</div>
        </article>
    </div>

    <!-- Interactive Charts Section -->
    <div class="charts-dual-grid">
        <!-- Chart 1: Donut Status Distribution -->
        <article class="chart-card">
            <div class="chart-header">
                <div>
                    <span class="chart-kicker">{{ __('Workflow Status') }}</span>
                    <h3 class="chart-title">{{ __('Program Pipeline Distribution') }}</h3>
                </div>
                <div class="chart-badge">{{ $totalPrograms }} {{ __('Total') }}</div>
            </div>

            <div class="donut-container">
                <div class="donut-visual">
                    <svg class="donut-svg" viewBox="0 0 120 120">
                        <circle class="donut-bg" cx="60" cy="60" r="45" />
                        @if($totalPrograms > 0)
                            @foreach($donutSegments as $seg)
                                @if($seg['value'] > 0)
                                    <circle class="donut-segment" cx="60" cy="60" r="45"
                                        stroke="{{ $seg['color'] }}"
                                        stroke-dasharray="{{ $seg['dashArray'] }}"
                                        stroke-dashoffset="{{ $seg['dashOffset'] }}"
                                        data-tooltip="{{ $seg['label'] }}: {{ $seg['value'] }} ({{ $seg['percent'] }}%)" />
                                @endif
                            @endforeach
                        @else
                            <circle class="donut-segment-empty" cx="60" cy="60" r="45" />
                        @endif
                    </svg>
                    <div class="donut-center">
                        <span class="donut-count">{{ $totalPrograms }}</span>
                        <span class="donut-label">{{ __('Programs') }}</span>
                    </div>
                </div>

                <div class="donut-legend-list">
                    @foreach($donutSegments as $seg)
                        <div class="donut-legend-item">
                            <div class="legend-meta">
                                <span class="legend-dot" style="background: {{ $seg['color'] }};"></span>
                                <span class="legend-name">{{ $seg['label'] }}</span>
                            </div>
                            <div class="legend-stat">
                                <strong>{{ $seg['value'] }}</strong>
                                <span class="legend-pct">({{ $seg['percent'] }}%)</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </article>

        <!-- Chart 2: 6-Month Curved Bar Trend -->
        <article class="chart-card">
            <div class="chart-header">
                <div>
                    <span class="chart-kicker">{{ __('Six Months Timeline') }}</span>
                    <h3 class="chart-title">{{ __('Monthly Program Activity') }}</h3>
                </div>
                <div class="chart-legend-top">
                    <span><i class="dot-created"></i>{{ __('Created') }}</span>
                    <span><i class="dot-approved"></i>{{ __('Approved') }}</span>
                </div>
            </div>

            <div class="activity-bars-wrapper">
                <div class="activity-bars-grid">
                    @foreach($programDashboard['trend'] ?? [] as $period)
                        <div class="bar-column" title="{{ $period['label'] }}: {{ $period['created'] }} {{ __('created') }}, {{ $period['approved'] }} {{ __('approved') }}">
                            <div class="bar-stage">
                                <div class="bar-fill bar-created" style="height: {{ max(4, $period['created_height']) }}%;">
                                    @if($period['created'] > 0)
                                        <span class="bar-val">{{ $period['created'] }}</span>
                                    @endif
                                </div>
                                <div class="bar-fill bar-approved" style="height: {{ max(4, $period['approved_height']) }}%;">
                                    @if($period['approved'] > 0)
                                        <span class="bar-val">{{ $period['approved'] }}</span>
                                    @endif
                                </div>
                            </div>
                            <span class="bar-label">{{ $period['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
            
            <div class="activity-summary-footer">
                <div class="summary-pill">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                    <span>{{ __('Real-time monthly activity tracks paperwork submissions & director approvals.') }}</span>
                </div>
            </div>
        </article>
    </div>

    <!-- Recently Updated Programs Table -->
    <article class="recent-programs-card">
        <div class="recent-card-head">
            <div>
                <h3 class="recent-title">{{ __('Recently Updated Programs') }}</h3>
                <p class="recent-subtitle">{{ __('Latest activity and approval changes across your registered events.') }}</p>
            </div>
            <a class="btn-link" href="{{ route('admin.programs.index') }}">{{ __('View All Programs') }} &rarr;</a>
        </div>
        
        @if(($programDashboard['recent'] ?? collect())->isEmpty())
            <div class="empty-state-card">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 9.75h6m-6 3h3m-6-6h1.5m6.75-9.75H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                </svg>
                <h4>{{ __('No programs yet') }}</h4>
                <p>{{ __('Create your first program in Program Management to start the workflow.') }}</p>
                <a href="{{ route('admin.programs.create') }}" class="btn-create">{{ __('Create New Program') }}</a>
            </div>
        @else
            <div class="table-responsive">
                <table class="program-table">
                    <thead>
                        <tr>
                            <th>{{ __('Program Title') }}</th>
                            <th>{{ __('Branch') }}</th>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th style="text-align:right;">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($programDashboard['recent'] as $program)
                            @php
                                $badgeStyle = $statusMeta[$program->status]['badge'] ?? 'slate';
                            @endphp
                            <tr>
                                <td>
                                    <a href="{{ route('admin.programs.show', $program->id) }}" class="program-title-link">
                                        {{ $program->title }}
                                    </a>
                                </td>
                                <td><span class="branch-tag">{{ strtoupper($program->approval_branch ?: '-') }}</span></td>
                                <td>{{ $program->starts_at ? \Illuminate\Support\Carbon::parse($program->starts_at)->format('d M Y') : '-' }}</td>
                                <td>
                                    <span class="status-badge badge-{{ $badgeStyle }}">
                                        {{ $statusMeta[$program->status]['label'] ?? __(ucwords(str_replace('_', ' ', $program->status))) }}
                                    </span>
                                </td>
                                <td style="text-align:right;">
                                    <a href="{{ route('admin.programs.show', $program->id) }}" class="table-action-btn">{{ __('View Details') }} &rarr;</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </article>
</section>

@push('styles')
<style>
.staff-program-board {
    display: grid;
    gap: 1.5rem;
    margin-bottom: 2rem;
}

/* Header */
.program-board-head {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 1rem;
    padding-bottom: 0.25rem;
}

.kicker {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: var(--c-accent, #c48e42);
    font-size: 0.72rem;
    font-weight: 800;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    margin-bottom: 4px;
}

.program-board-head h2 {
    margin: 0 0 4px 0;
    font-size: 1.5rem;
    font-weight: 800;
    letter-spacing: -0.02em;
    color: var(--c-text-primary, #171310);
}

body[data-theme="dark"] .program-board-head h2 {
    color: #fdf8f3;
}

.program-board-head p {
    margin: 0;
    color: var(--c-text-secondary, #7f7165);
    font-size: 0.9rem;
}

.btn-primary-action {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 18px;
    background: linear-gradient(135deg, #f3d49b 0%, #c48e42 100%);
    color: #17120c !important;
    font-size: 0.88rem;
    font-weight: 700;
    border-radius: 12px;
    text-decoration: none;
    box-shadow: 0 6px 16px rgba(196, 142, 66, 0.24);
    transition: all 0.2s ease;
    white-space: nowrap;
}

.btn-primary-action:hover {
    transform: translateY(-1px);
    box-shadow: 0 10px 22px rgba(196, 142, 66, 0.34);
}

/* 4 KPI Grid */
.kpi-grid-4 {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 1rem;
}

.kpi-card {
    background: var(--c-surface, #ffffff);
    border: 1px solid var(--c-border, #eadfd2);
    border-radius: 18px;
    padding: 1.25rem 1.15rem;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
    display: flex;
    flex-direction: column;
    position: relative;
    overflow: hidden;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
}

body[data-theme="dark"] .kpi-card {
    background: #171310;
    border-color: rgba(226, 209, 192, 0.14);
}

.kpi-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
}

.kpi-icon {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(196, 142, 66, 0.12);
    color: #c48e42;
}

.kpi-icon svg {
    width: 20px;
    height: 20px;
}

.tone-blue .kpi-icon { background: rgba(59, 130, 246, 0.12); color: #3b82f6; }
.tone-amber .kpi-icon { background: rgba(245, 158, 11, 0.12); color: #f59e0b; }
.tone-emerald .kpi-icon { background: rgba(16, 185, 129, 0.12); color: #10b981; }
.tone-slate .kpi-icon { background: rgba(148, 163, 184, 0.12); color: #94a3b8; }

.kpi-pill {
    font-size: 0.72rem;
    font-weight: 700;
    padding: 3px 9px;
    border-radius: 999px;
    background: rgba(0,0,0,0.04);
    color: var(--c-text-secondary, #7f7165);
}

body[data-theme="dark"] .kpi-pill {
    background: rgba(255,255,255,0.06);
    color: #b8a899;
}

.pill-alert {
    background: rgba(239, 68, 68, 0.12) !important;
    color: #ef4444 !important;
    border: 1px solid rgba(239, 68, 68, 0.28);
}

.kpi-value {
    font-size: 1.85rem;
    font-weight: 800;
    letter-spacing: -0.03em;
    line-height: 1.1;
    color: var(--c-text-primary, #171310);
    margin-bottom: 4px;
}

body[data-theme="dark"] .kpi-value {
    color: #fdf8f3;
}

.kpi-label {
    font-size: 0.88rem;
    font-weight: 700;
    color: var(--c-text-primary, #171310);
    margin-bottom: 2px;
}

body[data-theme="dark"] .kpi-label {
    color: #fdf8f3;
}

.kpi-subtext {
    font-size: 0.76rem;
    color: var(--c-text-secondary, #7f7165);
    margin-top: auto;
}

/* Charts Dual Grid */
.charts-dual-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1.25rem;
}

.chart-card {
    background: var(--c-surface, #ffffff);
    border: 1px solid var(--c-border, #eadfd2);
    border-radius: 22px;
    padding: 1.5rem;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
    display: flex;
    flex-direction: column;
}

body[data-theme="dark"] .chart-card {
    background: #171310;
    border-color: rgba(226, 209, 192, 0.14);
}

.chart-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 1.25rem;
}

.chart-kicker {
    font-size: 0.7rem;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--c-accent, #c48e42);
    display: block;
    margin-bottom: 2px;
}

.chart-title {
    margin: 0;
    font-size: 1.05rem;
    font-weight: 700;
    color: var(--c-text-primary, #171310);
}

body[data-theme="dark"] .chart-title {
    color: #fdf8f3;
}

.chart-badge {
    font-size: 0.76rem;
    font-weight: 700;
    padding: 4px 10px;
    background: rgba(196, 142, 66, 0.12);
    color: #c48e42;
    border-radius: 999px;
    white-space: nowrap;
}

/* Donut Visual */
.donut-container {
    display: grid;
    grid-template-columns: 140px 1fr;
    align-items: center;
    gap: 1.5rem;
    margin-top: 0.5rem;
}

.donut-visual {
    position: relative;
    width: 140px;
    height: 140px;
    margin: 0 auto;
}

.donut-svg {
    width: 100%;
    height: 100%;
    transform: rotate(-90deg);
}

.donut-bg {
    fill: none;
    stroke: rgba(0, 0, 0, 0.06);
    stroke-width: 14;
}

body[data-theme="dark"] .donut-bg {
    stroke: rgba(255, 255, 255, 0.06);
}

.donut-segment {
    fill: none;
    stroke-width: 14;
    stroke-linecap: round;
    transition: stroke-width 0.2s ease, filter 0.2s ease;
}

.donut-segment:hover {
    stroke-width: 17;
    filter: drop-shadow(0 0 6px rgba(0, 0, 0, 0.25));
}

.donut-segment-empty {
    fill: none;
    stroke: rgba(0, 0, 0, 0.08);
    stroke-width: 14;
    stroke-dasharray: 4 4;
}

.donut-center {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
}

.donut-count {
    font-size: 1.6rem;
    font-weight: 800;
    color: var(--c-text-primary, #171310);
    line-height: 1;
}

body[data-theme="dark"] .donut-count {
    color: #fdf8f3;
}

.donut-label {
    font-size: 0.72rem;
    color: var(--c-text-secondary, #7f7165);
    font-weight: 600;
    margin-top: 2px;
}

.donut-legend-list {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.donut-legend-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 0.8rem;
    padding: 3px 6px;
    border-radius: 6px;
}

.donut-legend-item:hover {
    background: rgba(0, 0, 0, 0.03);
}

body[data-theme="dark"] .donut-legend-item:hover {
    background: rgba(255, 255, 255, 0.04);
}

.legend-meta {
    display: flex;
    align-items: center;
    gap: 8px;
    color: var(--c-text-primary, #171310);
}

body[data-theme="dark"] .legend-meta {
    color: #fdf8f3;
}

.legend-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
}

.legend-stat {
    display: flex;
    align-items: center;
    gap: 4px;
}

.legend-stat strong {
    font-weight: 700;
    color: var(--c-text-primary, #171310);
}

body[data-theme="dark"] .legend-stat strong {
    color: #fdf8f3;
}

.legend-pct {
    font-size: 0.74rem;
    color: var(--c-text-secondary, #7f7165);
}

/* Activity Bars Trend Chart */
.chart-legend-top {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 0.76rem;
    color: var(--c-text-secondary, #7f7165);
}

.chart-legend-top span {
    display: flex;
    align-items: center;
    gap: 5px;
}

.dot-created {
    width: 8px;
    height: 8px;
    border-radius: 2px;
    background: #3b82f6;
}

.dot-approved {
    width: 8px;
    height: 8px;
    border-radius: 2px;
    background: #10b981;
}

.activity-bars-wrapper {
    height: 160px;
    display: flex;
    align-items: flex-end;
    margin: 1rem 0 0.75rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid var(--c-border, #eadfd2);
}

body[data-theme="dark"] .activity-bars-wrapper {
    border-color: rgba(226, 209, 192, 0.12);
}

.activity-bars-grid {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 8px;
}

.bar-column {
    flex: 1;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-end;
    gap: 6px;
    cursor: default;
}

.bar-stage {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: flex-end;
    justify-content: center;
    gap: 4px;
}

.bar-fill {
    width: 14px;
    border-radius: 4px 4px 0 0;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    display: flex;
    justify-content: center;
}

.bar-created {
    background: linear-gradient(180deg, #60a5fa 0%, #3b82f6 100%);
    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.25);
}

.bar-approved {
    background: linear-gradient(180deg, #34d399 0%, #10b981 100%);
    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.25);
}

.bar-column:hover .bar-fill {
    filter: brightness(1.15);
    transform: scaleY(1.04);
    transform-origin: bottom;
}

.bar-val {
    position: absolute;
    top: -18px;
    font-size: 0.68rem;
    font-weight: 800;
    color: var(--c-text-primary, #171310);
    opacity: 0;
    transition: opacity 0.2s ease;
}

body[data-theme="dark"] .bar-val {
    color: #fdf8f3;
}

.bar-column:hover .bar-val {
    opacity: 1;
}

.bar-label {
    font-size: 0.72rem;
    font-weight: 700;
    color: var(--c-text-secondary, #7f7165);
}

.activity-summary-footer {
    margin-top: auto;
    padding-top: 0.5rem;
}

.summary-pill {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.75rem;
    color: var(--c-text-secondary, #7f7165);
    line-height: 1.4;
}

.summary-pill svg {
    color: var(--c-accent, #c48e42);
    flex-shrink: 0;
}

/* Recent Programs Table Card */
.recent-programs-card {
    background: var(--c-surface, #ffffff);
    border: 1px solid var(--c-border, #eadfd2);
    border-radius: 22px;
    padding: 1.5rem;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
}

body[data-theme="dark"] .recent-programs-card {
    background: #171310;
    border-color: rgba(226, 209, 192, 0.14);
}

.recent-card-head {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 1.25rem;
}

.recent-title {
    margin: 0 0 2px 0;
    font-size: 1.05rem;
    font-weight: 700;
    color: var(--c-text-primary, #171310);
}

body[data-theme="dark"] .recent-title {
    color: #fdf8f3;
}

.recent-subtitle {
    margin: 0;
    font-size: 0.8rem;
    color: var(--c-text-secondary, #7f7165);
}

.btn-link {
    font-size: 0.84rem;
    font-weight: 700;
    color: var(--c-accent, #c48e42);
    text-decoration: none;
}

.btn-link:hover {
    text-decoration: underline;
}

.table-responsive {
    overflow-x: auto;
}

.program-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.86rem;
}

.program-table th {
    text-align: left;
    padding: 10px 12px;
    font-size: 0.74rem;
    font-weight: 800;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: var(--c-text-secondary, #7f7165);
    border-bottom: 1px solid var(--c-border, #eadfd2);
}

body[data-theme="dark"] .program-table th {
    border-color: rgba(226, 209, 192, 0.12);
}

.program-table td {
    padding: 12px;
    border-bottom: 1px solid rgba(0, 0, 0, 0.04);
    color: var(--c-text-primary, #171310);
}

body[data-theme="dark"] .program-table td {
    border-color: rgba(255, 255, 255, 0.04);
    color: #fdf8f3;
}

.program-title-link {
    font-weight: 700;
    color: var(--c-text-primary, #171310);
    text-decoration: none;
}

.program-title-link:hover {
    color: var(--c-accent, #c48e42);
    text-decoration: underline;
}

body[data-theme="dark"] .program-title-link {
    color: #fdf8f3;
}

.branch-tag {
    font-size: 0.72rem;
    font-weight: 800;
    padding: 3px 8px;
    background: rgba(0, 0, 0, 0.05);
    border-radius: 6px;
    color: var(--c-text-secondary, #7f7165);
}

body[data-theme="dark"] .branch-tag {
    background: rgba(255, 255, 255, 0.06);
    color: #b8a899;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    font-size: 0.74rem;
    font-weight: 700;
    padding: 3px 9px;
    border-radius: 999px;
}

.badge-slate { background: rgba(148, 163, 184, 0.14); color: #64748b; }
.badge-amber { background: rgba(245, 158, 11, 0.14); color: #d97706; }
.badge-purple { background: rgba(168, 85, 247, 0.14); color: #9333ea; }
.badge-emerald { background: rgba(16, 185, 129, 0.14); color: #059669; }
.badge-blue { background: rgba(59, 130, 246, 0.14); color: #2563eb; }
.badge-gold { background: rgba(196, 142, 66, 0.14); color: #c48e42; }
.badge-rose { background: rgba(239, 68, 68, 0.14); color: #dc2626; }

.table-action-btn {
    font-size: 0.78rem;
    font-weight: 700;
    color: var(--c-accent, #c48e42);
    text-decoration: none;
}

.table-action-btn:hover {
    text-decoration: underline;
}

/* Empty State Card */
.empty-state-card {
    text-align: center;
    padding: 36px 20px;
    color: var(--c-text-secondary, #7f7165);
}

.empty-state-card svg {
    width: 44px;
    height: 44px;
    margin-bottom: 10px;
    color: var(--c-text-muted, #b8a899);
}

.empty-state-card h4 {
    margin: 0 0 4px 0;
    font-size: 1rem;
    font-weight: 700;
    color: var(--c-text-primary, #171310);
}

body[data-theme="dark"] .empty-state-card h4 {
    color: #fdf8f3;
}

.empty-state-card p {
    margin: 0 0 16px 0;
    font-size: 0.84rem;
}

.btn-create {
    display: inline-flex;
    align-items: center;
    padding: 8px 16px;
    background: var(--c-surface-2, #f5efe9);
    border: 1px solid var(--c-border, #eadfd2);
    color: var(--c-text-primary, #171310);
    font-size: 0.82rem;
    font-weight: 700;
    border-radius: 10px;
    text-decoration: none;
    transition: all 0.2s;
}

.btn-create:hover {
    background: var(--c-border, #eadfd2);
}

body[data-theme="dark"] .btn-create {
    background: rgba(255, 255, 255, 0.08);
    border-color: rgba(255, 255, 255, 0.12);
    color: #fdf8f3;
}

/* Responsive */
@media (max-width: 1024px) {
    .kpi-grid-4 {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .charts-dual-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 640px) {
    .kpi-grid-4 {
        grid-template-columns: 1fr;
    }
    .program-board-head {
        align-items: flex-start;
        flex-direction: column;
    }
    .btn-primary-action {
        width: 100%;
        justify-content: center;
    }
    .donut-container {
        grid-template-columns: 1fr;
    }
}
</style>
@endpush
