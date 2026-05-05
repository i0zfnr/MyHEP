@extends('layouts.app')

@section('title', 'Daftar Kesalahan')

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
    .rule-row { border:1px solid #ede4d9; border-radius:10px; padding:10px; margin-bottom:10px; }
    .rule-top { display:flex; gap:8px; align-items:flex-start; }
    .rule-note { margin-top:10px; display:none; }
    .rule-row.show-note .rule-note { display:block; }
    .actions { display:flex; gap:10px; flex-wrap:wrap; }
    .btn { display:inline-block; border:1px solid #cbb9a4; background:#fff; color:#8a7362; border-radius:8px; padding:9px 14px; text-decoration:none; font-weight:600; font-size:14px; cursor:pointer; }
    .btn-primary { background:linear-gradient(135deg,#A48D78,#CBB9A4); color:#fff; border:none; }
    .error { margin-bottom:12px; background:#fef2f2; border:1px solid #fecaca; color:#991b1b; border-radius:8px; padding:10px; font-size:13px; }
    .camera-panel { margin-top:10px; border:1px dashed #d8c8b7; border-radius:10px; padding:10px; background:#fcfaf8; }
    .camera-live { width:100%; max-width:320px; border-radius:8px; border:1px solid #ede4d9; background:#111; display:none; }
    .camera-actions { display:flex; gap:8px; flex-wrap:wrap; margin-top:8px; }
    .camera-msg { margin-top:8px; color:#7a6555; font-size:12px; }
    .camera-msg.err { color:#991b1b; background:#fff; border:none; margin-bottom:0; padding:0; }
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
    .hint { font-size:12px; color:#7a6555; margin-top:5px; }
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
    <h2 style="margin:0;font-size:1.1rem;font-weight:700;color:#2d1f14;">Daftar Kesalahan Pelajar</h2>
@endsection

@section('content')
<div class="wrap">
    @if ($errors->any())
        <div class="error">@foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
    @endif

    <div id="ajax_form_feedback" class="error" style="display:none;"></div>

    <form id="offense_form" method="POST" action="{{ route('admin.offenses.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="card">
            <h2>Maklumat Kesalahan</h2>
            <div class="body">
                <div class="grid grid-2">
                    <div>
                        <label for="student_search">Cari Pelajar (Nama / Matrik)</label>
                        <input type="text" id="student_search" placeholder="Contoh: 23DIT0001 atau Irfan">
                        <p class="hint">Taip sekurang-kurangnya 2 huruf untuk cari pelajar melalui AJAX.</p>

                        <label for="student_id">Pelajar</label>
                        <select name="student_id" id="student_id" required>
                            <option value="">Pilih pelajar</option>
                            @foreach($students as $student)
                                <option value="{{ $student->id }}" {{ (string)old('student_id') === (string)$student->id ? 'selected' : '' }}>{{ $student->full_name }} ({{ $student->matric_no }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="place">Tempat</label>
                        <input type="text" id="place" name="place" value="{{ old('place') }}" required>
                    </div>
                </div>
                <div class="grid grid-3" style="margin-top:12px;">
                    <div><label for="offense_date">Tarikh</label><input type="date" id="offense_date" name="offense_date" value="{{ old('offense_date') }}" required></div>
                    <div><label for="offense_time">Masa</label><input type="time" id="offense_time" name="offense_time" value="{{ old('offense_time') }}" required></div>
                    <div><label for="fine_amount">Jumlah Denda (RM)</label><input type="number" id="fine_amount" name="fine_amount" min="0" step="0.01" value="{{ old('fine_amount', '0.00') }}" required></div>
                </div>
                <div style="margin-top:12px;">
                    <label for="evidence_photo">Gambar Bukti (Opsyenal)</label>
                    <input type="file" id="evidence_photo" name="evidence_photo" accept="image/jpeg,image/png,image/webp" capture="environment">
                    <small style="display:block; margin-top:6px; color:#7a6555;">Ambil gambar terus dari kamera atau upload fail (JPG/PNG/WEBP, max 5MB).</small>
                    <div class="camera-panel">
                        <div class="camera-actions">
                            <button class="btn" type="button" id="open_camera_btn">Guna Kamera</button>
                            <button class="btn" type="button" id="capture_camera_btn" style="display:none;">Tangkap Gambar</button>
                            <button class="btn" type="button" id="close_camera_btn" style="display:none;">Tutup Kamera</button>
                        </div>
                        <video id="camera_live" class="camera-live" autoplay playsinline></video>
                        <canvas id="camera_canvas" style="display:none;"></canvas>
                        <div id="camera_msg" class="camera-msg">Tekan "Guna Kamera" untuk benarkan akses kamera.</div>
                    </div>
                    <img id="evidence_preview" alt="Preview gambar bukti" style="display:none; margin-top:10px; max-width:220px; border-radius:8px; border:1px solid #ede4d9;">
                </div>
            </div>
        </div>

        <div class="card">
            <h2>Pilih Peraturan Dilanggar</h2>
            <div class="body">
                <div class="rules-toolbar">
                    <input type="text" id="rule_search" placeholder="Cari rujukan atau peraturan...">
                    <label class="rules-selected-only" for="rule_selected_only">
                        <input type="checkbox" id="rule_selected_only">
                        Tunjuk dipilih sahaja
                    </label>
                    <button type="button" class="btn" id="rule_clear_btn">Reset</button>
                    <span class="rules-selected-count" id="rule_selected_count">0 dipilih</span>
                </div>
                <div class="rules-list" id="rules_list">
                @foreach($offenseTypes as $type)
                    <div class="rule-row" data-requires-note="{{ $type->requires_note ? '1' : '0' }}" data-rule-text="{{ strtolower(__($type->rule_reference) . ' ' . __($type->description)) }}">
                        <div class="rule-top">
                            <input type="checkbox" id="rule_{{ $type->id }}" name="offense_type_ids[]" value="{{ $type->id }}" {{ in_array($type->id, old('offense_type_ids', [])) ? 'checked' : '' }}>
                            <label for="rule_{{ $type->id }}" style="margin:0; font-weight:500; color:#2d1f14;"><strong>{{ __($type->rule_reference) }}</strong> - {{ __($type->description) }}</label>
                        </div>
                        <div class="rule-note">
                            <label for="note_{{ $type->id }}">Catatan</label>
                            <textarea id="note_{{ $type->id }}" name="notes[{{ $type->id }}]" rows="2" placeholder="Isi catatan jika perlu">{{ old('notes.'.$type->id) }}</textarea>
                        </div>
                    </div>
                @endforeach
                </div>
            </div>
        </div>

        <div class="actions">
            <button class="btn btn-primary" type="submit">Simpan Kesalahan</button>
            <a href="{{ route('admin.dashboard') }}" class="btn">Batal</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    const evidenceInput = document.getElementById('evidence_photo');
    const evidencePreview = document.getElementById('evidence_preview');
    const studentSearch = document.getElementById('student_search');
    const studentSelect = document.getElementById('student_id');
    const offenseForm = document.getElementById('offense_form');
    const ajaxFormFeedback = document.getElementById('ajax_form_feedback');
    const openCameraBtn = document.getElementById('open_camera_btn');
    const captureCameraBtn = document.getElementById('capture_camera_btn');
    const closeCameraBtn = document.getElementById('close_camera_btn');
    const cameraLive = document.getElementById('camera_live');
    const cameraCanvas = document.getElementById('camera_canvas');
    const cameraMsg = document.getElementById('camera_msg');
    let cameraStream = null;

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

    if (studentSearch && studentSelect) {
        let studentSearchTimer = null;

        studentSearch.addEventListener('input', () => {
            const q = studentSearch.value.trim();
            if (studentSearchTimer) clearTimeout(studentSearchTimer);

            if (q.length < 2) return;

            studentSearchTimer = setTimeout(async () => {
                try {
                    const resp = await fetch(`{{ route('admin.students.search') }}?q=${encodeURIComponent(q)}`, {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const payload = await resp.json().catch(() => ({ data: [] }));
                    if (!resp.ok || !Array.isArray(payload.data)) return;

                    const current = studentSelect.value;
                    studentSelect.innerHTML = '<option value="">Pilih pelajar</option>';
                    payload.data.forEach((s) => {
                        const opt = document.createElement('option');
                        opt.value = String(s.id);
                        opt.textContent = `${s.full_name} (${s.matric_no})`;
                        if (String(s.id) === current) opt.selected = true;
                        studentSelect.appendChild(opt);
                    });

                    if (!studentSelect.value && payload.data.length === 1) {
                        studentSelect.value = String(payload.data[0].id);
                    }
                } catch (e) {
                    // silent fallback to manual dropdown selection
                }
            }, 320);
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
                submitBtn.textContent = 'Menyimpan...';
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
                    showAjaxError(errors.length ? errors : 'Sila semak semula input borang.');
                } else {
                    showAjaxError(payload.message || 'Gagal menyimpan rekod kesalahan.');
                }
            } catch (error) {
                showAjaxError('Ralat rangkaian. Sila cuba semula.');
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Simpan Kesalahan';
                }
            }
        });
    }

    const stopCamera = () => {
        if (cameraStream) {
            cameraStream.getTracks().forEach((track) => track.stop());
            cameraStream = null;
        }
        if (cameraLive) {
            cameraLive.pause();
            cameraLive.srcObject = null;
            cameraLive.style.display = 'none';
        }
        if (captureCameraBtn) captureCameraBtn.style.display = 'none';
        if (closeCameraBtn) closeCameraBtn.style.display = 'none';
    };

    if (openCameraBtn && captureCameraBtn && closeCameraBtn && cameraLive && cameraCanvas && evidenceInput) {
        openCameraBtn.addEventListener('click', async () => {
            stopCamera();
            try {
                cameraStream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: { ideal: 'environment' } },
                    audio: false
                });
                cameraLive.srcObject = cameraStream;
                cameraLive.style.display = 'block';
                captureCameraBtn.style.display = 'inline-block';
                closeCameraBtn.style.display = 'inline-block';
                cameraMsg.textContent = 'Kamera aktif. Tekan "Tangkap Gambar" untuk guna sebagai bukti.';
                cameraMsg.classList.remove('err');
            } catch (err) {
                cameraMsg.textContent = 'Akses kamera gagal. Sila benarkan permission kamera dalam browser atau guna upload fail.';
                cameraMsg.classList.add('err');
            }
        });

        captureCameraBtn.addEventListener('click', () => {
            if (!cameraStream) return;
            const width = cameraLive.videoWidth || 1280;
            const height = cameraLive.videoHeight || 720;
            cameraCanvas.width = width;
            cameraCanvas.height = height;
            const ctx = cameraCanvas.getContext('2d');
            ctx.drawImage(cameraLive, 0, 0, width, height);

            cameraCanvas.toBlob((blob) => {
                if (!blob) {
                    cameraMsg.textContent = 'Gagal memproses gambar daripada kamera.';
                    cameraMsg.classList.add('err');
                    return;
                }

                const file = new File([blob], `offense-${Date.now()}.jpg`, { type: 'image/jpeg' });
                const dt = new DataTransfer();
                dt.items.add(file);
                evidenceInput.files = dt.files;
                evidenceInput.dispatchEvent(new Event('change'));

                cameraMsg.textContent = 'Gambar berjaya ditangkap dan dipilih.';
                cameraMsg.classList.remove('err');
                stopCamera();
            }, 'image/jpeg', 0.92);
        });

        closeCameraBtn.addEventListener('click', () => {
            stopCamera();
            cameraMsg.textContent = 'Kamera ditutup.';
            cameraMsg.classList.remove('err');
        });

        window.addEventListener('beforeunload', stopCamera);
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
            ruleSelectedCount.textContent = `${selectedCount} dipilih`;
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


