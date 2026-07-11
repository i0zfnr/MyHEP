const CACHE_VERSION = 'studentedge-pwa-v1';
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
