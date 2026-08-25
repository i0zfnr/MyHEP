<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ __('Program Check-in & Evaluation') }} &bull; {{ $program->title }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@600;700&display=swap" rel="stylesheet">

</head>
<body class="public-qr-checkin-page">

<div class="checkin-wrapper">

    <!-- Top Brand Header -->
    <header class="brand-header">
        <div class="brand-crest">
            <div class="brand-icon">PB</div>
            <div>
                <div class="brand-title">Politeknik Besut</div>
                <div class="brand-subtitle">MyHEP &bull; Official Check-in</div>
            </div>
        </div>
        <div class="live-badge">
            <span class="live-dot"></span>
            <span>{{ __('Live Check-in') }}</span>
        </div>
    </header>

    @if(session('success'))
        <!-- Success Card -->
        <div class="success-splash">
            <div class="success-icon-wrap">
                <svg viewBox="0 0 24 24" width="36" height="36" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <h2 style="font-size: 1.5rem; font-weight: 850; margin-bottom: 0.5rem; color: #1c1917;">{{ __('Check-in Successful!') }}</h2>
            <p style="font-size: 0.92rem; color: #78716c; max-width: 440px; margin: 0 auto 1.5rem;">
                {{ session('success') }}
            </p>
            <div style="background: #fafaf9; border: 1px solid #e7e5e4; border-radius: 12px; padding: 1rem; max-width: 380px; margin: 0 auto; font-size: 0.85rem; color: #57534e;">
                <div style="font-weight: 800; color: #1c1917; margin-bottom: 2px;">{{ $program->title }}</div>
                <div>📍 {{ $program->venue ?: __('Politeknik Besut') }} &bull; {{ now()->format('d M Y, g:i A') }}</div>
            </div>
        </div>
    @else

        @if($errors->any())
            <div class="alert-card alert-error">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.2" style="flex-shrink:0;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <div>
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Event Hero Card -->
        <section class="event-hero-card">
            <span class="event-eyebrow">
                <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                {{ __('Official Campus Program') }}
            </span>
            <h1 class="event-title">{{ $program->title }}</h1>
            <div class="event-meta-list">
                <span class="event-meta-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                    {{ $program->venue ?: __('Politeknik Besut Campus') }}
                </span>
                <span class="event-meta-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    {{ $program->starts_at ? \Carbon\Carbon::parse($program->starts_at)->format('d M Y, g:i A') : __('Today') }}
                </span>
            </div>
        </section>

        <!-- Main Check-in & Evaluation Form -->
        <form method="post" action="{{ route('public.programs.qr_checkin.store', $program->id) }}" id="publicCheckinForm" style="display: flex; flex-direction: column; gap: 1.25rem;">
            @csrf
            <input type="hidden" name="qr_token" value="{{ $token ?? old('qr_token') }}">
            <input type="hidden" name="latitude" id="attendanceLatitude" value="{{ old('latitude') }}">
            <input type="hidden" name="longitude" id="attendanceLongitude" value="{{ old('longitude') }}">
            <input type="hidden" name="location_accuracy_m" id="attendanceAccuracy" value="{{ old('location_accuracy_m') }}">
            <input type="hidden" name="location_captured_at" id="attendanceCapturedAt" value="{{ old('location_captured_at') }}">

            <!-- 1. Participant Details Card -->
            <section class="form-section-card">
                <div class="section-header">
                    <div class="section-title-wrap">
                        <div class="section-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        </div>
                        <h2 class="section-title">{{ __('Participant Information') }}</h2>
                    </div>
                    <span class="section-tag">{{ __('Step 1 of 2') }}</span>
                </div>

                <!-- Full Name -->
                <div class="input-group">
                    <label class="input-label" for="full_name">
                        <span>{{ __('Full Name') }} <span class="required-star">*</span></span>
                    </label>
                    <div class="input-field-wrap">
                        <input id="full_name" name="full_name" class="input-control" required value="{{ old('full_name') }}" placeholder="{{ __('e.g., Muhammad Danial bin Rosli') }}">
                        <svg class="input-field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </div>
                </div>

                <!-- Identifier -->
                <div class="input-group">
                    <label class="input-label" for="identifier">
                        <span>{{ __('Matric No. / IC / Phone Number') }} <span class="required-star">*</span></span>
                    </label>
                    <div class="input-field-wrap">
                        <input id="identifier" name="identifier" class="input-control" required value="{{ old('identifier') }}" placeholder="{{ __('e.g., 13DIT24F1001 or 013-9876543') }}">
                        <svg class="input-field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="13" x="3" y="5" rx="2"/><circle cx="9" cy="11.5" r="2.5"/><path d="M15 9.5h2"/><path d="M15 13.5h2"/></svg>
                    </div>
                </div>

                <!-- Institution / Department -->
                <div class="input-group">
                    <label class="input-label" for="institution_or_unit">
                        <span>{{ __('Institution / University / Class') }}</span>
                    </label>
                    <div class="input-field-wrap">
                        <input id="institution_or_unit" name="institution_or_unit" class="input-control" value="{{ old('institution_or_unit') }}" placeholder="{{ __('e.g., Politeknik Besut (DIT3A) or UiTM / UniSZA') }}">
                        <svg class="input-field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c0 2 2 3 6 3s6-1 6-3v-5"/></svg>
                    </div>
                </div>

                <!-- Email Address -->
                <div class="input-group">
                    <label class="input-label" for="email">
                        <span>{{ __('Email Address (Optional)') }}</span>
                    </label>
                    <div class="input-field-wrap">
                        <input id="email" type="email" name="email" class="input-control" value="{{ old('email') }}" placeholder="{{ __('name@example.com') }}">
                        <svg class="input-field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                    </div>
                </div>
            </section>

            @if($program->questionnaire_enabled)
                <!-- 2. Program Feedback & Evaluation Card -->
                <section class="form-section-card">
                    <div class="section-header">
                        <div class="section-title-wrap">
                            <div class="section-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                            </div>
                            <h2 class="section-title">{{ __('Borang SA-04(1) Evaluation') }}</h2>
                        </div>
                        <span class="section-tag" style="background: var(--pm-gold-light); color: var(--pm-gold-dark);">{{ __('Step 2 of 2') }}</span>
                    </div>

                    <!-- Overall Star Rating -->
                    <div class="star-rating-box">
                        <span class="star-rating-title">{{ __('Overall Program Satisfaction') }}</span>
                        <input type="hidden" name="satisfaction_rating" id="satisfactionRatingInput" value="{{ old('satisfaction_rating', 5) }}">
                        <div class="star-picker-track" id="starTrack">
                            @for($s = 1; $s <= 5; $s++)
                                <button type="button" class="star-btn {{ $s <= (old('satisfaction_rating', 5)) ? 'is-selected' : '' }}" data-star="{{ $s }}" aria-label="Rate {{ $s }} Star">
                                    <svg viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                </button>
                            @endfor
                        </div>
                        <div class="star-desc-pill" id="starDescPill">
                            ★★★★★ 5 &bull; {{ __('Extremely Satisfied') }}
                        </div>
                    </div>

                    <!-- SA-04 Question Items -->
                    @if($questions->isNotEmpty())
                        <div style="display: flex; flex-direction: column; gap: 0.85rem;">
                            @foreach($questions as $index => $q)
                                <div class="q-card" id="qCard_{{ $q->id }}">
                                    <div class="q-head">
                                        <span class="q-num-pill">{{ $index + 1 }}</span>
                                        <div class="q-title">
                                            {{ $q->question_text }}
                                            @if($q->is_required)<span class="q-req">*</span>@endif
                                        </div>
                                    </div>

                                    @if($q->question_type === 'rating_4')
                                        <!-- Interactive Likert 1-4 Segmented Pill Radio -->
                                        @php $currVal = (string) old('answers.'.$q->id, '4'); @endphp
                                        <div class="likert-grid">
                                            <label class="likert-option {{ $currVal === '1' ? 'is-selected' : '' }}" onclick="selectLikert(this)">
                                                <input type="radio" name="answers[{{ $q->id }}]" value="1" @checked($currVal === '1') @required($q->is_required)>
                                                <span class="likert-score">1</span>
                                                <span class="likert-label">{{ __('Sgt Tdk Setuju') }}</span>
                                            </label>
                                            <label class="likert-option {{ $currVal === '2' ? 'is-selected' : '' }}" onclick="selectLikert(this)">
                                                <input type="radio" name="answers[{{ $q->id }}]" value="2" @checked($currVal === '2') @required($q->is_required)>
                                                <span class="likert-score">2</span>
                                                <span class="likert-label">{{ __('Tidak Setuju') }}</span>
                                            </label>
                                            <label class="likert-option {{ $currVal === '3' ? 'is-selected' : '' }}" onclick="selectLikert(this)">
                                                <input type="radio" name="answers[{{ $q->id }}]" value="3" @checked($currVal === '3') @required($q->is_required)>
                                                <span class="likert-score">3</span>
                                                <span class="likert-label">{{ __('Setuju') }}</span>
                                            </label>
                                            <label class="likert-option {{ $currVal === '4' ? 'is-selected' : '' }}" onclick="selectLikert(this)">
                                                <input type="radio" name="answers[{{ $q->id }}]" value="4" @checked($currVal === '4') @required($q->is_required)>
                                                <span class="likert-score">4</span>
                                                <span class="likert-label">{{ __('Sgt Setuju') }}</span>
                                            </label>
                                        </div>
                                    @elseif($q->question_type === 'rating_5')
                                        <!-- Interactive Star 1-5 Segmented Pill Radio -->
                                        @php $currVal5 = (string) old('answers.'.$q->id, '5'); @endphp
                                        <div class="likert-grid star-grid">
                                            @for($st = 1; $st <= 5; $st++)
                                                <label class="likert-option {{ $currVal5 === (string)$st ? 'is-selected' : '' }}" onclick="selectLikert(this)">
                                                    <input type="radio" name="answers[{{ $q->id }}]" value="{{ $st }}" @checked($currVal5 === (string)$st) @required($q->is_required)>
                                                    <span class="likert-score">★ {{ $st }}</span>
                                                    <span class="likert-label">{{ $st === 5 ? __('Cemerlang') : ($st === 1 ? __('Lemah') : $st.'/5') }}</span>
                                                </label>
                                            @endfor
                                        </div>
                                    @else
                                        <!-- Text / Written Comments -->
                                        <textarea name="answers[{{ $q->id }}]" class="textarea-control" rows="3" @required($q->is_required)" placeholder="{{ __('Sila nyatakan ulasan / pandangan anda...') }}">{{ old('answers.'.$q->id) }}</textarea>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <!-- General Suggestions / Comments -->
                    <div class="input-group" style="margin-top: 0.4rem;">
                        <label class="input-label" for="feedback_comments">
                            <span>{{ __('Suggestions / Constructive Feedback') }}</span>
                        </label>
                        <textarea id="feedback_comments" name="feedback_comments" class="textarea-control" rows="3" placeholder="{{ __('What went well or what can we improve for future programs?') }}">{{ old('feedback_comments') }}</textarea>
                    </div>
                </section>
            @endif

            <!-- Submit Button -->
            <button class="btn-submit-hero" id="btnSubmitCheckin" type="submit">
                <span>{{ $program->questionnaire_enabled ? __('Submit Check-in & Evaluation') : __('Confirm Attendance') }}</span>
                <div class="btn-icon-bubble">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </div>
            </button>
        </form>
    @endif

</div>

<script>
// 1. Interactive Star Rating Handler
(() => {
    const starInput = document.getElementById('satisfactionRatingInput');
    const descPill = document.getElementById('starDescPill');
    const starButtons = document.querySelectorAll('#starTrack .star-btn');

    const starDescriptions = {
        1: "★☆☆☆☆ 1 • {{ __('Very Dissatisfied') }}",
        2: "★★☆☆☆ 2 • {{ __('Dissatisfied') }}",
        3: "★★★☆☆ 3 • {{ __('Neutral') }}",
        4: "★★★★☆ 4 • {{ __('Satisfied') }}",
        5: "★★★★★ 5 • {{ __('Extremely Satisfied') }}"
    };

    function updateStars(rating) {
        if (!starInput) return;
        starInput.value = rating;
        if (descPill) descPill.textContent = starDescriptions[rating] || (rating + " / 5 ★");

        starButtons.forEach(btn => {
            const btnVal = parseInt(btn.getAttribute('data-star'), 10);
            btn.classList.toggle('is-selected', btnVal <= rating);
        });
    }

    starButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const rating = parseInt(btn.getAttribute('data-star'), 10);
            updateStars(rating);
        });

        btn.addEventListener('mouseenter', () => {
            const hoverVal = parseInt(btn.getAttribute('data-star'), 10);
            starButtons.forEach(b => {
                const bVal = parseInt(b.getAttribute('data-star'), 10);
                b.classList.toggle('is-hovered', bVal <= hoverVal);
            });
        });

        btn.addEventListener('mouseleave', () => {
            starButtons.forEach(b => b.classList.remove('is-hovered'));
        });
    });
})();

// 2. Interactive Likert Segmented Radio Selector
function selectLikert(labelElement) {
    const radio = labelElement.querySelector('input[type="radio"]');
    if (!radio) return;
    radio.checked = true;

    // Remove is-selected from siblings
    const parent = labelElement.parentElement;
    parent.querySelectorAll('.likert-option').forEach(el => el.classList.remove('is-selected'));
    labelElement.classList.add('is-selected');

    // Highlight question card as answered
    const card = labelElement.closest('.q-card');
    if (card) card.classList.add('is-answered');
}

// 3. High-Accuracy Geolocation Auto-Capture
(() => {
    const latitude = document.getElementById('attendanceLatitude');
    const longitude = document.getElementById('attendanceLongitude');
    const accuracy = document.getElementById('attendanceAccuracy');
    const capturedAt = document.getElementById('attendanceCapturedAt');

    if (navigator.geolocation && latitude && longitude) {
        navigator.geolocation.getCurrentPosition(position => {
            latitude.value = position.coords.latitude;
            longitude.value = position.coords.longitude;
            if (accuracy) accuracy.value = position.coords.accuracy;
            if (capturedAt) capturedAt.value = new Date(position.timestamp).toISOString();
        }, () => {
            // Geolocation soft-fallback
        }, { enableHighAccuracy: true, timeout: 6000, maximumAge: 0 });
    }
})();
</script>

</body>
</html>
