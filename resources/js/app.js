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
