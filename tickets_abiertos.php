<?php 
$pagina_actual = 'Tickets en Estación';
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title>Mis Tickets | Regal Rexnord</title>
    <?php include 'includes/header.php'; ?>
    <style>
        .card-horizontal { border: none; border-radius: 8px; background: white; box-shadow: 0 2px 6px rgba(0,0,0,0.05); border-left: 5px solid var(--regal-blue); margin-bottom: 8px; }
        .border-alerta { border-left: 8px solid #ffc107 !important; } 
        .border-critico { border-left: 8px solid #dc3545 !important; animation: blink-red 2s infinite; } 
        @keyframes blink-red { 50% { box-shadow: 0 0 10px rgba(220, 53, 69, 0.5); } }
        .folio-badge { background: #e9ecef; color: var(--regal-blue); font-weight: bold; font-size: 0.7rem; padding: 2px 8px; border-radius: 4px; }
    </style>
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div class="container-fluid px-4">
    <div class="row mt-3 mb-3 align-items-center">
        <div class="col-md-6">
            <a href="panel_principal.php" class="btn btn-sm btn-outline-secondary px-3 fw-bold">
                <i class="bi bi-arrow-left me-1"></i> VOLVER AL PANEL
            </a>
        </div>
        <div class="col-md-6 text-end">
            <div id="loading-indicator" class="spinner-border spinner-border-sm text-primary d-none" role="status"></div>
            <small class="text-muted ms-2">Auto-actualización activa (5s)</small>
        </div>
    </div>

    <div class="row" id="contenedor-tickets">
        <div class="col-12 text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2">Cargando tus tickets...</p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
async function cargarTickets() {
    const contenedor = document.getElementById('contenedor-tickets');
    const loader = document.getElementById('loading-indicator');
    
    loader.classList.remove('d-none'); // Mostrar mini spinner

    try {
        const response = await fetch('backend/obtener_monitoreo_ajax.php');
        const html = await response.text();
        contenedor.innerHTML = html;
    } catch (error) {
        console.error("Error al refrescar tickets:", error);
    } finally {
        loader.classList.add('d-none'); // Ocultar mini spinner
    }
}

// 1. Cargar inmediatamente al entrar
cargarTickets();

// 2. Refrescar cada 5 segundos
setInterval(cargarTickets, 5000);

function finalizar(id) { if(confirm('¿Cerrar folio?')) window.location.href = `backend/cerrar_ticket.php?id=${id}`; }
</script>
</body>
</html>




