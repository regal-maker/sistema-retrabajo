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

        .card-ticket { 
            border: none; 
            border-radius: 12px; 
            background: white; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.08); 
            border-left: 7px solid var(--regal-blue); 
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }
        .card-ticket:hover { transform: scale(1.01); box-shadow: 0 6px 15px rgba(0,0,0,0.12); }

        .pieza-tag { 
            background: #eef2f7; 
            color: #334e68; 
            border: 1px solid #d1d9e6;
            padding: 5px 12px; 
            border-radius: 50px; 
            font-size: 0.85rem; 
            font-weight: 600;
            display: inline-block;
            margin: 2px;
        }

        .tiempo-contenedor { text-align: center; border-right: 1px solid #eee; }
        .tiempo-valor { font-size: 1.4rem; font-weight: 800; color: #2d3748; display: block; line-height: 1; }
        .tiempo-label { font-size: 0.65rem; text-transform: uppercase; color: #a0aec0; letter-spacing: 1px; }
        
        .folio-badge { 
            background: var(--regal-blue); 
            color: white; 
            font-size: 0.75rem; 
            padding: 3px 10px; 
            border-radius: 4px; 
            font-weight: bold;
        }

        .border-alerta { border-left-color: #ffc107 !important; } 
        .border-critico { border-left-color: #dc3545 !important; animation: pulse-red 2s infinite; } 
        @keyframes pulse-red { 
            0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.4); } 
            70% { box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); } 
            100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); } 
        }
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
        contenedor.innerHTML = '<div class="alert alert-danger">Error de conexión con el servidor.</div>';
    } finally {
        if(loader) loader.classList.add('d-none');
    }
}

cargarTickets();
setInterval(cargarTickets, 10000);

// Función Cerrar Ticket
function finalizar(id) {
    Swal.fire({
        title: '¿Concluir folio?',
        text: "¿Se arregló el problema del motor referente a este ticket?",
        input: 'textarea',
        inputPlaceholder: 'Escribe aquí la conclusión o detalles del cierre...',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, cerrar ticket',
        cancelButtonText: 'Cancelar',
        inputValidator: (value) => {
            if (!value) { return '¡Es obligatorio escribir una conclusión!' }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'backend/cerrar_ticket.php';

            const idInput = document.createElement('input');
            idInput.type = 'hidden'; idInput.name = 'id_ticket'; idInput.value = id;
            const conclusionInput = document.createElement('input');
            conclusionInput.type = 'hidden'; conclusionInput.name = 'conclusion'; conclusionInput.value = result.value;

            form.appendChild(idInput); form.appendChild(conclusionInput);
            document.body.appendChild(form);
            form.submit();
        }
    });
}

// Función Cancelar Ticket
function cancelarTicket(id) {
    Swal.fire({
        title: '¿Cancelar ticket?',
        text: "Esta acción marcará el ticket como Cancelado.",
        icon: 'error',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, cancelar',
        cancelButtonText: 'No, mantener'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'backend/cancelar_ticket.php';
            const idInput = document.createElement('input');
            idInput.type = 'hidden'; idInput.name = 'id_ticket'; idInput.value = id;
            form.appendChild(idInput);
            document.body.appendChild(form);
            form.submit();
        }
    })
}

// Función Editar Ticket (MODIFICADA)
function editarTicket(id, motorActual, tipoActual) {
    Swal.fire({
        title: 'Modificar Información del Ticket',
        html: `
            <div class="text-start px-2">
                <label class="form-label small fw-bold">MODELO / ID MOTOR:</label>
                <input id="swal-motor" class="form-control mb-3 fw-bold" value="${motorActual}" style="color: var(--regal-blue);">
                
                <label class="form-label small fw-bold">TIPO DE MOTOR:</label>
                <select id="swal-tipo" class="form-select">
                    <option value="AC" ${tipoActual === 'AC' ? 'selected' : ''}>AC</option>
                    <option value="DC" ${tipoActual === 'DC' ? 'selected' : ''}>DC</option>
                    <option value="STEPPER" ${tipoActual === 'STEPPER' ? 'selected' : ''}>STEPPER</option>
                    <option value="BALERO" ${tipoActual === 'BALERO' ? 'selected' : ''}>BALERO</option>
                </select>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Guardar Cambios',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#00539b',
        preConfirm: () => {
            const nuevoMotor = document.getElementById('swal-motor').value;
            const nuevoTipo = document.getElementById('swal-tipo').value;
            if (!nuevoMotor) {
                Swal.showValidationMessage('El ID del motor es obligatorio');
                return false;
            }
            return { id_ticket: id, motor: nuevoMotor, tipo: nuevoTipo };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'backend/modificar_ticket.php';

            Object.keys(result.value).forEach(key => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = result.value[key];
                form.appendChild(input);
            });

            document.body.appendChild(form);
            form.submit();
        }
    });
}

// Manejo de mensajes por URL
const urlParams = new URLSearchParams(window.location.search);
if (urlParams.has('msg')) {
    const msg = urlParams.get('msg');
    if (msg === 'success_cierre') Swal.fire('¡Cerrado!', 'El Ticket se ha cerrado correctamente.', 'success');
    else if (msg === 'success_cancel') Swal.fire('Cancelado', 'El Ticket ha sido cancelado.', 'info');
    else if (msg === 'success_edit') Swal.fire('Actualizado', 'La información se modificó correctamente.', 'success');
    window.history.replaceState({}, document.title, window.location.pathname);
}
</script>
</body>
</html>
