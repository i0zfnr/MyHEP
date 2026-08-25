@extends('layouts.app')

@section('title', __('Kemaskini Kesalahan'))



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
                    <input type="file" id="evidence_photo" name="evidence_photos[]" accept="image/jpeg,image/png,image/webp" capture="environment" multiple>
                    <small style="display:block; margin-top:6px; color:#7a6555;">{{ __('You can upload up to 3 evidence images (JPG/PNG/WEBP, max 5MB each).') }}</small>

                    @if(($offense->evidence_count ?? 0) > 0)
                        <div style="margin-top:10px;">
                            <div class="hint">{{ __('Current evidence images') }}</div>
                            <div id="current_evidence_grid" class="evidence-grid">
                                @foreach($offense->evidence_photos as $index => $photo)
                                    <div class="evidence-card {{ old('remove_evidence_photo') && $photo->is_primary ? 'is-removed' : '' }}" data-current-evidence-card="{{ $photo->is_primary ? 'primary' : $photo->id }}">
                                        <a href="{{ asset('storage/' . $photo->photo_path) }}" target="_blank">
                                            <img src="{{ asset('storage/' . $photo->photo_path) }}" alt="{{ __('Current evidence image') }}">
                                        </a>
                                        <small>{{ $photo->is_primary ? __('Primary image') : __('Image :number', ['number' => $index + 1]) }}</small>
                                        @if($photo->is_primary)
                                            <button type="button" class="btn btn-danger-soft" id="remove_current_evidence_btn">{{ __('Remove this image') }}</button>
                                            <input type="checkbox" name="remove_evidence_photo" id="remove_evidence_photo" value="1" {{ old('remove_evidence_photo') ? 'checked' : '' }} style="display:none;">
                                        @else
                                            <button type="button" class="btn btn-danger-soft" data-toggle-extra-remove="{{ $photo->id }}">{{ __('Remove this image') }}</button>
                                            <input type="checkbox" name="remove_evidence_extra_ids[]" value="{{ $photo->id }}" data-extra-remove-input="{{ $photo->id }}" {{ in_array((string) $photo->id, array_map('strval', old('remove_evidence_extra_ids', [])), true) ? 'checked' : '' }} style="display:none;">
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                            <div id="remove_current_evidence_state" class="hint" style="margin-top:8px; display:none;">{{ __('This image will be removed when you save changes.') }}</div>
                        </div>
                    @endif

                    <div class="preview-actions">
                        <button type="button" class="btn btn-danger-soft" id="remove_new_evidence_btn" style="display:none;">{{ __('Remove selected images') }}</button>
                    </div>
                    <div id="evidence_preview_grid" class="evidence-grid"></div>
                    <div id="evidence_count_hint" class="hint">{{ __('Current active evidence: :count / 3 images.', ['count' => $offense->evidence_count ?? 0]) }}</div>
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
    const evidencePreviewGrid = document.getElementById('evidence_preview_grid');
    const currentEvidenceGrid = document.getElementById('current_evidence_grid');
    const removeCurrentEvidenceBtn = document.getElementById('remove_current_evidence_btn');
    const removeCurrentEvidenceCheckbox = document.getElementById('remove_evidence_photo');
    const removeCurrentEvidenceState = document.getElementById('remove_current_evidence_state');
    const removeNewEvidenceBtn = document.getElementById('remove_new_evidence_btn');
    const evidenceCountHint = document.getElementById('evidence_count_hint');
    const offenseForm = document.getElementById('offense_form');
    const ajaxFormFeedback = document.getElementById('ajax_form_feedback');
    const maxEvidenceFiles = 3;
    let selectedEvidenceFiles = [];
    let evidenceObjectUrls = [];

    const currentExtraInputs = Array.from(document.querySelectorAll('[data-extra-remove-input]'));

    const currentActiveCount = () => {
        let count = 0;
        if (removeCurrentEvidenceCheckbox && !removeCurrentEvidenceCheckbox.checked) count += 1;
        currentExtraInputs.forEach((input) => {
            if (!input.checked) count += 1;
        });
        return count;
    };

    const syncCurrentEvidenceState = () => {
        if (!removeCurrentEvidenceCheckbox || !currentEvidenceGrid) return;
        const isMarked = removeCurrentEvidenceCheckbox.checked;
        const card = currentEvidenceGrid.querySelector('[data-current-evidence-card="primary"]');
        if (card) card.classList.toggle('is-removed', isMarked);
        if (removeCurrentEvidenceState) removeCurrentEvidenceState.style.display = isMarked ? 'block' : 'none';
        if (removeCurrentEvidenceBtn) {
            removeCurrentEvidenceBtn.textContent = isMarked
                ? @json(__('Cancel remove current image'))
                : @json(__('Remove this image'));
        }
        updateEvidenceCount();
    };

    const syncEvidenceInput = () => {
        if (!evidenceInput) return;
        const dt = new DataTransfer();
        selectedEvidenceFiles.forEach((file) => dt.items.add(file));
        evidenceInput.files = dt.files;
    };

    const updateEvidenceCount = () => {
        if (evidenceCountHint) {
            const total = currentActiveCount() + selectedEvidenceFiles.length;
            evidenceCountHint.textContent = @json(__('Current active evidence: :count / 3 images.', ['count' => '__COUNT__'])).replace('__COUNT__', String(total));
        }
    };

    const renderNewEvidencePreviews = () => {
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
                <button type="button" class="btn btn-danger-soft" data-remove-new-evidence-index="${index}">${@json(__('Remove this image'))}</button>
            `;
            evidencePreviewGrid.appendChild(card);
        });

        if (removeNewEvidenceBtn) {
            removeNewEvidenceBtn.style.display = selectedEvidenceFiles.length ? 'inline-block' : 'none';
        }
        updateEvidenceCount();
    };

    const mergeEvidenceFiles = (incomingFiles, replaceExisting = false) => {
        const nextFiles = replaceExisting ? [] : [...selectedEvidenceFiles];
        let trimmed = false;

        incomingFiles.forEach((file) => {
            if (currentActiveCount() + nextFiles.length < maxEvidenceFiles) {
                nextFiles.push(file);
            } else {
                trimmed = true;
            }
        });

        selectedEvidenceFiles = nextFiles;
        syncEvidenceInput();
        renderNewEvidencePreviews();

        if (trimmed && ajaxFormFeedback) {
            ajaxFormFeedback.innerHTML = `<div>${@json(__('You can upload up to 3 evidence images only.'))}</div>`;
            ajaxFormFeedback.style.display = 'block';
        }
    };

    if (evidenceInput) {
        evidenceInput.addEventListener('change', () => {
            const files = Array.from(evidenceInput.files || []);
            if (!files.length) {
                selectedEvidenceFiles = [];
                renderNewEvidencePreviews();
                return;
            }
            mergeEvidenceFiles(files, true);
        });
    }

    if (removeNewEvidenceBtn) {
        removeNewEvidenceBtn.addEventListener('click', () => {
            selectedEvidenceFiles = [];
            syncEvidenceInput();
            renderNewEvidencePreviews();
        });
    }

    if (removeCurrentEvidenceBtn && removeCurrentEvidenceCheckbox) {
        removeCurrentEvidenceBtn.addEventListener('click', () => {
            removeCurrentEvidenceCheckbox.checked = !removeCurrentEvidenceCheckbox.checked;
            syncCurrentEvidenceState();
        });
        syncCurrentEvidenceState();
    }

    if (currentEvidenceGrid) {
        currentEvidenceGrid.addEventListener('click', (event) => {
            const trigger = event.target.closest('[data-toggle-extra-remove]');
            if (!trigger) return;

            const id = trigger.getAttribute('data-toggle-extra-remove');
            const input = document.querySelector(`[data-extra-remove-input="${id}"]`);
            const card = document.querySelector(`[data-current-evidence-card="${id}"]`);
            if (!input || !card) return;

            input.checked = !input.checked;
            card.classList.toggle('is-removed', input.checked);
            trigger.textContent = input.checked
                ? @json(__('Cancel remove current image'))
                : @json(__('Remove this image'));
            if (removeCurrentEvidenceState) {
                removeCurrentEvidenceState.style.display = document.querySelectorAll('[data-extra-remove-input]:checked').length || (removeCurrentEvidenceCheckbox && removeCurrentEvidenceCheckbox.checked) ? 'block' : 'none';
            }
            updateEvidenceCount();
        });

        currentExtraInputs.forEach((input) => {
            if (input.checked) {
                const card = document.querySelector(`[data-current-evidence-card="${input.value}"]`);
                const button = document.querySelector(`[data-toggle-extra-remove="${input.value}"]`);
                if (card) card.classList.add('is-removed');
                if (button) button.textContent = @json(__('Cancel remove current image'));
            }
        });
    }

    if (evidencePreviewGrid) {
        evidencePreviewGrid.addEventListener('click', (event) => {
            const trigger = event.target.closest('[data-remove-new-evidence-index]');
            if (!trigger) return;

            const index = Number(trigger.getAttribute('data-remove-new-evidence-index'));
            if (Number.isNaN(index)) return;

            selectedEvidenceFiles = selectedEvidenceFiles.filter((_, fileIndex) => fileIndex !== index);
            syncEvidenceInput();
            renderNewEvidencePreviews();
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
    renderNewEvidencePreviews();
    updateEvidenceCount();
    applyRuleFilters();
</script>
@endpush


