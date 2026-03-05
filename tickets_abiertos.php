<?php 
$pagina_actual = 'Tickets en Estación';
include('config/conexion.php'); 
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }

// Inicialización de filtros para Tickets Abiertos
$where_clauses = ["t.estado = 'Abierto'"];
$params = [];

if (!empty($_GET['folio'])) { $where_clauses[] = "t.folio LIKE :folio"; $params['folio'] = "%".$_GET['folio']."%"; }
if (!empty($_GET['f_inicio'])) { $where_clauses[] = "DATE(t.fecha_apertura) >= :f_ini"; $params['f_ini'] = $_GET['f_inicio']; }
if (!empty($_GET['modelo'])) { $where_clauses[] = "t.id_motor LIKE :modelo"; $params['modelo'] = "%".$_GET['modelo']."%"; }
if (!empty($_GET['defecto'])) { $where_clauses[] = "d.nombre_defecto LIKE :defecto"; $params['defecto'] = "%".$_GET['defecto']."%"; }
if (!empty($_GET['operador'])) { $where_clauses[] = "u.nombre LIKE :operador"; $params['operador'] = "%".$_GET['operador']."%"; }

$where_sql = "WHERE " . implode(" AND ", $where_clauses);

$sql = "SELECT t.*, d.nombre_defecto, u.nombre as operador,
        (SELECT GROUP_CONCAT(CONCAT(tp.cantidad, 'x ', cp.descripcion) SEPARATOR '||') 
         FROM ticket_piezas tp JOIN catalogo_piezas cp ON tp.id_pieza = cp.id 
         WHERE tp.id_ticket = t.id) as piezas_detalle
        FROM tickets t
        LEFT JOIN catalogo_defectos d ON t.id_defecto = d.id
        LEFT JOIN usuarios u ON t.id_usuario_apertura = u.id
        $where_sql
        ORDER BY t.fecha_apertura DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tickets = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title>Monitoreo | Regal Rexnord</title>
    <?php include 'includes/header.php'; ?>
    <style>
        .card-horizontal { border: none; border-radius: 8px; background: white; box-shadow: 0 2px 6px rgba(0,0,0,0.05); border-left: 5px solid var(--regal-blue); margin-bottom: 8px; }
        .serie-txt { font-size: 1.2rem; font-weight: 800; color: #212529; line-height: 1.2; }
        .folio-badge { background: #e9ecef; color: var(--regal-blue); font-weight: bold; font-size: 0.7rem; padding: 2px 8px; border-radius: 4px; }
        .filter-box { background: white; border-radius: 10px; padding: 12px; margin-bottom: 15px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .cant-circle { background: var(--regal-blue); color: white; width: 20px; height: 20px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 0.65rem; margin-right: 8px; font-weight: bold; }
        .pieza-item { display: flex; justify-content: space-between; padding: 5px 12px; border-bottom: 1px solid #edf0f2; font-size: 0.8rem; }
        .no-tickets-container { padding: 60px 20px; text-align: center; background: white; border-radius: 12px; border: 2px dashed #dee2e6; }
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
    <div class="filter-box">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-1"><label class="form-label fw-bold x-small mb-1">Folio</label><input type="text" name="folio" class="form-control form-control-sm" placeholder="RT-..." value="<?php echo $_GET['folio']??''; ?>"></div>
            <div class="col-md-2"><label class="form-label fw-bold x-small mb-1">Fecha</label><input type="date" name="f_inicio" class="form-control form-control-sm" value="<?php echo $_GET['f_inicio']??''; ?>"></div>
            <div class="col-md-3"><label class="form-label fw-bold x-small mb-1">Modelo / Serie</label><input type="text" name="modelo" class="form-control form-control-sm" placeholder="Buscar motor..." value="<?php echo $_GET['modelo']??''; ?>"></div>
            <div class="col-md-2"><label class="form-label fw-bold x-small mb-1">Defecto</label><input type="text" name="defecto" class="form-control form-control-sm" placeholder="Defecto..." value="<?php echo $_GET['defecto']??''; ?>"></div>
            <div class="col-md-2"><label class="form-label fw-bold x-small mb-1">Operador</label><input type="text" name="operador" class="form-control form-control-sm" placeholder="Nombre..." value="<?php echo $_GET['operador']??''; ?>"></div>
            <div class="col-md-2 d-flex gap-1"><button type="submit" class="btn btn-primary btn-sm w-100 fw-bold">Filtrar</button><a href="tickets_abiertos.php" class="btn btn-secondary btn-sm"><i class="bi bi-x-circle"></i></a></div>
        </form>
    </div>

    <div class="row" id="contenedor">
        <?php if (empty($tickets)): ?>
            <div class="col-12">
                <div class="no-tickets-container shadow-sm">
                    <i class="bi bi-clipboard-check text-muted" style="font-size: 3rem;"></i>
                    <h3 class="fw-bold text-muted mt-2">No hay tickets abiertos</h3>
                    <p class="text-secondary small">Todos los motores han sido procesados o no coinciden con los filtros.</p>
                </div>
            </div>
        <?php else: ?>
            <?php foreach($tickets as $t): ?>
            <div class="col-lg-12">
                <div class="card card-horizontal">
                    <div class="card-body p-2 px-3">
                        <div class="row align-items-center">
                            <div class="col-md-2 border-end text-center">
                                <span class="folio-badge mb-1 d-inline-block"><?php echo $t['folio']; ?></span><br>
                                <span class="badge bg-info text-dark w-100" style="font-size: 0.6rem;"><?php echo strtoupper($t['tipo_motor_captura']); ?></span>
                            </div>

                            <div class="col-md-3 border-end">
                                <small class="text-muted fw-bold d-block" style="font-size: 0.6rem;">MOTOR / MODELO</small>
                                <div class="serie-txt"><?php echo $t['id_motor']; ?></div>
                                <span class="badge bg-primary" style="font-size: 0.6rem;"><?php echo $t['cantidad_motores']; ?> UNIDADES</span>
                            </div>

                            <div class="col-md-3 border-end text-center">
                                <button class="btn btn-link btn-acordeon py-1" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?php echo $t['id']; ?>">
                                    <i class="bi bi-list-check me-1"></i> Ver Piezas Scrap
                                </button>
                                <div class="text-muted mt-1" style="font-size: 0.7rem;">Defecto: <b><?php echo $t['nombre_defecto']; ?></b></div>
                            </div>

                            <div class="col-md-2 border-end text-center">
                                <small class="text-muted fw-bold d-block" style="font-size: 0.6rem;">OPERADOR</small>
                                <span class="fw-bold text-dark" style="font-size: 0.8rem;"><?php echo $t['operador']; ?></span>
                            </div>

                            <div class="col-md-2 text-center d-flex flex-column gap-1">
                                <button class="btn btn-success btn-sm fw-bold" onclick="finalizar(<?php echo $t['id']; ?>)">CERRAR</button>
                                <button class="btn btn-link text-danger p-0 fw-bold" style="font-size: 0.65rem; text-decoration: none;" onclick="cancelar(<?php echo $t['id']; ?>)">
                                    <i class="bi bi-trash"></i> Cancelar
                                </button>
                            </div>
                        </div>

                        <div class="collapse" id="collapse-<?php echo $t['id']; ?>">
                            <div class="mt-2 bg-white border rounded shadow-sm overflow-hidden">
                                <?php 
                                if (!empty($t['piezas_detalle'])) {
                                    $lista = explode('||', $t['piezas_detalle']);
                                    foreach ($lista as $p) {
                                        $partes = explode('x ', $p);
                                        echo "<div class='pieza-item'><div><span class='cant-circle'>{$partes[0]}</span> <b>{$partes[1]}</b></div></div>";
                                    }
                                } else { echo "<div class='p-2 text-center text-muted small'>Sin piezas registradas.</div>"; }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ATENCIÓN: Las rutas de JS ahora apuntan a backend/
function finalizar(id) { if(confirm('¿Confirmas el cierre de este folio?')) window.location.href = `backend/cerrar_ticket.php?id=${id}`; }
function cancelar(id) { if(confirm('¿Deseas CANCELAR este folio?')) window.location.href = `backend/cancelar_ticket.php?id=${id}`; }
</script>
</body>

</html>
