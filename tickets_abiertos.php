<?php 
$pagina_actual = 'Tickets en Estación';
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Tickets | Regal Rexnord</title>
    <?php include 'includes/header.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root { --regal-blue: #00539b; --regal-gray: #f8f9fa; }
        body { background-color: #f4f7f6; }
        .card-ticket { border: none; border-radius: 12px; background: white; box-shadow: 0 4px 12px rgba(0,0,0,0.08); border-left: 7px solid var(--regal-blue); margin-bottom: 15px; transition: all 0.3s ease; }
        .card-ticket:hover { transform: scale(1.01); box-shadow: 0 6px 15px rgba(0,0,0,0.12); }
        .pieza-tag { background: #eef2f7; color: #334e68; border: 1px solid #d1d9e6; padding: 5px 12px; border-radius: 50px; font-size: 0.85rem; font-weight: 600; display: inline-block; margin: 2px; }
        .folio-badge { background: var(--regal-blue); color: white; font-size: 0.75rem; padding: 3px 10px; border-radius: 4px; font-weight: bold; }
        .border-alerta { border-left-color: #ffc107 !important; } 
        .border-critico { border-left-color: #dc3545 !important; animation: pulse-red 2s infinite; } 
        @keyframes pulse-red { 0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.4); } 70% { box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); } 100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); } }
        /* Estilos para el grid de piezas en edición */
        .btn-pieza-edit { font-size: 0.7rem; padding: 8px 4px; font-weight: bold; height: 100%; transition: 0.2s; }
    </style>
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div class="container-fluid px-4">
    <div class="row mt-4 mb-3 align-items-center">
        <div class="col-md-6">
            <a href="panel_principal.php" class="btn btn-sm btn-outline-secondary px-3 fw-bold shadow-sm">
                <i class="bi bi-arrow-left me-1"></i> VOLVER AL PANEL
            </a>
            <h4 class="d-inline-block ms-3 fw-bold text-dark">Monitoreo de Estación</h4>
        </div>
        <div class="col-md-6 text-end">
            <div id="loading-indicator" class="spinner-border spinner-border-sm text-primary d-none" role="status"></div>
            <span class="badge bg-light text-dark border ms-2">
                <i class="bi bi-arrow-repeat me-1"></i> Actualización: 10s
            </span>
        </div>
    </div>

    <div class="row" id="contenedor-tickets">
        <div class="col-12 text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2 text-muted">Sincronizando con la línea de producción...</p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
let editScrapItems = []; // Almacena piezas seleccionadas en el modal

async function cargarTickets() {
    const contenedor = document.getElementById('contenedor-tickets');
    const loader = document.getElementById('loading-indicator');
    if(loader) loader.classList.remove('d-none');
    try {
        const response = await fetch('backend/obtener_monitoreo_ajax.php');
        const html = await response.text();
        contenedor.innerHTML = html;
    } catch (error) {
        console.error("Error:", error);
        contenedor.innerHTML = '<div class="alert alert-danger">Error de conexión.</div>';
    } finally {
        if(loader) loader.classList.add('d-none');
    }
}

cargarTickets();
setInterval(cargarTickets, 10000);

function finalizar(id) {
    Swal.fire({
        title: '¿Concluir folio?',
        text: "¿Se arregló el problema del motor?",
        input: 'textarea',
        inputPlaceholder: 'Escribe la conclusión del cierre...',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#198754',
        confirmButtonText: 'Sí, cerrar ticket',
        inputValidator: (value) => { if (!value) return '¡Debes escribir una conclusión!' }
    }).then((result) => {
        if (result.isConfirmed) {
            enviarForm('backend/cerrar_ticket.php', { id_ticket: id, conclusion: result.value });
        }
    });
}

function cancelarTicket(id) {
    Swal.fire({
        title: '¿Cancelar ticket?',
        text: "Se marcará como Cancelado.",
        icon: 'error',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Sí, cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            enviarForm('backend/cancelar_ticket.php', { id_ticket: id });
        }
    });
}

function editarTicket(id, motorActual, tipoActual, severidadActual, cantidadActual, defectoActualId) {
    editScrapItems = []; // Resetear lista de piezas al abrir

    Swal.fire({
        title: 'MODIFICAR TICKET COMPLETO',
        width: '850px',
        html: `
            <div class="text-start p-2" style="font-size: 0.85rem;">
                <div class="row g-3 mb-3">
                    <div class="col-md-8">
                        <label class="form-label small fw-bold">NÚMERO DE SERIE / MODELO</label>
                        <input type="text" id="edit-modelo" class="form-control border-primary" value="${motorActual}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">CANTIDAD MOTORES</label>
                        <input type="number" id="edit-cantidad" class="form-control" value="${cantidadActual}" min="1">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-primary">CONFIGURACIÓN</label>
                        <div class="d-flex gap-2">
                            <input type="radio" class="btn-check" name="edit-tipo" id="edit-balero" value="Balero" ${tipoActual === 'Balero' ? 'checked' : ''} onchange="cargarPiezasEdicion('${id}', 'Balero')">
                            <label class="btn btn-outline-primary w-100 fw-bold" for="edit-balero">BALERO</label>
                            <input type="radio" class="btn-check" name="edit-tipo" id="edit-buje" value="Buje" ${tipoActual === 'Buje' ? 'checked' : ''} onchange="cargarPiezasEdicion('${id}', 'Buje')">
                            <label class="btn btn-outline-primary w-100 fw-bold" for="edit-buje">BUJE</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-primary">GRAVEDAD</label>
                        <div class="d-flex gap-2">
                            <input type="radio" class="btn-check" name="edit-sev" id="edit-baja" value="Baja" ${severidadActual === 'Baja' ? 'checked' : ''}>
                            <label class="btn btn-outline-success w-100 fw-bold" for="edit-baja">BAJA</label>
                            <input type="radio" class="btn-check" name="edit-sev" id="edit-media" value="Media" ${severidadActual === 'Media' ? 'checked' : ''}>
                            <label class="btn btn-outline-warning w-100 fw-bold" for="edit-media">MEDIA</label>
                            <input type="radio" class="btn-check" name="edit-sev" id="edit-alta" value="Alta" ${severidadActual === 'Alta' ? 'checked' : ''}>
                            <label class="btn btn-outline-danger w-100 fw-bold" for="edit-alta">ALTA</label>
                        </div>
                    </div>
                </div>

                <label class="form-label small fw-bold text-primary">3. COMPONENTES (Haz clic para seleccionar/quitar)</label>
                <div id="gridPiezasEdicion" class="row g-2 mb-3 border rounded p-2 bg-light" style="max-height: 180px; overflow-y: auto;"></div>

                <div class="mb-2">
                    <label class="form-label small fw-bold">TIPO DE DEFECTO PRINCIPAL</label>
                    <select id="edit-defecto" class="form-select border-primary"></select>
                </div>
            </div>
        `,
        didOpen: () => {
            fetch('backend/obtener_defectos_json.php').then(res => res.json()).then(data => {
                const select = document.getElementById('edit-defecto');
                data.forEach(d => {
                    const opt = document.createElement('option');
                    opt.value = d.id; opt.text = d.nombre_defecto;
                    if(d.id == defectoActualId) opt.selected = true;
                    select.appendChild(opt);
                });
            });
            cargarPiezasEdicion(id, tipoActual);
        },
        showCancelButton: true,
        confirmButtonText: 'GUARDAR CAMBIOS',
        confirmButtonColor: '#00539b',
        preConfirm: () => {
            if (editScrapItems.length === 0) { Swal.showValidationMessage('Selecciona al menos una pieza'); return false; }
            return {
                id_ticket: id,
                motor: document.getElementById('edit-modelo').value,
                cantidad: document.getElementById('edit-cantidad').value,
                tipo: document.querySelector('input[name="edit-tipo"]:checked').value,
                severidad: document.querySelector('input[name="edit-sev"]:checked').value,
                id_defecto: document.getElementById('edit-defecto').value,
                piezas_id: editScrapItems.map(i => i.id),
                piezas_cant: editScrapItems.map(i => i.qty)
            };
        }
    }).then((result) => {
        if (result.isConfirmed) enviarForm('backend/modificar_ticket.php', result.value);
    });
}

function cargarPiezasEdicion(idTicket, tipo) {
    const grid = document.getElementById('gridPiezasEdicion');
    grid.innerHTML = '<div class="text-center w-100 small py-3">Cargando componentes...</div>';
    fetch(`backend/obtener_piezas_ticket.php?id_ticket=${idTicket}&tipo=${tipo}`).then(res => res.json()).then(data => {
        grid.innerHTML = "";
        if(editScrapItems.length === 0) {
            for (let id_p in data.asignadas) { editScrapItems.push({ id: id_p, qty: data.asignadas[id_p] }); }
        }
        data.catalogo.forEach(p => {
            const isActive = editScrapItems.find(i => i.id == p.id);
            grid.innerHTML += `<div class="col-md-3"><button type="button" class="btn btn-sm w-100 btn-pieza-edit ${isActive ? 'btn-primary' : 'btn-outline-secondary'}" id="btn-edit-p-${p.id}" onclick="togglePiezaEdicion('${p.id}')">${p.descripcion}</button></div>`;
        });
    });
}

function togglePiezaEdicion(id) {
    const idx = editScrapItems.findIndex(i => i.id == id);
    const btn = document.getElementById(`btn-edit-p-${id}`);
    if (idx > -1) {
        editScrapItems.splice(idx, 1);
        btn.classList.replace('btn-primary', 'btn-outline-secondary');
    } else {
        editScrapItems.push({ id: id, qty: 1 });
        btn.classList.replace('btn-outline-secondary', 'btn-primary');
    }
}

function enviarForm(action, data) {
    const form = document.createElement('form');
    form.method = 'POST'; form.action = action;
    Object.keys(data).forEach(key => {
        if(Array.isArray(data[key])) {
            data[key].forEach(val => {
                const input = document.createElement('input');
                input.type = 'hidden'; input.name = key + '[]'; input.value = val;
                form.appendChild(input);
            });
        } else {
            const input = document.createElement('input');
            input.type = 'hidden'; input.name = key; input.value = data[key];
            form.appendChild(input);
        }
    });
    document.body.appendChild(form);
    form.submit();
}

// Mensajes URL
const urlParams = new URLSearchParams(window.location.search);
if (urlParams.has('msg')) {
    const msg = urlParams.get('msg');
    if (msg === 'success_edit') Swal.fire('Actualizado', 'Ticket modificado con éxito', 'success');
    else if (msg === 'success_cierre') Swal.fire('Cerrado', 'Ticket concluido', 'success');
    else if (msg === 'success_cancel') Swal.fire('Cancelado', 'Ticket anulado', 'info');
    window.history.replaceState({}, document.title, window.location.pathname);
}
</script>
</body>
</html>
