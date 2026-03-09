const CACHE_NAME = 'regal-retrabajo-v8';
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

// ESTRATEGIA CORREGIDA: Solo intercepta navegación (GET)
self.addEventListener('fetch', event => {
  // REGLA CRÍTICA: Ignorar peticiones POST para que la sincronización no choque
  if (event.request.method !== 'GET') return;

  event.respondWith(
    fetch(event.request).catch(() => {
      return caches.match(event.request).then(response => {
        // Devuelve el caché o una respuesta vacía válida
        return response || new Response('Offline', { status: 503 });
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
