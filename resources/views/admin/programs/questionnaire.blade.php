@extends('layouts.app')

@section('title', __('Questionnaire Builder - ').$program->title)



@push('styles')
@include('admin.programs.partials.design-system')
@endpush

@section('content')
<main class="pmr">
    @if(session('success'))
        <div class="alert alert-success" style="border-radius: 12px; margin-bottom: 0;">{{ session('success') }}</div>
    @endif
    @if(isset($errors) && $errors->any())
        <div class="alert alert-danger" style="border-radius: 12px; margin-bottom: 0;">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <!-- Hero Header with Modern Actions -->
    <header class="pmr-hero">
        <div>
            <span class="pmr-eyebrow">{{ __('PROGRAM QUESTIONNAIRE BUILDER') }}</span>
            <h1>{{ $program->title }}</h1>
            <p>{{ $program->reference_no ?: __('No reference number') }} &middot; {{ __('Venue:') }} <strong>{{ $program->venue ?: __('Not set') }}</strong></p>
        </div>
        <div class="pmr-actions">
            <!-- Toggle Analytics & Visualization Button -->
            <button type="button" class="pmr-btn" id="btnToggleAnalytics" style="background: linear-gradient(135deg, rgba(212,175,55,0.15), rgba(99,102,241,0.15)); border-color: var(--pm-accent); color: var(--text); font-weight: 850; gap: 0.6rem;">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M18 20V10M12 20V4M6 20v-6"/></svg>
                <span id="btnToggleAnalyticsText">{{ __('Statistics & Visualization') }}</span>
                <span id="analyticsStatusPill" style="font-size: 0.7rem; padding: 2px 8px; border-radius: 999px; background: rgba(16,185,129,0.2); color: #059669; font-weight: 900; border: 1px solid rgba(16,185,129,0.4);">ON</span>
            </button>
            <a class="pmr-btn" href="{{ route('admin.programs.operations', $program->id) }}">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 12H5m7 7-7-7 7-7"/></svg>
                {{ __('Back to Operations') }}
            </a>
        </div>
    </header>

    <!-- WOW Interactive Visual Analytics & Statistics Dashboard -->
    <section id="analyticsDashboardSection" class="pmr-card" style="border: 1px solid rgba(212,175,55,0.35); background: linear-gradient(170deg, var(--surface, #fff), color-mix(in srgb, var(--pm-accent, #b99150) 4%, var(--surface, #fff))); box-shadow: 0 12px 35px rgba(36,26,18,0.06);">
        <div style="display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.25rem; border-bottom: 1px solid var(--border); padding-bottom: 1rem;">
            <div>
                <span class="pmr-eyebrow" style="color: #6366f1; display: inline-flex; align-items: center; gap: 0.4rem;">
                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M18 20V10M12 20V4M6 20v-6"/></svg>
                    {{ __('FEEDBACK VISUALIZATION & ANALYTICS') }}
                </span>
                <h2 style="font-size: 1.35rem; margin: 0.2rem 0 0.1rem; color: var(--text);">{{ __('Participant Satisfaction & Performance Statistics') }}</h2>
                <p style="margin: 0; font-size: 0.84rem; color: var(--text-muted);">{{ __('Real-time summary of program questionnaire, respondent breakdown, and detailed score distribution.') }}</p>
            </div>
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <span style="font-size: 0.78rem; font-weight: 750; color: var(--text-muted);">{{ __('Response Rate:') }}</span>
                <strong style="font-size: 0.95rem; color: var(--pm-accent);">{{ $analytics['response_rate'] }}%</strong>
            </div>
        </div>

        <!-- 1. Executive Metric KPI Grid -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
            <!-- KPI 1: Total Respondents -->
            <div style="background: var(--surface, #fff); border: 1px solid var(--border); border-radius: 14px; padding: 1.15rem; box-shadow: 0 4px 12px rgba(0,0,0,0.02);">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.4rem;">
                    <span style="font-size: 0.74rem; font-weight: 800; text-transform: uppercase; color: var(--text-muted);">{{ __('Total Respondents') }}</span>
                    <span style="width: 32px; height: 32px; border-radius: 8px; background: rgba(99,102,241,0.12); color: #6366f1; display: grid; place-items: center;">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </span>
                </div>
                <strong style="font-size: 1.8rem; font-weight: 900; color: var(--text); display: block;">{{ number_format($analytics['total_responses']) }}</strong>
                <span style="font-size: 0.8rem; color: var(--text-muted);">{{ __('From :total attendees (:rate%)', ['total' => $analytics['total_attendances'], 'rate' => $analytics['response_rate']]) }}</span>
            </div>

            <!-- KPI 2: CSI Index -->
            <div style="background: var(--surface, #fff); border: 1px solid var(--border); border-radius: 14px; padding: 1.15rem; box-shadow: 0 4px 12px rgba(0,0,0,0.02);">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.4rem;">
                    <span style="font-size: 0.74rem; font-weight: 800; text-transform: uppercase; color: var(--text-muted);">{{ __('Satisfaction Index (CSI)') }}</span>
                    <span style="width: 32px; height: 32px; border-radius: 8px; background: rgba(212,175,55,0.15); color: #b99150; display: grid; place-items: center;">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                    </span>
                </div>
                <div style="display: flex; align-items: baseline; gap: 0.4rem;">
                    <strong style="font-size: 1.8rem; font-weight: 900; color: var(--pm-accent);">{{ $analytics['overall_avg'] > 0 ? number_format($analytics['overall_avg'], 2) : '0.00' }}</strong>
                    <span style="font-size: 0.9rem; color: var(--text-muted); font-weight: 700;">/ 5.0</span>
                </div>
                <span style="font-size: 0.8rem; color: #059669; font-weight: 750;">{{ $analytics['satisfaction_percentage'] }}% {{ __('Positive Rating') }}</span>
            </div>

            <!-- KPI 3: Category Breakdown -->
            <div style="background: var(--surface, #fff); border: 1px solid var(--border); border-radius: 14px; padding: 1.15rem; box-shadow: 0 4px 12px rgba(0,0,0,0.02);">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.4rem;">
                    <span style="font-size: 0.74rem; font-weight: 800; text-transform: uppercase; color: var(--text-muted);">{{ __('Category Breakdown') }}</span>
                    <span style="width: 32px; height: 32px; border-radius: 8px; background: rgba(16,185,129,0.12); color: #059669; display: grid; place-items: center;">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                    </span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.75rem; margin-top: 0.2rem;">
                    <div>
                        <strong style="font-size: 1.25rem; font-weight: 900; color: #059669;">{{ number_format($analytics['internal_count']) }}</strong>
                        <span style="display: block; font-size: 0.75rem; color: var(--text-muted);">{{ __('PB Students') }}</span>
                    </div>
                    <div style="width: 1px; height: 26px; background: var(--border);"></div>
                    <div>
                        <strong style="font-size: 1.25rem; font-weight: 900; color: #0284c7;">{{ number_format($analytics['external_count']) }}</strong>
                        <span style="display: block; font-size: 0.75rem; color: var(--text-muted);">{{ __('External Guests') }}</span>
                    </div>
                </div>
            </div>

            <!-- KPI 4: Question Count -->
            <div style="background: var(--surface, #fff); border: 1px solid var(--border); border-radius: 14px; padding: 1.15rem; box-shadow: 0 4px 12px rgba(0,0,0,0.02);">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.4rem;">
                    <span style="font-size: 0.74rem; font-weight: 800; text-transform: uppercase; color: var(--text-muted);">{{ __('Questionnaire Items') }}</span>
                    <span style="width: 32px; height: 32px; border-radius: 8px; background: rgba(234,179,8,0.14); color: #ca8a04; display: grid; place-items: center;">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                    </span>
                </div>
                <strong style="font-size: 1.8rem; font-weight: 900; color: var(--text); display: block;">{{ count($questions) }}</strong>
                <span style="font-size: 0.8rem; color: var(--text-muted);">{{ __('Standard Politeknik SA-04 Format') }}</span>
            </div>
        </div>

        <!-- 2. Dual Visual Charts Grid: Donut Pie + Rating Histogram -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 1.25rem; margin-bottom: 1.5rem;">
            <!-- Left Chart: Donut Breakdown (Pie Chart) -->
            <div style="background: var(--surface, #fff); border: 1px solid var(--border); border-radius: 16px; padding: 1.35rem;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem;">
                    <div>
                        <span style="font-size: 0.72rem; font-weight: 800; text-transform: uppercase; color: var(--text-muted); display: inline-flex; align-items: center; gap: 0.35rem;">
                            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 2a10 10 0 0 1 10 10H12z"/></svg>
                            {{ __('COMPOSITION CHART') }}
                        </span>
                        <h3 style="font-size: 1.05rem; font-weight: 850; margin: 0.1rem 0 0;">{{ __('Participant Composition Breakdown') }}</h3>
                    </div>
                    <span style="font-size: 0.76rem; font-weight: 800; background: rgba(212,175,55,0.12); color: var(--pm-accent); padding: 0.25rem 0.65rem; border-radius: 999px;">
                        {{ $analytics['total_attendances'] }} {{ __('Attendees') }}
                    </span>
                </div>

                @php
                    $totalAtt = max(1, $analytics['total_attendances']);
                    $pbPct = round(($analytics['internal_count'] / $totalAtt) * 100);
                    $extPct = 100 - $pbPct;
                    // SVG Donut calculation
                    $radius = 42;
                    $circ = 2 * pi() * $radius; // ~263.89
                    $pbOffset = $circ * ($pbPct / 100);
                @endphp

                <div style="display: flex; align-items: center; justify-content: center; gap: 2rem; flex-wrap: wrap; padding: 0.75rem 0;">
                    <!-- SVG Pie / Donut Chart -->
                    <div style="position: relative; width: 140px; height: 140px;">
                        <svg width="140" height="140" viewBox="0 0 100 100" style="transform: rotate(-90deg);">
                            <!-- Background Track -->
                            <circle cx="50" cy="50" r="{{ $radius }}" fill="transparent" stroke="#f1f5f9" stroke-width="14" />
                            <!-- External Segment -->
                            <circle cx="50" cy="50" r="{{ $radius }}" fill="transparent" stroke="#0284c7" stroke-width="14" stroke-dasharray="{{ $circ }}" stroke-dashoffset="0" />
                            <!-- Internal PB Segment -->
                            <circle cx="50" cy="50" r="{{ $radius }}" fill="transparent" stroke="#10b981" stroke-width="14" stroke-dasharray="{{ $pbOffset }} {{ $circ }}" stroke-linecap="round" />
                        </svg>
                        <div style="position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center;">
                            <strong style="font-size: 1.35rem; font-weight: 900; color: var(--text); line-height: 1;">{{ $pbPct }}%</strong>
                            <span style="font-size: 0.68rem; font-weight: 750; color: var(--text-muted); text-transform: uppercase;">{{ __('PB Students') }}</span>
                        </div>
                    </div>

                    <!-- Legend & Details -->
                    <div style="display: flex; flex-direction: column; gap: 0.75rem; min-width: 150px;">
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 1rem;">
                            <div style="display: flex; align-items: center; gap: 0.45rem;">
                                <span style="width: 12px; height: 12px; border-radius: 4px; background: #10b981;"></span>
                                <span style="font-size: 0.84rem; font-weight: 750; color: var(--text);">{{ __('PB Students') }}</span>
                            </div>
                            <strong style="font-size: 0.88rem; color: #059669;">{{ $analytics['internal_count'] }} ({{ $pbPct }}%)</strong>
                        </div>
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 1rem;">
                            <div style="display: flex; align-items: center; gap: 0.45rem;">
                                <span style="width: 12px; height: 12px; border-radius: 4px; background: #0284c7;"></span>
                                <span style="font-size: 0.84rem; font-weight: 750; color: var(--text);">{{ __('External Guests') }}</span>
                            </div>
                            <strong style="font-size: 0.88rem; color: #0284c7;">{{ $analytics['external_count'] }} ({{ $extPct }}%)</strong>
                        </div>
                        <div style="border-top: 1px dashed var(--border); padding-top: 0.6rem; display: flex; align-items: center; justify-content: space-between; font-size: 0.8rem; color: var(--text-muted);">
                            <span>{{ __('Total Respondents') }}</span>
                            <strong style="color: var(--text);">{{ $analytics['total_responses'] }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Chart: Score Distribution Histogram Bars (5★ to 1★) -->
            <div style="background: var(--surface, #fff); border: 1px solid var(--border); border-radius: 16px; padding: 1.35rem;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
                    <div>
                        <span style="font-size: 0.72rem; font-weight: 800; text-transform: uppercase; color: var(--text-muted);">{{ __('OVERALL SCORE DISTRIBUTION') }}</span>
                        <h3 style="font-size: 1.05rem; font-weight: 850; margin: 0.1rem 0 0;">{{ __('Star Rating Breakdown') }}</h3>
                    </div>
                    <span style="font-size: 0.76rem; font-weight: 800; background: rgba(99,102,241,0.12); color: #6366f1; padding: 0.25rem 0.65rem; border-radius: 999px;">
                        {{ __('Scale 1 - 5') }}
                    </span>
                </div>

                @php
                    $totalRatings = max(1, array_sum($analytics['rating_distribution']));
                    $ratingLabels = [
                        5 => ['label' => '5 ★ '.__('Outstanding / Strongly Agree'), 'color' => '#d4af37'],
                        4 => ['label' => '4 ★ '.__('Excellent / Agree'), 'color' => '#10b981'],
                        3 => ['label' => '3 ★ '.__('Moderate / Neutral'), 'color' => '#0284c7'],
                        2 => ['label' => '2 ★ '.__('Unsatisfactory / Disagree'), 'color' => '#f59e0b'],
                        1 => ['label' => '1 ★ '.__('Poor / Strongly Disagree'), 'color' => '#ef4444'],
                    ];
                @endphp

                <div style="display: flex; flex-direction: column; gap: 0.65rem;">
                    @foreach([5, 4, 3, 2, 1] as $star)
                        @php
                            $cnt = $analytics['rating_distribution'][$star] ?? 0;
                            $pct = round(($cnt / $totalRatings) * 100);
                            $cfg = $ratingLabels[$star];
                        @endphp
                        <div>
                            <div style="display: flex; align-items: center; justify-content: space-between; font-size: 0.8rem; font-weight: 750; margin-bottom: 0.2rem;">
                                <span style="color: var(--text);">{{ $cfg['label'] }}</span>
                                <span style="color: var(--text-muted);"><strong>{{ $cnt }}</strong> ({{ $pct }}%)</span>
                            </div>
                            <div style="height: 9px; border-radius: 999px; background: #f1f5f9; overflow: hidden; width: 100%;">
                                <div style="height: 100%; width: {{ $pct }}%; background: {{ $cfg['color'] }}; border-radius: 999px; transition: width 0.5s ease;"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- 3. Question Item Performance Breakdown (SA-04 Questions Histogram) -->
        @if(!empty($analytics['question_stats']))
            <div style="background: var(--surface, #fff); border: 1px solid var(--border); border-radius: 16px; padding: 1.35rem; margin-bottom: 1.5rem;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.5rem;">
                    <div>
                        <span style="font-size: 0.72rem; font-weight: 800; text-transform: uppercase; color: var(--text-muted);">{{ __('ITEM-BY-ITEM PERFORMANCE ANALYSIS') }}</span>
                        <h3 style="font-size: 1.05rem; font-weight: 850; margin: 0.1rem 0 0;">{{ __('Average Scores for SA-04 Items') }}</h3>
                    </div>
                    <span style="font-size: 0.76rem; font-weight: 800; color: var(--text-muted);">
                        {{ count($analytics['question_stats']) }} {{ __('Items Evaluated') }}
                    </span>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1rem;">
                    @foreach($analytics['question_stats'] as $idx => $qStat)
                        @php
                            $maxScore = $qStat['type'] === 'rating_4' ? 4 : 5;
                            $score = $qStat['avg_score'] ?? 0;
                            $scorePct = $maxScore > 0 ? round(($score / $maxScore) * 100) : 0;
                            $barColor = $scorePct >= 80 ? '#10b981' : ($scorePct >= 60 ? '#d4af37' : '#f59e0b');
                        @endphp
                        <div style="border: 1px solid var(--border); border-radius: 12px; padding: 1rem; background: var(--bg-alt, #faf7f2);">
                            <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 0.5rem; margin-bottom: 0.5rem;">
                                <div style="display: flex; align-items: center; gap: 0.4rem;">
                                    <span style="width: 22px; height: 22px; border-radius: 6px; background: var(--pm-accent); color: #fff; font-size: 0.72rem; font-weight: 900; display: grid; place-items: center;">{{ $idx + 1 }}</span>
                                    <span style="font-size: 0.84rem; font-weight: 800; color: var(--text); line-height: 1.35;">{{ \Illuminate\Support\Str::limit($qStat['text'], 65) }}</span>
                                </div>
                                @if($score > 0)
                                    <strong style="font-size: 0.95rem; font-weight: 900; color: {{ $barColor }}; white-space: nowrap;">
                                        {{ number_format($score, 2) }} <span style="font-size: 0.72rem; color: var(--text-muted);">/{{ $maxScore }}</span>
                                    </strong>
                                @endif
                            </div>

                            @if($score > 0)
                                <div style="height: 7px; border-radius: 999px; background: #e2e8f0; overflow: hidden; margin-bottom: 0.35rem;">
                                    <div style="height: 100%; width: {{ $scorePct }}%; background: {{ $barColor }}; border-radius: 999px;"></div>
                                </div>
                                <div style="display: flex; justify-content: space-between; font-size: 0.72rem; color: var(--text-muted);">
                                    <span>{{ $qStat['total_answers'] }} {{ __('responses recorded') }}</span>
                                    <span style="font-weight: 750; color: {{ $barColor }};">{{ $scorePct }}%</span>
                                </div>
                            @else
                                <span style="font-size: 0.75rem; color: var(--text-muted); font-style: italic;">{{ __('No numerical responses yet') }}</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- 4. Qualitative Feedback Highlights Stream -->
        @if(!empty($analytics['recent_comments']) && $analytics['recent_comments']->isNotEmpty())
            <div style="background: var(--surface, #fff); border: 1px solid var(--border); border-radius: 16px; padding: 1.35rem;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
                    <div>
                        <span style="font-size: 0.72rem; font-weight: 800; text-transform: uppercase; color: var(--text-muted);">{{ __('PARTICIPANT VOICES & COMMENTS') }}</span>
                        <h3 style="font-size: 1.05rem; font-weight: 850; margin: 0.1rem 0 0;">{{ __('Recent Participant Feedback') }}</h3>
                    </div>
                    <span style="font-size: 0.76rem; font-weight: 800; color: #6366f1;">{{ count($analytics['recent_comments']) }} {{ __('Recent Comments') }}</span>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 0.85rem;">
                    @foreach($analytics['recent_comments'] as $comm)
                        <div style="background: var(--bg-alt, #faf7f2); border: 1px solid var(--border); border-radius: 12px; padding: 0.95rem;">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.4rem;">
                                <strong style="font-size: 0.86rem; color: var(--text);">{{ $comm->full_name }}</strong>
                                @if($comm->satisfaction_rating)
                                    <span style="font-size: 0.78rem; font-weight: 800; color: #b99150;">★ {{ $comm->satisfaction_rating }}/5</span>
                                @endif
                            </div>
                            <p style="font-size: 0.82rem; color: var(--text-secondary, #4b5563); margin: 0 0 0.5rem; font-style: italic; line-height: 1.4;">
                                "{{ \Illuminate\Support\Str::limit($comm->feedback_comments, 140) }}"
                            </p>
                            <div style="display: flex; align-items: center; justify-content: space-between; font-size: 0.72rem; color: var(--text-muted);">
                                <span>{{ $comm->attendee_type === 'internal' ? __('PB Student') : ($comm->institution_or_unit ?: __('External Guest')) }}</span>
                                <span>{{ \Illuminate\Support\Carbon::parse($comm->checked_in_at)->diffForHumans() }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </section>

    <!-- 1. Questionnaire Publishing Mode Configuration -->
    <section class="pmr-card">
        <span class="pmr-eyebrow" style="display: inline-flex; align-items: center; gap: 0.35rem;">
            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
            {{ __('QUESTIONNAIRE CONTROL & PUBLISHING') }}
        </span>
        <h2>{{ __('Questionnaire Publishing Settings') }}</h2>
        <p class="subtitle">{{ __('Define how this feedback form is published: directly in the Politeknik Besut student portal without QR scanning, via QR code scan, or closed/draft.') }}</p>

        <form method="post" action="{{ route('admin.programs.questionnaire-setting.update', $program->id) }}" class="pmr-mode-panel">
            @csrf @method('put')
            <label for="participationMode" style="font-size: 0.8rem; font-weight: 800; text-transform: uppercase; color: var(--text-muted, #746b62);">{{ __('Publishing Mode') }}</label>
            <select id="participationMode" name="questionnaire_publish_mode">
                <option value="internal_system" @selected(($program->questionnaire_publish_mode ?? 'internal_system') === 'internal_system' && $program->questionnaire_enabled)>
                    {{ __('Mode 1: Direct in System (Politeknik Besut students respond directly on portal/PWA without scanning QR)') }}
                </option>
                <option value="qr_code" @selected(($program->questionnaire_publish_mode ?? '') === 'qr_code' && $program->questionnaire_enabled)>
                    {{ __('Mode 2: QR Code Scan (Student PWA & External Guests scan QR code)') }}
                </option>
                <option value="closed" @selected(!$program->questionnaire_enabled || ($program->questionnaire_publish_mode ?? '') === 'closed')>
                    {{ __('Questionnaire Closed / Draft (Attendance Only Mode)') }}
                </option>
            </select>
            <div class="pmr-mode-actions">
                <button class="pmr-btn primary" type="submit">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    {{ __('Save Mode Settings') }}
                </button>
            </div>
        </form>
    </section>

    <!-- 2. Interactive Questionnaire Builder Workspace -->
    <div id="questionnaireBuilderContent">

        <!-- Question Editor Form with 100% Official SA-04(1) Baseline -->
        <section class="pmr-card">
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; margin-bottom: 0.75rem; border-bottom: 1px solid var(--border); padding-bottom: 0.75rem;">
                <div>
                    <span class="pmr-eyebrow" style="display: inline-flex; align-items: center; gap: 0.35rem;">
                        <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                        {{ __('OFFICIAL TEMPLATE SA-04(1) (P00) (24-12-24)') }}
                    </span>
                    <h2>{{ __('Program Evaluation Form & Questionnaire') }}</h2>
                </div>
                <div style="display: flex; gap: 0.6rem; flex-wrap: wrap;">
                    <button type="button" class="pmr-btn" id="btnLoadSa04" style="background: rgba(212,175,55,0.12); border-color: var(--pm-accent); color: var(--text); font-weight: 800; font-size: 0.82rem;">
                        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/></svg>
                        {{ __('Reload Official SA-04(1) Template') }}
                    </button>
                    <button type="button" class="pmr-btn" onclick="addQuestionRow()" style="font-size: 0.82rem;">
                        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        {{ __('+ Add Additional Question') }}
                    </button>
                </div>
            </div>

            <!-- Official Scoring Guide Banner -->
            <div style="background: var(--bg-alt, #faf7f2); border: 1px solid var(--border); border-radius: 12px; padding: 0.9rem 1.15rem; margin-bottom: 1.25rem;">
                <span style="font-size: 0.76rem; font-weight: 800; text-transform: uppercase; color: var(--text-muted); display: block; margin-bottom: 0.4rem;">
                    {{ __('SA-04(1) Form Scoring Guide:') }}
                </span>
                <div style="display: flex; gap: 1rem; flex-wrap: wrap; font-size: 0.82rem; font-weight: 700; color: var(--text);">
                    <span><strong>1:</strong> {{ __('Strongly Disagree') }}</span>
                    <span><strong>2:</strong> {{ __('Disagree') }}</span>
                    <span><strong>3:</strong> {{ __('Agree') }}</span>
                    <span><strong>4:</strong> {{ __('Strongly Agree') }}</span>
                </div>
            </div>

            <form method="post" action="{{ route('admin.programs.survey.save', $program->id) }}">
                @csrf
                <input type="hidden" name="title" value="{{ $survey->title ?? __('Program Evaluation Form [SA-04(1)] - :title', ['title' => $program->title]) }}">
                <input type="hidden" name="description" value="{{ $survey->description ?? __('Please provide your feedback regarding this training program by marking the appropriate score according to the guide above.') }}">

                <div id="questionsContainer">
                    @forelse($questions as $index => $q)
                        <div class="pmr-q-item">
                            <div class="pmr-q-head">
                                <div class="pmr-q-title">
                                    <span class="pmr-q-number">{{ $index + 1 }}</span>
                                    <span>{{ __('Question') }} {{ $index + 1 }}</span>
                                </div>
                                <button type="button" class="pmr-btn" style="padding: 4px 10px; font-size: 0.76rem; min-height: 28px; color: #dc2626; border-color: rgba(220,38,38,0.25);" onclick="this.closest('.pmr-q-item').remove()">
                                    <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                    {{ __('Delete') }}
                                </button>
                            </div>
                            <label class="pmr-q-field">
                                <span>{{ __('Question Text') }}</span>
                                <input type="text" name="questions[{{ $index }}][question_text]" value="{{ $q->question_text }}" required>
                            </label>
                            <label class="pmr-q-field">
                                <span>{{ __('Response Type / Scale') }}</span>
                                <select name="questions[{{ $index }}][question_type]">
                                    <option value="rating_4" @selected($q->question_type === 'rating_4')>{{ __('Likert Scale 1-4 (Form SA-04: Strongly Disagree - Strongly Agree)') }}</option>
                                    <option value="rating_5" @selected($q->question_type === 'rating_5')>{{ __('Star Scale 1-5 (Very Low - Outstanding)') }}</option>
                                    <option value="text" @selected($q->question_type === 'text')>{{ __('Written Response / Comments (Long Written Answer)') }}</option>
                                </select>
                            </label>
                            <label class="pmr-q-required">
                                <input type="hidden" name="questions[{{ $index }}][is_required]" value="0">
                                <input type="checkbox" name="questions[{{ $index }}][is_required]" value="1" @checked($q->is_required)>
                                <span>{{ __('Required question') }}</span>
                            </label>
                        </div>
                    @empty
                        <!-- Standard SA-04(1) items will be dynamically initialized via JS -->
                    @endforelse
                </div>

                <div style="display: flex; gap: 10px; margin-top: 1.25rem; flex-wrap: wrap;">
                    <button type="button" class="pmr-btn" onclick="addQuestionRow()">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        {{ __('Add Question') }}
                    </button>
                    <button type="submit" class="pmr-btn primary">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        {{ __('Save Questionnaire') }}
                    </button>
                </div>
            </form>

            @if($survey && $survey->status !== 'published')
                <form method="post" action="{{ route('admin.programs.survey.publish', $program->id) }}" style="margin-top: 14px; border-top: 1px solid var(--border, #eadac8); padding-top: 14px;">
                    @csrf
                    <button type="submit" class="pmr-btn primary" style="width: 100%; justify-content: center; min-height: 44px; font-size: 0.95rem;">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                        {{ __('Publish Questionnaire to Participants') }}
                    </button>
                </form>
            @endif
        </section>

    </div>
</main>

<script>
let questionCounter = {{ count($questions) }};
const isEnglish = @json(app()->getLocale() === 'en');

// 100% Official Borang SA-04(1) (P00) (24-12-24) Template Items (Bilingual Support)
const officialSa04Questions = isEnglish ? [
    // Lecturer / Speaker Evaluation
    { text: 'Training objectives were achieved', type: 'rating_4', required: true },
    { text: 'Training content was suitable and well-structured', type: 'rating_4', required: true },
    { text: 'Delivery was effective and engaging', type: 'rating_4', required: true },
    { text: 'Effective use of teaching aids and materials', type: 'rating_4', required: true },

    // Training Execution Evaluation
    { text: 'Training venue environment was suitable and conducive', type: 'rating_4', required: true },
    { text: 'Program planning and execution were smoothly conducted', type: 'rating_4', required: true },
    { text: 'Time allocated for each module was sufficient', type: 'rating_4', required: true },

    // Training Effectiveness on Participants
    { text: 'Increased knowledge and understanding', type: 'rating_4', required: true },
    { text: 'More confident in applying skills and knowledge learned', type: 'rating_4', required: true },
    { text: 'Overall, this program was successful and beneficial', type: 'rating_4', required: true },

    // Participant Comments
    { text: 'Willingness to share acquired knowledge. YES / NO, if NO please state reason.', type: 'text', required: false }
] : [
    // Penilaian Penceramah
    { text: 'Objektif latihan tercapai', type: 'rating_4', required: true },
    { text: 'Kandungan latihan adalah sesuai', type: 'rating_4', required: true },
    { text: 'Penyampaian yang baik dan berkesan', type: 'rating_4', required: true },
    { text: 'Penggunaan alat bantuan mengajar dengan berkesan.', type: 'rating_4', required: true },

    // Penilaian Pelaksanaan Latihan
    { text: 'Suasana tempat latihan yang sesuai / kondusif', type: 'rating_4', required: true },
    { text: 'Perancangan dan perlaksanaan program telah dibuat dengan lancar', type: 'rating_4', required: true },
    { text: 'Masa yang diperuntukan bagi setiap modul adalah sesuai', type: 'rating_4', required: true },

    // Penilaian Keberkesanan Latihan Terhadap Peserta
    { text: 'Meningkatkan pengetahuan / pemahaman.', type: 'rating_4', required: true },
    { text: 'Lebih berkeyakinan mengajar modul berkenaan / menjalankan tugas berkaitan / mengaplikasi apa yang dipelajari.', type: 'rating_4', required: true },
    { text: 'Pada keseluruhannya latihan ini adalah berjaya dan bermanfaat.', type: 'rating_4', required: true },

    // Ulasan Peserta
    { text: 'Kesediaan untuk berkongsi ilmu yang diperolehi berkaitan latihan. YA / TIDAK, jika TIDAK sila nyatakan sebab.', type: 'text', required: false }
];

function escapeQuestionValue(value) {
    return String(value).replace(/[&<>'"]/g, character => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;'
    })[character]);
}

function addQuestionRow(text = '', type = 'rating_4', required = true) {
    const container = document.getElementById('questionsContainer');
    if (!container) return;
    const div = document.createElement('div');
    div.className = 'pmr-q-item';
    div.innerHTML = `
        <div class="pmr-q-head">
            <div class="pmr-q-title">
                <span class="pmr-q-number">${questionCounter + 1}</span>
                <span>{{ __('Question') }} ${questionCounter + 1}</span>
            </div>
            <button type="button" class="pmr-btn" style="padding: 4px 10px; font-size: 0.76rem; min-height: 28px; color: #dc2626; border-color: rgba(220,38,38,0.25);" onclick="this.closest('.pmr-q-item').remove()">
                <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                {{ __('Delete') }}
            </button>
        </div>
        <label class="pmr-q-field">
            <span>{{ __('Question Text') }}</span>
            <input type="text" name="questions[${questionCounter}][question_text]" value="${escapeQuestionValue(text)}" required placeholder="{{ __('Enter question text') }}">
        </label>
        <label class="pmr-q-field">
            <span>{{ __('Response Type / Scale') }}</span>
            <select name="questions[${questionCounter}][question_type]">
                <option value="rating_4" ${type === 'rating_4' ? 'selected' : ''}>{{ __('Likert Scale 1-4 (Form SA-04: Strongly Disagree - Strongly Agree)') }}</option>
                <option value="rating_5" ${type === 'rating_5' ? 'selected' : ''}>{{ __('Star Scale 1-5 (Very Low - Outstanding)') }}</option>
                <option value="text" ${type === 'text' ? 'selected' : ''}>{{ __('Written Response / Comments (Long Written Answer)') }}</option>
            </select>
        </label>
        <label class="pmr-q-required">
            <input type="hidden" name="questions[${questionCounter}][is_required]" value="0">
            <input type="checkbox" name="questions[${questionCounter}][is_required]" value="1" ${required ? 'checked' : ''}>
            <span>{{ __('Required question') }}</span>
        </label>
    `;
    container.appendChild(div);
    questionCounter++;
}

document.getElementById('btnLoadSa04')?.addEventListener('click', () => {
    const container = document.getElementById('questionsContainer');
    if (!container) return;
    container.innerHTML = '';
    questionCounter = 0;
    officialSa04Questions.forEach(q => {
        addQuestionRow(q.text, q.type, q.required);
    });
});

// Auto-populate with official SA-04 questions if container is currently empty
if (questionCounter === 0) {
    document.getElementById('btnLoadSa04')?.click();
}

// Analytics Dashboard ON/OFF Toggle Logic
const btnToggleAnalytics = document.getElementById('btnToggleAnalytics');
const analyticsSection = document.getElementById('analyticsDashboardSection');
const analyticsStatusPill = document.getElementById('analyticsStatusPill');

function setAnalyticsVisibility(show) {
    if (!analyticsSection) return;
    if (show) {
        analyticsSection.style.display = 'block';
        if (analyticsStatusPill) {
            analyticsStatusPill.textContent = 'ON';
            analyticsStatusPill.style.background = 'rgba(16,185,129,0.2)';
            analyticsStatusPill.style.color = '#059669';
            analyticsStatusPill.style.borderColor = 'rgba(16,185,129,0.4)';
        }
    } else {
        analyticsSection.style.display = 'none';
        if (analyticsStatusPill) {
            analyticsStatusPill.textContent = 'OFF';
            analyticsStatusPill.style.background = 'rgba(100,116,139,0.2)';
            analyticsStatusPill.style.color = '#64748b';
            analyticsStatusPill.style.borderColor = 'rgba(100,116,139,0.4)';
        }
    }
    localStorage.setItem('show_questionnaire_analytics', show ? 'true' : 'false');
}

// Load saved preference, default to true (ON)
const savedAnalyticsState = localStorage.getItem('show_questionnaire_analytics');
const isAnalyticsVisible = savedAnalyticsState === null ? true : savedAnalyticsState === 'true';
setAnalyticsVisibility(isAnalyticsVisible);

btnToggleAnalytics?.addEventListener('click', () => {
    const isCurrentlyVisible = analyticsSection.style.display !== 'none';
    setAnalyticsVisibility(!isCurrentlyVisible);
});
</script>
@endsection
