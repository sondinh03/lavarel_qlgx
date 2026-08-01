const CACHE_NAME = 'mvgx-cache-v2';
const urlsToCache = [
  '/',
  '/offline',
  '/site.webmanifest',
  '/web-app-manifest-192x192.png',
  '/web-app-manifest-512x512.png',
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => cache.addAll(urlsToCache)).catch(() => undefined)
  );
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) =>
      Promise.all(
        cacheNames
          .filter((cacheName) => cacheName !== CACHE_NAME)
          .map((cacheName) => caches.delete(cacheName))
      )
    ).then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', (event) => {
  const { request } = event;

  if (request.method !== 'GET') {
    return;
  }

  const isNavigation = request.mode === 'navigate';

  if (!isNavigation) {
    event.respondWith(
      caches.match(request).then((cached) => cached || fetch(request))
    );
    return;
  }

  event.respondWith(
    fetch(request)
      .then((response) => {
        if (response && response.status === 200) {
          const responseClone = response.clone();
          caches.open(CACHE_NAME).then((cache) => cache.put(request, responseClone));
        }
        return response;
      })
      .catch(() =>
        caches.match(request).then((response) => response || caches.match('/offline'))
      )
  );
});

self.addEventListener('push', (event) => {
  let payload = {
    title: 'Thông báo mới',
    body: 'Bạn có thông báo mới trên Mục vụ Giáo xứ.',
    icon: '/web-app-manifest-192x192.png',
    badge: '/web-app-manifest-192x192.png',
    data: { url: '/thong-bao' },
  };

  try {
    if (event.data) {
      const json = event.data.json();
      payload = Object.assign({}, payload, json, {
        data: Object.assign({}, payload.data, json.data || {}),
      });
    }
  } catch (e) {
    try {
      const text = event.data && event.data.text();
      if (text) {
        payload.body = text;
      }
    } catch (err) {
      // keep defaults
    }
  }

  const title = payload.title || 'Thông báo mới';
  const options = {
    body: payload.body || '',
    icon: payload.icon || '/web-app-manifest-192x192.png',
    badge: payload.badge || '/web-app-manifest-192x192.png',
    data: payload.data || { url: '/thong-bao' },
    tag: payload.tag || 'mvgx-notification',
    renotify: true,
    vibrate: [120, 60, 120],
  };

  event.waitUntil(
    self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
      const hasFocused = clientList.some((client) => client.focused);
      if (hasFocused) {
        clientList.forEach((client) => {
          client.postMessage({ type: 'mvgx:notification', payload: payload });
        });
        return undefined;
      }
      return self.registration.showNotification(title, options);
    })
  );
});

self.addEventListener('notificationclick', (event) => {
  event.notification.close();

  const targetUrl = (event.notification.data && event.notification.data.url) || '/thong-bao';
  const absoluteUrl = new URL(targetUrl, self.location.origin).href;

  event.waitUntil((async () => {
    const clientList = await self.clients.matchAll({ type: 'window', includeUncontrolled: true });
    for (let i = 0; i < clientList.length; i += 1) {
      const client = clientList[i];
      if (client.url.startsWith(self.location.origin) && 'focus' in client) {
        await client.focus();
        if (typeof client.navigate === 'function') {
          try {
            await client.navigate(absoluteUrl);
            return;
          } catch (e) {
            // fall through to postMessage / openWindow
          }
        }
        client.postMessage({ type: 'mvgx:navigate', url: absoluteUrl });
        return;
      }
    }
    if (self.clients.openWindow) {
      await self.clients.openWindow(absoluteUrl);
    }
  })());
});
