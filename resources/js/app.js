const initializeLiveFilters = () => {
    document.querySelectorAll('[data-live-filter-form]').forEach((form) => {
        if (!(form instanceof HTMLFormElement) || form.dataset.liveFilterReady === 'true') return;
        form.dataset.liveFilterReady = 'true';
        const status = form.querySelector('[data-live-filter-status]');
        let timer = null;
        let request = null;

        const run = async (requestedUrl = null) => {
            request?.abort();
            request = new AbortController();
            const url = requestedUrl instanceof URL ? requestedUrl : new URL(form.action || window.location.href, window.location.origin);
            if (!(requestedUrl instanceof URL)) {
                const params = new URLSearchParams(new FormData(form));
                [...params.entries()].forEach(([key, value]) => { if (!String(value).trim()) params.delete(key); });
                url.search = params.toString();
            }
            if (status) status.textContent = 'Searching…';
            form.setAttribute('aria-busy', 'true');

            try {
                const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, signal: request.signal });
                if (!response.ok) throw new Error('Search request failed');
                const page = new DOMParser().parseFromString(await response.text(), 'text/html');
                const next = page.querySelector('[data-live-filter-results]');
                const current = document.querySelector('[data-live-filter-results]');
                if (!next || !current) throw new Error('Search results missing');
                current.replaceWith(next);
                registerVirtualTables();
                window.history.replaceState({}, '', `${url.pathname}${url.search}`);
                if (status) status.textContent = 'Results updated';
            } catch (error) {
                if (error.name !== 'AbortError' && status) status.textContent = 'Unable to update results';
            } finally {
                form.removeAttribute('aria-busy');
            }
        };

        const schedule = () => {
            window.clearTimeout(timer);
            timer = window.setTimeout(() => run(), Number(form.dataset.liveFilterDelay || 350));
        };
        form.addEventListener('submit', (event) => { event.preventDefault(); run(); });
        form.querySelectorAll('input').forEach((field) => field.addEventListener('input', schedule));
        form.querySelectorAll('select').forEach((field) => field.addEventListener('change', () => run()));
    });
};

const registerVirtualTables = () => {
    document.querySelectorAll('.account-table-wrap, .laptop-table-wrap, .admin-doc-table-wrap, [data-virtual-table]').forEach((wrap) => {
        if (!(wrap instanceof HTMLElement) || wrap.dataset.virtualizedReady === 'true') return;
        if (wrap.hasAttribute('data-no-virtual')) return;
        const table = wrap.querySelector('table');
        if (!table) return;
        const tbody = table.querySelector('tbody');
        if (!tbody) return;

        const rows = Array.from(tbody.children).filter((child) => child.tagName === 'TR' && !child.classList.contains('virtual-spacer'));
        if (rows.length <= 15) return;

        wrap.dataset.virtualizedReady = 'true';

        const sampleRow = rows[0];
        const rowHeight = Math.max(38, Math.round(sampleRow.getBoundingClientRect().height || 52));
        const totalItems = rows.length;
        const rowTemplates = rows.map((r) => r.outerHTML);

        wrap.style.position = 'relative';
        wrap.style.maxHeight = '680px';
        wrap.style.overflowY = 'auto';
        wrap.style.willChange = 'transform';

        let ticking = false;
        const renderSlice = () => {
            const scrollTop = wrap.scrollTop;
            const viewportHeight = wrap.clientHeight || 680;
            const buffer = 6;

            const startIndex = Math.max(0, Math.floor(scrollTop / rowHeight) - buffer);
            const endIndex = Math.min(totalItems, Math.ceil((scrollTop + viewportHeight) / rowHeight) + buffer);

            const topPad = startIndex * rowHeight;
            const bottomPad = Math.max(0, (totalItems - endIndex) * rowHeight);

            const visibleRowsHtml = rowTemplates.slice(startIndex, endIndex).join('');

            tbody.innerHTML = `
                <tr class="virtual-spacer" style="height:${topPad}px; border:none!important; background:transparent!important;"><td colspan="100%" style="padding:0;border:none!important;background:transparent!important;"></td></tr>
                ${visibleRowsHtml}
                <tr class="virtual-spacer" style="height:${bottomPad}px; border:none!important; background:transparent!important;"><td colspan="100%" style="padding:0;border:none!important;background:transparent!important;"></td></tr>
            `;

            ticking = false;
        };

        wrap.addEventListener('scroll', () => {
            if (!ticking) {
                window.requestAnimationFrame(renderSlice);
                ticking = true;
            }
        }, { passive: true });

        renderSlice();
    });
};

const PWA_PROMPT_KEY = 'studentedge-pwa-dismissed-v1';
const PUSH_PROMPT_KEY = 'studentedge-push-dismissed-v1';
const THEME_KEY = 'studentedge-theme';
const ACCENT_THEME_KEY = 'studentedge-accent-theme';
const GLASS_TRANSPARENCY_KEY = 'studentedge-glass-transparency';

const normalizeTheme = (theme) => (theme === 'dark' ? 'dark' : 'light');
const normalizeAccentTheme = (theme) => ['gold', 'candy_blue', 'lavender', 'orchid', 'violet'].includes(theme) ? theme : 'gold';
const normalizeGlassTransparency = (value) => Math.min(100, Math.max(0, Number.isFinite(Number(value)) ? Number(value) : 40));
const updateGlassControls = (value) => {
    const transparency = normalizeGlassTransparency(value);

    document.querySelectorAll('[data-glass-output]').forEach((output) => {
        output.textContent = `${transparency}%`;
    });
    document.querySelectorAll('.glass-slider').forEach((slider) => {
        slider.style.setProperty('--glass-range-progress', `${transparency}%`);
    });

    return transparency;
};

const applyGlassTransparency = (value, persist = true) => {
    const transparency = updateGlassControls(value);
    const ratio = transparency / 100;
    const root = document.documentElement;

    // Transparency controls a bounded physical material, never raw element opacity.
    root.style.setProperty('--glass-user-transparency', ratio.toFixed(2));
    root.style.setProperty('--student-nav-material-alpha', (0.86 - (ratio * 0.34)).toFixed(2));
    root.style.setProperty('--student-nav-active-alpha', (0.90 - (ratio * 0.22)).toFixed(2));
    root.style.setProperty('--student-nav-reflection-alpha', (0.34 - (ratio * 0.16)).toFixed(2));
    root.style.setProperty('--student-nav-active-reflection-alpha', (0.46 - (ratio * 0.24)).toFixed(2));
    root.style.setProperty('--student-nav-blur', `${18 + (ratio * 8)}px`);
    root.style.setProperty('--student-nav-active-blur', `${10 + (ratio * 4)}px`);
    root.style.setProperty('--student-nav-saturation', `${132 + (ratio * 16)}%`);
    root.dataset.glassTransparency = String(transparency);
    root.dataset.glassHigh = transparency >= 70 ? 'true' : 'false';

    if (document.body) {
        document.body.dataset.glassTransparency = String(transparency);
        document.body.dataset.glassHigh = transparency >= 70 ? 'true' : 'false';
    }

    if (persist) {
        window.localStorage.setItem(GLASS_TRANSPARENCY_KEY, String(transparency));
    }

    return transparency;
};

const applyTheme = (theme, persist = true) => {
    const nextTheme = normalizeTheme(theme);
    const isDark = nextTheme === 'dark';
    const isChanging = document.documentElement.dataset.theme !== nextTheme;

    if (isChanging) {
        document.documentElement.classList.add('se-theme-switching');
    }

    document.documentElement.dataset.theme = nextTheme;
    document.documentElement.style.colorScheme = nextTheme;

    if (document.body) {
        document.body.dataset.theme = nextTheme;
    }

    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        const label = isDark ? button.dataset.lightLabel : button.dataset.darkLabel;
        const accessibleLabel = isDark ? button.dataset.switchLight : button.dataset.switchDark;
        const labelElement = button.querySelector('[data-theme-label]');

        button.setAttribute('aria-pressed', isDark ? 'true' : 'false');
        button.setAttribute('aria-label', accessibleLabel || label || 'Change theme');
        button.setAttribute('title', accessibleLabel || label || 'Change theme');
        if (labelElement) {
            labelElement.textContent = label || '';
        }
    });

    const themeMeta = document.querySelector('meta[name="theme-color"]');
    if (themeMeta) {
        themeMeta.setAttribute('content', isDark ? '#090909' : '#f8f7f3');
    }

    if (persist) {
        window.localStorage.setItem(THEME_KEY, nextTheme);
    }

    if (isChanging) {
        requestAnimationFrame(() => requestAnimationFrame(() => {
            document.documentElement.classList.remove('se-theme-switching');
        }));
    }

    return nextTheme;
};

const applyAccentTheme = (theme, persist = true) => {
    const nextTheme = normalizeAccentTheme(theme);
    document.documentElement.dataset.accentTheme = nextTheme;
    document.body?.setAttribute('data-accent-theme', nextTheme);

    if (persist) {
        window.localStorage.setItem(ACCENT_THEME_KEY, nextTheme);
    }

    return nextTheme;
};

const persistThemeToServer = async (theme) => {
    const url = document.querySelector('meta[name="theme-update-url"]')?.getAttribute('content');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    if (!url || !csrf) {
        return;
    }

    await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrf,
        },
        credentials: 'same-origin',
        body: JSON.stringify({ theme }),
    });
};

const registerThemeUi = () => {
    const initialTheme = window.localStorage.getItem(THEME_KEY)
        || document.documentElement.dataset.theme
        || document.body?.dataset.theme
        || 'light';

    applyTheme(initialTheme, false);
    const initialAccentTheme = document.documentElement.dataset.accentTheme
        || document.body?.dataset.accentTheme
        || window.localStorage.getItem(ACCENT_THEME_KEY)
        || 'gold';
    applyAccentTheme(initialAccentTheme, false);
    const initialGlassTransparency = window.localStorage.getItem(GLASS_TRANSPARENCY_KEY)
        || document.documentElement.dataset.glassTransparency
        || '40';
    applyGlassTransparency(initialGlassTransparency, false);
    const settingsForm = document.querySelector('[data-settings-form]');

    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const nextTheme = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
            applyTheme(nextTheme);
            if (!settingsForm) {
                persistThemeToServer(nextTheme).catch(() => {});
            }
        });
    });

    if (settingsForm) {
        settingsForm.querySelectorAll('input[name="theme"]').forEach((input) => {
            input.addEventListener('change', () => applyTheme(input.value, false));
        });
        settingsForm.querySelectorAll('input[name="accent_theme"]').forEach((input) => {
            input.addEventListener('change', () => applyAccentTheme(input.value));
        });
        const autosave = settingsForm.querySelector('[data-settings-autosave]');
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        let saveTimer = null;
        let saveSequence = 0;

        const setAutosaveStatus = (message, state = '') => {
            if (!autosave) return;
            autosave.textContent = message;
            autosave.dataset.state = state;
        };
        const savePreferences = async () => {
            const sequence = ++saveSequence;
            setAutosaveStatus('Saving...', 'saving');
            try {
                const response = await fetch(settingsForm.action, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf || '' },
                    body: new FormData(settingsForm),
                });
                if (!response.ok) throw new Error('Unable to save changes.');
                const saved = await response.json();
                if (sequence !== saveSequence) return;
                setAutosaveStatus('Saved', 'saved');
                if (saved.locale && saved.locale !== document.documentElement.lang) {
                    window.location.reload();
                }
            } catch (_) {
                if (sequence === saveSequence) setAutosaveStatus('Could not save. Try again.', 'error');
            }
        };
        const scheduleSave = () => {
            window.clearTimeout(saveTimer);
            saveTimer = window.setTimeout(savePreferences, 280);
        };

        settingsForm.addEventListener('submit', (event) => {
            event.preventDefault();
            window.clearTimeout(saveTimer);
            savePreferences();
        });
        settingsForm.querySelectorAll('input[type="radio"]').forEach((input) => {
            input.addEventListener('change', savePreferences);
        });
        const glassInput = settingsForm.querySelector('input[name="glass_transparency"]');
        let glassPreviewFrame = null;

        glassInput?.addEventListener('input', () => {
            if (glassPreviewFrame !== null) {
                window.cancelAnimationFrame(glassPreviewFrame);
            }

            glassPreviewFrame = window.requestAnimationFrame(() => {
                applyGlassTransparency(glassInput.value, false);
                glassPreviewFrame = null;
            });
        });
        glassInput?.addEventListener('change', () => {
            if (glassPreviewFrame !== null) {
                window.cancelAnimationFrame(glassPreviewFrame);
                glassPreviewFrame = null;
            }
            applyGlassTransparency(glassInput.value);
            window.clearTimeout(saveTimer);
            savePreferences();
        });

        settingsForm.addEventListener('change', () => {
            const selectedTheme = settingsForm.querySelector('input[name="theme"]:checked')?.value;
            if (selectedTheme) {
                window.localStorage.setItem(THEME_KEY, normalizeTheme(selectedTheme));
            }
            const selectedAccentTheme = settingsForm.querySelector('input[name="accent_theme"]:checked')?.value;
            if (selectedAccentTheme) {
                applyAccentTheme(selectedAccentTheme);
            }
            const selectedGlassTransparency = settingsForm.querySelector('input[name="glass_transparency"]')?.value;
            if (selectedGlassTransparency) {
                applyGlassTransparency(selectedGlassTransparency);
            }
        });
    }
};

const registerLiquidGlassUi = () => {
    const canTrackPointer = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (!canTrackPointer || reduceMotion) {
        return;
    }

    const selector = [
        '.settings-intro',
        '.settings-panel',
        '.settings-option',
        '.header-support',
        '.header-user',
        '.se-theme-toggle',
        'body > .shell .panel',
        'body > .shell .info-card',
    ].join(',');

    document.querySelectorAll(selector).forEach((surface) => {
        let frame = null;
        let latestEvent = null;

        surface.dataset.liquidResponsive = 'true';

        surface.addEventListener('pointerenter', () => {
            surface.dataset.liquidActive = 'true';
        });

        surface.addEventListener('pointermove', (event) => {
            latestEvent = event;
            if (frame !== null) {
                return;
            }

            frame = window.requestAnimationFrame(() => {
                const rect = surface.getBoundingClientRect();
                const x = ((latestEvent.clientX - rect.left) / rect.width) * 100;
                const y = ((latestEvent.clientY - rect.top) / rect.height) * 100;

                surface.style.setProperty('--liquid-x', `${Math.max(0, Math.min(100, x))}%`);
                surface.style.setProperty('--liquid-y', `${Math.max(0, Math.min(100, y))}%`);
                frame = null;
            });
        }, { passive: true });

        surface.addEventListener('pointerleave', () => {
            if (frame !== null) {
                window.cancelAnimationFrame(frame);
                frame = null;
            }
            surface.dataset.liquidActive = 'false';
            surface.style.removeProperty('--liquid-x');
            surface.style.removeProperty('--liquid-y');
        });
    });
};

const PWA_DISPLAY_MODE_QUERIES = [
    '(display-mode: standalone)',
    '(display-mode: fullscreen)',
    '(display-mode: minimal-ui)',
    '(display-mode: window-controls-overlay)',
];

const isStandaloneMode = () =>
    PWA_DISPLAY_MODE_QUERIES.some((query) => window.matchMedia(query).matches)
    || window.navigator.standalone === true
    || document.referrer.startsWith('android-app://');

const syncPwaDisplayMode = () => {
    const body = document.body;
    if (!body) return;

    const standalone = isStandaloneMode();
    body.classList.toggle('pwa-standalone', standalone);
    body.classList.toggle(
        'has-student-bottom-nav',
        standalone && (
            body.classList.contains('student-bottom-nav-pwa-eligible')
            || body.classList.contains('student-bottom-nav-eligible')
        ),
    );
};

const isIosSafari = () => {
    const ua = window.navigator.userAgent;
    const isIos = /iPad|iPhone|iPod/.test(ua) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
    const isSafari = /Safari/.test(ua) && !/CriOS|FxiOS|EdgiOS|OPiOS/.test(ua);

    return isIos && isSafari;
};

const createPromptShell = () => {
    if (document.getElementById('pwaPrompt')) {
        return document.getElementById('pwaPrompt');
    }

    const style = document.createElement('style');
    style.textContent = `
        .pwa-prompt {
            position: fixed;
            right: 18px;
            bottom: 18px;
            z-index: 9999;
            width: min(360px, calc(100vw - 24px));
            border-radius: 22px;
            border: 1px solid var(--se-border, rgba(226, 209, 192, .18));
            background:
                radial-gradient(circle at top right, color-mix(in srgb, var(--se-accent, #c7863f) 14%, transparent), transparent 32%),
                var(--se-surface, #ffffff);
            color: var(--se-text, #211a14);
            box-shadow: var(--se-shadow-lg, 0 28px 60px rgba(0,0,0,.20));
            overflow: hidden;
            opacity: 0;
            transform: translateY(14px);
            pointer-events: none;
            transition: opacity .24s ease, transform .24s ease;
            font-family: "Plus Jakarta Sans", "Inter", system-ui, sans-serif;
        }
        .pwa-prompt.is-visible {
            opacity: 1;
            transform: translateY(0);
            pointer-events: auto;
        }
        .pwa-prompt-body {
            padding: 16px 16px 14px;
            display: grid;
            gap: 10px;
        }
        .pwa-prompt-kicker {
            display: inline-flex;
            align-items: center;
            width: fit-content;
            padding: 6px 10px;
            border-radius: 999px;
            background: var(--se-primary-soft, #f5eadc);
            color: var(--se-primary-strong, #7d582f);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }
        .pwa-prompt-title {
            margin: 0;
            font-size: 1.02rem;
            font-weight: 800;
            letter-spacing: -.02em;
        }
        .pwa-prompt-copy {
            margin: 0;
            color: var(--se-text-soft, #65584d);
            font-size: .88rem;
            line-height: 1.6;
            overflow-wrap: anywhere;
        }
        .pwa-prompt-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
        }
        .pwa-prompt-btn {
            appearance: none;
            border: 1px solid var(--se-border-strong, #ccb79e);
            border-radius: 14px;
            background: var(--se-surface-soft, #fbfaf7);
            color: var(--se-text, #211a14);
            font: inherit;
            font-size: .86rem;
            font-weight: 800;
            padding: 11px 14px;
            cursor: pointer;
            transition: transform .18s ease, border-color .18s ease, background-color .18s ease;
        }
        .pwa-prompt-btn:hover {
            transform: translateY(-1px);
            border-color: var(--se-primary-muted, #dbc3a4);
            background: var(--se-primary-soft, #f5eadc);
        }
        .pwa-prompt-btn.primary {
            background: linear-gradient(135deg, var(--se-primary-strong, #7d582f), var(--se-primary, #b18452));
            color: #fff;
            border-color: var(--se-primary-strong, #7d582f);
            box-shadow: 0 12px 28px rgba(112, 77, 36, .24);
        }
        html[data-theme="dark"] .pwa-prompt-btn.primary,
        body[data-theme="dark"] .pwa-prompt-btn.primary { color: #21160c; }
        .pwa-prompt-btn.link {
            border-color: transparent;
            background: transparent;
            color: var(--se-text-muted, #8b7c6f);
            padding-left: 0;
            padding-right: 0;
        }
        .pwa-prompt-steps {
            margin: 0;
            padding-left: 18px;
            color: var(--se-text-soft, #65584d);
            font-size: .84rem;
            line-height: 1.6;
        }
        @media (max-width: 640px) {
            .pwa-prompt {
                left: 12px;
                right: 12px;
                bottom: 12px;
                width: auto;
                max-height: min(44vh, 320px);
                overflow-y: auto;
                border-radius: 20px;
            }
            body.student-bottom-nav-eligible .pwa-prompt,
            body.has-student-bottom-nav .pwa-prompt {
                bottom: calc(var(--student-bottom-nav-total, 5rem) + var(--student-bottom-nav-overlay-gap, 14px));
                max-height: min(34vh, 250px);
            }
            .pwa-prompt-body {
                min-width: 0;
                padding: 13px;
                gap: 7px;
            }
            .pwa-prompt-kicker {
                padding: 5px 8px;
                font-size: 9px;
            }
            .pwa-prompt-title {
                font-size: .94rem;
            }
            .pwa-prompt-copy,
            .pwa-prompt-steps {
                font-size: .78rem;
                line-height: 1.45;
            }
            body.student-bottom-nav-eligible .pwa-prompt-copy,
            body.has-student-bottom-nav .pwa-prompt-copy {
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }
            body.student-bottom-nav-eligible .pwa-prompt-actions,
            body.has-student-bottom-nav .pwa-prompt-actions {
                display: grid;
                grid-template-columns: minmax(0, 1fr) auto;
                gap: 8px;
                align-items: center;
            }
            .pwa-prompt-btn {
                padding: 9px 12px;
                border-radius: 12px;
                font-size: .8rem;
            }
            body.student-bottom-nav-eligible .pwa-prompt-btn,
            body.has-student-bottom-nav .pwa-prompt-btn {
                flex: initial;
                min-height: 40px;
                text-align: center;
            }
            body.student-bottom-nav-eligible .pwa-prompt-btn.link,
            body.has-student-bottom-nav .pwa-prompt-btn.link {
                flex: initial;
                padding-inline: 8px;
                white-space: nowrap;
            }
            @media (max-width: 360px) {
                body.student-bottom-nav-eligible .pwa-prompt,
                body.has-student-bottom-nav .pwa-prompt {
                    bottom: calc(var(--student-bottom-nav-total, 5rem) + 10px);
                }
                body.student-bottom-nav-eligible .pwa-prompt-actions,
                body.has-student-bottom-nav .pwa-prompt-actions {
                    grid-template-columns: 1fr;
                }
                body.student-bottom-nav-eligible .pwa-prompt-btn.link,
                body.has-student-bottom-nav .pwa-prompt-btn.link {
                    padding-block: 4px;
                }
            }
        }
    `;
    document.head.appendChild(style);

    const shell = document.createElement('section');
    shell.id = 'pwaPrompt';
    shell.className = 'pwa-prompt';
    shell.innerHTML = '<div class="pwa-prompt-body"></div>';
    document.body.appendChild(shell);

    return shell;
};

const hidePrompt = (persistDismissal = false) => {
    const shell = document.getElementById('pwaPrompt');
    if (!shell) {
        return;
    }

    if (persistDismissal) {
        window.localStorage.setItem(PWA_PROMPT_KEY, '1');
    }

    shell.classList.remove('is-visible');
};

const clearPromptDismissal = (key) => {
    window.localStorage.removeItem(key);
};

const showPrompt = ({ kicker, title, copy, actions = '', extra = '', dismissalKey = null }) => {
    if (dismissalKey && window.localStorage.getItem(dismissalKey) === '1') {
        return;
    }

    const shell = createPromptShell();
    shell.querySelector('.pwa-prompt-body').innerHTML = `
        <span class="pwa-prompt-kicker">${kicker}</span>
        <h2 class="pwa-prompt-title">${title}</h2>
        <p class="pwa-prompt-copy">${copy}</p>
        ${extra}
        <div class="pwa-prompt-actions">${actions}</div>
    `;
    shell.classList.add('is-visible');
}

const getCsrfToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

const getPushConfig = () => window.studentEdgePush || null;

const urlBase64ToUint8Array = (base64String) => {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);

    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }

    return outputArray;
};

const syncPushSubscription = async () => {
    const config = getPushConfig();
    const csrf = getCsrfToken();

    if (!config?.enabled || !config?.authenticated || !config?.publicKey || !csrf) {
        return false;
    }

    if (!('serviceWorker' in navigator) || !('PushManager' in window) || !('Notification' in window)) {
        return false;
    }

    if (Notification.permission !== 'granted') {
        return false;
    }

    const registration = await navigator.serviceWorker.ready;
    let subscription = await registration.pushManager.getSubscription();

    if (!subscription) {
        subscription = await registration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: urlBase64ToUint8Array(config.publicKey),
        });
    }

    const payload = subscription.toJSON();
    payload.contentEncoding = window.PushManager?.supportedContentEncodings?.[0] || 'aes128gcm';

    await fetch(config.subscribeUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrf,
        },
        credentials: 'same-origin',
        body: JSON.stringify(payload),
    });

    return true;
};

const unlinkPushSubscription = async (useKeepalive = false) => {
    const config = getPushConfig();
    const csrf = getCsrfToken();

    if (!config?.authenticated || !csrf || !('serviceWorker' in navigator)) {
        return;
    }

    try {
        const registration = await navigator.serviceWorker.ready;
        const subscription = await registration.pushManager.getSubscription();

        if (!subscription) {
            return;
        }

        fetch(config.unsubscribeUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
            credentials: 'same-origin',
            keepalive: useKeepalive,
            body: JSON.stringify({ endpoint: subscription.endpoint }),
        }).catch(() => {});
    } catch (error) {
        // Keep logout reliable even if push cleanup fails.
    }
};

const registerPwaPromptUi = () => {
    let deferredPrompt = null;

    window.addEventListener('beforeinstallprompt', (event) => {
        if (!['/student/dashboard', '/admin/dashboard'].includes(window.location.pathname)) {
            return;
        }

        event.preventDefault();
        deferredPrompt = event;

        showPrompt({
            kicker: 'Install App',
            title: 'Install MyHEP',
            copy: 'Add MyHEP to your home screen for faster access and a cleaner mobile app experience.',
            dismissalKey: PWA_PROMPT_KEY,
            actions: `
                <button type="button" class="pwa-prompt-btn primary" id="pwaInstallBtn">Install now</button>
                <button type="button" class="pwa-prompt-btn link" id="pwaDismissBtn">Not now</button>
            `,
        });

        document.getElementById('pwaInstallBtn')?.addEventListener('click', async () => {
            if (!deferredPrompt) {
                return;
            }

            deferredPrompt.prompt();
            await deferredPrompt.userChoice.catch(() => null);
            deferredPrompt = null;
            hidePrompt(false);
        });

        document.getElementById('pwaDismissBtn')?.addEventListener('click', () => {
            hidePrompt(true);
        });
    });

    window.addEventListener('appinstalled', () => {
        deferredPrompt = null;
        hidePrompt(false);
    });

    if (isIosSafari() && !isStandaloneMode() && !window.localStorage.getItem(PWA_PROMPT_KEY)) {
        showPrompt({
            kicker: 'Add to Home Screen',
            title: 'Install MyHEP on iPhone',
            copy: 'Safari does not show the standard install popup. Use the share menu once to pin MyHEP like an app.',
            dismissalKey: PWA_PROMPT_KEY,
            extra: `
                <ol class="pwa-prompt-steps">
                    <li>Tap the <strong>Share</strong> button in Safari.</li>
                    <li>Choose <strong>Add to Home Screen</strong>.</li>
                    <li>Tap <strong>Add</strong> to finish.</li>
                </ol>
            `,
            actions: `
                <button type="button" class="pwa-prompt-btn link" id="pwaDismissBtn">Close</button>
            `,
        });

        document.getElementById('pwaDismissBtn')?.addEventListener('click', () => {
            hidePrompt(true);
        });
    }
};

const registerPushPromptUi = () => {
    const config = getPushConfig();

    if (!config?.enabled || !config?.authenticated) {
        return;
    }

    if (!('Notification' in window) || !('PushManager' in window) || !('serviceWorker' in navigator)) {
        return;
    }

    if (!isStandaloneMode()) {
        return;
    }

    if (Notification.permission === 'granted') {
        clearPromptDismissal(PUSH_PROMPT_KEY);
        syncPushSubscription().catch(() => {});
        return;
    }

    if (Notification.permission === 'denied' || window.localStorage.getItem(PUSH_PROMPT_KEY) === '1') {
        return;
    }

    showPrompt({
        kicker: config.prompt?.kicker || 'Notifications',
        title: config.prompt?.title || 'Turn on push notifications',
        copy: config.prompt?.copy || 'Get instant alerts when fines, stickers, and important account updates happen.',
        actions: `
            <button type="button" class="pwa-prompt-btn primary" id="pushEnableBtn">${config.prompt?.enable || 'Enable notifications'}</button>
            <button type="button" class="pwa-prompt-btn link" id="pushLaterBtn">${config.prompt?.later || 'Maybe later'}</button>
        `,
    });

    document.getElementById('pushEnableBtn')?.addEventListener('click', async () => {
        try {
            const permission = await Notification.requestPermission();

            if (permission === 'granted') {
                clearPromptDismissal(PUSH_PROMPT_KEY);
                hidePrompt(false);
                syncPushSubscription().catch(() => {});
                return;
            }
        } catch (error) {
            // Keep the app usable even if permission request fails.
        }

        window.localStorage.setItem(PUSH_PROMPT_KEY, '1');
        hidePrompt(false);
    });

    document.getElementById('pushLaterBtn')?.addEventListener('click', () => {
        window.localStorage.setItem(PUSH_PROMPT_KEY, '1');
        hidePrompt(false);
    });
};

const registerLogoutPushCleanup = () => {
    const config = getPushConfig();

    if (!config?.authenticated) {
        return;
    }

    document.querySelectorAll('form[action$="/logout"]').forEach((form) => {
        form.addEventListener('submit', () => {
            unlinkPushSubscription(true);
        });
    });
};

const getUiConfig = () => window.studentEdgeUi || { labels: {} };

const setSurfaceOrigin = (surface, trigger, prefix = 'se-popup-origin') => {
    if (!surface || !trigger) return;

    const triggerRect = trigger.getBoundingClientRect();
    const surfaceRect = surface.getBoundingClientRect();
    const x = Math.max(0, Math.min(surfaceRect.width, triggerRect.left + (triggerRect.width / 2) - surfaceRect.left));
    const y = Math.max(0, Math.min(surfaceRect.height, triggerRect.top + (triggerRect.height / 2) - surfaceRect.top));

    surface.style.setProperty(`--${prefix}-x`, `${x}px`);
    surface.style.setProperty(`--${prefix}-y`, `${y}px`);
};

const registerNotificationCenter = () => {
    const config = getUiConfig();
    const center = document.getElementById('notificationCenter');
    const list = center?.querySelector('[data-notification-list]');
    const panel = center?.querySelector('.se-notification-panel');
    const triggers = Array.from(document.querySelectorAll('[data-notification-trigger]'));

    if (!config.authenticated || !config.notificationUrl || !center || !list || triggers.length === 0) {
        return;
    }

    let loading = false;
    let loaded = false;

    const positionForMobile = () => {
        if (window.innerWidth > 767) {
            center.style.removeProperty('--se-notification-top');
            return;
        }

        const visibleHeaders = Array.from(document.querySelectorAll('.app-layout .topbar, .app-layout .page-header'))
            .filter((element) => window.getComputedStyle(element).display !== 'none')
            .map((element) => element.getBoundingClientRect())
            .filter((rect) => rect.bottom > 0 && rect.top < window.innerHeight);
        const lowerEdge = visibleHeaders.reduce((maximum, rect) => Math.max(maximum, rect.bottom), 0);
        const safeTop = Math.max(10, Math.min(window.innerHeight - 120, Math.round(lowerEdge + 10)));

        center.style.setProperty('--se-notification-top', `${safeTop}px`);
    };

    const setCount = (count) => {
        document.querySelectorAll('[data-notification-count]').forEach((badge) => {
            const safeCount = Math.max(0, Number(count) || 0);
            badge.textContent = safeCount > 99 ? '99+' : String(safeCount);
            badge.hidden = safeCount === 0;
        });
    };

    const renderMessage = (message) => {
        const empty = document.createElement('div');
        empty.className = 'se-notification-empty';
        empty.textContent = message;
        list.replaceChildren(empty);
    };

    const renderItems = (items) => {
        if (!Array.isArray(items) || items.length === 0) {
            renderMessage(config.labels?.notificationEmpty || 'There are no notifications to show.');
            return;
        }

        const fragment = document.createDocumentFragment();

        items.forEach((item) => {
            const link = document.createElement('a');
            link.className = 'se-notification-item is-entering';
            link.href = item.url || '#';
            link.dataset.tone = item.tone || 'info';
            link.addEventListener('click', () => close());

            const icon = document.createElement('span');
            icon.className = 'se-notification-item-icon';
            icon.setAttribute('aria-hidden', 'true');
            icon.textContent = String(item.type || 'system').slice(0, 2).toUpperCase();

            const copy = document.createElement('span');
            copy.className = 'se-notification-item-copy';
            const title = document.createElement('strong');
            const body = document.createElement('span');
            title.textContent = item.title || '';
            body.textContent = item.body || '';
            copy.append(title, body);

            const time = document.createElement('span');
            time.className = 'se-notification-time';
            time.textContent = item.time || '';

            link.append(icon, copy, time);
            fragment.append(link);
        });

        list.replaceChildren(fragment);
        window.requestAnimationFrame(() => {
            list.querySelectorAll('.se-notification-item.is-entering').forEach((item) => {
                item.classList.remove('is-entering');
            });
        });
    };

    const load = async () => {
        if (loading) return;
        loading = true;

        try {
            const response = await fetch(config.notificationUrl, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });

            if (!response.ok) throw new Error('Notification request failed');
            const payload = await response.json();
            setCount(payload.count);
            renderItems(payload.items);
            loaded = true;
        } catch (error) {
            renderMessage(config.labels?.notificationError || 'Notifications could not be loaded. Try again.');
        } finally {
            loading = false;
        }
    };

    const close = () => {
        center.classList.remove('is-open');
        center.setAttribute('aria-hidden', 'true');
        triggers.forEach((trigger) => trigger.setAttribute('aria-expanded', 'false'));
        if (!document.querySelector('.se-media-modal.is-open, .se-filter-sheet.is-open')) {
            document.body.style.overflow = '';
        }
    };

    const open = (trigger) => {
        positionForMobile();
        setSurfaceOrigin(panel, trigger, 'se-notification-origin');
        center.classList.add('is-open');
        center.setAttribute('aria-hidden', 'false');
        triggers.forEach((trigger) => trigger.setAttribute('aria-expanded', 'true'));
        if (window.innerWidth <= 767) document.body.style.overflow = 'hidden';
        if (!loaded) load();
    };

    triggers.forEach((trigger) => {
        trigger.addEventListener('click', (event) => {
            event.stopPropagation();
            center.classList.contains('is-open') ? close() : open(trigger);
        });
    });

    center.querySelector('[data-notification-close]')?.addEventListener('click', close);
    center.addEventListener('click', (event) => {
        if (event.target === center) close();
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && center.classList.contains('is-open')) close();
    });
    window.addEventListener('resize', () => {
        if (center.classList.contains('is-open')) positionForMobile();
    });

    load();
};

const registerMediaViewer = () => {
    const config = getUiConfig();
    const modal = document.getElementById('mediaPreviewModal');
    const dialog = modal?.querySelector('.se-media-dialog');
    const stage = modal?.querySelector('[data-media-stage]');
    const title = document.getElementById('mediaPreviewTitle');
    const openLink = modal?.querySelector('[data-media-open]');
    const downloadLink = modal?.querySelector('[data-media-download]');
    const supportedFile = /\.(?:avif|gif|jpe?g|png|webp|pdf)(?:[?#].*)?$/i;

    if (!modal || !stage || !title || !openLink || !downloadLink) return;

    let returnFocus = null;

    const close = () => {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        window.setTimeout(() => {
            if (!modal.classList.contains('is-open')) {
                stage.replaceChildren();
            }
        }, 420);
        if (!document.querySelector('.se-notification-center.is-open, .se-filter-sheet.is-open')) {
            document.body.style.overflow = '';
        }
        returnFocus?.focus?.();
    };

    const open = (anchor) => {
        const url = new URL(anchor.href, window.location.href);
        const label = anchor.dataset.mediaTitle
            || anchor.querySelector('img')?.alt
            || anchor.textContent.trim()
            || config.labels?.mediaPreview
            || 'File preview';
        const isPdf = /\.pdf(?:[?#].*)?$/i.test(url.href);
        const media = document.createElement(isPdf ? 'iframe' : 'img');

        if (isPdf) {
            media.src = url.href;
            media.title = label;
        } else {
            media.src = url.href;
            media.alt = label;
        }

        title.textContent = label;
        openLink.href = url.href;
        downloadLink.href = url.href;
        stage.replaceChildren(media);
        returnFocus = anchor;
        setSurfaceOrigin(dialog, anchor);
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        modal.querySelector('[data-media-close]')?.focus();
    };

    document.addEventListener('click', (event) => {
        const anchor = event.target instanceof Element
            ? event.target.closest('a[data-media-viewer], .page-body a[target="_blank"]')
            : null;

        if (!anchor || anchor.hasAttribute('data-media-ignore') || !supportedFile.test(anchor.href)) return;
        if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
        event.preventDefault();
        open(anchor);
    });

    modal.querySelectorAll('[data-media-close]').forEach((button) => button.addEventListener('click', close));
    modal.addEventListener('click', (event) => {
        if (event.target === modal) close();
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && modal.classList.contains('is-open')) close();
    });
};

const registerLiquidFilterSheets = () => {
    const config = getUiConfig();
    const mediaQuery = window.matchMedia('(max-width: 767px)');

    document.querySelectorAll('[data-filter-sheet]').forEach((target, index) => {
        const parent = target.parentNode;
        if (!parent) return;

        const placeholder = document.createComment(`studentedge-filter-${index}`);
        const trigger = document.createElement('button');
        const backdrop = document.createElement('div');
        const sheet = document.createElement('section');
        const headingId = `filterSheetTitle${index}`;
        const label = target.dataset.filterTitle || config.labels?.filters || 'Filters';

        trigger.type = 'button';
        trigger.className = 'se-filter-toggle';
        trigger.innerHTML = '<svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M4 6h16M7 12h10M10 18h4"/></svg><span></span>';
        trigger.querySelector('span').textContent = label;
        trigger.setAttribute('aria-expanded', 'false');
        trigger.setAttribute('aria-controls', `filterSheet${index}`);

        backdrop.className = 'se-filter-backdrop';
        backdrop.setAttribute('aria-hidden', 'true');

        sheet.className = 'se-filter-sheet';
        sheet.id = `filterSheet${index}`;
        sheet.setAttribute('aria-hidden', 'true');
        sheet.setAttribute('aria-labelledby', headingId);
        sheet.innerHTML = `
            <div class="se-filter-sheet-head">
                <div><span class="se-filter-sheet-kicker">MyHEP</span><h2 id="${headingId}"></h2></div>
                <button type="button" class="se-icon-button" data-filter-close aria-label="${config.labels?.closeFilters || 'Close filters'}">&times;</button>
            </div>
            <div class="se-filter-sheet-body"></div>
        `;
        sheet.querySelector('h2').textContent = label;

        parent.insertBefore(placeholder, target);
        parent.insertBefore(trigger, target);
        document.body.append(backdrop, sheet);

        const sheetBody = sheet.querySelector('.se-filter-sheet-body');

        const close = () => {
            sheet.classList.remove('is-open');
            sheet.setAttribute('aria-hidden', 'true');
            backdrop.classList.remove('is-open');
            backdrop.setAttribute('aria-hidden', 'true');
            trigger.setAttribute('aria-expanded', 'false');
            if (!document.querySelector('.se-notification-center.is-open, .se-media-modal.is-open')) {
                document.body.style.overflow = '';
            }
        };

        const open = () => {
            setSurfaceOrigin(sheet, trigger);
            sheet.classList.add('is-open');
            sheet.setAttribute('aria-hidden', 'false');
            backdrop.classList.add('is-open');
            backdrop.setAttribute('aria-hidden', 'false');
            trigger.setAttribute('aria-expanded', 'true');
            document.body.style.overflow = 'hidden';
            requestAnimationFrame(() => {
                if (!sheet.classList.contains('is-open')) return;
                const focusTarget = sheet.querySelector('input, select, button:not([data-filter-close])');

                try {
                    focusTarget?.focus({ preventScroll: true });
                } catch {
                    focusTarget?.focus();
                }
            });
        };

        const sync = () => {
            close();
            if (mediaQuery.matches) {
                sheetBody.append(target);
                return;
            }

            placeholder.parentNode?.insertBefore(target, trigger.nextSibling);
        };

        trigger.addEventListener('click', open);
        backdrop.addEventListener('click', close);
        sheet.querySelector('[data-filter-close]')?.addEventListener('click', close);
        const filterForm = target.matches('form') ? target : target.querySelector('form');
        filterForm?.addEventListener('submit', close);
        mediaQuery.addEventListener('change', sync);
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && sheet.classList.contains('is-open')) close();
        });
        sync();
    });
};

const registerLoadingUi = () => {
    const setLoading = (form = null, submitter = null) => {
        document.body.classList.add('is-navigating');

        const button = submitter || form?.querySelector('button[type="submit"], input[type="submit"]');
        if (button) {
            button.classList.add('is-submit-loading');
            button.setAttribute('aria-busy', 'true');
            button.style.pointerEvents = 'none';
            setTimeout(() => {
                button.disabled = true;
            }, 0);
        }

        const target = form?.closest('.ui-card, .card, .panel, .data-card, .bugs-card, .settings-panel');
        target?.classList.add('liquid-loading-target');
    };

    window.studentEdgeSetLoading = setLoading;

    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement)
            || event.defaultPrevented
            || form.hasAttribute('data-confirm-message')
            || form.hasAttribute('data-live-filter-form')) return;
        setLoading(form, event.submitter);
    });

    document.addEventListener('click', (event) => {
        if (event.defaultPrevented) return;
        const anchor = event.target instanceof Element ? event.target.closest('a[href]') : null;
        if (!anchor || anchor.target || anchor.hasAttribute('download') || anchor.dataset.mediaViewer !== undefined) return;
        if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey || event.button !== 0) return;

        const url = new URL(anchor.href, window.location.href);
        if (url.origin !== window.location.origin || (url.protocol !== 'http:' && url.protocol !== 'https:')) return;
        if (url.pathname === window.location.pathname && url.search === window.location.search && url.hash) return;
        document.body.classList.add('is-navigating');
    });

    window.addEventListener('pageshow', () => {
        document.body.classList.remove('is-navigating');
        document.querySelectorAll('.is-submit-loading').forEach((button) => {
            button.classList.remove('is-submit-loading');
            button.removeAttribute('aria-busy');
            button.disabled = false;
        });
        document.querySelectorAll('.liquid-loading-target').forEach((target) => target.classList.remove('liquid-loading-target'));
    });
};

const registerTabMotionUi = () => {
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reduceMotion) return;

    const fadePanel = (panel) => {
        if (!panel) return;
        panel.classList.add('se-tab-panel-fading');
        window.setTimeout(() => panel.classList.remove('se-tab-panel-fading'), 150);
    };

    document.addEventListener('click', (event) => {
        const tab = event.target instanceof Element
            ? event.target.closest('[role="tab"], [data-tab-target], [data-bs-toggle="tab"]')
            : null;

        if (!tab) return;

        const targetSelector = tab.getAttribute('aria-controls')
            ? `#${CSS.escape(tab.getAttribute('aria-controls'))}`
            : tab.getAttribute('data-tab-target')
                || tab.getAttribute('data-bs-target')
                || tab.getAttribute('href');

        if (!targetSelector || targetSelector === '#') return;

        try {
            fadePanel(document.querySelector(targetSelector));
        } catch {
            // Ignore non-selector hrefs.
        }
    }, true);
};

const registerProfilePhotoCropper = () => {
    const input = document.querySelector('[data-profile-photo-input]');
    const modal = document.querySelector('[data-profile-crop-modal]');
    const cropImage = modal?.querySelector('[data-profile-crop-image]');
    const preview = document.querySelector('[data-profile-photo-preview]');
    const placeholder = document.querySelector('[data-profile-photo-placeholder]');

    if (!(input instanceof HTMLInputElement) || !modal || !(cropImage instanceof HTMLImageElement)) return;

    let cropper = null;
    let sourceUrl = null;
    let previewUrl = null;
    let acceptedFile = null;

    const setInputFile = (file) => {
        const transfer = new DataTransfer();
        if (file) transfer.items.add(file);
        input.files = transfer.files;
    };

    const closeCropper = (restoreAccepted = false) => {
        cropper?.destroy();
        cropper = null;
        if (sourceUrl) URL.revokeObjectURL(sourceUrl);
        sourceUrl = null;
        cropImage.removeAttribute('src');
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('profile-crop-open');
        if (restoreAccepted) setInputFile(acceptedFile);
        input.focus({ preventScroll: true });
    };

    const statusPill = modal?.querySelector('[data-face-detection-status]');
    const guideOverlay = modal?.querySelector('[data-face-guide-overlay]');
    const profileStandardEnabled = modal.dataset.profileStandardEnabled === 'true';

    const getLocalizedText = (key, defaultText) => modal.dataset[key] || defaultText;

    let lastDetectedFaces = null;
    let evalTimeout = null;

    const updateStatusPill = (status, text) => {
        if (!statusPill) return;
        statusPill.className = `face-detect-status is-${status}`;
        statusPill.innerHTML = status === 'detected'
            ? `<span>✓</span> <span>${text || getLocalizedText('textVerified', 'Face Verified')}</span>`
            : (status === 'missing'
                ? `<span>⚠️</span> <span>${text || getLocalizedText('textUnclear', 'Unclear Face')}</span>`
                : `<span>🔍</span> <span>${text || getLocalizedText('textEvaluating', 'Evaluating Face...')}</span>`);
    };

    const analyzeFacePresence = async (canvas) => {
        if (!canvas) return { detected: true, reason: 'unsupported' };

        // 1. Native Shape Detection API (Chrome / Edge / Chromium Android)
        if ('FaceDetector' in window) {
            try {
                const detector = new window.FaceDetector({ fastMode: true, maxDetectedFaces: 5 });
                const faces = await detector.detect(canvas);
                if (faces && faces.length > 0) {
                    return { detected: true, count: faces.length, source: 'ai' };
                }
                return { detected: false, count: 0, source: 'ai' };
            } catch (err) {
                // Fallback to pixel analysis
            }
        }

        // 2. Client-side Skin Tone & Feature Variance Heuristic
        try {
            const ctx = canvas.getContext('2d', { willReadFrequently: true });
            if (!ctx) return { detected: true, reason: 'fallback' };

            const w = canvas.width;
            const h = canvas.height;
            const imgData = ctx.getImageData(Math.floor(w * 0.2), Math.floor(h * 0.15), Math.floor(w * 0.6), Math.floor(h * 0.6));
            const data = imgData.data;

            let skinPixels = 0;
            let totalChecked = 0;
            let luminanceSum = 0;

            for (let i = 0; i < data.length; i += 16) {
                const r = data[i];
                const g = data[i + 1];
                const b = data[i + 2];
                totalChecked++;

                // YCbCr conversion
                const y = 0.299 * r + 0.587 * g + 0.114 * b;
                const cb = 128 - 0.168736 * r - 0.331264 * g + 0.5 * b;
                const cr = 128 + 0.5 * r - 0.418688 * g - 0.081312 * b;
                luminanceSum += y;

                // Human skin tone chroma bounding box
                if (r > 60 && g > 40 && b > 20 && r > b && (r - g) >= 10 && cr >= 130 && cr <= 178 && cb >= 75 && cb <= 130) {
                    skinPixels++;
                }
            }

            const skinRatio = totalChecked > 0 ? (skinPixels / totalChecked) : 0;
            // Face detected if central region has reasonable proportion of human portrait tones
            if (skinRatio >= 0.12 && skinRatio <= 0.92) {
                return { detected: true, count: 1, source: 'heuristic' };
            }
            return { detected: false, count: 0, source: 'heuristic' };
        } catch (e) {
            return { detected: true, reason: 'error_fallback' };
        }
    };

    const triggerFaceCheck = () => {
        if (!profileStandardEnabled || !cropper) return;
        if (evalTimeout) clearTimeout(evalTimeout);
        updateStatusPill('checking', getLocalizedText('textEvaluating', 'Evaluating Face...'));

        evalTimeout = setTimeout(async () => {
            if (!cropper) return;
            const testCanvas = cropper.getCroppedCanvas({ width: 320, height: 320, imageSmoothingEnabled: false });
            if (!testCanvas) return;

            const res = await analyzeFacePresence(testCanvas);
            lastDetectedFaces = res;

            if (res.detected) {
                updateStatusPill('detected', res.count > 1 ? `${res.count} ${getLocalizedText('textVerified', 'Faces Detected')}` : getLocalizedText('textVerified', 'Face Verified'));
            } else {
                updateStatusPill('missing', getLocalizedText('textUnclear', 'Unclear Face'));
            }
        }, 320);
    };

    let CropperClass = null;
    const getCropper = async () => {
        if (!CropperClass) {
            const module = await import('cropperjs');
            await import('cropperjs/dist/cropper.css');
            CropperClass = module.default;
        }
        return CropperClass;
    };

    const openCropper = async (file) => {
        sourceUrl = URL.createObjectURL(file);
        cropImage.src = sourceUrl;
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('profile-crop-open');
        if (profileStandardEnabled) {
            updateStatusPill('checking', getLocalizedText('textEvaluating', 'Evaluating Face...'));
        }

        const Cropper = await getCropper();
        cropper = new Cropper(cropImage, {
            aspectRatio: 1,
            viewMode: 1,
            dragMode: 'move',
            autoCropArea: 1,
            background: false,
            guides: false,
            center: true,
            highlight: false,
            cropBoxMovable: false,
            cropBoxResizable: false,
            toggleDragModeOnDblclick: false,
            responsive: true,
            restore: false,
            ready() {
                triggerFaceCheck();
            },
            cropend() {
                triggerFaceCheck();
            },
            zoom() {
                triggerFaceCheck();
            },
        });
    };

    input.addEventListener('change', () => {
        const file = input.files?.[0];
        if (!file) return;
        if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
            input.setCustomValidity(input.dataset.invalidType || 'Choose a JPG, PNG, or WEBP image.');
            input.reportValidity();
            setInputFile(acceptedFile);
            return;
        }
        input.setCustomValidity('');
        openCropper(file);
    });

    modal.addEventListener('click', (event) => {
        const action = event.target instanceof Element
            ? event.target.closest('[data-profile-crop-action]')?.getAttribute('data-profile-crop-action')
            : null;

        if (!action || !cropper) return;

        if (action === 'cancel') {
            closeCropper(true);
            return;
        }
        if (action === 'rotate-left') {
            cropper.rotate(-90);
            triggerFaceCheck();
        }
        if (action === 'rotate-right') {
            cropper.rotate(90);
            triggerFaceCheck();
        }
        if (action === 'reset') {
            cropper.reset();
            triggerFaceCheck();
        }
        if (action === 'toggle-guide') {
            if (!profileStandardEnabled) return;
            guideOverlay?.classList.toggle('is-hidden');
        }

        if (action === 'apply') {
            if (profileStandardEnabled && lastDetectedFaces && !lastDetectedFaces.detected) {
                const warningMsg = getLocalizedText('textConfirmWarning', 'The system detected that your face may be unclear or outside the oval guide. Are you sure you want to use this photo as your official matric photo?');
                const proceed = confirm(warningMsg);
                if (!proceed) return;
            }

            const canvas = cropper.getCroppedCanvas({
                width: 800,
                height: 800,
                fillColor: '#ffffff',
                imageSmoothingEnabled: true,
                imageSmoothingQuality: 'high',
            });

            canvas.toBlob((blob) => {
                if (!blob) return;
                const baseName = (input.files?.[0]?.name || 'profile-photo').replace(/\.[^.]+$/, '');
                acceptedFile = new File([blob], `${baseName}-cropped.jpg`, {
                    type: 'image/jpeg',
                    lastModified: Date.now(),
                });
                setInputFile(acceptedFile);

                if (preview instanceof HTMLImageElement) {
                    if (previewUrl) URL.revokeObjectURL(previewUrl);
                    previewUrl = URL.createObjectURL(blob);
                    preview.src = previewUrl;
                    preview.hidden = false;
                }
                if (placeholder instanceof HTMLElement) placeholder.hidden = true;
                closeCropper(false);
            }, 'image/jpeg', 0.92);
        }
    });

    modal.addEventListener('click', (event) => {
        if (event.target === modal) closeCropper(true);
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && modal.classList.contains('is-open')) closeCropper(true);
    });
};

const registerBackToTop = () => {
    const button = document.getElementById('seBackToTop');
    const viewport = document.querySelector('[data-main-scroll]') || document.querySelector('[data-lenis-main]');

    if (!(button instanceof HTMLButtonElement) || !(viewport instanceof HTMLElement)) {
        return;
    }

    let frame = 0;
    const sync = () => {
        frame = 0;
        const threshold = Math.max(360, viewport.clientHeight * .55);
        const visible = viewport.scrollTop > threshold;
        button.classList.toggle('is-visible', visible);
        button.setAttribute('aria-hidden', visible ? 'false' : 'true');
        button.tabIndex = visible ? 0 : -1;
    };

    viewport.addEventListener('scroll', () => {
        if (!frame) frame = window.requestAnimationFrame(sync);
    }, { passive: true });

    button.addEventListener('click', () => {
        viewport.scrollTo({
            top: 0,
            behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth',
        });
    });

    sync();
};

const registerAiSessionCleanup = () => {
    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || !form.action.endsWith('/logout')) return;

        try {
            Object.keys(sessionStorage)
                .filter((key) => key.startsWith('studentedge.ai.active.'))
                .forEach((key) => sessionStorage.removeItem(key));
        } catch (_) {
            // Logout must continue even when browser storage is unavailable.
        }
    });
};

if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        const isLocalhost = ['localhost', '127.0.0.1', '::1'].includes(window.location.hostname);
        const canRegister = window.isSecureContext || isLocalhost;

        if (!canRegister) {
            return;
        }

        navigator.serviceWorker.register('/sw.js?v=13').catch(() => {
            // Keep the app usable even if PWA registration fails.
        });
    });
}

window.addEventListener('DOMContentLoaded', () => {
    registerVirtualTables();
    initializeLiveFilters();
    syncPwaDisplayMode();
    registerThemeUi();
    registerLiquidGlassUi();
    registerNotificationCenter();
    registerMediaViewer();
    registerLiquidFilterSheets();
    registerLoadingUi();
    registerTabMotionUi();
    registerProfilePhotoCropper();
    registerBackToTop();
    registerAiSessionCleanup();
    registerPwaPromptUi();
    registerPushPromptUi();
    registerLogoutPushCleanup();
});

PWA_DISPLAY_MODE_QUERIES.forEach((query) => {
    window.matchMedia(query).addEventListener?.('change', syncPwaDisplayMode);
});
