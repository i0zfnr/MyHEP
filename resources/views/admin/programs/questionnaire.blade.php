@extends('layouts.app')

@section('title', __('Questionnaire Builder - ').$program->title)

@push('styles')
<style>
.pmr {
    max-width: 1300px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}
.pmr-hero {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1.5rem;
    padding: 1.5rem 1.75rem;
    border-radius: 18px;
    background: var(--surface, #fff);
    border: 1px solid var(--border, #eadac8);
    box-shadow: 0 10px 28px rgba(36,26,18,0.06);
}
.pmr-hero h1 { margin: 0.2rem 0 0.25rem; font-size: 1.45rem; font-weight: 850; color: var(--text, #241d16); }
.pmr-hero p { margin: 0; font-size: 0.85rem; color: var(--text-muted, #746b62); }
.pmr-eyebrow { font-size: 0.72rem; font-weight: 850; letter-spacing: 0.08em; text-transform: uppercase; color: var(--pm-accent, #b99150); }

.pmr-actions {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    flex-wrap: wrap;
}
.pmr-btn {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 0.5rem !important;
    min-height: 42px !important;
    padding: 0.55rem 1.1rem !important;
    border-radius: 10px !important;
    border: 1px solid var(--border, #eadac8) !important;
    background: var(--surface, #fff) !important;
    color: var(--text, #241d16) !important;
    font-size: 0.86rem !important;
    font-weight: 750 !important;
    text-decoration: none !important;
    cursor: pointer !important;
    white-space: nowrap !important;
    transition: transform 0.15s ease, box-shadow 0.15s ease, background 0.15s ease !important;
}
.pmr-btn:hover {
    transform: translateY(-1px) !important;
    box-shadow: 0 6px 16px rgba(36,26,18,0.08) !important;
    background: var(--surface-hover, #fdfbf7) !important;
}
.pmr-btn.primary {
    background: var(--pm-accent, #b99150) !important;
    border-color: var(--pm-accent, #b99150) !important;
    color: #fff !important;
}
.pmr-btn.primary:hover {
    background: color-mix(in srgb, var(--pm-accent, #b99150) 88%, #000) !important;
}
.pmr-btn.public-checkin {
    background: #0284c7 !important;
    border-color: #0284c7 !important;
    color: #fff !important;
}
.pmr-btn.public-checkin:hover {
    background: #0369a1 !important;
}
.pmr-btn svg, .pmr svg {
    width: 16px !important;
    height: 16px !important;
    flex-shrink: 0 !important;
    stroke: currentColor !important;
    stroke-width: 2 !important;
    fill: none !important;
}

.pmr-card {
    background: var(--surface, #fff);
    border: 1px solid var(--border, #eadac8);
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: 0 4px 18px rgba(36,26,18,0.04);
}
.pmr-card h2 { margin: 0.2rem 0 0.4rem; font-size: 1.15rem; font-weight: 800; }
.pmr-card p.subtitle { margin: 0 0 1.25rem; font-size: 0.86rem; color: var(--text-muted, #746b62); line-height: 1.45; }

.pmr-mode-panel {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    padding: 1.1rem 1.25rem;
    border-radius: 12px;
    background: color-mix(in srgb, var(--pm-accent, #b99150) 6%, var(--surface, #fff));
    border: 1px solid color-mix(in srgb, var(--pm-accent, #b99150) 20%, var(--border, #eadac8));
}
.pmr-mode-panel select {
    width: 100%;
    padding: 9px 12px;
    border-radius: 9px;
    border: 1px solid var(--border, #eadac8);
    font-size: 0.92rem;
    background: #fff;
}
.pmr-mode-actions {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    margin-top: 0.5rem;
    flex-wrap: wrap;
}

.pmr-ai-box {
    margin-top: 1.25rem;
    padding: 1.25rem;
    border-radius: 14px;
    background: linear-gradient(135deg, color-mix(in srgb, #6366f1 8%, var(--surface, #fff)), color-mix(in srgb, var(--pm-accent, #b99150) 8%, var(--surface, #fff)));
    border: 1px solid color-mix(in srgb, #6366f1 24%, var(--border, #eadac8));
}
.pmr-ai-grid {
    display: grid;
    grid-template-columns: 1.5fr 1fr;
    gap: 12px;
}
.pmr-ai-grid label {
    font-size: 0.75rem;
    font-weight: 800;
    text-transform: uppercase;
    color: var(--text-muted, #746b62);
    display: block;
    margin-bottom: 4px;
}
.pmr-ai-grid select {
    width: 100%;
    padding: 9px 12px;
    border-radius: 8px;
    border: 1px solid var(--border, #eadac8);
    background: #fff;
    font-size: 0.88rem;
}

.pmr-q-item {
    padding: 1.15rem;
    border-radius: 12px;
    border: 1px solid var(--border, #eadac8);
    background: color-mix(in srgb, var(--surface, #fff) 96%, var(--pm-accent, #b99150));
    margin-bottom: 12px;
    transition: transform 150ms ease, box-shadow 150ms ease;
}
.pmr-q-item:hover {
    box-shadow: 0 4px 14px rgba(36,26,18,0.06);
}
.pmr-q-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.75rem;
}
.pmr-q-title {
    display: flex;
    align-items: center;
    gap: 0.45rem;
    font-size: 0.88rem;
    font-weight: 800;
}
.pmr-q-number {
    display: inline-grid;
    place-items: center;
    width: 24px;
    height: 24px;
    border-radius: 7px;
    background: var(--pm-accent, #b99150);
    color: #fff;
    font-size: 0.75rem;
    font-weight: 900;
}
.pmr-q-field {
    display: block;
    margin-bottom: 0.65rem;
}
.pmr-q-field span {
    display: block;
    font-size: 0.72rem;
    font-weight: 800;
    color: var(--text-muted, #746b62);
    margin-bottom: 4px;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}
.pmr-q-field input, .pmr-q-field select {
    width: 100%;
    padding: 9px 12px;
    border: 1px solid var(--border, #eadac8);
    border-radius: 8px;
    font-size: 0.9rem;
    background: #fff;
    color: var(--text, #241d16);
    box-sizing: border-box;
}
.pmr-q-required {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.82rem;
    font-weight: 700;
    color: var(--text, #241d16);
    cursor: pointer;
    margin-top: 4px;
}
.pmr-remove {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    min-height: 32px;
    padding: 0.3rem 0.6rem;
    border: 0;
    border-radius: 8px;
    background: transparent;
    color: var(--se-danger, #b42318);
    font: inherit;
    font-size: 0.76rem;
    font-weight: 800;
    cursor: pointer;
}
.pmr-remove:hover {
    background: color-mix(in srgb, var(--se-danger, #b42318) 8%, transparent);
}
.pmr-remove svg {
    width: 14px !important;
    height: 14px !important;
    fill: none;
    stroke: currentColor;
    stroke-width: 2;
}

.pmr-published-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.65rem 1rem;
    border-radius: 10px;
    background: color-mix(in srgb, #21835a 12%, var(--surface, #fff));
    color: #187048;
    border: 1px solid color-mix(in srgb, #21835a 30%, var(--border, #eadac8));
    font-size: 0.84rem;
    font-weight: 800;
}
.pmr-published-badge::before {
    content: '';
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: currentColor;
    box-shadow: 0 0 0 3px color-mix(in srgb, currentColor 18%, transparent);
}

@media (max-width: 768px) {
    .pmr-hero {
        flex-direction: column;
        align-items: stretch;
    }
    .pmr-ai-grid {
        grid-template-columns: 1fr;
    }
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
            <a class="pmr-btn" href="{{ route('admin.programs.operations', $program->id) }}">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 12H5m7 7-7-7 7-7"/></svg>
                {{ __('Back to Operations') }}
            </a>
            <a class="pmr-btn public-checkin" href="{{ $publicCheckinUrl }}" target="_blank" rel="noopener">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 5h5v5"/><path d="m10 14 9-9"/><path d="M19 13v5a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1h5"/></svg>
                {{ __('Open Public Check-in') }}
            </a>
        </div>
    </header>

    <!-- 1. Participation Mode Configuration -->
    <section class="pmr-card">
        <span class="pmr-eyebrow">{{ __('MODE SETTING') }}</span>
        <h2>{{ __('Participation Mode') }}</h2>
        <p class="subtitle">{{ __('Choose whether participants are required to complete a questionnaire upon checking in or record attendance only.') }}</p>

        <form method="post" action="{{ route('admin.programs.questionnaire-setting.update', $program->id) }}" class="pmr-mode-panel">
            @csrf @method('put')
            <label for="participationMode" style="font-size: 0.8rem; font-weight: 800; text-transform: uppercase; color: var(--text-muted, #746b62);">{{ __('Selected Mode') }}</label>
            <select id="participationMode" name="questionnaire_enabled" data-saved-mode="{{ $program->questionnaire_enabled ? '1' : '0' }}" @disabled($program->attendance_status === 'open')>
                <option value="1" @selected($program->questionnaire_enabled)>{{ __('Attendance + Questionnaire (Students answer questions during QR check-in)') }}</option>
                <option value="0" @selected(!$program->questionnaire_enabled)>{{ __('Attendance Only (Fast QR check-in without questionnaire)') }}</option>
            </select>
            <div class="pmr-mode-actions">
                <button class="pmr-btn primary" type="submit" @disabled($program->attendance_status === 'open')>{{ __('Save Mode Selection') }}</button>
                @if($program->attendance_status === 'open')
                    <p class="pmr-mode-status">{{ __('Close attendance in the operations page before changing this mode.') }}</p>
                @endif
                <p id="participationModeNotice" class="pmr-mode-status" style="display:none;color:#8a5a13;">{{ __('Save this selection to apply the chosen questionnaire mode.') }}</p>
            </div>
        </form>

        <div id="attendanceOnlyMessage" class="pmr-attendance-mode" style="margin-top: 1rem;" @if($program->questionnaire_enabled) hidden @endif>
            <span class="pmr-attendance-mode__icon" aria-hidden="true">&#10003;</span>
            <div>
                <strong>{{ __('Attendance-Only Mode Active') }}</strong>
                <p>{{ __('Students can check in directly using their student account or the public QR scanner. No questionnaire is required for this program.') }}</p>
            </div>
        </div>
    </section>

    <!-- 2. Interactive Questionnaire Builder Workspace -->
    <div id="questionnaireBuilderContent" @if(!$program->questionnaire_enabled) hidden @endif>

        <!-- AI Assistant & Official Template Card -->
        <section class="pmr-card" style="margin-bottom: 1.25rem;">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;">
                <div>
                    <span class="pmr-eyebrow" style="color: #6366f1;">{{ __('TEMPLAT RASMI & PENJANA AI') }}</span>
                    <h2>{{ __('Jana Soalan Berpandukan Templat Borang SA-04') }}</h2>
                </div>
                <button type="button" class="pmr-btn" id="btnLoadSa04" style="background:color-mix(in srgb,var(--pm-accent) 10%,#fff);border-color:var(--pm-accent);color:var(--pm-accent-strong);font-weight:800;">
                    <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                    {{ __('Muat Templat Rasmi SA-04(1) (1-Klik)') }}
                </button>
            </div>
            <p class="subtitle">{{ __('Gunakan templat rasmi Borang SA-04 Politeknik Besut (Penilaian Penceramah, Pelaksanaan, Keberkesanan & Ulasan) atau minta AI menjana soalan khusus mengikut kertas kerja program anda.') }}</p>

            <div class="pmr-ai-box">
                <div class="pmr-ai-grid">
                    <div>
                        <label for="aiFocus">{{ __('Pilihan Templat / Fokus AI') }}</label>
                        <select id="aiFocus">
                            <option value="official_sa04_1" selected>{{ __('📋 Templat Rasmi SA-04(1) — Penilaian Peserta (10 Item Skala 1-4 + Ulasan)') }}</option>
                            <option value="official_sa04_3">{{ __('📋 Templat Rasmi SA-04(3) — Penilaian Keberkesanan Staf (7 Item Skala 0-5 + Komen)') }}</option>
                            <option value="ai_tailored_sa04">{{ __('✨ AI: Jana Soalan Khusus Mengikut Kertas Kerja Program (Format SA-04)') }}</option>
                            <option value="satisfaction">{{ __('✨ AI: Fokus Kepuasan & Logistik Program') }}</option>
                            <option value="effectiveness">{{ __('✨ AI: Fokus Keberkesanan & Hasil Pembelajaran') }}</option>
                        </select>
                    </div>
                    <div>
                        <label for="aiCount">{{ __('Bilangan Soalan') }}</label>
                        <select id="aiCount">
                            <option value="5">5 {{ __('Soalan') }}</option>
                            <option value="8">8 {{ __('Soalan') }}</option>
                            <option value="11" selected>11 {{ __('Soalan (Lengkap SA-04)') }}</option>
                        </select>
                    </div>
                </div>
                <button type="button" class="pmr-btn primary" id="btnGenerateAi" style="width: 100%; margin-top: 14px; justify-content: center;">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m12 3-1.9 5.8a2 2 0 0 1-1.3 1.3L3 12l5.8 1.9a2 2 0 0 1 1.3 1.3L12 21l1.9-5.8a2 2 0 0 1 1.3-1.3L21 12l-5.8-1.9a2 2 0 0 1-1.3-1.3L12 3z"/></svg>
                    {{ __('Jana / Muat Soalan Mengikut Templat Dipilih') }}
                </button>
            </div>
        </section>

        <!-- Question List & Form -->
        <section class="pmr-card">
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; margin-bottom: 0.5rem;">
                <div>
                    <span class="pmr-eyebrow">{{ __('SENARAI SOALAN') }}</span>
                    <h2>{{ __('Susun & Kemaskini Soalan Kaji Selidik') }}</h2>
                </div>
                @if($survey && $survey->status === 'published')
                    <span class="pmr-published-badge">{{ __('Live & Diterbitkan Kepada Peserta') }}</span>
                @endif
            </div>
            <p class="subtitle">{{ __('Tetapkan soalan yang akan dipaparkan kepada pelajar / peserta semasa mengimbas kod QR kehadiran.') }}</p>

            <form method="post" action="{{ route('admin.programs.survey.save', $program->id) }}">
                @csrf
                <div style="margin-bottom: 1.25rem;">
                    <label style="font-weight: 800; font-size: 0.85rem; display: block; margin-bottom: 4px;">{{ __('Tajuk Kaji Selidik / Borang') }}</label>
                    <input name="title" required value="{{ old('title', $survey->title ?? __('Borang Penilaian Program [SA-04(1)] - ').$program->title) }}" style="width: 100%; padding: 10px 14px; border-radius: 10px; border: 1px solid var(--border, #eadac8); font-size: 0.95rem; box-sizing: border-box;">
                </div>

                <div id="questionsContainer">
                    @forelse($questions as $index => $q)
                        <div class="pmr-q-item">
                            <div class="pmr-q-head">
                                <strong class="pmr-q-title"><span class="pmr-q-number">{{ $index + 1 }}</span>{{ __('Soalan') }}</strong>
                                <button class="pmr-remove" type="button" onclick="this.closest('.pmr-q-item').remove()"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3m-8 0 1 13h8l1-13M10 11v5m4-5v5"/></svg>{{ __('Padam') }}</button>
                            </div>
                            <label class="pmr-q-field"><span>{{ __('Teks Soalan / Kriteria Penilaian') }}</span><input name="questions[{{ $index }}][question_text]" value="{{ $q->question_text }}" required placeholder="{{ __('Masukkan teks soalan') }}"></label>
                            <label class="pmr-q-field"><span>{{ __('Jenis Jawapan / Skala') }}</span><select name="questions[{{ $index }}][question_type]">
                                <option value="rating_4" @selected($q->question_type === 'rating_4')>{{ __('Skala Likert 1-4 (Borang SA-04: Sangat Tidak Setuju - Sangat Setuju)') }}</option>
                                <option value="rating_5" @selected($q->question_type === 'rating_5')>{{ __('Skala 1-5 Bintang (Sangat Rendah - Sangat Cemerlang)') }}</option>
                                <option value="text" @selected($q->question_type === 'text')>{{ __('Jawapan Bertulis / Ulasan (Long Written Answer)') }}</option>
                            </select></label>
                            <label class="pmr-q-required">
                                <input type="hidden" name="questions[{{ $index }}][is_required]" value="0">
                                <input type="checkbox" name="questions[{{ $index }}][is_required]" value="1" @checked($q->is_required) style="width:auto;margin:0;">
                                {{ __('Soalan wajib dijawab') }}
                            </label>
                        </div>
                    @empty
                        <div class="pmr-q-item">
                            <div class="pmr-q-head"><strong class="pmr-q-title"><span class="pmr-q-number">1</span>{{ __('Soalan') }}</strong></div>
                            <label class="pmr-q-field"><span>{{ __('Teks Soalan / Kriteria Penilaian') }}</span><input type="text" name="questions[0][question_text]" value="Objektif latihan / program tercapai." required></label>
                            <label class="pmr-q-field"><span>{{ __('Jenis Jawapan / Skala') }}</span><select name="questions[0][question_type]">
                                <option value="rating_4" selected>{{ __('Skala Likert 1-4 (Borang SA-04: Sangat Tidak Setuju - Sangat Setuju)') }}</option>
                                <option value="rating_5">{{ __('Skala 1-5 Bintang (Sangat Rendah - Sangat Cemerlang)') }}</option>
                                <option value="text">{{ __('Jawapan Bertulis / Ulasan (Long Written Answer)') }}</option>
                            </select></label>
                            <label class="pmr-q-required">
                                <input type="hidden" name="questions[0][is_required]" value="0">
                                <input type="checkbox" name="questions[0][is_required]" value="1" checked style="width:auto;margin:0;">
                                {{ __('Soalan wajib dijawab') }}
                            </label>
                        </div>
                    @endforelse
                </div>

                <div style="display: flex; gap: 10px; margin-top: 1.25rem; flex-wrap: wrap;">
                    <button type="button" class="pmr-btn" onclick="addQuestionRow()">+ {{ __('Tambah Soalan') }}</button>
                    <button type="submit" class="pmr-btn primary">{{ __('Simpan Draf Soal Selidik') }}</button>
                </div>
            </form>

            @if($survey && $survey->status !== 'published')
                <form method="post" action="{{ route('admin.programs.survey.publish', $program->id) }}" style="margin-top: 14px; border-top: 1px solid var(--border, #eadac8); padding-top: 14px;">
                    @csrf
                    <button type="submit" class="pmr-btn primary" style="width: 100%; justify-content: center; min-height: 44px; font-size: 0.95rem;">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                        {{ __('Terbitkan Soal Selidik Kepada Peserta') }}
                    </button>
                </form>
            @endif
        </section>

    </div>
</main>

<script>
let questionCounter = {{ count($questions) ?: 1 }};

const officialSa04Questions = [
    { text: 'Objektif latihan / program tercapai.', type: 'rating_4', required: true },
    { text: 'Kandungan latihan / pengisian adalah sesuai.', type: 'rating_4', required: true },
    { text: 'Penyampaian penceramah / fasilitator yang baik dan berkesan.', type: 'rating_4', required: true },
    { text: 'Penggunaan alat bantuan mengajar / modul dengan berkesan.', type: 'rating_4', required: true },
    { text: 'Suasana tempat latihan / lokasi program yang sesuai dan kondusif.', type: 'rating_4', required: true },
    { text: 'Perancangan dan pelaksanaan program telah dibuat dengan lancar.', type: 'rating_4', required: true },
    { text: 'Masa yang diperuntukkan bagi setiap modul / slot adalah sesuai.', type: 'rating_4', required: true },
    { text: 'Meningkatkan pengetahuan dan pemahaman peserta.', type: 'rating_4', required: true },
    { text: 'Lebih berkeyakinan menjalankan tugas berkaitan / mengaplikasi apa yang dipelajari.', type: 'rating_4', required: true },
    { text: 'Pada keseluruhannya latihan / program ini adalah berjaya dan bermanfaat.', type: 'rating_4', required: true },
    { text: 'Kesediaan untuk berkongsi ilmu yang diperolehi berkaitan latihan (Sila nyatakan YA atau TIDAK berserta ulasan jika TIDAK).', type: 'text', required: false }
];

function escapeQuestionValue(value) {
    return String(value).replace(/[&<>'"]/g, character => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;'
    })[character]);
}

function addQuestionRow(text = '', type = 'rating_4', required = true) {
    const container = document.getElementById('questionsContainer');
    const div = document.createElement('div');
    div.className = 'pmr-q-item';
    div.innerHTML = `
        <div class="pmr-q-head">
            <strong class="pmr-q-title"><span class="pmr-q-number">${questionCounter + 1}</span>Soalan</strong>
            <button class="pmr-remove" type="button" onclick="this.closest('.pmr-q-item').remove()"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3m-8 0 1 13h8l1-13M10 11v5m4-5v5"/></svg>Padam</button>
        </div>
        <label class="pmr-q-field"><span>Teks Soalan / Kriteria Penilaian</span><input type="text" name="questions[${questionCounter}][question_text]" value="${escapeQuestionValue(text)}" required placeholder="Masukkan teks soalan"></label>
        <label class="pmr-q-field"><span>Jenis Jawapan / Skala</span><select name="questions[${questionCounter}][question_type]">
            <option value="rating_4" ${type === 'rating_4' ? 'selected' : ''}>Skala Likert 1-4 (Borang SA-04: Sangat Tidak Setuju - Sangat Setuju)</option>
            <option value="rating_5" ${type === 'rating_5' ? 'selected' : ''}>Skala 1-5 Bintang (Sangat Rendah - Sangat Cemerlang)</option>
            <option value="text" ${type === 'text' ? 'selected' : ''}>Jawapan Bertulis / Ulasan (Long Written Answer)</option>
        </select></label>
        <label class="pmr-q-required">
            <input type="hidden" name="questions[${questionCounter}][is_required]" value="0">
            <input type="checkbox" name="questions[${questionCounter}][is_required]" value="1" ${required ? 'checked' : ''}>
            Soalan wajib dijawab
        </label>
    `;
    container.appendChild(div);
    questionCounter++;
}

document.getElementById('btnLoadSa04')?.addEventListener('click', () => {
    const container = document.getElementById('questionsContainer');
    container.innerHTML = '';
    questionCounter = 0;
    officialSa04Questions.forEach(q => {
        addQuestionRow(q.text, q.type, q.required);
    });
});

document.getElementById('btnGenerateAi')?.addEventListener('click', async () => {
    const focus = document.getElementById('aiFocus').value;
    const count = document.getElementById('aiCount').value;
    const btn = document.getElementById('btnGenerateAi');

    btn.disabled = true;
    btn.innerText = '{{ __('AI sedang menjana soalan mengikut templat...') }}';

    try {
        const response = await fetch('{{ route("admin.programs.ai-questionnaire", $program->id) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ focus: focus, question_count: count })
        });

        const data = await response.json();
        if (data.success && Array.isArray(data.questions)) {
            const container = document.getElementById('questionsContainer');
            container.innerHTML = '';
            questionCounter = 0;

            data.questions.forEach(q => {
                addQuestionRow(q.question_text, q.question_type || 'rating_4', q.is_required !== false);
            });
        }
    } catch (e) {
        alert('{{ __('Gagal menjana secara automatik. Memuatkan soalan templat standard.') }}');
        document.getElementById('btnLoadSa04')?.click();
    } finally {
        btn.disabled = false;
        btn.innerHTML = `<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m12 3-1.9 5.8a2 2 0 0 1-1.3 1.3L3 12l5.8 1.9a2 2 0 0 1 1.3 1.3L12 21l1.9-5.8a2 2 0 0 1 1.3-1.3L21 12l-5.8-1.9a2 2 0 0 1-1.3-1.3L12 3z"/></svg> {{ __('Jana / Muat Soalan Mengikut Templat Dipilih') }}`;
    }
});

const participationMode = document.getElementById('participationMode');
const questionnaireBuilderContent = document.getElementById('questionnaireBuilderContent');
const attendanceOnlyMessage = document.getElementById('attendanceOnlyMessage');
const participationModeNotice = document.getElementById('participationModeNotice');

function syncParticipationMode() {
    if (!participationMode) return;
    const questionnaireSelected = participationMode.value === '1';
    const selectionSaved = participationMode.value === participationMode.dataset.savedMode;
    if (questionnaireBuilderContent) questionnaireBuilderContent.hidden = !questionnaireSelected || !selectionSaved;
    if (attendanceOnlyMessage) attendanceOnlyMessage.hidden = questionnaireSelected || !selectionSaved;
    if (participationModeNotice) participationModeNotice.style.display = selectionSaved ? 'none' : 'block';
}

participationMode?.addEventListener('change', syncParticipationMode);
syncParticipationMode();
</script>
@endsection
