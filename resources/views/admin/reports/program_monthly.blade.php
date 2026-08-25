@extends('layouts.app')
@section('title', __('Monthly Program Report'))
@section('header')<h2 style="margin:0;font-size:1rem">{{ __('Monthly Program Report') }}</h2>@endsection



@section('content')
@php
    $counts = $programSummary['counts'] ?? [];
    $total = max(1, $counts['current_total'] ?? 0);
    $labels = ['draft'=>__('Draft'),'pending_deputy'=>__('Deputy Review'),'pending_director'=>__('Director Approval'),'approved'=>__('Approved'),'in_progress'=>__('In Progress'),'completed'=>__('Completed'),'rejected'=>__('Rejected')];
@endphp
<main class="pmr">
    <header class="pmr-hero"><div><span class="pmr-eyebrow">{{ __('Program Analytics') }}</span><h1>{{ __('Monthly Program Performance') }}</h1><p>{{ __('Programs created, routed, approved, completed, and assigned for your review.') }}</p><p><strong>{{ __('Report scope:') }}</strong> {{ $start->format('d M Y') }} – {{ $end->format('d M Y') }}</p></div><div class="pmr-actions"><button type="button" class="pmr-btn" onclick="window.print()">{{ __('Print / Save PDF') }}</button><form method="GET"><div><label for="programReportMonth">{{ __('Report month') }}</label><input id="programReportMonth" type="month" name="month" value="{{ $month }}" max="{{ now()->format('Y-m') }}"></div><button class="pmr-btn primary">{{ __('Generate Report') }}</button></form></div></header>

    <section class="pmr-kpis">
        @foreach([['created',__('Programs Created')],['submitted',__('Submitted')],['approved',__('Approved')],['completed',__('Completed')],['review_tasks',__('Review Tasks')],['current_total',__('All My Programs')]] as [$key,$label])
            <article class="pmr-kpi"><span>{{ $label }}</span><strong>{{ number_format($counts[$key] ?? 0) }}</strong></article>
        @endforeach
    </section>

    <section class="pmr-grid">
        <article class="pmr-card"><span class="pmr-eyebrow">{{ __('Six Months') }}</span><h3>{{ __('Program Creation and Approval Trend') }}</h3><div class="pmr-trend">@foreach($programSummary['trend'] ?? [] as $period)<div class="pmr-period" title="{{ $period['primary'] }} created, {{ $period['secondary'] }} approved"><div class="pmr-bars"><i style="height:{{ $period['primary_height'] }}%"></i><i class="approved" style="height:{{ $period['secondary_height'] }}%"></i></div><span>{{ $period['label'] }}</span></div>@endforeach</div><div class="pmr-legend"><span><i></i>{{ __('Created') }}</span><span><i class="approved"></i>{{ __('Approved') }}</span></div></article>
        <article class="pmr-card"><span class="pmr-eyebrow">{{ __('Current Workflow') }}</span><h3>{{ __('Program Status Distribution') }}</h3><div class="pmr-status">@foreach($programSummary['statuses'] ?? [] as $item)<div><div class="pmr-status-head"><span>{{ $labels[$item['status']] ?? $item['status'] }}</span><strong>{{ $item['value'] }}</strong></div><div class="pmr-track"><i style="width:{{ $item['value'] ? max(4,($item['value']/$total)*100) : 0 }}%"></i></div></div>@endforeach</div><p style="margin-top:1rem"><strong>{{ number_format($programSummary['approval_rate'] ?? 0,1) }}%</strong> {{ __('approval rate for decisions made this month') }}</p></article>
    </section>

    <article class="pmr-card"><span class="pmr-eyebrow">{{ __('Monthly Activity') }}</span><h3>{{ __('Programs Updated During This Report') }}</h3>@if(($programSummary['recent'] ?? collect())->isEmpty())<p>{{ __('No program activity was recorded for this month.') }}</p>@else<div style="overflow-x:auto"><table class="pmr-table"><thead><tr><th>{{ __('Program') }}</th><th>{{ __('Branch') }}</th><th>{{ __('Program Date') }}</th><th>{{ __('Status') }}</th></tr></thead><tbody>@foreach($programSummary['recent'] as $program)<tr><td><a href="{{ route('admin.programs.show',$program->id) }}">{{ $program->title }}</a></td><td>{{ strtoupper($program->approval_branch ?: '-') }}</td><td>{{ $program->starts_at ? \Illuminate\Support\Carbon::parse($program->starts_at)->format('d M Y') : '-' }}</td><td><span class="pmr-badge">{{ $labels[$program->status] ?? $program->status }}</span></td></tr>@endforeach</tbody></table></div>@endif</article>
</main>
@endsection
