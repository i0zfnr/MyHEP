import QRCode from 'qrcode';
import * as pdfjsLib from 'pdfjs-dist';
import pdfWorkerUrl from 'pdfjs-dist/build/pdf.worker.min.mjs?url';

pdfjsLib.GlobalWorkerOptions.workerSrc = pdfWorkerUrl;

document.addEventListener('DOMContentLoaded', async () => {
    const qrTarget = document.body.dataset.qrTarget;
    const text = window.foodBankPosterText || {};
    const fileInput = document.getElementById('posterUpload');
    const status = document.getElementById('posterUploadStatus');
    const sourceCanvas = document.getElementById('posterSourceCanvas');
    const preview = document.getElementById('uploadedPosterSheet');
    const overlay = document.getElementById('qrOverlay');
    const downloadButton = document.getElementById('downloadComposedPoster');
    const xControl = document.getElementById('qrPositionX');
    const yControl = document.getElementById('qrPositionY');
    const sizeControl = document.getElementById('qrSize');
    const defaultQr = document.querySelector('.poster-sheet .qr-frame img');

    if (!fileInput || !sourceCanvas || !overlay) return;

    const setStatus = (message, state = '') => {
        status.textContent = message;
        status.dataset.state = state;
    };

    let position = { x: 38, y: 45, size: 24 };
    let qrImage = null;
    let dragging = false;
    let lastPointer = null;

    try {
        const qrDataUrl = await QRCode.toDataURL(qrTarget, {
            errorCorrectionLevel: 'H',
            margin: 4,
            width: 720,
            color: { dark: '#000000', light: '#FFFFFF' },
        });
        overlay.src = qrDataUrl;
        defaultQr.src = qrDataUrl;
        qrImage = new Image();
        qrImage.src = qrDataUrl;
        await qrImage.decode();
    } catch (error) {
        setStatus(text.qrError || 'The QR code could not be generated. Refresh the page.', 'error');
        return;
    }

    const updateOverlay = () => {
        const width = Math.min(position.size, 100);
        const height = width * (sourceCanvas.width / sourceCanvas.height);
        position.x = Math.max(0, Math.min(position.x, 100 - width));
        position.y = Math.max(0, Math.min(position.y, 100 - height));
        overlay.style.left = `${position.x}%`;
        overlay.style.top = `${position.y}%`;
        overlay.style.width = `${width}%`;
        overlay.style.setProperty('--qr-size-print', `${width}%`);
        xControl.max = String(Math.max(0, 100 - width));
        yControl.max = String(Math.max(0, 100 - height));
        xControl.value = String(Math.round(position.x));
        yControl.value = String(Math.round(position.y));
        sizeControl.value = String(Math.round(width));
    };

    const luminance = (data, width, x, y) => {
        const offset = (Math.floor(y) * width + Math.floor(x)) * 4;
        return (data[offset] * 0.299) + (data[offset + 1] * 0.587) + (data[offset + 2] * 0.114);
    };

    const detectBlankSquare = () => {
        const analysisCanvas = document.createElement('canvas');
        const scale = Math.min(1, 280 / sourceCanvas.width, 380 / sourceCanvas.height);
        analysisCanvas.width = Math.max(1, Math.round(sourceCanvas.width * scale));
        analysisCanvas.height = Math.max(1, Math.round(sourceCanvas.height * scale));
        const context = analysisCanvas.getContext('2d', { willReadFrequently: true });
        context.drawImage(sourceCanvas, 0, 0, analysisCanvas.width, analysisCanvas.height);
        const { data } = context.getImageData(0, 0, analysisCanvas.width, analysisCanvas.height);
        const width = analysisCanvas.width;
        const height = analysisCanvas.height;
        const minSide = Math.min(width, height);
        const step = Math.max(3, Math.round(minSide / 75));
        let best = null;

        for (let size = Math.round(minSide * 0.18); size <= minSide * 0.56; size += step) {
            const sampleInset = Math.max(2, Math.round(size * 0.14));
            const sampleInsetFar = Math.round(size * 0.86);
            for (let y = step; y + size < height - step; y += step) {
                for (let x = step; x + size < width - step; x += step) {
                    let bright = 0;
                    let interiorTotal = 0;
                    for (let gy = 0; gy < 6; gy++) {
                        for (let gx = 0; gx < 6; gx++) {
                            const px = x + sampleInset + ((sampleInsetFar - sampleInset) * gx / 5);
                            const py = y + sampleInset + ((sampleInsetFar - sampleInset) * gy / 5);
                            const value = luminance(data, width, px, py);
                            interiorTotal += value;
                            if (value > 205) bright++;
                        }
                    }

                    const whiteRatio = bright / 36;
                    if (whiteRatio < 0.92) continue;

                    const interiorAverage = interiorTotal / 36;
                    let edgeContrast = 0;
                    let edgeSamples = 0;
                    const edgeInset = Math.max(1, Math.round(size * 0.06));
                    const outside = Math.max(1, Math.round(size * 0.035));
                    for (let point = 0.12; point <= 0.88; point += 0.12) {
                        const along = size * point;
                        const positions = [
                            [x + along, y + edgeInset, x + along, y - outside],
                            [x + along, y + size - edgeInset, x + along, y + size + outside],
                            [x + edgeInset, y + along, x - outside, y + along],
                            [x + size - edgeInset, y + along, x + size + outside, y + along],
                        ];
                        positions.forEach(([innerX, innerY, outerX, outerY]) => {
                            const inner = luminance(data, width, innerX, innerY);
                            const outer = luminance(data, width, outerX, outerY);
                            edgeContrast += Math.max(0, interiorAverage - Math.min(inner, outer));
                            edgeSamples++;
                        });
                    }

                    const contrastScore = Math.min(1, (edgeContrast / edgeSamples) / 55);
                    const areaScore = size / minSide;
                    const score = (whiteRatio * 0.58) + (contrastScore * 0.3) + (areaScore * 0.12);
                    if (!best || score > best.score) best = { x, y, size, score };
                }
            }
        }

        if (!best || best.score < 0.68) return null;
        return {
            x: (best.x / width) * 100,
            y: (best.y / height) * 100,
            size: (best.size / width) * 82,
        };
    };

    const renderImage = async (file) => {
        const imageUrl = URL.createObjectURL(file);
        try {
            const image = new Image();
            image.src = imageUrl;
            await image.decode();
            const scale = Math.min(1, 3600 / image.naturalWidth, 3600 / image.naturalHeight, Math.sqrt(16000000 / (image.naturalWidth * image.naturalHeight)));
            sourceCanvas.width = Math.max(1, Math.round(image.naturalWidth * scale));
            sourceCanvas.height = Math.max(1, Math.round(image.naturalHeight * scale));
            sourceCanvas.getContext('2d').drawImage(image, 0, 0, sourceCanvas.width, sourceCanvas.height);
        } finally {
            URL.revokeObjectURL(imageUrl);
        }
    };

    const renderPdf = async (file) => {
        const document = await pdfjsLib.getDocument({ data: await file.arrayBuffer() }).promise;
        const page = await document.getPage(1);
        const baseViewport = page.getViewport({ scale: 1 });
        const scale = Math.min(4, 3600 / baseViewport.width, 3600 / baseViewport.height, Math.sqrt(16000000 / (baseViewport.width * baseViewport.height)));
        const viewport = page.getViewport({ scale });
        sourceCanvas.width = Math.round(viewport.width);
        sourceCanvas.height = Math.round(viewport.height);
        await page.render({ canvas: sourceCanvas, canvasContext: sourceCanvas.getContext('2d'), viewport }).promise;
        await document.destroy();
    };

    const loadPoster = async (file) => {
        if (!file) return;
        if (file.size > 20 * 1024 * 1024) {
            setStatus(text.fileSizeError || 'The poster file is larger than 20 MB.', 'error');
            return;
        }

        setStatus(text.reading || 'Reading the poster and searching for a QR space...');
        try {
            const isPdf = file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf');
            if (isPdf) {
                await renderPdf(file);
            } else if (['image/png', 'image/jpeg', 'image/webp'].includes(file.type)) {
                await renderImage(file);
            } else {
                throw new Error(text.fileTypeError || 'Choose a PNG, JPG, WebP, or PDF file.');
            }

            const landscape = sourceCanvas.width > sourceCanvas.height;
            document.body.classList.toggle('poster-landscape', landscape);
            document.body.classList.add('custom-poster-active');
            const detected = detectBlankSquare();
            const minQrWidth = (Math.min(sourceCanvas.width, sourceCanvas.height) * 0.28 / sourceCanvas.width) * 100;
            position = detected || {
                x: (100 - minQrWidth) / 2,
                y: 48,
                size: minQrWidth,
            };
            updateOverlay();
            downloadButton.disabled = false;
            setStatus(detected
                ? (text.detected || 'A suitable blank area was found. Drag the QR to adjust its position.')
                : (text.fallback || 'A QR placeholder could not be identified confidently. The QR is centered; drag it to adjust.'),
            detected ? 'success' : '');
        } catch (error) {
            document.body.classList.remove('custom-poster-active', 'poster-landscape');
            downloadButton.disabled = true;
            setStatus(error.message || text.readError || 'The poster could not be read. Try another file.', 'error');
        }
    };

    fileInput.addEventListener('change', (event) => loadPoster(event.target.files?.[0]));
    xControl.addEventListener('input', () => { position.x = Number(xControl.value); updateOverlay(); });
    yControl.addEventListener('input', () => { position.y = Number(yControl.value); updateOverlay(); });
    sizeControl.addEventListener('input', () => { position.size = Number(sizeControl.value); updateOverlay(); });

    overlay.addEventListener('pointerdown', (event) => {
        if (!document.body.classList.contains('custom-poster-active')) return;
        dragging = true;
        lastPointer = { x: event.clientX, y: event.clientY };
        overlay.setPointerCapture(event.pointerId);
        event.preventDefault();
    });
    overlay.addEventListener('pointermove', (event) => {
        if (!dragging) return;
        const bounds = preview.getBoundingClientRect();
        position.x += ((event.clientX - lastPointer.x) / bounds.width) * 100;
        position.y += ((event.clientY - lastPointer.y) / bounds.height) * 100;
        lastPointer = { x: event.clientX, y: event.clientY };
        updateOverlay();
    });
    overlay.addEventListener('pointerup', () => { dragging = false; lastPointer = null; });
    overlay.addEventListener('pointercancel', () => { dragging = false; lastPointer = null; });

    downloadButton.addEventListener('click', () => {
        if (!qrImage || !sourceCanvas.width) return;
        const output = document.createElement('canvas');
        output.width = sourceCanvas.width;
        output.height = sourceCanvas.height;
        const context = output.getContext('2d');
        context.drawImage(sourceCanvas, 0, 0);
        const qrWidth = output.width * (position.size / 100);
        context.drawImage(qrImage, output.width * (position.x / 100), output.height * (position.y / 100), qrWidth, qrWidth);
        output.toBlob((blob) => {
            if (!blob) {
                setStatus(text.downloadError || 'The poster could not be downloaded.', 'error');
                return;
            }
            const link = document.createElement('a');
            const downloadUrl = URL.createObjectURL(blob);
            link.href = downloadUrl;
            link.download = 'foodbank-poster-dengan-qr.png';
            link.click();
            window.setTimeout(() => URL.revokeObjectURL(downloadUrl), 1000);
        }, 'image/png');
    });
});
