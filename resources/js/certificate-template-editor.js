import * as pdfjsLib from 'pdfjs-dist';
import pdfWorkerUrl from 'pdfjs-dist/build/pdf.worker.min.mjs?url';

pdfjsLib.GlobalWorkerOptions.workerSrc = pdfWorkerUrl;

document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('templatePdfInput');
    const pageInput = document.querySelector('input[name="source_page"]');
    const canvas = document.getElementById('certCanvas');
    const pdfCanvas = document.getElementById('certPdfCanvas');
    const empty = document.querySelector('.cert-empty-preview');
    const status = document.getElementById('certPreviewStatus');
    const analyzeButton = document.getElementById('analyzeCertificateTemplate');
    const saveButton = document.getElementById('saveCertificateTemplate');
    const aiStatus = document.getElementById('certAiStatus');
    const fields = Array.from(document.querySelectorAll('.cert-drag-field'));
    const covers = Array.from(document.querySelectorAll('[data-cover-for]'));
    const pills = Array.from(document.querySelectorAll('[data-focus-field]'));
    const fieldToggles = Array.from(document.querySelectorAll('[data-toggle-field]'));
    const includedInputs = Array.from(document.querySelectorAll('[data-included-field]'));

    if (!input || !canvas || !(pdfCanvas instanceof HTMLCanvasElement)) return;

    const pageSize = {
        w: Number(canvas.dataset.pageWidthMm || 297),
        h: Number(canvas.dataset.pageHeightMm || 210),
    };

    let currentPdfBytes = null;
    let renderTask = null;

    const getInput = (prefix, suffix) => document.querySelector(`[data-field-input="${prefix}_${suffix}"]`);
    const setInput = (prefix, suffix, value) => {
        document.querySelectorAll(`[data-field-input="${prefix}_${suffix}"]`).forEach((input) => { input.value = value; });
    };

    const fitFieldsToPage = () => {
        fields.forEach((field) => {
            const prefix = field.dataset.prefix;
            const width = Number(getInput(prefix, 'w')?.value || 100);
            const fittedWidth = Math.min(width, pageSize.w - 10);
            const x = Math.min(Number(getInput(prefix, 'x')?.value || 0), pageSize.w - fittedWidth);
            setInput(prefix, 'w', fittedWidth.toFixed(1));
            setInput(prefix, 'x', Math.max(0, x).toFixed(1));
        });
    };

    const usePlacementOnly = () => {
        const aiCleanedInput = document.querySelector('[data-ai-cleaned]');
        if (aiCleanedInput) aiCleanedInput.value = '0';
        canvas.classList.remove('is-detected');
        aiStatus.textContent = 'Manual placement selected. The original PDF will be preserved without removing or covering any text.';
        aiStatus.className = 'cert-ai-status success';
        if (saveButton) saveButton.disabled = false;
    };

    const placeFieldFromInputs = (field) => {
        const prefix = field.dataset.prefix;
        const x = Number(getInput(prefix, 'x')?.value || 0);
        const y = Number(getInput(prefix, 'y')?.value || 0);
        const width = Number(getInput(prefix, 'w')?.value || 100);
        field.style.left = `${((x + (width / 2)) / pageSize.w) * 100}%`;
        field.style.top = `${(y / pageSize.h) * 100}%`;
        if (prefix === 'logo') {
            field.style.width = `${(width / pageSize.w) * 100}%`;
            field.style.height = `${(Number(getInput(prefix, 'h')?.value || 24) / pageSize.h) * 100}%`;
        }
    };

    const placeCoverFromInputs = (cover) => {
        const prefix = cover.dataset.coverFor;
        const x = Number(getInput(prefix, 'cover_x')?.value || 0);
        const y = Number(getInput(prefix, 'cover_y')?.value || 0);
        const width = Number(getInput(prefix, 'cover_w')?.value || 1);
        const height = Number(getInput(prefix, 'cover_h')?.value || 1);
        cover.style.left = `${(x / pageSize.w) * 100}%`;
        cover.style.top = `${(y / pageSize.h) * 100}%`;
        cover.style.width = `${(width / pageSize.w) * 100}%`;
        cover.style.height = `${(height / pageSize.h) * 100}%`;
        cover.style.background = getInput(prefix, 'cover_color')?.value || '#f4ebd6';
    };

    const setActive = (fieldKey) => {
        fields.forEach((field) => field.classList.toggle('is-active', field.dataset.certField === fieldKey));
        pills.forEach((pill) => pill.classList.toggle('active', pill.dataset.focusField === fieldKey));
    };

    const renderPdfPreview = async () => {
        if (!currentPdfBytes) return;

        try {
            status.textContent = 'Rendering preview...';
            renderTask?.cancel?.();

            const pdf = await pdfjsLib.getDocument({ data: currentPdfBytes.slice(0) }).promise;
            const requestedPage = Math.max(1, Math.min(Number(pageInput?.value || 1), pdf.numPages));
            const page = await pdf.getPage(requestedPage);
            const viewport = page.getViewport({ scale: 1 });
            const targetWidth = canvas.clientWidth || 960;
            const scale = Math.max(0.6, targetWidth / viewport.width);
            const scaled = page.getViewport({ scale });
            const context = pdfCanvas.getContext('2d');

            pdfCanvas.width = Math.floor(scaled.width);
            pdfCanvas.height = Math.floor(scaled.height);
            pdfCanvas.hidden = false;
            empty.style.display = 'none';
            canvas.style.aspectRatio = `${scaled.width} / ${scaled.height}`;
            pageSize.w = Number((viewport.width * 25.4 / 72).toFixed(2));
            pageSize.h = Number((viewport.height * 25.4 / 72).toFixed(2));
            canvas.dataset.pageWidthMm = String(pageSize.w);
            canvas.dataset.pageHeightMm = String(pageSize.h);
            fitFieldsToPage();
            fields.forEach(placeFieldFromInputs);
            covers.forEach(placeCoverFromInputs);

            renderTask = page.render({ canvasContext: context, viewport: scaled });
            await renderTask.promise;
            status.textContent = `${input.files?.[0]?.name || 'PDF'} · Page ${requestedPage}/${pdf.numPages}`;
            if (saveButton) saveButton.disabled = false;
        } catch (error) {
            if (error?.name === 'RenderingCancelledException') return;
            pdfCanvas.hidden = true;
            empty.style.display = '';
            empty.querySelector('strong').textContent = 'PDF preview could not be rendered';
            empty.querySelector('span').textContent = 'Try another exported PDF file, or save and let the server validation show the exact issue.';
            status.textContent = 'Preview failed';
        }
    };

    const renderImagePreview = async (file) => {
        const image = new Image();
        const imageUrl = URL.createObjectURL(file);
        try {
            await new Promise((resolve, reject) => {
                image.onload = resolve;
                image.onerror = reject;
                image.src = imageUrl;
            });
            pageSize.w = image.width >= image.height ? 297 : 210;
            pageSize.h = image.width >= image.height ? 210 : 297;
            canvas.dataset.pageWidthMm = String(pageSize.w);
            canvas.dataset.pageHeightMm = String(pageSize.h);
            canvas.style.aspectRatio = `${pageSize.w} / ${pageSize.h}`;
            const targetWidth = canvas.clientWidth || 960;
            const targetHeight = targetWidth * pageSize.h / pageSize.w;
            pdfCanvas.width = Math.floor(targetWidth * window.devicePixelRatio);
            pdfCanvas.height = Math.floor(targetHeight * window.devicePixelRatio);
            pdfCanvas.style.width = '100%';
            pdfCanvas.style.height = '100%';
            pdfCanvas.hidden = false;
            empty.style.display = 'none';
            const context = pdfCanvas.getContext('2d');
            context.scale(window.devicePixelRatio, window.devicePixelRatio);
            context.fillStyle = '#ffffff';
            context.fillRect(0, 0, targetWidth, targetHeight);
            const scale = Math.min(targetWidth / image.width, targetHeight / image.height);
            const drawWidth = image.width * scale;
            const drawHeight = image.height * scale;
            context.drawImage(image, (targetWidth - drawWidth) / 2, (targetHeight - drawHeight) / 2, drawWidth, drawHeight);
            fitFieldsToPage();
            fields.forEach(placeFieldFromInputs);
            status.textContent = `${file.name} · Image design`;
            if (saveButton) saveButton.disabled = false;
        } finally {
            URL.revokeObjectURL(imageUrl);
        }
    };

    fields.forEach(placeFieldFromInputs);

    input.addEventListener('change', async () => {
        const file = input.files?.[0];
        if (!file) return;
        const nameInput = document.querySelector('input[name="name"]');
        if (nameInput && !nameInput.value.trim()) {
            nameInput.value = file.name.replace(/\.pdf$/i, '').replace(/[-_]+/g, ' ').trim();
        }
        const isPdf = file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf');
        if (isPdf) {
            currentPdfBytes = await file.arrayBuffer();
            await renderPdfPreview();
            if (analyzeButton) analyzeButton.hidden = false;
        } else {
            currentPdfBytes = null;
            if (analyzeButton) analyzeButton.hidden = true;
            try {
                await renderImagePreview(file);
            } catch {
                aiStatus.textContent = 'Image preview failed. Please choose a valid PNG or JPG design.';
                aiStatus.className = 'cert-ai-status error';
                return;
            }
        }
        canvas.classList.add('has-pdf');
        aiStatus.textContent = 'Blank design selected. Choose the fields you need and drag them into position.';
        aiStatus.className = 'cert-ai-status success';
    });

    pageInput?.addEventListener('change', renderPdfPreview);

    analyzeButton?.addEventListener('click', async () => {
        const file = input.files?.[0];
        if (!file) {
            aiStatus.textContent = 'Choose a certificate PDF containing Name and IC placeholders or sample details first.';
            aiStatus.className = 'cert-ai-status error';
            return;
        }
        if (!['student_name', 'ic_no'].every((key) => fieldToggles.find((toggle) => toggle.dataset.toggleField === key)?.checked)) {
            aiStatus.textContent = 'AI cleanup is optional and requires both Name and IC fields. You can still position the selected fields manually.';
            aiStatus.className = 'cert-ai-status error';
            return;
        }

        analyzeButton.disabled = true;
        if (saveButton) saveButton.disabled = true;
        const aiCleanedInput = document.querySelector('[data-ai-cleaned]');
        if (aiCleanedInput) aiCleanedInput.value = '0';
        aiStatus.textContent = 'AI is locating the existing Name and IC areas...';
        aiStatus.className = 'cert-ai-status';

        try {
            const formData = new FormData();
            formData.append('template_pdf', file);
            formData.append('source_page', pageInput?.value || '1');
            const response = await fetch(analyzeButton.dataset.analyzeUrl, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: formData,
            });
            const responseBody = await response.text();
            let result = {};
            try {
                result = responseBody ? JSON.parse(responseBody) : {};
            } catch {
                result = {};
            }

            if (!response.ok) {
                const temporaryFailure = [429, 500, 502, 503, 504].includes(response.status);
                const fallbackMessage = temporaryFailure
                ? 'AI is temporarily unavailable. You can still position the fields manually.'
                    : `AI detection failed (HTTP ${response.status}). You can still position the fields manually.`;
                throw new Error(result.message || fallbackMessage);
            }

            pageSize.w = Number(result.page?.width_mm || pageSize.w);
            pageSize.h = Number(result.page?.height_mm || pageSize.h);
            Object.entries({ student_name: 'name', ic_no: 'ic' }).forEach(([key, prefix]) => {
                const detected = result.fields?.[key];
                if (!detected) return;
                setInput(prefix, 'x', detected.x_mm);
                setInput(prefix, 'y', detected.y_mm);
                setInput(prefix, 'w', detected.width_mm);
                setInput(prefix, 'font', detected.font_size);
                setInput(prefix, 'cover_x', detected.cover.x_mm);
                setInput(prefix, 'cover_y', detected.cover.y_mm);
                setInput(prefix, 'cover_w', detected.cover.width_mm);
                setInput(prefix, 'cover_h', detected.cover.height_mm);
                setInput(prefix, 'cover_color', detected.cover.color);
            });
            fields.forEach(placeFieldFromInputs);
            covers.forEach(placeCoverFromInputs);
            setActive('student_name');
            canvas.classList.add('is-detected');
            analyzeButton.textContent = 'Run AI Detection Again';
            aiStatus.textContent = result.message;
            aiStatus.className = 'cert-ai-status success';
            if (aiCleanedInput) aiCleanedInput.value = '1';
            if (saveButton) saveButton.disabled = false;
        } catch (error) {
            aiStatus.textContent = error.message || 'AI detection failed. Position the fields manually.';
            aiStatus.className = 'cert-ai-status error';
            canvas.classList.remove('is-detected');
            if (saveButton && currentPdfBytes) saveButton.disabled = false;
        } finally {
            analyzeButton.disabled = false;
            analyzeButton.hidden = false;
        }
    });

    pills.forEach((pill) => {
        pill.addEventListener('click', () => setActive(pill.dataset.focusField));
    });

    fieldToggles.forEach((toggle) => {
        toggle.addEventListener('change', () => {
            const key = toggle.dataset.toggleField;
            const field = fields.find((item) => item.dataset.certField === key);
            const included = includedInputs.find((item) => item.value === key);
            if (field) field.hidden = !toggle.checked;
            if (included) included.disabled = !toggle.checked;
            if (toggle.checked && field) setActive(key);
            if (document.querySelector('[data-ai-cleaned]')?.value === '1') usePlacementOnly();
        });
    });

    fields.forEach((field) => {
        field.addEventListener('pointerdown', (event) => {
            event.preventDefault();
            field.setPointerCapture(event.pointerId);
            setActive(field.dataset.certField);
            field.style.cursor = 'grabbing';
            const initialRect = field.getBoundingClientRect();
            const grabOffsetX = event.clientX - (initialRect.left + (initialRect.width / 2));
            const grabOffsetY = event.clientY - initialRect.top;

            const move = (moveEvent) => {
                const rect = canvas.getBoundingClientRect();
                const outputWidthMm = Number(getInput(field.dataset.prefix, 'w')?.value || 100);
                const outputWidthPx = (outputWidthMm / pageSize.w) * rect.width;
                const halfOutputWidth = outputWidthPx / 2;
                const centerX = Math.max(
                    halfOutputWidth,
                    Math.min(rect.width - halfOutputWidth, moveEvent.clientX - rect.left - grabOffsetX)
                );
                const y = Math.max(0, Math.min(rect.height, moveEvent.clientY - rect.top - grabOffsetY));
                const xMm = +((((centerX - halfOutputWidth) / rect.width) * pageSize.w).toFixed(1));
                const yMm = +((y / rect.height) * pageSize.h).toFixed(1);
                field.style.left = `${(centerX / rect.width) * 100}%`;
                field.style.top = `${(y / rect.height) * 100}%`;
                getInput(field.dataset.prefix, 'x').value = xMm;
                getInput(field.dataset.prefix, 'y').value = yMm;
            };

            const up = () => {
                field.style.cursor = 'grab';
                field.removeEventListener('pointermove', move);
                field.removeEventListener('pointerup', up);
                field.removeEventListener('pointercancel', up);
            };

            field.addEventListener('pointermove', move);
            field.addEventListener('pointerup', up);
            field.addEventListener('pointercancel', up);
        });
    });

    document.querySelectorAll('[data-field-input$="_w"]').forEach((control) => {
        control.addEventListener('input', () => {
            const prefix = control.dataset.fieldInput.replace(/_w$/, '');
            const field = fields.find((item) => item.dataset.prefix === prefix);
            if (field) placeFieldFromInputs(field);
        });
    });

    document.querySelectorAll('[data-field-input]').forEach((control) => {
        control.addEventListener('input', () => {
            const name = control.dataset.fieldInput;
            document.querySelectorAll(`[data-field-input="${name}"]`).forEach((other) => {
                if (other !== control) other.value = control.value;
            });
            const field = fields.find((item) => item.dataset.prefix === name.replace(/_(?:w|h|font|x|y)$/, ''));
            if (field) placeFieldFromInputs(field);
        });
    });
});
