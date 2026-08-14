@extends('layouts.app')
@section('title', $program->title)
@section('header')<h2 style="margin:0;font-size:1.15rem;font-weight:800;">{{ __('Program Attendance') }}</h2>@endsection
@section('content')
<main style="max-width:900px;margin:0 auto;padding:1.5rem;">
<section class="card" style="padding:1.5rem;">
    <h1>{{ $program->title }}</h1><p>{{ $program->venue }} &middot; {{ $program->participation_points }} {{ __('participation points') }}</p>
    @if($errors->any())<div class="alert alert-danger">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
    @if($attendance)<div class="alert alert-success">{{ __('You already submitted attendance for this program.') }}</div>
    @else
    <div style="padding:1rem;border:1px solid var(--border);border-radius:12px;margin:1rem 0;"><strong>{{ $student->full_name }}</strong><div>{{ $student->matric_no }} &middot; {{ $student->program }}</div></div>
    <form method="post" action="{{ route('student.programs.attendance.store',$program->id) }}" id="studentProgramAttendance">@csrf
        <input type="hidden" name="latitude" id="paLat"><input type="hidden" name="longitude" id="paLng"><input type="hidden" name="location_accuracy_m" id="paAccuracy"><input type="hidden" name="location_captured_at" id="paCaptured">
        @if(!$program->questionnaire_enabled)<div class="alert alert-success">{{ __('This program uses attendance-only mode. No questionnaire is required.') }}</div>@endif
        @foreach($questions as $question)
        <div style="display:grid;gap:.4rem;margin-bottom:1rem;"><label style="font-weight:800;">{{ $question->question_text }} @if($question->is_required)<span style="color:#b42318">*</span>@endif</label>
            @if($question->question_type === 'rating_5')<select name="answers[{{ $question->id }}]" @required($question->is_required) style="padding:.8rem;border:1px solid var(--border);border-radius:10px;"><option value="">{{ __('Choose rating') }}</option>@for($i=5;$i>=1;$i--)<option value="{{ $i }}">{{ $i }} / 5</option>@endfor</select>
            @else<textarea name="answers[{{ $question->id }}]" rows="5" @required($question->is_required) style="padding:.8rem;border:1px solid var(--border);border-radius:10px;" placeholder="{{ __('Type your answer here...') }}">{{ old('answers.'.$question->id) }}</textarea>@endif
        </div>
        @endforeach
        <div id="paStatus" class="alert alert-warning">{{ __('Location permission is required before submission.') }}</div>
        <button id="paSubmit" class="btn btn-primary" type="submit" disabled>{{ __('Submit Attendance & Questionnaire') }}</button>
    </form>
    @endif
</section></main>
<script>
(() => { const status=document.getElementById('paStatus'),button=document.getElementById('paSubmit'); if(!status||!navigator.geolocation)return;
navigator.geolocation.getCurrentPosition(p=>{document.getElementById('paLat').value=p.coords.latitude;document.getElementById('paLng').value=p.coords.longitude;document.getElementById('paAccuracy').value=p.coords.accuracy;document.getElementById('paCaptured').value=new Date(p.timestamp).toISOString();status.textContent=`{{ __('Location captured') }} (${Math.round(p.coords.accuracy)}m)`;status.className='alert alert-success';button.disabled=false;},()=>{status.textContent='{{ __('Location access failed. Enable GPS permission and reload this page.') }}';status.className='alert alert-danger';},{enableHighAccuracy:true,timeout:15000,maximumAge:0}); })();
</script>
@endsection
