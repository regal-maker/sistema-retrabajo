<?php
session_start();
date_default_timezone_set('America/Mexico_City');
require_once 'config/conexion.php';

$roles_autorizados = ['Administrador', 'Ingeniero', 'Supervisor'];
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_rol'], $roles_autorizados)) {
    header("Location: dashboard.php?error=acceso_restringido"); exit();
}

$pagina_actual = 'ANÁLISIS DE INDICADORES (KPI)';

// Lógica de Conteo Mensual: Si no hay filtro, mostrar el mes actual
$fecha_inicio = $_GET['inicio'] ?? date('Y-m-01'); // Primer día del mes actual
$fecha_fin = $_GET['fin'] ?? date('Y-m-d');
$umbral_horas = 2;

$params = ['inicio' => $fecha_inicio . ' 00:00:00', 'fin' => $fecha_fin . ' 23:59:59'];

// 1. KPI: Motores retrabajados (Suma cantidad_motores del periodo)
$stmt1 = $pdo->prepare("SELECT SUM(cantidad_motores) FROM tickets WHERE (fecha_apertura BETWEEN :inicio AND :fin) AND estado != 'Cancelado'");
$stmt1->execute($params);
$countMotores = $stmt1->fetchColumn() ?: 0;

// 2. Listado para Acordeón: Folios Activos
$foliosActivos = $pdo->query("SELECT folio, id_motor, fecha_apertura FROM tickets WHERE estado = 'Abierto' ORDER BY fecha_apertura ASC")->fetchAll(PDO::FETCH_ASSOC);

// 3. Listado para Acordeón: Folios con Retardo (>2h)
$foliosRetardo = $pdo->query("SELECT folio, id_motor, fecha_apertura, TIMESTAMPDIFF(HOUR, fecha_apertura, NOW()) as horas 
                              FROM tickets WHERE estado = 'Abierto' AND TIMESTAMPDIFF(HOUR, fecha_apertura, NOW()) >= $umbral_horas 
                              ORDER BY fecha_apertura ASC")->fetchAll(PDO::FETCH_ASSOC);

// 4. Datos para Gráficas (Picos, Severidad y Pareto)
$picosData = $pdo->prepare("SELECT DATE(fecha_apertura) as fecha, SUM(cantidad_motores) as total FROM tickets WHERE (fecha_apertura BETWEEN :inicio AND :fin) AND estado != 'Cancelado' GROUP BY DATE(fecha_apertura) ORDER BY fecha ASC");
$picosData->execute($params);
$picosData = $picosData->fetchAll(PDO::FETCH_ASSOC);

$sevData = $pdo->prepare("SELECT severidad, SUM(cantidad_motores) as total FROM tickets WHERE (fecha_apertura BETWEEN :inicio AND :fin) AND estado != 'Cancelado' GROUP BY severidad");
$sevData->execute($params);
$sevData = $sevData->fetchAll(PDO::FETCH_ASSOC);

$paretoData = $pdo->prepare("SELECT d.nombre_defecto, SUM(t.cantidad_motores) as total FROM tickets t JOIN catalogo_defectos d ON t.id_defecto = d.id WHERE (t.fecha_apertura BETWEEN :inicio AND :fin) AND t.estado != 'Cancelado' GROUP BY d.id ORDER BY total DESC LIMIT 10");
$paretoData->execute($params);
$paretoData = $paretoData->fetchAll(PDO::FETCH_ASSOC);

$colorMap = ['Baja' => '#10b981', 'Media' => '#ffb007', 'Alta' => '#e74a3b', 'Critica' => '#6f42c1'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>Dashboard Pro | Regal Rexnord</title>
    <?php include 'includes/header.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        .kpi-card { background: white; border-radius: 12px; padding: 15px; border: none; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        .kpi-val { font-size: 2rem; font-weight: 800; color: var(--regal-blue); line-height: 1; }
        .kpi-label { font-size: 0.65rem; font-weight: 700; color: #6c757d; text-transform: uppercase; margin-top: 5px; }
        .acc-list { font-size: 0.75rem; max-height: 150px; overflow-y: auto; }
        .retraso-alert { color: var(--danger); animation: blink 2s infinite; }
        @keyframes blink { 0% { opacity: 1; } 50% { opacity: 0.5; } 100% { opacity: 1; } }
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
    <div class="kpi-card mb-4 py-2">
        <form class="row align-items-end g-2">
            <div class="col-md-3"><label class="small fw-bold">DESDE:</label><input type="date" name="inicio" class="form-control form-control-sm" value="<?php echo $fecha_inicio; ?>"></div>
            <div class="col-md-3"><label class="small fw-bold">HASTA:</label><input type="date" name="fin" class="form-control form-control-sm" value="<?php echo $fecha_fin; ?>"></div>
            <div class="col-md-3"><button type="submit" class="btn btn-primary btn-sm w-100 fw-bold">FILTRAR PERIODO</button></div>
            <div class="col-md-3 text-end"><small class="text-muted">Mostrando: <?php echo date('M Y', strtotime($fecha_inicio)); ?></small></div>
        </form>
    </div>

    <div class="row g-3 mb-4 text-center">
        <div class="col-md-4">
            <div class="kpi-card border-bottom border-primary border-4">
                <div class="kpi-val"><?php echo $countMotores; ?></div>
                <div class="kpi-label">Motores Retrabajados</div>
                <small class="text-muted d-block mt-2" style="font-size: 0.6rem;">Total de unidades en el periodo seleccionado</small>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="kpi-card">
                <div class="kpi-val"><?php echo count($foliosActivos); ?></div>
                <div class="kpi-label">Folios Activos</div>
                <button class="btn btn-link btn-sm p-0 mt-2 fw-bold text-decoration-none" type="button" data-bs-toggle="collapse" data-bs-target="#listAbiertos">
                    Ver Lista <i class="bi bi-chevron-down"></i>
                </button>
                <div class="collapse mt-2" id="listAbiertos">
                    <div class="acc-list list-group list-group-flush text-start border-top">
                        <?php foreach($foliosActivos as $f): ?>
                            <div class="list-group-item p-1 border-0">
                                <span class="fw-bold text-primary"><?php echo $f['folio']; ?></span> - <small><?php echo $f['id_motor']; ?></small>
                            </div>
                        <?php endforeach; if(empty($foliosActivos)) echo "<small class='text-muted'>Sin folios</small>"; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="kpi-card">
                <div class="kpi-val retraso-alert"><?php echo count($foliosRetardo); ?></div>
                <div class="kpi-label text-danger">Folios con Retardo (>2h)</div>
                <button class="btn btn-link btn-sm p-0 mt-2 fw-bold text-danger text-decoration-none" type="button" data-bs-toggle="collapse" data-bs-target="#listRetardo">
                    Ver Críticos <i class="bi bi-chevron-down"></i>
                </button>
                <div class="collapse mt-2" id="listRetardo">
                    <div class="acc-list list-group list-group-flush text-start border-top">
                        <?php foreach($foliosRetardo as $f): ?>
                            <div class="list-group-item p-1 border-0">
                                <span class="fw-bold text-danger"><?php echo $f['folio']; ?></span> - <small><?php echo $f['horas']; ?>h retraso</small>
                            </div>
                        <?php endforeach; if(empty($foliosRetardo)) echo "<small class='text-muted'>Sin retrasos</small>"; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-8"><div class="kpi-card"><h6 class="card-title-custom fw-bold small text-muted">TENDENCIA: PICOS DE PRODUCCIÓN</h6><div style="height: 250px;"><canvas id="chartPicos"></canvas></div></div></div>
        <div class="col-lg-4"><div class="kpi-card"><h6 class="card-title-custom fw-bold small text-muted">SEVERIDAD</h6><div style="height: 250px;"><canvas id="chartSev"></canvas></div></div></div>
        <div class="col-lg-12"><div class="kpi-card"><h6 class="card-title-custom fw-bold small text-muted">PARETO: DEFECTOS (UNIDADES)</h6><div style="height: 300px;"><canvas id="chartPareto"></canvas></div></div></div>
    </div>
</div>

<script>
// Lógica de gráficas igual a tu versión anterior pero optimizada visualmente
new Chart(document.getElementById('chartPicos'), {
    type: 'line',
    data: {
        labels: <?php echo json_encode(array_column($picosData, 'fecha')); ?>,
        datasets: [{ label: 'Motores', data: <?php echo json_encode(array_column($picosData, 'total')); ?>, borderColor: '#004a99', fill: true, backgroundColor: 'rgba(0, 74, 153, 0.1)', tension: 0.3 }]
    },
    options: { maintainAspectRatio: false }
});

new Chart(document.getElementById('chartSev'), {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode(array_column($sevData, 'severidad')); ?>,
        datasets: [{ data: <?php echo json_encode(array_column($sevData, 'total')); ?>, backgroundColor: [<?php foreach($sevData as $s) echo "'".$colorMap[$s['severidad']]."',"; ?>] }]
    },
    options: { maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
});

new Chart(document.getElementById('chartPareto'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode(array_column($paretoData, 'nombre_defecto')); ?>,
        datasets: [{ label: 'Motores', data: <?php echo json_encode(array_column($paretoData, 'total')); ?>, backgroundColor: '#004a99' }]
    },
    options: { indexAxis: 'y', maintainAspectRatio: false }
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
