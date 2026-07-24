const CACHE_VERSION = 'studentedge-pwa-v4';
const STATIC_CACHE = `${CACHE_VERSION}-static`;
const RUNTIME_CACHE = `${CACHE_VERSION}-runtime`;
const OFFLINE_URL = '/offline.html';

const STATIC_ASSETS = [
    '/',
    OFFLINE_URL,
    '/manifest.webmanifest',
    '/images/pwa/icon-192.png',
    '/images/pwa/icon-512.png',
    '/images/newlogo.png',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(STATIC_CACHE).then((cache) => cache.addAll(STATIC_ASSETS)).then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(
                keys
                    .filter((key) => ![STATIC_CACHE, RUNTIME_CACHE].includes(key))
                    .map((key) => caches.delete(key))
            )
        ).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    if (event.request.method !== 'GET') {
        return;
    }

    const url = new URL(event.request.url);

    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request)
                .then((response) => {
                    const copy = response.clone();
                    caches.open(RUNTIME_CACHE).then((cache) => cache.put(event.request, copy));
                    return response;
                })
                .catch(async () => {
                    const cached = await caches.match(event.request);
                    return cached || caches.match(OFFLINE_URL);
                })
        );
        return;
    }

    if (url.origin === self.location.origin) {
        event.respondWith(
            caches.match(event.request).then((cached) => {
                const networkFetch = fetch(event.request)
                    .then((response) => {
                        if (response && response.status === 200) {
                            const copy = response.clone();
                            caches.open(RUNTIME_CACHE).then((cache) => cache.put(event.request, copy));
                        }
                        return response;
                    })
                    .catch(() => cached);

                return cached || networkFetch;
            })
        );
    }
});

self.addEventListener('push', (event) => {
    let payload = {};

    try {
        payload = event.data ? event.data.json() : {};
    } catch (error) {
        payload = {};
    }

    const title = payload.title || 'StudentEdge';
    const options = {
        body: payload.body || 'You have a new notification.',
        icon: payload.icon || '/images/pwa/icon-192.png',
        badge: payload.badge || '/images/pwa/icon-192.png',
        tag: payload.tag || 'studentedge-general',
        renotify: Boolean(payload.renotify),
        requireInteraction: Boolean(payload.requireInteraction),
        data: {
            url: payload.url || '/',
        },
    };

    event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const targetUrl = new URL(event.notification.data?.url || '/', self.location.origin).href;

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
            for (const client of clientList) {
                if (client.url === targetUrl && 'focus' in client) {
                    return client.focus();
                }
            }

            for (const client of clientList) {
                if (client.url.startsWith(self.location.origin) && 'focus' in client) {
                    client.navigate(targetUrl);
                    return client.focus();
                }
            }

            if (clients.openWindow) {
                return clients.openWindow(targetUrl);
            }
        })
    );
});
