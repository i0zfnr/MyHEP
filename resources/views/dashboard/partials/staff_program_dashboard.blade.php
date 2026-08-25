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
                    <span class="chart-kicker">{{ __('Workflow') }}</span>
                    <h3 class="chart-title">{{ __('Program Status Distribution') }}</h3>
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
                    <span class="chart-kicker">{{ __('Six Months') }}</span>
                    <h3 class="chart-title">{{ __('Program Activity') }}</h3>
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
                <div class="empty-icon-circle">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 9.75h6m-6 3h3m-6-6h1.5m6.75-9.75H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                    </svg>
                </div>
                <h4>{{ __('No programs recorded yet') }}</h4>
                <p>{{ __('Create your first program in Program Management to initiate the review and approval workflow.') }}</p>
                <a href="{{ route('admin.programs.create') }}" class="btn-create">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    <span>{{ __('Create New Program') }}</span>
                </a>
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
