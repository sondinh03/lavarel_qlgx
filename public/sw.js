const CACHE_NAME = 'attendance-cache-v1';
const urlsToCache = [
  '/',
  '/css/app.css',
  '/js/app.js',
  '/site.webmanifest',
  '/offline', // Thêm route offline nếu có
];

// Install event
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        return cache.addAll(urlsToCache);
      })
  );
  self.skipWaiting();
});

// Fetch event - Network first + safe caching
self.addEventListener('fetch', event => {
  const { request } = event;

  // Ignore non-GET requests (e.g., POST for attendance saving)
  if (request.method !== 'GET') {
    return event.respondWith(fetch(request));
  }

  // Only handle navigation / document requests for offline fallback
  const isNavigation = request.mode === 'navigate';

  if (!isNavigation) {
    return event.respondWith(
      caches.match(request).then(cached => cached || fetch(request))
    );
  }

  event.respondWith(
    fetch(request)
      .then(response => {
        // Cache successful navigation responses (status 200)
        if (response && response.status === 200) {
          const responseClone = response.clone();
          caches.open(CACHE_NAME).then(cache => cache.put(request, responseClone));
        }

        return response;
      })
      .catch(() => {
        // Offline: Trả về cache hoặc offline page
        return caches.match(request)
          .then(response => response || caches.match('/offline'));
      })
  );
});

// Activate event
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (cacheName !== CACHE_NAME) {
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
  self.clients.claim();
});