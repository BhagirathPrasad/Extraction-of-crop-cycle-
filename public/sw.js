/**
 * CropsCycle Service Worker — Offline-first with network fallback.
 * Caches shell assets and serves offline.html when fully offline.
 */

const CACHE_VERSION = 'cropscycle-v1.2';
const SHELL_CACHE  = CACHE_VERSION + '-shell';
const DATA_CACHE   = CACHE_VERSION + '-data';

// Static shell assets to cache on install
const SHELL_ASSETS = [
    '/offline.html',
    '/manifest.json',
    'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@400;500;600;700&display=swap',
    'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css',
];

// ── Install: Cache shell assets ─────────────────────────────────────────
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(SHELL_CACHE).then(cache => {
            console.log('[SW] Caching shell assets');
            return cache.addAll(SHELL_ASSETS);
        })
    );
    self.skipWaiting();
});

// ── Activate: Clean old caches ──────────────────────────────────────────
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys => {
            return Promise.all(
                keys.filter(key => key !== SHELL_CACHE && key !== DATA_CACHE)
                    .map(key => {
                        console.log('[SW] Deleting old cache:', key);
                        return caches.delete(key);
                    })
            );
        })
    );
    self.clients.claim();
});

// ── Fetch: Network-first for API, Cache-first for assets ───────────────
self.addEventListener('fetch', event => {
    const { request } = event;
    const url = new URL(request.url);

    // Skip non-GET requests
    if (request.method !== 'GET') return;

    // Skip Chrome extension and other non-http requests
    if (!url.protocol.startsWith('http')) return;

    // API / AJAX requests: Network only (don't cache dynamic data)
    if (request.headers.get('X-Requested-With') === 'XMLHttpRequest' ||
        url.pathname.startsWith('/api/') ||
        url.pathname.includes('/notifications/') ||
        url.pathname.includes('/search/suggestions')) {
        return;
    }

    // Navigation requests: Network-first with offline fallback
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request)
                .catch(() => caches.match('/offline.html'))
        );
        return;
    }

    // Static assets: Cache-first (fonts, CSS, JS, images)
    if (url.pathname.match(/\.(css|js|woff2?|ttf|eot|png|jpg|jpeg|gif|svg|ico)$/) ||
        url.hostname === 'fonts.googleapis.com' ||
        url.hostname === 'fonts.gstatic.com' ||
        url.hostname === 'cdn.jsdelivr.net') {
        event.respondWith(
            caches.match(request).then(cached => {
                if (cached) return cached;
                return fetch(request).then(response => {
                    if (response.ok) {
                        const clone = response.clone();
                        caches.open(DATA_CACHE).then(cache => cache.put(request, clone));
                    }
                    return response;
                }).catch(() => new Response('', { status: 408, statusText: 'Offline' }));
            })
        );
        return;
    }
});

// ── Push Notification handler (future use) ──────────────────────────────
self.addEventListener('push', event => {
    const data = event.data ? event.data.json() : {};
    const options = {
        body: data.body || 'CropsCycle has a new update for you.',
        icon: '/icons/icon-192x192.png',
        badge: '/icons/icon-72x72.png',
        vibrate: [100, 50, 100],
        data: { url: data.url || '/dashboard' },
        actions: [
            { action: 'view', title: 'View' },
            { action: 'dismiss', title: 'Dismiss' },
        ],
    };

    event.waitUntil(
        self.registration.showNotification(data.title || 'CropsCycle Alert', options)
    );
});

self.addEventListener('notificationclick', event => {
    event.notification.close();
    if (event.action === 'dismiss') return;
    const url = event.notification.data?.url || '/dashboard';
    event.waitUntil(clients.openWindow(url));
});
