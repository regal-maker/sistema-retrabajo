// Configuración de la base de datos local
const DB_NAME_SYNC = "RegalOfflineDB";
const STORE_NAME_SYNC = "folios_pendientes";
const DB_VERSION = 2; // Mantener versión 2 para asegurar la estructura
let bloqueoSincronizacion = false;

function abrirDBSync() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open(DB_NAME_SYNC, DB_VERSION);

        request.onupgradeneeded = e => {
            const db = e.target.result;
            if (!db.objectStoreNames.contains(STORE_NAME_SYNC)) {
                db.createObjectStore(STORE_NAME_SYNC, { keyPath: "id", autoIncrement: true });
                console.log("Almacén 'folios_pendientes' verificado/creado.");
            }
        };

        request.onsuccess = e => resolve(e.target.result);
        request.onerror = e => reject(e.target.error);
    });
}

async function procesarSincronizacionGlobal() {
    if (bloqueoSincronizacion || !navigator.onLine) return;
    
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
            console.log(`Sincronización activa: ${folios.length} tickets en cola.`);

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
                    // Nota: 'backend/guardar_ticket_simple.php' sigue siendo el archivo físico en el server
                    const res = await fetch('backend/guardar_ticket_simple.php', { 
                        method: 'POST', 
                        body: formData,
                        redirect: 'manual' 
                    });

                    // Si el servidor responde OK o redirige (éxito), borramos localmente
                    if (res.ok || res.type === 'opaqueredirect') {
                        const delTx = db.transaction(STORE_NAME_SYNC, "readwrite");
                        await delTx.objectStore(STORE_NAME_SYNC).delete(folio.id);
                        console.log(`Folio ${folio.id} enviado y limpiado de IndexedDB.`);
                    }
                } catch (err) { 
                    console.error("Error al sincronizar folio individual:", err);
                    break; 
                }
            }
            bloqueoSincronizacion = false;
            
            // Lógica de recarga compatible con URLs amigables
            const currentPath = window.location.pathname;
            if (currentPath.includes('tickets_abiertos') || currentPath.includes('consultas')) {
                window.location.reload();
            }
        };
    } catch (e) { 
        bloqueoSincronizacion = false;
        console.error("Fallo en el proceso de sincronización:", e);
    }
}

// Escuchadores globales
window.addEventListener('online', () => {
    console.log("Conexión restablecida. Iniciando sincronización...");
    setTimeout(procesarSincronizacionGlobal, 2000);
});

window.addEventListener('load', () => {
    if (navigator.onLine) {
        procesarSincronizacionGlobal();
    }
});
