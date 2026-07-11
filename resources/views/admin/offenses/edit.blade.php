@extends('layouts.app')

@section('title', __('Kemaskini Kesalahan'))

@push('styles')
<style>
    .wrap { max-width: 1000px; margin: 0 auto; }
    .card { background:#fff; border:1px solid #ede4d9; border-radius:12px; margin-bottom:16px; }
    .card h2 { margin:0; padding:14px 16px; border-bottom:1px solid #ede4d9; font-size:16px; }
    .card .body { padding:16px; }
    label { font-size:13px; font-weight:600; color:#7a6555; display:block; margin-bottom:6px; }
    input, select, textarea { width:100%; border:1px solid #e5d8c8; border-radius:8px; padding:9px 10px; font-size:14px; }
    .grid { display:grid; grid-template-columns:1fr; gap:12px; }
    @media (min-width:900px) { .grid-2 { grid-template-columns:1fr 1fr; } .grid-3 { grid-template-columns:1fr 1fr 1fr; } }
    .rule-row { border:1px solid #ede4d9; border-radius:10px; padding:10px 12px; margin-bottom:10px; }
    .rule-top { display:grid; grid-template-columns:18px minmax(0, 1fr); gap:10px; align-items:start; }
    .rule-top input[type="checkbox"] { width:16px !important; height:16px; margin:2px 0 0; padding:0; justify-self:start; }
    .rule-top label { min-width:0; line-height:1.45; }
    .rule-note { margin-top:10px; display:none; }
    .rule-row.show-note .rule-note { display:block; }
    .actions { display:flex; gap:10px; flex-wrap:wrap; }
    .btn { display:inline-block; border:1px solid #cbb9a4; background:#fff; color:#8a7362; border-radius:8px; padding:9px 14px; text-decoration:none; font-weight:600; font-size:14px; cursor:pointer; }
    .btn-primary { background:linear-gradient(135deg,#A48D78,#CBB9A4); color:#fff; border:none; }
    .error { margin-bottom:12px; background:#fef2f2; border:1px solid #fecaca; color:#991b1b; border-radius:8px; padding:10px; font-size:13px; }
    .rules-toolbar { display:grid; grid-template-columns:1fr; gap:8px; margin-bottom:12px; }
    @media (min-width:900px) { .rules-toolbar { grid-template-columns:1.2fr auto auto auto; align-items:center; } }
    .rules-toolbar input[type="text"] { width:100%; }
    .rules-selected-only { display:flex; align-items:center; gap:6px; color:#7a6555; font-size:13px; }
    .rules-selected-only input { width:auto; }
    .rules-selected-count { font-size:12px; color:#7a6555; }
    .rules-list {
        max-height: 52vh;
        overflow: auto;
        padding-right: 4px;
    }
    @media (max-width: 680px) {
        .rules-list { max-height: 42vh; }
    }
        /* Admin UX Identity v2 */
    :root {
        --admin-ink: #241a12;
        --admin-muted: #7b6757;
        --admin-line: #eadfce;
        --admin-soft: #f8f2ea;
        --admin-accent: #8f6f52;
        --admin-accent-2: #c7a98b;
        --admin-glow: rgba(143, 111, 82, 0.18);
    }
    body {
        background:
            radial-gradient(1200px 480px at -10% -15%, #efe3d6 0%, transparent 55%),
            radial-gradient(900px 360px at 110% -10%, #f4eadf 0%, transparent 52%),
            linear-gradient(180deg, #faf7f2 0%, #f6f1ea 100%);
    }
    .wrap {
        width: min(1180px, 100%);
        position: relative;
        isolation: isolate;
    }
    .wrap > * + * {
        margin-top: 1rem;
    }
    .card,
    .panel {
        border: 1px solid var(--admin-line);
        border-radius: 16px;
        background: linear-gradient(180deg, #fff 0%, #fffdfa 100%);
        box-shadow:
            0 1px 2px rgba(36, 26, 18, 0.07),
            0 10px 26px rgba(61, 46, 34, 0.06);
        overflow: hidden;
        transition: transform 220ms ease, box-shadow 220ms ease, border-color 220ms ease;
    }
    .card:hover,
    .panel:hover {
        transform: translateY(-2px);
        border-color: #dfccb6;
        box-shadow:
            0 4px 14px rgba(36, 26, 18, 0.10),
            0 18px 34px rgba(61, 46, 34, 0.10);
    }
    .head,
    .card h2 {
        position: relative;
        border-bottom: 1px solid var(--admin-line);
        background:
            linear-gradient(180deg, #fff 0%, #fbf5ee 100%);
    }
    .head::before,
    .card h2::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: linear-gradient(180deg, var(--admin-accent) 0%, var(--admin-accent-2) 100%);
    }
    .head h1,
    .card h2 {
        color: var(--admin-ink);
        letter-spacing: 0.01em;
    }
    .btn {
        border-radius: 10px;
        border: 1px solid #ceb79f;
        background: linear-gradient(180deg, #ffffff 0%, #f9f3ec 100%);
        color: #6e5745;
        font-weight: 700;
        transition: transform 170ms ease, box-shadow 170ms ease, background-color 170ms ease, border-color 170ms ease, color 170ms ease;
    }
    .btn:hover {
        transform: translateY(-1px);
        border-color: #bb9c7d;
        color: #5d4737;
        box-shadow: 0 8px 18px rgba(98, 74, 53, 0.14);
    }
    .btn:focus-visible {
        outline: none;
        box-shadow: 0 0 0 4px var(--admin-glow);
    }
    .btn-primary {
        border-color: #7f6249 !important;
        background: linear-gradient(135deg, #8f6f52 0%, #c0a183 100%) !important;
        color: #fff !important;
    }
    .btn-primary:hover {
        border-color: #6f533e !important;
        background: linear-gradient(135deg, #7a5e46 0%, #b08f70 100%) !important;
    }
    input,
    select,
    textarea {
        border-color: #dfceb9 !important;
        background: #fffdfb;
        color: var(--admin-ink);
        transition: border-color 150ms ease, box-shadow 150ms ease, background-color 150ms ease;
    }
    input::placeholder,
    textarea::placeholder {
        color: #9e8a78;
    }
    input:focus,
    select:focus,
    textarea:focus {
        border-color: #b69372 !important;
        box-shadow: 0 0 0 4px rgba(182, 147, 114, 0.19);
        outline: none;
        background: #fff;
    }
    .filters {
        background: linear-gradient(180deg, #fffdfb 0%, #faf4ed 100%);
        border-top: 1px solid #efe4d8;
        border-bottom: 1px solid #efe4d8;
    }
    table {
        width: 100%;
    }
    th {
        background: #f9f1e8 !important;
        color: #7b6757 !important;
        letter-spacing: 0.06em;
    }
    table tbody tr {
        transition: background-color 140ms ease;
    }
    table tbody tr:hover {
        background: #fcf7f1;
    }
    .ok,
    .msg-ok {
        border-radius: 12px;
        border-color: #b8e5c7 !important;
    }
    .err,
    .error,
    .msg-err {
        border-radius: 12px;
    }
    @media (max-width: 980px) {
        .head {
            align-items: flex-start;
        }
        .head > div,
        .head form {
            width: 100%;
        }
        .head .btn {
            width: auto;
        }
        .stats {
            grid-template-columns: 1fr !important;
        }
        th,
        td {
            font-size: 12px !important;
            padding: 9px 10px !important;
        }
    }</style>
@endpush

@section('header')
    <h2 style="margin:0;font-size:1.1rem;font-weight:700;color:#2d1f14;">{{ __('Kemaskini Kesalahan Pelajar') }}</h2>
@endsection

@section('content')
<div class="wrap">
    @if ($errors->any())
        <div class="error">@foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
    @endif

    <div id="ajax_form_feedback" class="error" style="display:none;"></div>

    <form id="offense_form" method="POST" action="{{ route('admin.offenses.update', $offense->id) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="card">
            <h2>{{ __('Maklumat Kesalahan') }}</h2>
            <div class="body">
                <div class="grid grid-2">
                    <div>
                        <label for="student_id">{{ __('Pelajar') }}</label>
                        <select name="student_id" id="student_id" required>
                            <option value="">{{ __('Pilih pelajar') }}</option>
                            @foreach($students as $student)
                                <option value="{{ $student->id }}" {{ (string)old('student_id', $offense->student_id) === (string)$student->id ? 'selected' : '' }}>{{ $student->full_name }} ({{ $student->matric_no }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="place">{{ __('Tempat') }}</label>
                        <input type="text" id="place" name="place" value="{{ old('place', $offense->place) }}" required>
                    </div>
                </div>
                <div class="grid grid-3" style="margin-top:12px;">
                    <div><label for="offense_date">{{ __('Tarikh') }}</label><input type="date" id="offense_date" name="offense_date" value="{{ old('offense_date', $offense->offense_date) }}" required></div>
                    <div><label for="offense_time">{{ __('Masa') }}</label><input type="time" id="offense_time" name="offense_time" value="{{ old('offense_time', substr($offense->offense_time, 0, 5)) }}" required></div>
                    <div><label for="fine_amount">{{ __('Jumlah Denda (RM)') }}</label><input type="number" id="fine_amount" name="fine_amount" min="0" step="0.01" value="{{ old('fine_amount', $offense->fine_amount) }}" required></div>
                </div>
                <div style="margin-top:12px;">
                    <label for="status">{{ __('Status') }}</label>
                    <select id="status" name="status" required>
                        @php $currentStatus = old('status', $offense->status); @endphp
                        <option value="unpaid" {{ $currentStatus === 'unpaid' ? 'selected' : '' }}>{{ __('unpaid') }}</option>
                        <option value="applied" {{ $currentStatus === 'applied' ? 'selected' : '' }}>{{ __('applied') }}</option>
                        <option value="paid" {{ $currentStatus === 'paid' ? 'selected' : '' }}>{{ __('paid') }}</option>
                    </select>
                </div>
                <div style="margin-top:12px;">
                    <label for="evidence_photo">{{ __('Gambar Bukti (Opsyenal)') }}</label>
                    <input type="file" id="evidence_photo" name="evidence_photo" accept="image/jpeg,image/png,image/webp" capture="environment">
                    <small style="display:block; margin-top:6px; color:#7a6555;">{{ __('Upload gambar baharu jika mahu gantikan gambar sedia ada (JPG/PNG/WEBP, max 5MB).') }}</small>

                    @if(!empty($offense->evidence_photo_path))
                        <div style="margin-top:10px;">
                            <a href="{{ asset('storage/' . $offense->evidence_photo_path) }}" target="_blank" class="btn" style="padding:6px 10px; font-size:12px;">{{ __('Lihat Gambar Semasa') }}</a>
                            <div style="margin-top:8px;">
                                <img id="current_evidence_preview" src="{{ asset('storage/' . $offense->evidence_photo_path) }}" alt="{{ __('Gambar bukti semasa') }}" style="max-width:220px; border-radius:8px; border:1px solid #ede4d9;">
                            </div>
                            <label style="display:flex; align-items:center; gap:8px; margin-top:8px; font-weight:500;">
                                <input type="checkbox" name="remove_evidence_photo" value="1" {{ old('remove_evidence_photo') ? 'checked' : '' }} style="width:auto;">
                                {{ __('Buang gambar bukti semasa') }}
                            </label>
                        </div>
                    @endif

                    <img id="evidence_preview" alt="{{ __('Preview gambar baharu') }}" style="display:none; margin-top:10px; max-width:220px; border-radius:8px; border:1px solid #ede4d9;">
                </div>
            </div>
        </div>

        <div class="card">
            <h2>{{ __('Pilih Peraturan Dilanggar') }}</h2>
            <div class="body">
                <div class="rules-toolbar">
                    <input type="text" id="rule_search" placeholder="{{ __('Cari rujukan atau peraturan...') }}">
                    <label class="rules-selected-only" for="rule_selected_only">
                        <input type="checkbox" id="rule_selected_only">
                        {{ __('Tunjuk dipilih sahaja') }}
                    </label>
                    <button type="button" class="btn" id="rule_clear_btn">{{ __('Reset') }}</button>
                    <span class="rules-selected-count" id="rule_selected_count">{{ __('0 dipilih') }}</span>
                </div>
                <div class="rules-list" id="rules_list">
                @foreach($offenseTypes as $type)
                    @php
                        $isSelected = in_array($type->id, old('offense_type_ids', $selectedTypeIds));
                        $noteValue = old('notes.'.$type->id, $selectedNotes[(string) $type->id] ?? '');
                    @endphp
                    <div class="rule-row {{ $isSelected && $type->requires_note ? 'show-note' : '' }}" data-requires-note="{{ $type->requires_note ? '1' : '0' }}" data-rule-text="{{ strtolower(__($type->rule_reference) . ' ' . __($type->description)) }}">
                        <div class="rule-top">
                            <input type="checkbox" id="rule_{{ $type->id }}" name="offense_type_ids[]" value="{{ $type->id }}" {{ $isSelected ? 'checked' : '' }}>
                            <label for="rule_{{ $type->id }}" style="margin:0; font-weight:500; color:#2d1f14;"><strong>{{ __($type->rule_reference) }}</strong> - {{ __($type->description) }}</label>
                        </div>
                        <div class="rule-note">
                            <label for="note_{{ $type->id }}">{{ __('Catatan') }}</label>
                            <textarea id="note_{{ $type->id }}" name="notes[{{ $type->id }}]" rows="2" placeholder="{{ __('Isi catatan jika perlu') }}">{{ $noteValue }}</textarea>
                        </div>
                    </div>
                @endforeach
                </div>
            </div>
        </div>

        <div class="actions">
            <button class="btn btn-primary" type="submit">{{ __('Simpan Perubahan') }}</button>
            <a href="{{ route('admin.offenses.index') }}" class="btn">{{ __('Batal') }}</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    const evidenceInput = document.getElementById('evidence_photo');
    const evidencePreview = document.getElementById('evidence_preview');
    const offenseForm = document.getElementById('offense_form');
    const ajaxFormFeedback = document.getElementById('ajax_form_feedback');

    if (evidenceInput && evidencePreview) {
        evidenceInput.addEventListener('change', () => {
            const file = evidenceInput.files && evidenceInput.files[0] ? evidenceInput.files[0] : null;
            if (!file) {
                evidencePreview.style.display = 'none';
                evidencePreview.removeAttribute('src');
                return;
            }
            evidencePreview.src = URL.createObjectURL(file);
            evidencePreview.style.display = 'block';
        });
    }

    const showAjaxError = (messages) => {
        if (!ajaxFormFeedback) return;
        const list = Array.isArray(messages) ? messages : [messages];
        ajaxFormFeedback.innerHTML = list.map((msg) => `<div>${msg}</div>`).join('');
        ajaxFormFeedback.style.display = 'block';
        ajaxFormFeedback.scrollIntoView({ behavior: 'smooth', block: 'center' });
    };

    if (offenseForm && window.fetch) {
        offenseForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            if (ajaxFormFeedback) {
                ajaxFormFeedback.style.display = 'none';
                ajaxFormFeedback.innerHTML = '';
            }

            const submitBtn = offenseForm.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = @json(__('Menyimpan...'));
            }

            try {
                const response = await fetch(offenseForm.action, {
                    method: 'POST',
                    body: new FormData(offenseForm),
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    }
                });

                const payload = await response.json().catch(() => ({}));

                if (response.ok && payload.ok) {
                    window.location.href = payload.redirect || "{{ route('admin.offenses.index') }}";
                    return;
                }

                if (response.status === 422 && payload.errors) {
                    const errors = Object.values(payload.errors).flat();
                    showAjaxError(errors.length ? errors : @json(__('Sila semak semula input borang.')));
                } else {
                    showAjaxError(payload.message || @json(__('Gagal mengemaskini rekod kesalahan.')));
                }
            } catch (error) {
                showAjaxError(@json(__('Ralat rangkaian. Sila cuba semula.')));
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = @json(__('Simpan Perubahan'));
                }
            }
        });
    }

    const ruleRows = Array.from(document.querySelectorAll('.rule-row'));
    const ruleSearch = document.getElementById('rule_search');
    const ruleSelectedOnly = document.getElementById('rule_selected_only');
    const ruleClearBtn = document.getElementById('rule_clear_btn');
    const ruleSelectedCount = document.getElementById('rule_selected_count');

    const applyRuleFilters = () => {
        const term = (ruleSearch?.value || '').trim().toLowerCase();
        const selectedOnly = ruleSelectedOnly?.checked;
        let selectedCount = 0;

        ruleRows.forEach((row) => {
            const checkbox = row.querySelector('input[type="checkbox"]');
            const requiresNote = row.dataset.requiresNote === '1';
            const text = row.dataset.ruleText || '';
            const isChecked = checkbox ? checkbox.checked : false;
            const match = !term || text.includes(term);
            const visible = match && (!selectedOnly || isChecked);

            row.style.display = visible ? '' : 'none';
            if (isChecked) selectedCount += 1;

            if (checkbox && isChecked && requiresNote) row.classList.add('show-note');
            else row.classList.remove('show-note');
        });

        if (ruleSelectedCount) {
            ruleSelectedCount.textContent = `${selectedCount} ${@json(__('dipilih'))}`;
        }
    };

    ruleRows.forEach((row) => {
        const checkbox = row.querySelector('input[type="checkbox"]');
        if (checkbox) checkbox.addEventListener('change', applyRuleFilters);
    });
    if (ruleSearch) ruleSearch.addEventListener('input', applyRuleFilters);
    if (ruleSelectedOnly) ruleSelectedOnly.addEventListener('change', applyRuleFilters);
    if (ruleClearBtn) {
        ruleClearBtn.addEventListener('click', () => {
            if (ruleSearch) ruleSearch.value = '';
            if (ruleSelectedOnly) ruleSelectedOnly.checked = false;
            applyRuleFilters();
        });
    }
    applyRuleFilters();
</script>
@endpush


