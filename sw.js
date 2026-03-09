const CACHE_NAME = 'regal-retrabajo-v9';
const urlsToCache = [
  './', './index.php', './dashboard.php', './captura_motor.php',
  './tickets_abiertos.php', './consultas.php', './assets/css/style.css',
  './assets/img/icono-app.png', './assets/img/RRX_Logo_White_GreenLeaf.png'
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => cache.addAll(urlsToCache))
  );
});

self.addEventListener('fetch', event => {
  // Solo interceptar peticiones GET (navegación), ignorar POST de sincronización
  if (event.request.method !== 'GET') return;

  event.respondWith(
    fetch(event.request).catch(() => {
      return caches.match(event.request).then(response => {
        return response || new Response('Offline', { 
            status: 503, 
            statusText: 'Service Unavailable' 
        });
      });
    })
  );
});

self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys => Promise.all(
      keys.filter(key => key !== CACHE_NAME).map(key => caches.delete(key))
    ))
  );
});
