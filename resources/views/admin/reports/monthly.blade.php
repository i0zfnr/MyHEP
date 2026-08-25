@extends('layouts.app')

@section('title', __('Monthly Operations Report'))



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

    <p class="report-footer-note">{{ __('Generated') }} {{ now()->format('d M Y, H:i') }} · {{ __('MyHEP monthly operational analytics') }}</p>
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
