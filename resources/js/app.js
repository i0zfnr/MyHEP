const PWA_PROMPT_KEY = 'studentedge-pwa-dismissed-v1';
const PUSH_PROMPT_KEY = 'studentedge-push-dismissed-v1';

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
            border: 1px solid rgba(226, 209, 192, .14);
            background:
                radial-gradient(circle at top right, rgba(215, 191, 168, .12), transparent 32%),
                linear-gradient(180deg, rgba(29, 26, 23, .96), rgba(17, 15, 13, .98));
            color: #f7efe8;
            box-shadow: 0 28px 60px rgba(0,0,0,.34), inset 0 1px 0 rgba(255,255,255,.05);
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
            background: rgba(95, 190, 145, .12);
            color: #d8f7e7;
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
            color: #c8b8a9;
            font-size: .88rem;
            line-height: 1.6;
        }
        .pwa-prompt-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
        }
        .pwa-prompt-btn {
            appearance: none;
            border: 1px solid rgba(226, 209, 192, .18);
            border-radius: 14px;
            background: rgba(255,255,255,.04);
            color: #f7efe8;
            font: inherit;
            font-size: .86rem;
            font-weight: 800;
            padding: 11px 14px;
            cursor: pointer;
            transition: transform .18s ease, border-color .18s ease, background-color .18s ease;
        }
        .pwa-prompt-btn:hover {
            transform: translateY(-1px);
            border-color: rgba(226, 209, 192, .28);
            background: rgba(255,255,255,.08);
        }
        .pwa-prompt-btn.primary {
            background: linear-gradient(135deg, #c9ae95 0%, #ecd7c3 100%);
            color: #2a1d15;
            border-color: rgba(215, 191, 168, .55);
            box-shadow: 0 12px 28px rgba(201, 174, 149, .22);
        }
        .pwa-prompt-btn.link {
            border-color: transparent;
            background: transparent;
            color: #a99888;
            padding-left: 0;
            padding-right: 0;
        }
        .pwa-prompt-steps {
            margin: 0;
            padding-left: 18px;
            color: #c8b8a9;
            font-size: .84rem;
            line-height: 1.6;
        }
        @media (max-width: 640px) {
            .pwa-prompt {
                right: 12px;
                bottom: 12px;
                width: calc(100vw - 24px);
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
    registerPwaPromptUi();
    registerPushPromptUi();
    registerLogoutPushCleanup();
});
