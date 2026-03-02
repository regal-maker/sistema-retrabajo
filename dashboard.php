<?php
session_start();
// Si no hay sesión iniciada, regresamos al login por seguridad
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Definimos los roles con privilegios para el Panel de Inteligencia
$roles_autorizados = ['Administrador', 'Ingeniero', 'Supervisor'];
$es_admin = isset($_SESSION['user_rol']) && in_array($_SESSION['user_rol'], $roles_autorizados);
$pagina_actual = 'PANEL PRINCIPAL'; 
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title>Panel Principal - Regal</title>
    <?php include 'includes/header.php'; ?>
    
    <style>
        /* Estilos únicos de esta pantalla (Tarjetas del menú) */
        .menu-card { transition: all 0.3s ease; border: none; border-radius: 15px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); height: 100%; }
        .menu-card:hover { transform: translateY(-5px); box-shadow: 0 8px 20px rgba(0,0,0,0.12); }
        .icon-box { font-size: 3rem; margin-bottom: 1rem; }
        .btn-custom { border-radius: 8px; font-weight: 600; padding: 10px; }
    </style>
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="container mt-3">
    <div class="row g-4 justify-content-center">
        <div class="col-md-6 col-lg-3">
            <div class="card menu-card text-center p-4">
                <div class="card-body">
                    <i class="bi bi-qr-code-scan icon-box text-primary"></i>
                    <h5 class="fw-bold">Nueva Captura</h5>
                    <p class="text-muted small">Registrar un nuevo folio de retrabajo de motor.</p>
                    <a href="captura_motor.php" class="btn btn-primary w-100 btn-custom">Ir a Captura</a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card menu-card text-center p-4">
                <div class="card-body">
                    <i class="bi bi-clock-history icon-box text-warning"></i>
                    <h5 class="fw-bold">Tickets Abiertos</h5>
                    <p class="text-muted small">Gestionar folios en proceso y tiempos de respuesta.</p>
                    <a href="tickets_abiertos.php" class="btn btn-warning w-100 btn-custom text-white">Ver Monitoreo</a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card menu-card text-center p-4">
                <div class="card-body">
                    <i class="bi bi-search icon-box text-success"></i>
                    <h5 class="fw-bold">Historial</h5>
                    <p class="text-muted small">Consultar folios cerrados y soluciones técnicas aplicadas.</p>
                    <a href="consultas.php" class="btn btn-success w-100 btn-custom">Ver Consultas</a>
                </div>
            </div>
        </div>

        <?php if ($es_admin ): ?>
       <div class="col-md-6 col-lg-3">
            <div class="card menu-card text-center p-4">
                <div class="card-body">
                    <i class="bi bi-graph-up-arrow icon-box text-black"></i>
                    <h5 class="fw-bold">Panel de Estadísticas</h5>
                    <p class="small opacity-75">Análisis estadístico de fallas y Pareto de defectos.</p>
                    <a href="dashboard_admin.php" class="btn btn-light w-100 btn-custom text-primary">Panel Admin</a>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>