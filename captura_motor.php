<?php 
$pagina_actual = 'CAPTURA DE MOTOR';
include('config/conexion.php'); 
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }
$defectos = $pdo->query("SELECT * FROM catalogo_defectos ORDER BY nombre_defecto ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title>Captura | Regal Rexnord</title>
    <?php include 'includes/header.php'; ?>
    <style>
        .card-captura { background: white; border-radius: 12px; border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .btn-pieza { font-size: 0.75rem; font-weight: 600; transition: 0.2s; height: 100%; }
        /* Efecto al seleccionar pieza */
        .btn-pieza-item.active-pieza { background-color: var(--bs-primary) !important; color: white !important; border-color: var(--bs-primary) !important; }
        
        /* Estilos reducidos para el resumen de scrap */
        .item-scrap { 
            background: #fff; 
            border-left: 3px solid var(--bs-primary); 
            padding: 6px 8px; 
            margin-bottom: 5px; 
            border-radius: 4px; 
            box-shadow: 0 1px 3px rgba(0,0,0,0.05); 
        }
        
        .sub-washer { background: #e9ecef; border-radius: 8px; padding: 10px; margin-top: 10px; }
    </style>
</head>
<body>

<?php include 'includes/navbar.php'; ?>
<div clas="row g-4">
            <a href="panel_principal.php" class="btn btn-sm btn-outline-secondary px-3 fw-bold">
                            <i class="bi bi-arrow-left me-1"></i> VOLVER AL PANEL
                        </a>
        </div>

<div class="container-fluid px-4">
    <form id="formCaptura" action="backend/guardar_ticket_simple.php" method="POST">
        <div class="row g-4">
            <div class="col-md-7">
                
                <div class="card-captura p-4 mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold text-primary mb-0">1. DATOS DEL MOTOR</h6>
                       
                    </div>

                    <div class="row g-3">
                        <div class="col-md-7">
                            <label class="form-label small fw-bold">NÚMERO DE SERIE / MODELO</label>
                            <input type="text" name="modelo_motor" class="form-control form-control-lg border-primary" placeholder="Escriba o escanee..." required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small fw-bold">CANTIDAD DE MOTORES</label>
                            <input type="number" name="cantidad_motores" class="form-control form-control-lg" value="1" min="1">
                        </div>
                        <div class="col-12 mt-3">
                            <label class="form-label small fw-bold">TIPO DE CONFIGURACIÓN</label>
                            <div class="d-flex gap-2">
                                <input type="radio" class="btn-check" name="tipo_motor" id="t_balero" value="Balero" onchange="cargarPiezas('Balero')" checked>
                                <label class="btn btn-outline-primary w-100 fw-bold py-3" for="t_balero">BALERO</label>
                                <input type="radio" class="btn-check" name="tipo_motor" id="t_buje" value="Buje" onchange="cargarPiezas('Buje')">
                                <label class="btn btn-outline-primary w-100 fw-bold py-3" for="t_buje">BUJE</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-captura p-4 mb-4">
                    <h6 class="fw-bold text-primary mb-3">2. SEVERIDAD DEL DEFECTO</h6>
                    <div class="d-flex gap-3">
                        <input type="radio" class="btn-check" name="severidad" id="sev_baja" value="Baja" required>
                        <label class="btn btn-outline-success w-100 fw-bold py-2" for="sev_baja">BAJA</label>
                        
                        <input type="radio" class="btn-check" name="severidad" id="sev_media" value="Media">
                        <label class="btn btn-outline-warning w-100 fw-bold py-2" for="sev_media">MEDIA</label>
                        
                        <input type="radio" class="btn-check" name="severidad" id="sev_alta" value="Alta">
                        <label class="btn btn-outline-danger w-100 fw-bold py-2" for="sev_alta">ALTA</label>
                    </div>
                </div>

                <div class="card-captura p-4">
                    <h6 class="fw-bold text-primary mb-3">3. COMPONENTES DEL CATÁLOGO</h6>
                    <div id="gridPiezas" class="row g-2"></div>
                    <div id="subMenurandelas" class="sub-washer d-none">
                        <h6 class="small fw-bold border-bottom pb-2 mb-2">MEDIDAS DE WASHERS (SÓLO BUJE)</h6>
                        <div id="listaMedidas" class="d-flex flex-wrap gap-2"></div>
                    </div>
                </div>
            </div>

            <div class="col-md-5">
                <div class="card-captura p-4 border-top border-danger border-4 sticky-top" style="top: 20px;">
                    <h6 class="fw-bold text-danger mb-3"><i class="bi bi-trash3-fill me-2"></i>RESUMEN PARA SCRAP</h6>
                    
                    <div id="listaScrap" class="mb-4" style="min-height: 250px; max-height: 400px; overflow-y: auto;">
                        <p class="text-center text-muted py-5 small">Seleccione componentes para scrap...</p>
                    </div>
                    
                    <div class="mb-4 pt-3 border-top">
                        <label class="form-label small fw-bold">TIPO DE DEFECTO PRINCIPAL</label>
                        <select name="id_defecto" class="form-select form-select-lg" required>
                            <option value="">Seleccione...</option>
                            <?php foreach($defectos as $d): ?>
                                <option value="<?php echo $d['id']; ?>"><?php echo $d['nombre_defecto']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div id="hiddenFields"></div>
                    <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold shadow">GENERAR FOLIO</button>
                </div>
            </div>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
let scrapItems = [];
let arandelasCargadas = false;

function cargarPiezas(tipo) {
    const grid = document.getElementById('gridPiezas');
    const subMenu = document.getElementById('subMenurandelas');
    
    // Vaciar carrito al cambiar de tipo de motor
    scrapItems = [];
    arandelasCargadas = false; 
    renderScrap();
    
    grid.innerHTML = '<div class="text-center p-4"><div class="spinner-border text-primary" role="status"></div></div>';
    subMenu.classList.add('d-none');

    fetch(`backend/obtener_piezas_catalogo.php?tipo=${tipo}`)
        .then(res => res.json())
        .then(data => {
            grid.innerHTML = "";
            data.forEach(p => {
                if (p.descripcion.includes("Washer .") || p.descripcion.includes("Washer 1") || p.descripcion.includes("Washer 5")) return;
                
                if (p.descripcion === "Arandelas Generales") {
                    grid.innerHTML += `<div class="col-md-4"><button type="button" class="btn btn-warning btn-pieza w-100 p-3 shadow-sm" onclick="toggleArandelas('${tipo}')"><i class="bi bi-layers-half me-1"></i> ARANDELAS</button></div>`;
                } else {
                    grid.innerHTML += `<div class="col-md-4"><button type="button" id="btn-pieza-${p.id}" class="btn btn-outline-secondary btn-pieza btn-pieza-item w-100 p-3 shadow-sm" onclick="toggleScrap('${p.id}', '${p.descripcion}')">${p.descripcion}</button></div>`;
                }
            });
        });
}

function toggleArandelas(tipo) {
    const subMenu = document.getElementById('subMenurandelas');
    const lista = document.getElementById('listaMedidas');
    
    if (subMenu.classList.contains('d-none')) {
        subMenu.classList.remove('d-none');
        
        // Evitar peticiones repetitivas al servidor
        if (!arandelasCargadas) {
            lista.innerHTML = '<span class="small text-muted">Cargando...</span>';
            fetch(`backend/obtener_piezas_catalogo.php?tipo=${tipo}`)
                .then(res => res.json())
                .then(data => {
                    lista.innerHTML = "";
                    data.filter(p => p.descripcion.includes("Washer") && p.descripcion !== "Arandelas Generales").forEach(p => {
                        lista.innerHTML += `<button type="button" id="btn-pieza-${p.id}" class="btn btn-sm btn-outline-secondary btn-pieza-item mb-1" onclick="toggleScrap('${p.id}', '${p.descripcion}')">${p.descripcion}</button>`;
                    });
                    arandelasCargadas = true;
                    renderScrap(); // Actualiza estilos si ya había alguna seleccionada
                });
        }
    } else { 
        subMenu.classList.add('d-none'); 
    }
}

// Alterna entre seleccionar y deseleccionar
function toggleScrap(id, nombre = '') {
    const index = scrapItems.findIndex(i => i.id === id);
    
    if(index > -1) { 
        scrapItems.splice(index, 1); // Lo quita
    } else { 
        scrapItems.push({ id, nombre, qty: 1 }); // Lo agrega
    }
    renderScrap();
}

// Actualiza la cantidad desde el input del resumen
function updateQty(id, newQty) {
    const item = scrapItems.find(i => i.id === id);
    if (item) {
        item.qty = Math.max(1, parseInt(newQty) || 1);
        renderScrap();
    }
}

function renderScrap() {
    const container = document.getElementById('listaScrap');
    const hidden = document.getElementById('hiddenFields');
    
    // 1. Limpiar estilos visuales de todos los botones del catálogo
    document.querySelectorAll('.btn-pieza-item').forEach(btn => {
        btn.classList.remove('active-pieza', 'btn-primary');
        btn.classList.add('btn-outline-secondary');
    });

    if (scrapItems.length === 0) {
        container.innerHTML = '<p class="text-center text-muted py-5 small">Seleccione componentes...</p>';
        hidden.innerHTML = "";
        return;
    }

    container.innerHTML = "";
    hidden.innerHTML = "";
    
    scrapItems.forEach(item => {
        // 2. Marcar visualmente el botón si está en la lista de scrap
        const btnElement = document.getElementById(`btn-pieza-${item.id}`);
        if (btnElement) {
            btnElement.classList.remove('btn-outline-secondary');
            btnElement.classList.add('active-pieza');
        }

        // 3. Generar el HTML compacto del resumen
        container.innerHTML += `
            <div class="item-scrap d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center" style="width: 85%;">
                    <input type="number" class="form-control form-control-sm me-2 text-center px-1 py-0" style="width: 45px; height: 24px; font-size: 0.75rem;" value="${item.qty}" min="1" onchange="updateQty('${item.id}', this.value)">
                    <span class="text-truncate fw-semibold" style="font-size: 0.75rem; line-height: 1;" title="${item.nombre}">${item.nombre}</span>
                </div>
                <button type="button" class="btn btn-sm text-danger p-0 border-0" onclick="toggleScrap('${item.id}')">
                    <i class="bi bi-x-circle-fill" style="font-size: 1rem;"></i>
                </button>
            </div>`;
            
        // 4. Inputs ocultos para enviar por POST
        hidden.innerHTML += `<input type="hidden" name="piezas_id[]" value="${item.id}"><input type="hidden" name="piezas_cant[]" value="${item.qty}">`;
    });
}

// Validación antes de enviar el formulario
document.getElementById('formCaptura').addEventListener('submit', function(e) {
    if (scrapItems.length === 0) {
        e.preventDefault();
        alert('Por favor, seleccione al menos un componente del catálogo para continuar.');
    }
});

window.onload = () => cargarPiezas('Balero');
</script>
</body>
</html>






