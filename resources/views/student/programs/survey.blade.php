@extends('layouts.app')

@section('title', __('Borang Soal Selidik Program') . ' - ' . $program->title)

@section('header')
    <h2 style="margin:0;font-size:1.15rem;font-weight:800;">{{ __('Soal Selidik Program') }}</h2>
@endsection

@section('content')
<main class="student-survey-page" style="max-width: 860px; margin: 0 auto; padding: 1.5rem 1rem;">
    @if(session('success'))
        <div class="alert alert-success" style="border-radius: 14px; margin-bottom: 1.25rem;">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger" style="border-radius: 14px; margin-bottom: 1.25rem;">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <section class="card" style="padding: 1.75rem; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.06);">
        <div style="border-bottom: 1px solid var(--border); padding-bottom: 1.25rem; margin-bottom: 1.5rem;">
            <div style="display:inline-flex; align-items:center; gap:0.4rem; font-size:0.75rem; font-weight:800; text-transform:uppercase; letter-spacing:0.08em; color:var(--primary); background:rgba(212,175,55,0.12); padding:0.3rem 0.75rem; border-radius:999px; margin-bottom:0.5rem;">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                <span>{{ __('Borang SA-04(1) (P00) (24-12-24)') }}</span>
            </div>
            <h1 style="font-size: 1.6rem; font-weight: 900; margin: 0 0 0.4rem; color: var(--text);">{{ $program->title }}</h1>
            <p style="margin: 0 0 0.75rem; font-size: 0.88rem; color: var(--text-muted);">
                {{ $program->venue ?: __('Tempat Acara') }} &bull; {{ $program->starts_at ? \Carbon\Carbon::parse($program->starts_at)->format('d M Y') : '' }}
            </p>
            <p style="margin: 0; font-size: 0.84rem; color: var(--text-secondary); line-height: 1.45;">
                {{ __('Sila maklumkan pandangan anda terhadap program latihan yang telah diikuti ini dengan menandakan pilihan pada ruangan di bawah berpandukan kepada skor berikut:') }}
            </p>
            <div style="display: flex; gap: 1rem; flex-wrap: wrap; margin-top: 0.65rem; font-size: 0.8rem; font-weight: 750; color: var(--text);">
                <span style="background: var(--bg-alt, #faf7f2); padding: 0.25rem 0.6rem; border-radius: 6px; border: 1px solid var(--border);"><strong>1:</strong> Sangat Tidak Setuju</span>
                <span style="background: var(--bg-alt, #faf7f2); padding: 0.25rem 0.6rem; border-radius: 6px; border: 1px solid var(--border);"><strong>2:</strong> Tidak Setuju</span>
                <span style="background: var(--bg-alt, #faf7f2); padding: 0.25rem 0.6rem; border-radius: 6px; border: 1px solid var(--border);"><strong>3:</strong> Setuju</span>
                <span style="background: var(--bg-alt, #faf7f2); padding: 0.25rem 0.6rem; border-radius: 6px; border: 1px solid var(--border);"><strong>4:</strong> Sangat Setuju</span>
            </div>
        </div>

        <div style="background: color-mix(in srgb, var(--primary) 6%, var(--surface, #fff)); border: 1px solid var(--border); border-radius: 14px; padding: 1rem 1.25rem; margin-bottom: 1.75rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem;">
            <div>
                <strong style="font-size: 0.95rem; display: block; color: var(--text);">{{ $student->full_name }}</strong>
                <span style="font-size: 0.82rem; color: var(--text-muted);">{{ $student->matric_no }} &bull; {{ $student->program }}</span>
            </div>
            @if($alreadySubmitted)
                <span style="font-size: 0.78rem; font-weight: 800; padding: 0.35rem 0.85rem; border-radius: 999px; background: rgba(16, 185, 129, 0.12); color: #059669; border: 1px solid rgba(16, 185, 129, 0.25);">
                    {{ __('Sudah Dihantar (Boleh Kemaskini)') }}
                </span>
            @endif
        </div>

        @if($questions->isEmpty())
            <div style="text-align:center; padding: 2.5rem 1rem; color: var(--text-muted);">
                <p>{{ __('Tiada soalan dalam borang soal selidik ini buat masa ini.') }}</p>
                <a href="{{ route('student.programs.index') }}" class="btn btn-secondary" style="margin-top: 1rem;">{{ __('Kembali ke Program') }}</a>
            </div>
        @else
            <form method="post" action="{{ route('student.programs.survey.store', $program->id) }}">
                @csrf
                <div style="display: flex; flex-direction: column; gap: 1.4rem;">
                    @foreach($questions as $index => $question)
                        <div style="background: var(--surface, #fff); border: 1px solid var(--border); border-radius: 14px; padding: 1.25rem; transition: border-color 0.2s;">
                            <label style="display: block; font-weight: 800; font-size: 0.96rem; margin-bottom: 0.75rem; color: var(--text);">
                                <span style="color: var(--primary); margin-right: 0.35rem;">{{ $index + 1 }}.</span>
                                {{ $question->question_text }}
                                @if($question->is_required)
                                    <span style="color: #ef4444;">*</span>
                                @endif
                            </label>

                            @if($question->question_type === 'rating_4')
                                <div class="student-survey-options" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 0.6rem;">
                                    @php
                                        $options = [
                                            '4' => '4 - Sangat Setuju',
                                            '3' => '3 - Setuju',
                                            '2' => '2 - Tidak Setuju',
                                            '1' => '1 - Sangat Tidak Setuju'
                                        ];
                                    @endphp
                                    @foreach($options as $val => $lbl)
                                        <label class="student-survey-option" style="display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem 0.9rem; border: 1px solid var(--border); border-radius: 10px; cursor: pointer; font-size: 0.84rem; font-weight: 600; background: var(--bg-alt, #faf7f2);">
                                            <input type="radio" name="answers[{{ $question->id }}]" value="{{ $val }}" @required($question->is_required) {{ old('answers.'.$question->id, $existingAnswers[(int) $question->id] ?? null) == $val ? 'checked' : '' }}>
                                            <span>{{ $lbl }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            @elseif($question->question_type === 'rating_5')
                                <div class="student-survey-rating" style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                    @for($i = 1; $i <= 5; $i++)
                                        <label class="student-survey-rating-option" style="flex: 1; min-width: 60px; text-align: center; padding: 0.75rem 0.5rem; border: 1px solid var(--border); border-radius: 10px; cursor: pointer; font-size: 0.9rem; font-weight: 700; background: var(--bg-alt, #faf7f2);">
                                            <input type="radio" name="answers[{{ $question->id }}]" value="{{ $i }}" @required($question->is_required) {{ old('answers.'.$question->id, $existingAnswers[(int) $question->id] ?? null) == $i ? 'checked' : '' }} style="display: block; margin: 0 auto 0.35rem;">
                                            <span>{{ $i }} ★</span>
                                        </label>
                                    @endfor
                                </div>
                            @else
                                <textarea name="answers[{{ $question->id }}]" rows="3" @required($question->is_required) style="width: 100%; box-sizing: border-box; padding: 0.85rem; border: 1px solid var(--border); border-radius: 10px; font-family: inherit; font-size: 0.88rem;" placeholder="{{ __('Sila nyatakan ulasan / pandangan anda...') }}">{{ old('answers.'.$question->id, $existingAnswers[(int) $question->id] ?? '') }}</textarea>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div style="display: flex; gap: 1rem; align-items: center; justify-content: flex-end; margin-top: 2rem;">
                    <a href="{{ route('student.programs.index') }}" class="btn btn-secondary" style="padding: 0.75rem 1.4rem;">{{ __('Batal') }}</a>
                    <button type="submit" class="btn btn-primary" style="padding: 0.75rem 1.8rem; font-weight: 800;">
                        {{ $alreadySubmitted ? __('Kemaskini Soal Selidik') : __('Hantar Soal Selidik') }}
                    </button>
                </div>
            </form>
        @endif
    </section>
</main>
@endsection
