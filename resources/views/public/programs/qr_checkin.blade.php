<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Program Check-in & Survey') }} - {{ $program->title }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --se-primary: #99702d;
            --se-bg: #faf7f2;
            --se-card: #ffffff;
            --se-text: #211a14;
            --se-muted: #746b62;
            --se-border: #e7dbcb;
        }
        body {
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            background: var(--se-bg);
            color: var(--se-text);
            margin: 0;
            padding: 16px;
            display: flex;
            justify-content: center;
        }
        .checkin-card {
            background: var(--se-card);
            border: 1px solid var(--se-border);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(153, 112, 45, 0.08);
            max-width: 540px;
            width: 100%;
            padding: 24px 28px;
            box-sizing: border-box;
        }
        .checkin-eyebrow {
            font-size: 0.72rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--se-primary);
        }
        .checkin-card h1 { font-size: 1.6rem; margin: 4px 0 8px; font-weight: 800; }
        .checkin-meta { font-size: 0.88rem; color: var(--se-muted); margin-bottom: 20px; }

        .form-group { display: flex; flex-direction: column; gap: 6px; margin-bottom: 14px; }
        .form-group label { font-size: 0.85rem; font-weight: 700; }
        .form-group label span { color: #b42318; }
        .form-group input, .form-group textarea, .form-group select {
            padding: 12px 14px;
            border: 1px solid var(--se-border);
            border-radius: 12px;
            font-size: 0.95rem;
            font-family: inherit;
            background: var(--se-bg);
        }
        .form-group input:focus, .form-group textarea:focus {
            outline: none;
            border-color: var(--se-primary);
            background: #fff;
        }
        .btn-submit {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 12px;
            background: var(--se-primary);
            color: #ffffff;
            font-weight: 800;
            font-size: 1rem;
            cursor: pointer;
            margin-top: 10px;
        }
        .btn-submit:hover { opacity: 0.9; }
        .btn-submit:disabled { cursor: not-allowed; opacity: .55; }
        .location-status { padding: 12px; border-radius: 12px; margin: 14px 0; font-size: .86rem; font-weight: 700; background: #fff5df; color: #8a5717; }
        .location-status.ready { background: #e7f7ee; color: #18734a; }
        .location-status.error { background: #fff0ee; color: #b42318; }

        .star-rating { display: flex; gap: 8px; font-size: 1.5rem; cursor: pointer; }
        .alert-success { background: #e7f7ee; color: #18734a; padding: 14px; border-radius: 12px; font-weight: 700; margin-bottom: 16px; }
    </style>
</head>
<body>

<main class="checkin-card">
    <span class="checkin-eyebrow">{{ __('PROGRAM CHECK-IN & FEEDBACK') }}</span>
    <h1>{{ $program->title }}</h1>
    <div class="checkin-meta">
        📍 {{ $program->venue ?: __('Politeknik Besut') }} &middot;
        🕒 {{ $program->starts_at ? \Illuminate\Support\Carbon::parse($program->starts_at)->format('d M Y, g:i A') : __('Today') }}
    </div>

    @if(session('success'))
        <div class="alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div style="background: #fff0ee; color: #b42318; padding: 12px; border-radius: 12px; margin-bottom: 16px; font-size: 0.85rem;">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form method="post" action="{{ route('public.programs.qr_checkin.store', $program->id) }}">
        @csrf
        <input type="hidden" name="latitude" id="attendanceLatitude" value="{{ old('latitude') }}">
        <input type="hidden" name="longitude" id="attendanceLongitude" value="{{ old('longitude') }}">
        <input type="hidden" name="location_accuracy_m" id="attendanceAccuracy" value="{{ old('location_accuracy_m') }}">
        <input type="hidden" name="location_captured_at" id="attendanceCapturedAt" value="{{ old('location_captured_at') }}">
        <div class="form-group">
            <label for="full_name">{{ __('Full Name') }} <span>*</span></label>
            <input id="full_name" name="full_name" required value="{{ old('full_name') }}" placeholder="{{ __('Enter your full name') }}">
        </div>

        <div class="form-group">
            <label for="identifier">{{ __('Matric No / IC No / Phone') }} <span>*</span></label>
            <input id="identifier" name="identifier" required value="{{ old('identifier') }}" placeholder="{{ __('e.g., 13DKM24F1001 or 0123456789') }}">
        </div>

        <div class="form-group">
            <label for="institution_or_unit">{{ __('Institution / Class / Department') }}</label>
            <input id="institution_or_unit" name="institution_or_unit" value="{{ old('institution_or_unit') }}" placeholder="{{ __('e.g., DKM5A or External Visitor') }}">
        </div>

        <div class="form-group">
            <label for="email">{{ __('Email Address') }}</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="{{ __('name@example.com') }}">
        </div>

        @if($program->questionnaire_enabled)
        <hr style="border: 0; border-top: 1px solid var(--se-border); margin: 20px 0;">

        <h3 style="font-size: 1.1rem; font-weight: 800; margin: 0 0 12px;">{{ __('Program Feedback') }}</h3>

        <div class="form-group">
            <label>{{ __('Overall Program Satisfaction') }}</label>
            <select name="satisfaction_rating">
                <option value="5">★★★★★ 5 - {{ __('Extremely Satisfied') }}</option>
                <option value="4">★★★★☆ 4 - {{ __('Satisfied') }}</option>
                <option value="3">★★★☆☆ 3 - {{ __('Neutral') }}</option>
                <option value="2">★★☆☆☆ 2 - {{ __('Dissatisfied') }}</option>
                <option value="1">★☆☆☆☆ 1 - {{ __('Very Dissatisfied') }}</option>
            </select>
        </div>

        @if($questions->isNotEmpty())
            @foreach($questions as $q)
                <div class="form-group">
                    <label>
                        {{ $q->question_text }}
                        @if($q->is_required)<span>*</span>@endif
                    </label>
                    @if($q->question_type === 'rating_5')
                        <select name="answers[{{ $q->id }}]" @required($q->is_required)>
                            <option value="5">5 / 5</option>
                            <option value="4">4 / 5</option>
                            <option value="3">3 / 5</option>
                            <option value="2">2 / 5</option>
                            <option value="1">1 / 5</option>
                        </select>
                    @else
                        <textarea name="answers[{{ $q->id }}]" rows="5" @required($q->is_required) placeholder="{{ __('Describe what you learned or gained from this program...') }}">{{ old('answers.'.$q->id) }}</textarea>
                    @endif
                </div>
            @endforeach
        @endif

        <div class="form-group">
            <label for="feedback_comments">{{ __('Comments / Suggestions') }}</label>
            <textarea id="feedback_comments" name="feedback_comments" rows="3" placeholder="{{ __('What can we improve for future events?') }}"></textarea>
        </div>
        @endif

        <div id="locationStatus" class="location-status" role="status">
            {{ __('Location permission is required. Please allow GPS access to submit attendance.') }}
        </div>
        <button class="btn-submit" id="attendanceSubmit" type="submit" disabled>{{ $program->questionnaire_enabled ? __('Check In & Submit Feedback') : __('Submit Attendance') }}</button>
    </form>
</main>

<script>
(() => {
    const status = document.getElementById('locationStatus');
    const submit = document.getElementById('attendanceSubmit');
    const latitude = document.getElementById('attendanceLatitude');
    const longitude = document.getElementById('attendanceLongitude');
    const accuracy = document.getElementById('attendanceAccuracy');
    const capturedAt = document.getElementById('attendanceCapturedAt');

    if (!navigator.geolocation) {
        status.textContent = @json(__('This device does not support location access. Attendance cannot be submitted.'));
        status.classList.add('error');
        return;
    }

    navigator.geolocation.getCurrentPosition(position => {
        latitude.value = position.coords.latitude;
        longitude.value = position.coords.longitude;
        accuracy.value = position.coords.accuracy;
        capturedAt.value = new Date(position.timestamp).toISOString();
        status.textContent = @json(__('Location captured. You can now submit your attendance and answers.')) + ` (${Math.round(position.coords.accuracy)}m accuracy)`;
        status.classList.add('ready');
        submit.disabled = false;
    }, error => {
        status.textContent = error.code === error.PERMISSION_DENIED
            ? @json(__('Location permission was denied. Enable location access and reload this page.'))
            : @json(__('Your location could not be captured. Move to an open area and reload this page.'));
        status.classList.add('error');
    }, { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 });
})();
</script>

</body>
</html>
