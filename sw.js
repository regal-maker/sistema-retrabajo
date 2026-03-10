// 1. Configuración de Caché (Archivos estáticos)
const CACHE_NAME = 'regal-retrabajo-v15';
const urlsToCache = [
  './',
  './index',
  './dashboard',
  './captura_motor',
  './tickets_abiertos',
  './consultas',
  './assets/css/style.css',
  './assets/img/icono-app.png',
  './assets/img/RRX_Logo_White_GreenLeaf.png'
];

// 2. Configuración de IndexedDB (Sincronización)
const DB_NAME_SYNC = "RegalOfflineDB";
const STORE_NAME_SYNC = "folios_pendientes";
const DB_VERSION = 2;
let bloqueoSincronizacion = false;

// Función para abrir DB dentro del Worker (sin usar window)
function abrirDBSync() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open(DB_NAME_SYNC, DB_VERSION);
        request.onupgradeneeded = e => {
            const db = e.target.result;
            if (!db.objectStoreNames.contains(STORE_NAME_SYNC)) {
                db.createObjectStore(STORE_NAME_SYNC, { keyPath: "id", autoIncrement: true });
            }
        };
        request.onsuccess = e => resolve(e.target.result);
        request.onerror = e => reject(e.target.error);
    });
}

// 3. Instalación: Guardar archivos en Caché
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => cache.addAll(urlsToCache))
    );
});

// 4. Activación: Limpiar cachés viejos
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys => Promise.all(
            keys.filter(key => key !== CACHE_NAME).map(key => caches.delete(key))
        ))
    );
});

// 5. Fetch: Estrategia de Red con caída a Caché (Modo Offline)
self.addEventListener('fetch', event => {
    if (event.request.method !== 'GET') return;
    event.respondWith(
        fetch(event.request).catch(() => caches.match(event.request))
    );
});

// 6. Sincronización en segundo plano (Lógica que pediste)
async function procesarSincronizacionGlobal() {
    if (bloqueoSincronizacion) return;
    
    try {
        const db = await abrirDBSync();
        if (!db.objectStoreNames.contains(STORE_NAME_SYNC)) return;

        const tx = db.transaction(STORE_NAME_SYNC, "readonly");
        const store = tx.objectStore(STORE_NAME_SYNC);
        const req = store.getAll();

        req.onsuccess = async () => {
            const folios = req.result;
            if (folios.length === 0) return;

            bloqueoSincronizacion = true;
            for (const folio of folios) {
                const formData = new FormData();
                Object.keys(folio).forEach(key => {
                    if (Array.isArray(folio[key])) {
                        folio[key].forEach(val => formData.append(`${key}[]`, val));
                    } else {
                        formData.append(key, folio[key]);
                    }
                });

                try {
                    const res = await fetch('backend/guardar_ticket_simple.php', { 
                        method: 'POST', 
                        body: formData
                    });

                    if (res.ok) {
                        const delTx = db.transaction(STORE_NAME_SYNC, "readwrite");
                        await delTx.objectStore(STORE_NAME_SYNC).delete(folio.id);
                    }
                } catch (err) { break; }
            }
            bloqueoSincronizacion = false;
        };
    } catch (e) { bloqueoSincronizacion = false; }
}

// El Service Worker no tiene evento 'load', usa 'sync' o mensajes
self.addEventListener('sync', event => {
    if (event.tag === 'sync-tickets') {
        event.waitUntil(procesarSincronizacionGlobal());
    }
});
