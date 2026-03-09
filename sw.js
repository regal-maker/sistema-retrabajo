const CACHE_NAME = 'regal-retrabajo-v5';
const urlsToCache = [
  './',
  './index.php',
  './dashboard.php',
  './captura_motor.php',
  './tickets_abiertos.php',
  './consultas.php',
  './assets/css/style.css',
  './assets/img/icono-app.png',
  './assets/img/RRX_Logo_White_GreenLeaf.png'
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => cache.addAll(urlsToCache))
  );
});

// Estrategia Network First: intenta internet, si falla da el caché
self.addEventListener('fetch', event => {
  event.respondWith(
    fetch(event.request).catch(() => caches.match(event.request))
  );
});

self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys => Promise.all(
      keys.filter(key => key !== CACHE_NAME).map(key => caches.delete(key))
    ))
  );
});
