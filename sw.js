const CACHE_NAME = 'regal-retrabajo-v10';
const urlsToCache = [
  './',
  './index.php',
  './dashboard.php',
  './captura_motor.php',
  './tickets_abiertos.php',
  './consultas.php',
  './assets/css/style.css',
  './assets/img/icono-app.png',
  './assets/img/RRX_Logo_White_GreenLeaf.png',
  'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
  'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css'
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => {
      console.log('Caché abierto');
      return cache.addAll(urlsToCache);
    })
  );
});

self.addEventListener('fetch', event => {
  if (event.request.method !== 'GET') return;

  event.respondWith(
    fetch(event.request)
      .then(response => {
        // Si la respuesta es válida, la devolvemos
        return response;
      })
      .catch(() => {
        // Si falla la red (offline), buscamos en el caché
        return caches.match(event.request).then(res => {
          return res || new Response('Recurso no disponible offline', { status: 503 });
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
