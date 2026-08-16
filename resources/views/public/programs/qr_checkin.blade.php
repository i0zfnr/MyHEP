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
            color: var(--se-text);
            margin: 0;
            min-height: 100vh;
            padding: 28px 16px;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            box-sizing: border-box;
            background: radial-gradient(circle at 50% -10%, rgba(153,112,45,.12), transparent 34%), var(--se-bg);
        }
        .checkin-card {
            position: relative;
            overflow: hidden;
            background: var(--se-card);
            border: 1px solid var(--se-border);
            border-radius: 22px;
            box-shadow: 0 22px 55px rgba(74, 50, 27, 0.12);
            max-width: 540px;
            width: 100%;
            padding: 0 28px 26px;
            box-sizing: border-box;
        }
        .checkin-card::before { content:''; display:block; height:5px; margin:0 -28px 22px; background:linear-gradient(90deg,#76521f,var(--se-primary),#d3b476); }
        .checkin-head { padding-bottom:18px; margin-bottom:20px; border-bottom:1px solid var(--se-border); }
        .checkin-eyebrow {
            font-size: 0.72rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--se-primary);
        }
        .checkin-card h1 { font-size: 1.65rem; margin: 5px 0 9px; font-weight: 800; letter-spacing:-.025em; }
        .checkin-meta { display:flex; align-items:center; flex-wrap:wrap; gap:6px; font-size: 0.82rem; color: var(--se-muted); }

        .form-group { display: flex; flex-direction: column; gap: 6px; margin-bottom: 14px; }
        .form-group label { font-size: 0.85rem; font-weight: 700; }
        .form-group label span { color: #b42318; }
        .form-group input, .form-group textarea, .form-group select {
            padding: 12px 14px;
            border: 1px solid var(--se-border);
            border-radius: 12px;
            font-size: 0.95rem;
            font-family: inherit;
            background: #fff;
            color:var(--se-text);
            transition:border-color .16s ease,box-shadow .16s ease,background .16s ease;
        }
        .form-group input:hover, .form-group textarea:hover, .form-group select:hover { border-color:color-mix(in srgb,var(--se-primary) 38%,var(--se-border)); }
        .form-group input:focus, .form-group textarea:focus, .form-group select:focus {
            outline: none;
            border-color: var(--se-primary);
            background: #fff;
            box-shadow:0 0 0 3px rgba(153,112,45,.12);
        }
        .btn-submit {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 13px;
            background: linear-gradient(135deg,#8b6225,#ad8137);
            color: #ffffff;
            font-weight: 800;
            font-size: 1rem;
            cursor: pointer;
            margin-top: 8px;
            box-shadow:0 10px 22px rgba(153,112,45,.22);
            transition:transform .16s ease,box-shadow .16s ease,opacity .16s ease;
        }
        .btn-submit:hover { transform:translateY(-1px); box-shadow:0 14px 28px rgba(153,112,45,.28); }
        .btn-submit:disabled { cursor: not-allowed; opacity: .55; }
        .location-status { display:flex; align-items:flex-start; gap:9px; padding:11px 12px; border:1px solid #f0d7a7; border-radius:11px; margin:16px 0; font-size:.8rem; line-height:1.45; font-weight:700; background:#fff8e9; color:#8a5717; }
        .location-status::before { content:'i'; display:grid; place-items:center; flex:0 0 20px; width:20px; height:20px; border-radius:50%; background:currentColor; color:#fff; font-size:.7rem; font-weight:900; }
        .location-status.ready { border-color:#bfe4d1; background:#edf9f2; color:#18734a; }
        .location-status.error { background: #fff0ee; color: #b42318; }

        .star-rating { display: flex; gap: 8px; font-size: 1.5rem; cursor: pointer; }
        .alert-success { background: #e7f7ee; color: #18734a; padding: 14px; border-radius: 12px; font-weight: 700; margin-bottom: 16px; }
        @media(max-width:560px){ body{padding:0;background:var(--se-bg)} .checkin-card{min-height:100vh;border:0;border-radius:0;padding:0 20px 24px;box-shadow:none}.checkin-card::before{margin:0 -20px 20px}.checkin-head{margin-bottom:18px}.form-group{margin-bottom:13px} }
    </style>
</head>
<body>

<main class="checkin-card">
    <div class="checkin-head">
        <span class="checkin-eyebrow">{{ __('PROGRAM CHECK-IN & FEEDBACK') }}</span>
        <h1>{{ $program->title }}</h1>
        <div class="checkin-meta">
            <span>📍 {{ $program->venue ?: __('Politeknik Besut') }}</span>
            <span aria-hidden="true">&middot;</span>
            <span>🕒 {{ $program->starts_at ? \Illuminate\Support\Carbon::parse($program->starts_at)->format('d M Y, g:i A') : __('Today') }}</span>
        </div>
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
                    @if($q->question_type === 'rating_4')
                        <select name="answers[{{ $q->id }}]" @required($q->is_required)>
                            <option value="">{{ __('— Sila pilih skor penilaian —') }}</option>
                            <option value="4" @selected(old('answers.'.$q->id) === '4')>4 — Sangat Setuju</option>
                            <option value="3" @selected(old('answers.'.$q->id) === '3')>3 — Setuju</option>
                            <option value="2" @selected(old('answers.'.$q->id) === '2')>2 — Tidak Setuju</option>
                            <option value="1" @selected(old('answers.'.$q->id) === '1')>1 — Sangat Tidak Setuju</option>
                        </select>
                    @elseif($q->question_type === 'rating_5')
                        <select name="answers[{{ $q->id }}]" @required($q->is_required)>
                            <option value="">{{ __('— Sila pilih skor (1-5) —') }}</option>
                            <option value="5" @selected(old('answers.'.$q->id) === '5')>5 / 5 ★</option>
                            <option value="4" @selected(old('answers.'.$q->id) === '4')>4 / 5 ★</option>
                            <option value="3" @selected(old('answers.'.$q->id) === '3')>3 / 5 ★</option>
                            <option value="2" @selected(old('answers.'.$q->id) === '2')>2 / 5 ★</option>
                            <option value="1" @selected(old('answers.'.$q->id) === '1')>1 / 5 ★</option>
                        </select>
                    @else
                        <textarea name="answers[{{ $q->id }}]" rows="4" @required($q->is_required) placeholder="{{ __('Sila nyatakan ulasan / pandangan anda...') }}">{{ old('answers.'.$q->id) }}</textarea>
                    @endif
                </div>
            @endforeach
        @endif

        <div class="form-group">
            <label for="feedback_comments">{{ __('Comments / Suggestions') }}</label>
            <textarea id="feedback_comments" name="feedback_comments" rows="3" placeholder="{{ __('What can we improve for future events?') }}"></textarea>
        </div>
        @endif

        @if($program->latitude !== null && $program->longitude !== null)
            <div id="locationStatus" class="location-status" role="status">
                {{ __('Location permission is required. Please allow GPS access to submit attendance.') }}
            </div>
            <button class="btn-submit" id="attendanceSubmit" type="submit" disabled>{{ $program->questionnaire_enabled ? __('Check In & Submit Feedback') : __('Submit Attendance') }}</button>
        @else
            <div id="locationStatus" class="location-status ready" role="status">
                {{ __('GPS verification is not enabled for this program. You can submit attendance now.') }}
            </div>
            <button class="btn-submit" id="attendanceSubmit" type="submit">{{ $program->questionnaire_enabled ? __('Check In & Submit Feedback') : __('Submit Attendance') }}</button>
        @endif
    </form>
</main>

@if($program->latitude !== null && $program->longitude !== null)
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
@endif

</body>
</html>
