const CACHE_NAME = 'regal-retrabajo-v1';
const urlsToCache = [
  './',
  './index.php',
  './assets/css/style.css',
  './assets/img/RRX_Logo_White_GreenLeaf.png'
];

// Instalar el Service Worker y guardar archivos base en caché
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        return cache.addAll(urlsToCache);
      })
  );
});

// Interceptar peticiones para que la app cargue más rápido
self.addEventListener('fetch', event => {
  event.respondWith(
    caches.match(event.request)
      .then(response => {
        // Devuelve el archivo del caché si existe, si no, lo descarga de internet
        return response || fetch(event.request);
      })
  );
});

// Limpiar cachés viejos si actualizas la versión
self.addEventListener('activate', event => {
  const cacheWhitelist = [CACHE_NAME];
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (cacheWhitelist.indexOf(cacheName) === -1) {
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
});