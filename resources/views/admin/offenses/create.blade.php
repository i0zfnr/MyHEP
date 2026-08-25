@extends('layouts.app')

@section('title', __('Daftar Kesalahan'))



@section('header')
    <h2 style="margin:0;font-size:1.1rem;font-weight:700;color:#2d1f14;">{{ __('Daftar Kesalahan Pelajar') }}</h2>
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
            <h2>{{ __('Maklumat Kesalahan') }}</h2>
            <div class="body">
                <div class="grid grid-2">
                    <div>
                        <label for="student_search">{{ __('Cari Pelajar (Nama / Matrik)') }}</label>
                        <div class="student-search-shell">
                            <input type="search" id="student_search" value="{{ $selectedStudent?->full_name ?? '' }}" placeholder="{{ __('Contoh: 23DIT0001 atau Irfan') }}" autocomplete="off" aria-autocomplete="list" aria-controls="student_search_results" aria-expanded="false">
                            <div id="student_search_results" class="student-search-results" role="listbox" hidden></div>
                        </div>
                        <input type="hidden" name="student_id" id="student_id" value="{{ old('student_id') }}" required>
                        <div id="selected_student" class="selected-student" @if(!$selectedStudent) hidden @endif>
                            <div>
                                <strong id="selected_student_name">{{ $selectedStudent?->full_name }}</strong>
                                <span id="selected_student_matric">{{ $selectedStudent?->matric_no }}</span>
                            </div>
                            <button type="button" class="btn" id="clear_student_btn">{{ __('Tukar') }}</button>
                        </div>
                        <p class="hint" id="student_search_hint">{{ __('Taip sekurang-kurangnya 2 huruf, kemudian pilih pelajar daripada hasil AJAX.') }}</p>
                    </div>
                    <div>
                        <label for="place">{{ __('Tempat') }}</label>
                        <input type="text" id="place" name="place" value="{{ old('place') }}" required>
                    </div>
                </div>
                <div class="grid grid-3" style="margin-top:12px;">
                    <div><label for="offense_date">{{ __('Tarikh') }}</label><input type="date" id="offense_date" name="offense_date" value="{{ old('offense_date') }}" required></div>
                    <div><label for="offense_time">{{ __('Masa') }}</label><input type="time" id="offense_time" name="offense_time" value="{{ old('offense_time') }}" required></div>
                    <div><label for="fine_amount">{{ __('Jumlah Denda (RM)') }}</label><input type="number" id="fine_amount" name="fine_amount" min="0" step="0.01" value="{{ old('fine_amount', '0.00') }}" required></div>
                </div>
                <div style="margin-top:12px;">
                    <label for="evidence_photo">{{ __('Gambar Bukti (Opsyenal)') }}</label>
                    <input type="file" id="evidence_photo" name="evidence_photos[]" accept="image/jpeg,image/png,image/webp" capture="environment" multiple>
                    <small style="display:block; margin-top:6px; color:#7a6555;">{{ __('You can upload up to 3 evidence images (JPG/PNG/WEBP, max 5MB each).') }}</small>
                    <div class="camera-panel">
                        <div class="camera-actions">
                            <button class="btn" type="button" id="open_camera_btn">{{ __('Guna Kamera') }}</button>
                            <button class="btn" type="button" id="capture_camera_btn" style="display:none;">{{ __('Tangkap Gambar') }}</button>
                            <button class="btn" type="button" id="close_camera_btn" style="display:none;">{{ __('Tutup Kamera') }}</button>
                            <button class="btn btn-danger-soft" type="button" id="remove_evidence_btn" style="display:none;">{{ __('Remove selected images') }}</button>
                        </div>
                        <video id="camera_live" class="camera-live" autoplay playsinline></video>
                        <canvas id="camera_canvas" style="display:none;"></canvas>
                        <div id="camera_msg" class="camera-msg">{{ __('Tekan "Guna Kamera" untuk benarkan akses kamera.') }}</div>
                    </div>
                    <div id="evidence_preview_grid" class="evidence-grid"></div>
                    <div id="evidence_count_hint" class="hint">{{ __('Selected :count / 3 images.', ['count' => 0]) }}</div>
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
                @if($offenseTypes->isEmpty())
                    <div class="rule-row" role="status">{{ __('No violated rules are configured. Run the latest database migrations or contact the System Admin.') }}</div>
                @endif
                @foreach($offenseTypes as $type)
                    <div class="rule-row" data-requires-note="{{ $type->requires_note ? '1' : '0' }}" data-rule-text="{{ strtolower(__($type->rule_reference) . ' ' . __($type->description)) }}">
                        <div class="rule-top">
                            <input type="checkbox" id="rule_{{ $type->id }}" name="offense_type_ids[]" value="{{ $type->id }}" {{ in_array($type->id, old('offense_type_ids', [])) ? 'checked' : '' }}>
                            <label for="rule_{{ $type->id }}" style="margin:0; font-weight:500; color:#2d1f14;"><strong>{{ __($type->rule_reference) }}</strong> - {{ __($type->description) }}</label>
                        </div>
                        <div class="rule-note">
                            <label for="note_{{ $type->id }}">{{ __('Catatan') }}</label>
                            <textarea id="note_{{ $type->id }}" name="notes[{{ $type->id }}]" rows="2" placeholder="{{ __('Isi catatan jika perlu') }}">{{ old('notes.'.$type->id) }}</textarea>
                        </div>
                    </div>
                @endforeach
                </div>
            </div>
        </div>

        <div class="actions">
            <button class="btn btn-primary" type="submit">{{ __('Simpan Kesalahan') }}</button>
            <a href="{{ route('admin.dashboard') }}" class="btn">{{ __('Batal') }}</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    const evidenceInput = document.getElementById('evidence_photo');
    const evidencePreviewGrid = document.getElementById('evidence_preview_grid');
    const evidenceCountHint = document.getElementById('evidence_count_hint');
    const studentSearch = document.getElementById('student_search');
    const studentId = document.getElementById('student_id');
    const studentResults = document.getElementById('student_search_results');
    const selectedStudent = document.getElementById('selected_student');
    const selectedStudentName = document.getElementById('selected_student_name');
    const selectedStudentMatric = document.getElementById('selected_student_matric');
    const clearStudentBtn = document.getElementById('clear_student_btn');
    const offenseForm = document.getElementById('offense_form');
    const ajaxFormFeedback = document.getElementById('ajax_form_feedback');
    const openCameraBtn = document.getElementById('open_camera_btn');
    const captureCameraBtn = document.getElementById('capture_camera_btn');
    const closeCameraBtn = document.getElementById('close_camera_btn');
    const removeEvidenceBtn = document.getElementById('remove_evidence_btn');
    const cameraLive = document.getElementById('camera_live');
    const cameraCanvas = document.getElementById('camera_canvas');
    const cameraMsg = document.getElementById('camera_msg');
    let cameraStream = null;
    const maxEvidenceFiles = 3;
    let selectedEvidenceFiles = [];
    let evidenceObjectUrls = [];

    const syncEvidenceInput = () => {
        if (!evidenceInput) return;
        const dt = new DataTransfer();
        selectedEvidenceFiles.forEach((file) => dt.items.add(file));
        evidenceInput.files = dt.files;
    };

    const updateEvidenceCount = () => {
        if (evidenceCountHint) {
            evidenceCountHint.textContent = @json(__('Selected :count / 3 images.', ['count' => '__COUNT__'])).replace('__COUNT__', String(selectedEvidenceFiles.length));
        }
    };

    const renderEvidencePreviews = () => {
        evidenceObjectUrls.forEach((url) => URL.revokeObjectURL(url));
        evidenceObjectUrls = [];

        if (!evidencePreviewGrid) return;
        evidencePreviewGrid.innerHTML = '';

        selectedEvidenceFiles.forEach((file, index) => {
            const objectUrl = URL.createObjectURL(file);
            evidenceObjectUrls.push(objectUrl);

            const card = document.createElement('div');
            card.className = 'evidence-card';
            card.innerHTML = `
                <img src="${objectUrl}" alt="Evidence ${index + 1}">
                <small>${@json(__('Image :number', ['number' => '__NUM__'])).replace('__NUM__', String(index + 1))}</small>
                <button type="button" class="btn btn-danger-soft" data-remove-evidence-index="${index}">${@json(__('Remove this image'))}</button>
            `;
            evidencePreviewGrid.appendChild(card);
        });

        if (removeEvidenceBtn) {
            removeEvidenceBtn.style.display = selectedEvidenceFiles.length ? 'inline-block' : 'none';
        }
        updateEvidenceCount();
    };

    const clearSelectedEvidence = () => {
        selectedEvidenceFiles = [];
        syncEvidenceInput();
        renderEvidencePreviews();
        cameraMsg.textContent = @json(__('Gambar bukti dibuang. Anda boleh pilih atau tangkap semula.'));
        cameraMsg.classList.remove('err');
    };

    const mergeEvidenceFiles = (incomingFiles, replaceExisting = false) => {
        const nextFiles = replaceExisting ? [] : [...selectedEvidenceFiles];
        let trimmed = false;

        incomingFiles.forEach((file) => {
            if (nextFiles.length < maxEvidenceFiles) {
                nextFiles.push(file);
            } else {
                trimmed = true;
            }
        });

        selectedEvidenceFiles = nextFiles;
        syncEvidenceInput();
        renderEvidencePreviews();

        if (trimmed) {
            cameraMsg.textContent = @json(__('Only the first 3 images were kept.'));
            cameraMsg.classList.remove('err');
        }
    };

    if (evidenceInput) {
        evidenceInput.addEventListener('change', () => {
            const files = Array.from(evidenceInput.files || []);
            if (!files.length) {
                selectedEvidenceFiles = [];
                renderEvidencePreviews();
                return;
            }
            mergeEvidenceFiles(files, true);
        });
    }

    if (removeEvidenceBtn) {
        removeEvidenceBtn.addEventListener('click', () => {
            stopCamera();
            clearSelectedEvidence();
        });
    }

    if (evidencePreviewGrid) {
        evidencePreviewGrid.addEventListener('click', (event) => {
            const trigger = event.target.closest('[data-remove-evidence-index]');
            if (!trigger) return;

            const index = Number(trigger.getAttribute('data-remove-evidence-index'));
            if (Number.isNaN(index)) return;

            selectedEvidenceFiles = selectedEvidenceFiles.filter((_, fileIndex) => fileIndex !== index);
            syncEvidenceInput();
            renderEvidencePreviews();
        });
    }

    if (studentSearch && studentId && studentResults) {
        let studentSearchTimer = null;
        let studentSearchRequest = null;

        const closeStudentResults = () => {
            studentResults.hidden = true;
            studentSearch.setAttribute('aria-expanded', 'false');
        };

        const showStudentState = (message) => {
            studentResults.replaceChildren();
            const state = document.createElement('div');
            state.className = 'student-search-state';
            state.textContent = message;
            studentResults.appendChild(state);
            studentResults.hidden = false;
            studentSearch.setAttribute('aria-expanded', 'true');
        };

        const clearSelectedStudent = ({ keepSearch = false } = {}) => {
            studentId.value = '';
            if (!keepSearch) studentSearch.value = '';
            if (selectedStudent) selectedStudent.hidden = true;
            if (selectedStudentName) selectedStudentName.textContent = '';
            if (selectedStudentMatric) selectedStudentMatric.textContent = '';
        };

        const chooseStudent = (student) => {
            studentId.value = String(student.id);
            studentSearch.value = student.full_name || student.matric_no || '';
            if (selectedStudentName) selectedStudentName.textContent = student.full_name || '';
            if (selectedStudentMatric) selectedStudentMatric.textContent = student.matric_no || '';
            if (selectedStudent) selectedStudent.hidden = false;
            closeStudentResults();
        };

        const renderStudentResults = (students) => {
            studentResults.replaceChildren();
            if (!students.length) {
                showStudentState(@json(__('Tiada pelajar sepadan ditemui.')));
                return;
            }

            students.forEach((student) => {
                const option = document.createElement('button');
                option.type = 'button';
                option.className = 'student-search-option';
                option.setAttribute('role', 'option');
                const name = document.createElement('strong');
                name.textContent = student.full_name || '-';
                const matric = document.createElement('span');
                matric.textContent = student.matric_no || '-';
                option.append(name, matric);
                option.addEventListener('click', () => chooseStudent(student));
                studentResults.appendChild(option);
            });
            studentResults.hidden = false;
            studentSearch.setAttribute('aria-expanded', 'true');
        };

        studentSearch.addEventListener('input', () => {
            const q = studentSearch.value.trim();
            if (studentSearchTimer) clearTimeout(studentSearchTimer);
            studentSearchRequest?.abort();
            clearSelectedStudent({ keepSearch: true });

            if (q.length < 2) {
                closeStudentResults();
                return;
            }

            showStudentState(@json(__('Mencari pelajar...')));

            studentSearchTimer = setTimeout(async () => {
                studentSearchRequest = new AbortController();
                try {
                    const resp = await fetch(`{{ route('admin.students.search') }}?q=${encodeURIComponent(q)}`, {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        signal: studentSearchRequest.signal,
                    });
                    const payload = await resp.json().catch(() => ({ data: [] }));
                    if (!resp.ok || !Array.isArray(payload.data)) throw new Error('Student search failed');
                    renderStudentResults(payload.data);
                } catch (e) {
                    if (e?.name !== 'AbortError') showStudentState(@json(__('Carian pelajar gagal. Sila cuba semula.')));
                }
            }, 320);
        });

        studentSearch.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') closeStudentResults();
            if (event.key === 'ArrowDown') {
                event.preventDefault();
                studentResults.querySelector('.student-search-option')?.focus();
            }
        });
        studentResults.addEventListener('keydown', (event) => {
            const options = Array.from(studentResults.querySelectorAll('.student-search-option'));
            const index = options.indexOf(document.activeElement);
            if (event.key === 'ArrowDown' && index < options.length - 1) {
                event.preventDefault();
                options[index + 1].focus();
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                if (index > 0) options[index - 1].focus();
                else studentSearch.focus();
            } else if (event.key === 'Escape') {
                closeStudentResults();
                studentSearch.focus();
            }
        });
        clearStudentBtn?.addEventListener('click', () => {
            clearSelectedStudent();
            closeStudentResults();
            studentSearch.focus();
        });
        document.addEventListener('click', (event) => {
            if (!event.target.closest('.student-search-shell')) closeStudentResults();
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

            if (!studentId?.value) {
                showAjaxError(@json(__('Sila cari dan pilih seorang pelajar sebelum menyimpan kesalahan.')));
                studentSearch?.focus();
                return;
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
                    showAjaxError(payload.message || @json(__('Gagal menyimpan rekod kesalahan.')));
                }
            } catch (error) {
                showAjaxError(@json(__('Ralat rangkaian. Sila cuba semula.')));
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = @json(__('Simpan Kesalahan'));
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
                cameraMsg.textContent = @json(__('Kamera aktif. Tekan "Tangkap Gambar" untuk guna sebagai bukti.'));
                cameraMsg.classList.remove('err');
            } catch (err) {
                cameraMsg.textContent = @json(__('Akses kamera gagal. Sila benarkan permission kamera dalam browser atau guna upload fail.'));
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
                    cameraMsg.textContent = @json(__('Gagal memproses gambar daripada kamera.'));
                    cameraMsg.classList.add('err');
                    return;
                }

                const file = new File([blob], `offense-${Date.now()}.jpg`, { type: 'image/jpeg' });
                mergeEvidenceFiles([file], false);

                cameraMsg.textContent = @json(__('Camera photo added to evidence list.'));
                cameraMsg.classList.remove('err');
                stopCamera();
            }, 'image/jpeg', 0.92);
        });

        closeCameraBtn.addEventListener('click', () => {
            stopCamera();
            cameraMsg.textContent = @json(__('Kamera ditutup.'));
            cameraMsg.classList.remove('err');
        });

        window.addEventListener('beforeunload', stopCamera);
    }

    renderEvidencePreviews();

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


