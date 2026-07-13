const PWA_PROMPT_KEY = 'studentedge-pwa-dismissed-v1';
const PUSH_PROMPT_KEY = 'studentedge-push-dismissed-v1';
const THEME_KEY = 'studentedge-theme';

const normalizeTheme = (theme) => (theme === 'dark' ? 'dark' : 'light');

const applyTheme = (theme, persist = true) => {
    const nextTheme = normalizeTheme(theme);
    const isDark = nextTheme === 'dark';

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

    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const nextTheme = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
            applyTheme(nextTheme);
            persistThemeToServer(nextTheme).catch(() => {});
        });
    });

    const settingsForm = document.querySelector('[data-settings-form]');
    if (settingsForm) {
        settingsForm.querySelectorAll('input[name="theme"]').forEach((input) => {
            input.addEventListener('change', () => applyTheme(input.value, false));
        });
        settingsForm.addEventListener('submit', () => {
            const selectedTheme = settingsForm.querySelector('input[name="theme"]:checked')?.value;
            if (selectedTheme) {
                window.localStorage.setItem(THEME_KEY, normalizeTheme(selectedTheme));
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
        '.page-body .ui-card',
        '.page-body .card',
        '.page-body .portal-card',
        '.page-body .stat-card',
        '.page-body .data-card',
        '.page-body .monitor-card',
        '.page-body .monitor-kpi',
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

const isStandaloneMode = () =>
    window.matchMedia('(display-mode: standalone)').matches
    || window.navigator.standalone === true;

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
            .pwa-prompt-btn {
                padding: 9px 12px;
                border-radius: 12px;
                font-size: .8rem;
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
        event.preventDefault();
        deferredPrompt = event;

        showPrompt({
            kicker: 'Install App',
            title: 'Install StudentEdge',
            copy: 'Add StudentEdge to your home screen for faster access and a cleaner mobile app experience.',
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
            title: 'Install StudentEdge on iPhone',
            copy: 'Safari does not show the standard install popup. Use the share menu once to pin StudentEdge like an app.',
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
                await syncPushSubscription();
                clearPromptDismissal(PUSH_PROMPT_KEY);
                hidePrompt(false);
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

const registerNotificationCenter = () => {
    const config = getUiConfig();
    const center = document.getElementById('notificationCenter');
    const list = center?.querySelector('[data-notification-list]');
    const triggers = Array.from(document.querySelectorAll('[data-notification-trigger]'));

    if (!config.authenticated || !config.notificationUrl || !center || !list || triggers.length === 0) {
        return;
    }

    let loading = false;
    let loaded = false;

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
            link.className = 'se-notification-item';
            link.href = item.url || '#';
            link.dataset.tone = item.tone || 'info';

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

    const open = () => {
        center.classList.add('is-open');
        center.setAttribute('aria-hidden', 'false');
        triggers.forEach((trigger) => trigger.setAttribute('aria-expanded', 'true'));
        if (window.innerWidth <= 767) document.body.style.overflow = 'hidden';
        if (!loaded) load();
    };

    triggers.forEach((trigger) => {
        trigger.addEventListener('click', (event) => {
            event.stopPropagation();
            center.classList.contains('is-open') ? close() : open();
        });
    });

    center.querySelector('[data-notification-close]')?.addEventListener('click', close);
    center.addEventListener('click', (event) => {
        if (event.target === center) close();
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && center.classList.contains('is-open')) close();
    });

    load();
};

const registerMediaViewer = () => {
    const config = getUiConfig();
    const modal = document.getElementById('mediaPreviewModal');
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
        stage.replaceChildren();
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
                <div><span class="se-filter-sheet-kicker">StudentEdge</span><h2 id="${headingId}"></h2></div>
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
            sheet.classList.add('is-open');
            sheet.setAttribute('aria-hidden', 'false');
            backdrop.classList.add('is-open');
            backdrop.setAttribute('aria-hidden', 'false');
            trigger.setAttribute('aria-expanded', 'true');
            document.body.style.overflow = 'hidden';
            sheet.querySelector('input, select, button:not([data-filter-close])')?.focus();
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
            button.disabled = true;
        }

        const target = form?.closest('.ui-card, .card, .panel, .data-card, .bugs-card, .settings-panel');
        target?.classList.add('liquid-loading-target');
    };

    window.studentEdgeSetLoading = setLoading;

    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || form.hasAttribute('data-confirm-message')) return;
        setLoading(form, event.submitter);
    });

    document.addEventListener('click', (event) => {
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

if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        const isLocalhost = ['localhost', '127.0.0.1', '::1'].includes(window.location.hostname);
        const canRegister = window.isSecureContext || isLocalhost;

        if (!canRegister) {
            return;
        }

        navigator.serviceWorker.register('/sw.js').catch(() => {
            // Keep the app usable even if PWA registration fails.
        });
    });
}

window.addEventListener('DOMContentLoaded', () => {
    registerThemeUi();
    registerLiquidGlassUi();
    registerNotificationCenter();
    registerMediaViewer();
    registerLiquidFilterSheets();
    registerLoadingUi();
    registerPwaPromptUi();
    registerPushPromptUi();
    registerLogoutPushCleanup();
});
