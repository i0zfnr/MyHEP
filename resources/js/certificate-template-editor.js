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
    const fields = Array.from(document.querySelectorAll('.cert-drag-field'));
    const pills = Array.from(document.querySelectorAll('[data-focus-field]'));

    if (!input || !canvas || !(pdfCanvas instanceof HTMLCanvasElement)) return;

    const pageSize = {
        w: Number(canvas.dataset.pageWidthMm || 297),
        h: Number(canvas.dataset.pageHeightMm || 210),
    };

    let currentPdfBytes = null;
    let renderTask = null;

    const getInput = (prefix, suffix) => document.querySelector(`[data-field-input="${prefix}_${suffix}"]`);

    const placeFieldFromInputs = (field) => {
        const prefix = field.dataset.prefix;
        const x = Number(getInput(prefix, 'x')?.value || 0);
        const y = Number(getInput(prefix, 'y')?.value || 0);
        field.style.left = `${(x / pageSize.w) * 100}%`;
        field.style.top = `${(y / pageSize.h) * 100}%`;
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
            fields.forEach(placeFieldFromInputs);

            renderTask = page.render({ canvasContext: context, viewport: scaled });
            await renderTask.promise;
            status.textContent = `${input.files?.[0]?.name || 'PDF'} · Page ${requestedPage}/${pdf.numPages}`;
        } catch (error) {
            if (error?.name === 'RenderingCancelledException') return;
            pdfCanvas.hidden = true;
            empty.style.display = '';
            empty.querySelector('strong').textContent = 'PDF preview could not be rendered';
            empty.querySelector('span').textContent = 'Try another exported PDF file, or save and let the server validation show the exact issue.';
            status.textContent = 'Preview failed';
        }
    };

    fields.forEach(placeFieldFromInputs);

    input.addEventListener('change', async () => {
        const file = input.files?.[0];
        if (!file) return;
        currentPdfBytes = await file.arrayBuffer();
        await renderPdfPreview();
    });

    pageInput?.addEventListener('change', renderPdfPreview);

    pills.forEach((pill) => {
        pill.addEventListener('click', () => setActive(pill.dataset.focusField));
    });

    fields.forEach((field) => {
        field.addEventListener('pointerdown', (event) => {
            event.preventDefault();
            field.setPointerCapture(event.pointerId);
            setActive(field.dataset.certField);
            field.style.cursor = 'grabbing';

            const move = (moveEvent) => {
                const rect = canvas.getBoundingClientRect();
                const x = Math.max(0, Math.min(rect.width, moveEvent.clientX - rect.left));
                const y = Math.max(0, Math.min(rect.height, moveEvent.clientY - rect.top));
                const xMm = +((x / rect.width) * pageSize.w).toFixed(1);
                const yMm = +((y / rect.height) * pageSize.h).toFixed(1);
                field.style.left = `${(x / rect.width) * 100}%`;
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
});
