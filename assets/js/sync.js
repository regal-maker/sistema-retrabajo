// Configuración de la base de datos local
const DB_NAME_SYNC = "RegalOfflineDB";
const STORE_NAME_SYNC = "folios_pendientes";
let bloqueoSincronizacion = false;

function abrirDBSync() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open(DB_NAME_SYNC, 1);
        request.onsuccess = e => resolve(e.target.result);
        request.onerror = e => reject(e.target.error);
    });
}

async function procesarSincronizacionGlobal() {
    if (bloqueoSincronizacion || !navigator.onLine) return;
    
    try {
        const db = await abrirDBSync();
        // Verificamos si existe el almacen de objetos antes de continuar
        if (!db.objectStoreNames.contains(STORE_NAME_SYNC)) return;

        const tx = db.transaction(STORE_NAME_SYNC, "readonly");
        const store = tx.objectStore(STORE_NAME_SYNC);
        const req = store.getAll();

        req.onsuccess = async () => {
            const folios = req.result;
            if (folios.length === 0) return;

            bloqueoSincronizacion = true;
            console.log(`Sincronización automática: ${folios.length} pendientes.`);

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
                        body: formData,
                        redirect: 'manual' 
                    });

                    const delTx = db.transaction(STORE_NAME_SYNC, "readwrite");
                    await delTx.objectStore(STORE_NAME_SYNC).delete(folio.id);
                } catch (err) { break; }
            }
            bloqueoSincronizacion = false;
            
            // Si el usuario está en tickets_abiertos.php, recargar para mostrar los nuevos datos
            if (window.location.pathname.includes('tickets_abiertos.php')) {
                window.location.reload();
            }
        };
    } catch (e) { bloqueoSincronizacion = false; }
}

// Escuchadores globales
window.addEventListener('online', () => setTimeout(procesarSincronizacionGlobal, 2000));
window.addEventListener('load', procesarSincronizacionGlobal);