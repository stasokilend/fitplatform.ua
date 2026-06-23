const CACHE_NAME = 'fitplatform-static-v1';
const STATIC_ASSETS = [
  '/',
  '/dashboard.php',
  '/assets/css/style.css',
  '/assets/js/main.js',
  '/assets/js/mobile.js',
  '/assets/js/workout.js',
  '/manifest.webmanifest'
];

self.addEventListener('install', (event) => {
  event.waitUntil(caches.open(CACHE_NAME).then((cache) => cache.addAll(STATIC_ASSETS)));
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(caches.keys().then((keys) => Promise.all(keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key)))));
  self.clients.claim();
});

self.addEventListener('fetch', (event) => {
  if (event.request.method !== 'GET') return;
  event.respondWith(caches.match(event.request).then((cached) => cached || fetch(event.request)));
});

self.addEventListener('push', (event) => {
  const data = event.data ? event.data.json() : {};
  event.waitUntil(self.registration.showNotification(data.title || 'FitPlatform', {
    body: data.body || 'Нове сповіщення',
    icon: '/assets/icons/icon-192.svg',
    badge: '/assets/icons/icon-192.svg'
  }));
});
