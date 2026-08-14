@extends('layouts.app')
@section('title', $program->title)
@section('header')<h2 style="margin:0;font-size:1.15rem;font-weight:800;">{{ __('Program Details') }}</h2>@endsection

@push('styles')
@php
    $canUseAccent = (session('auth_user.role') === 'student' || session('auth_user.admin_role') === 'system_admin');
@endphp
<style>
.pmr {
    --pm-accent: {{ $canUseAccent ? 'var(--se-primary, #C8A96A)' : '#C8A96A' }};
    display: grid;
    gap: 1.25rem;
    color: var(--text, #241d16);
    max-width: 1140px;
    margin: 0 auto;
    padding: 1.5rem 1rem;
    font-family: inherit;
}
.pmr-hero, .pmr-card {
    background: var(--surface, #fff);
    border: 1px solid color-mix(in srgb, var(--pm-accent) 22%, var(--border, #eadac8));
    border-radius: 18px;
    box-shadow: var(--glass-shadow, 0 14px 36px rgba(0,0,0,0.06));
    backdrop-filter: blur(var(--glass-blur, 16px));
}
.pmr-hero {
    padding: 1.75rem;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1.5rem;
    background: linear-gradient(135deg, var(--surface, #fff), color-mix(in srgb, var(--pm-accent) 10%, var(--surface, #fff)));
}
.pmr-eyebrow {
    font-size: .72rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .12em;
    color: var(--pm-accent);
}
.pmr h1 {
    font-size: 2rem;
    margin: .35rem 0;
    font-weight: 800;
    color: var(--text, #241d16);
}
.pmr p {
    color: var(--text-muted, #746b62);
    margin: .25rem 0;
    font-size: 0.92rem;
}
.pmr-card { padding: 1.5rem 1.75rem; }
.pmr-card h2 { margin: 0 0 1rem 0; font-size: 1.35rem; font-weight: 800; color: var(--text, #241d16); }

.pmr-actions {
    display: flex;
    align-items: stretch;
    gap: .35rem;
    flex-wrap: wrap;
    padding: .4rem;
    border: 1px solid color-mix(in srgb, var(--pm-accent) 20%, var(--border, #eadac8));
    border-radius: 14px;
    background: color-mix(in srgb, var(--surface, #fff) 92%, var(--pm-accent) 8%);
}
.pmr-actions form { display:flex; margin:0; }
.pmr-btn {
    min-height: 42px;
    border: 1px solid var(--border, #eadac8);
    border-radius: 9px;
    padding: .7rem 1.1rem;
    background: var(--surface, #fff);
    color: var(--text, #241d16);
    font-weight: 800;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.88rem;
    transition: background 0.15s ease, border-color 0.15s ease;
}
.pmr-btn:hover { background: color-mix(in srgb, var(--pm-accent) 8%, var(--surface, #fff)); }
.pmr-btn.primary {
    background: var(--pm-accent);
    color: #fff;
    border-color: var(--pm-accent);
    box-shadow: 0 4px 14px color-mix(in srgb, var(--pm-accent) 30%, transparent);
}
.pmr-btn.primary:hover {
    background: color-mix(in srgb, var(--pm-accent) 85%, #000);
    border-color: color-mix(in srgb, var(--pm-accent) 85%, #000);
    color: #fff;
}

.pmr-badge {
    display: inline-flex;
    padding: .3rem .65rem;
    border-radius: 99px;
    background: color-mix(in srgb, var(--pm-accent) 12%, transparent);
    color: var(--pm-accent);
    font-size: .72rem;
    font-weight: 800;
    text-transform: uppercase;
}
.pmr-badge.pending_tpsa, .pmr-badge.pending_director, .pmr-badge.pending_kj_hep { background: #fff5df; color: #a15c08; }
.pmr-badge.active, .pmr-badge.archived, .pmr-badge.completed { background: #e7f7ee; color: #18734a; }
.pmr-badge.rejected { background: #fff0ee; color: #b42318; }

body[data-theme="dark"] .pmr-badge.pending_tpsa, 
body[data-theme="dark"] .pmr-badge.pending_director,
body[data-theme="dark"] .pmr-badge.pending_kj_hep {
    background: rgba(161, 92, 8, 0.25);
    color: #f59e0b;
}
body[data-theme="dark"] .pmr-badge.active, 
body[data-theme="dark"] .pmr-badge.archived, 
body[data-theme="dark"] .pmr-badge.completed {
    background: rgba(40, 104, 108, 0.3);
    color: #34d399;
}
body[data-theme="dark"] .pmr-badge.rejected {
    background: rgba(180, 35, 24, 0.25);
    color: #fca5a5;
}

.pmr-grid-3 {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
}
.pmr-data {
    padding: 1rem 1.15rem;
    border-radius: 14px;
    background: var(--surface, #fff);
    border: 1px solid color-mix(in srgb, var(--pm-accent) 20%, var(--border, #eadac8));
}
.pmr-data strong {
    display: block;
    margin-top: 4px;
    font-weight: 800;
    font-size: 0.95rem;
    color: var(--text, #241d16);
}
.pmr-wide { grid-column: 1 / -1; }

.pmr-version {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    padding: 1rem 0;
    border-bottom: 1px solid var(--border, #eadac8);
}
.pmr-version:last-child { border-bottom: 0; }

@media (max-width: 760px) {
    .pmr-hero { flex-direction: column; align-items: flex-start; }
    .pmr-grid-3 { grid-template-columns: 1fr; }
    .pmr-wide { grid-column: auto; }
}
</style>
@endpush
@push('styles')
@include('admin.programs.partials.design-system')
@endpush

@section('content')
<main class="pmr">
    @if(session('success'))
        <div class="alert alert-success" style="border-radius: 12px; margin-bottom: 0;">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger" style="border-radius: 12px; margin-bottom: 0;">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <!-- Hero Header -->
    <header class="pmr-hero">
        <div>
            <span class="pmr-badge {{ $program->status }}">{{ __(str_replace('_',' ',$program->status)) }}</span>
            <h1>{{ $program->title }}</h1>
            <p>{{ $program->reference_no ?: __('No reference number') }} &middot; {{ __('Directed by') }} <strong>{{ $program->director_name }}</strong></p>
        </div>
        <div class="pmr-actions">
            <a class="pmr-btn" href="{{ route('admin.programs.index') }}">{{ __('Back') }}</a>
            <a class="pmr-btn primary" href="{{ route('admin.programs.operations', $program->id) }}">
                {{ __('Operations & Attendance QR') }}
            </a>
            @if($canEdit && $program->status === 'active')
                <a class="pmr-btn" href="{{ route('admin.programs.edit', $program->id) }}">{{ __('Edit') }}</a>
            @endif
            @if(session('auth_user.admin_role') === 'system_admin')
                <form method="post" action="{{ route('admin.programs.destroy', $program->id) }}" onsubmit="return confirm('{{ __('Permanently delete this program and every paperwork version? This cannot be undone.') }}')">
                    @csrf
                    @method('delete')
                    <button class="pmr-btn pmr-action-danger" type="submit">{{ __('Delete Program') }}</button>
                </form>
            @endif
        </div>
    </header>

    <!-- Program Details -->
    <section class="pmr-card">
        <h2>{{ __('Program details') }}</h2>
        <div class="pmr-grid-3">
            <div class="pmr-data">
                <span class="pmr-eyebrow">{{ __('Schedule') }}</span>
                <strong>{{ $program->starts_at ? \Illuminate\Support\Carbon::parse($program->starts_at)->format('d M Y, g:i A') : __('Not set') }}</strong>
                <span style="font-size: 0.8rem; color: var(--text-secondary, #746b62);">{{ $program->ends_at ? __('until').' '.\Illuminate\Support\Carbon::parse($program->ends_at)->format('d M Y, g:i A') : '' }}</span>
            </div>
            <div class="pmr-data">
                <span class="pmr-eyebrow">{{ __('Venue') }}</span>
                <strong>{{ $program->venue ?: __('Not set') }}</strong>
                <span style="font-size: 0.8rem; color: var(--text-secondary, #746b62);">{{ $program->latitude && $program->longitude ? $program->latitude.', '.$program->longitude.' · '.$program->geofence_radius_m.'m' : __('Coordinates not set') }}</span>
            </div>
            <div class="pmr-data">
                <span class="pmr-eyebrow">{{ __('Participants') }}</span>
                <strong>{{ $program->target_participants ?: __('Not set') }}</strong>
                <span style="font-size: 0.8rem; color: var(--text-secondary, #746b62);">{{ $program->estimated_participants ? number_format($program->estimated_participants).' '.__('estimated') : '' }}</span>
            </div>
            <div class="pmr-data pmr-wide">
                <span class="pmr-eyebrow">{{ __('Background / description') }}</span>
                <strong style="white-space: pre-line;">{{ $program->description ?: __('Not provided') }}</strong>
            </div>
            <div class="pmr-data pmr-wide">
                <span class="pmr-eyebrow">{{ __('Objectives') }}</span>
                <strong style="white-space: pre-line;">{{ $program->objectives ?: __('Not provided') }}</strong>
            </div>
        </div>
    </section>

    <!-- Final Report Status -->
    @php
        $reportStatus = $report?->status ?? 'not_generated';
        $reportStage = match($reportStatus) {
            'draft', 'rejected' => __('Program Director'),
            'pending_tpsa' => __('TPSA'),
            'pending_director' => __('Polytechnic Director'),
            'pending_kj_hep' => __('KJ HEP'),
            'archived' => __('Archived under KJ HEP'),
            default => __('Not started'),
        };
        $reportStages = [
            'draft' => __('Program Director draft'),
            'pending_tpsa' => __('TPSA review'),
            'pending_director' => __('Polytechnic Director review'),
            'pending_kj_hep' => __('KJ HEP acceptance'),
            'archived' => __('Archived under KJ HEP'),
        ];
        $reportStageKeys = array_keys($reportStages);
        $currentStageKey = $reportStatus === 'rejected' ? 'draft' : $reportStatus;
        $currentStageIndex = array_search($currentStageKey, $reportStageKeys, true);
    @endphp
    <section class="pmr-card">
        <h2>{{ __('Report status') }}</h2>
        <div class="pmr-grid-3">
            <div class="pmr-data">
                <span class="pmr-eyebrow">{{ __('Current status') }}</span>
                <strong>{{ __(str_replace('_', ' ', ucfirst($reportStatus))) }}</strong>
            </div>
            <div class="pmr-data">
                <span class="pmr-eyebrow">{{ __('Current stage') }}</span>
                <strong>{{ $reportStage }}</strong>
            </div>
            <div class="pmr-data">
                <span class="pmr-eyebrow">{{ __('Last report update') }}</span>
                <strong>{{ $report?->updated_at ? \Illuminate\Support\Carbon::parse($report->updated_at)->format('d M Y, g:i A') : __('No report generated') }}</strong>
            </div>
        </div>
        <div class="pmr-report-flow" aria-label="{{ __('Post-program report workflow') }}">
            @foreach($reportStages as $stageKey => $stageLabel)
                @php
                    $stageIndex = array_search($stageKey, $reportStageKeys, true);
                    $stageState = $reportStatus === 'not_generated' ? 'waiting'
                        : ($stageIndex < $currentStageIndex ? 'complete' : ($stageIndex === $currentStageIndex ? 'current' : 'waiting'));
                    $stageText = $stageState === 'complete' ? __('Completed')
                        : ($stageState === 'current' ? ($reportStatus === 'rejected' ? __('Returned for correction') : __('Current stage')) : __('Waiting'));
                @endphp
                <div class="pmr-report-step is-{{ $stageState }}">
                    <span>{{ $stageLabel }}</span>
                    <strong>{{ $stageText }}</strong>
                </div>
            @endforeach
        </div>
        <p style="margin-top: 1rem;">{{ __('The final report moves from the Program Director to TPSA, the Polytechnic Director, and KJ HEP before it is archived.') }}</p>
    </section>

</main>
@endsection
