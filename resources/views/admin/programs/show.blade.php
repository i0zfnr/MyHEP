@extends('layouts.app')
@section('title', $program->title)
@section('header')<h2 style="margin:0;font-size:1.15rem;font-weight:800;">{{ __('Program Details') }}</h2>@endsection

@push('styles')
@php
    $canUseAccent = (session('auth_user.role') === 'student' || session('auth_user.admin_role') === 'system_admin');
@endphp

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
            @if($canEdit || session('auth_user.admin_role') === 'system_admin')
                <form method="post" action="{{ route('admin.programs.destroy', $program->id) }}" onsubmit="return confirm('{{ __('Adakah anda pasti mahu memadam program ini secara kekal? Tindakan ini tidak boleh diundur.') }}')">
                    @csrf
                    @method('delete')
                    <button class="pmr-btn pmr-action-danger" type="submit" style="background:#fee2e2; color:#b91c1c; border-color:#fca5a5;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:4px;"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                        {{ __('Delete Program') }}
                    </button>
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
