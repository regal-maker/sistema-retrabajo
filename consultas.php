<?php 
$pagina_actual = 'Historial de Tickets';
include('config/conexion.php'); 
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }

// Inicialización de filtros
$where_clauses = [];
$params = [];

// Lógica de filtrado
if (!empty($_GET['folio'])) { $where_clauses[] = "t.folio LIKE :folio"; $params['folio'] = "%".$_GET['folio']."%"; }
if (!empty($_GET['f_inicio'])) { $where_clauses[] = "DATE(t.fecha_apertura) >= :f_ini"; $params['f_ini'] = $_GET['f_inicio']; }
if (!empty($_GET['f_fin'])) { $where_clauses[] = "DATE(t.fecha_apertura) <= :f_fin"; $params['f_fin'] = $_GET['f_fin']; }
if (!empty($_GET['modelo'])) { $where_clauses[] = "t.id_motor LIKE :modelo"; $params['modelo'] = "%".$_GET['modelo']."%"; }
if (!empty($_GET['defecto'])) { $where_clauses[] = "d.nombre_defecto LIKE :defecto"; $params['defecto'] = "%".$_GET['defecto']."%"; }
if (!empty($_GET['estado'])) { $where_clauses[] = "t.estado = :estado"; $params['estado'] = $_GET['estado']; }
if (!empty($_GET['operador'])) { $where_clauses[] = "u.nombre LIKE :operador"; $params['operador'] = "%".$_GET['operador']."%"; }

$where_sql = count($where_clauses) > 0 ? "WHERE " . implode(" AND ", $where_clauses) : "";

$sql = "SELECT t.*, d.nombre_defecto, u.nombre as operador,
        (SELECT GROUP_CONCAT(CONCAT(tp.cantidad, 'x ', cp.descripcion) SEPARATOR '||') 
         FROM ticket_piezas tp JOIN catalogo_piezas cp ON tp.id_pieza = cp.id 
         WHERE tp.id_ticket = t.id) as piezas_detalle
        FROM tickets t
        LEFT JOIN catalogo_defectos d ON t.id_defecto = d.id
        LEFT JOIN usuarios u ON t.id_usuario_apertura = u.id
        $where_sql
        ORDER BY t.fecha_apertura DESC LIMIT 200";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tickets = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title>Historial Completo | Regal Rexnord</title>
    <?php include 'includes/header.php'; ?>
    <style>
        .card-historial { border: none; border-radius: 8px; margin-bottom: 8px; border-left: 5px solid var(--regal-blue); transition: 0.2s; }
        .serie-txt { font-size: 1.15rem; font-weight: 800; color: #212529; }
        .cant-circle { background: var(--regal-blue); color: white; width: 18px; height: 18px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 0.6rem; margin-right: 8px; font-weight: bold; }
        .filter-box { background: white; border-radius: 10px; padding: 15px; margin-bottom: 15px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .pieza-item { display: flex; align-items: center; padding: 6px 15px; border-bottom: 1px solid #eee; font-size: 0.8rem; }
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
            <div class="col-md-1">
                <label class="form-label fw-bold small mb-1">Folio</label>
                <input type="text" name="folio" class="form-control form-control-sm" placeholder="RT-..." value="<?php echo $_GET['folio']??''; ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-bold small mb-1">Rango Fechas</label>
                <div class="input-group input-group-sm">
                    <input type="date" name="f_inicio" class="form-control" value="<?php echo $_GET['f_inicio']??''; ?>">
                    <input type="date" name="f_fin" class="form-control" value="<?php echo $_GET['f_fin']??''; ?>">
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-bold small mb-1">Modelo / Serie</label>
                <input type="text" name="modelo" class="form-control form-control-sm" placeholder="Buscar..." value="<?php echo $_GET['modelo']??''; ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-bold small mb-1">Defecto</label>
                <input type="text" name="defecto" class="form-control form-control-sm" value="<?php echo $_GET['defecto']??''; ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-bold small mb-1">Estado</label>
                <select name="estado" class="form-select form-select-sm">
                    <option value="">-- Todos --</option>
                    <option value="Abierto" <?php echo ($_GET['estado']??'')=='Abierto'?'selected':''; ?>>Abiertos</option>
                    <option value="Cerrado" <?php echo ($_GET['estado']??'')=='Cerrado'?'selected':''; ?>>Cerrados</option>
                    <option value="Cancelado" <?php echo ($_GET['estado']??'')=='Cancelado'?'selected':''; ?>>Cancelados</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-bold small mb-1">Operador</label>
                <input type="text" name="operador" class="form-control form-control-sm" value="<?php echo $_GET['operador']??''; ?>">
            </div>
            <div class="col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold">Filtrar</button>
                <a href="consultas.php" class="btn btn-secondary btn-sm"><i class="bi bi-x-circle"></i></a>
            </div>
        </form>
    </div>

    <div class="row g-2">
        <?php foreach($tickets as $t): ?>
        <div class="col-lg-12">
            <div class="card card-historial shadow-sm">
                <div class="card-body p-2 px-3">
                    <div class="row align-items-center">
                        <div class="col-md-2 border-end text-center">
                            <span class="badge border text-primary" style="font-size: 0.75rem;"><?php echo $t['folio']; ?></span>
                            <div class="text-muted small mt-1"><?php echo date('d/m/y H:i', strtotime($t['fecha_apertura'])); ?></div>
                        </div>
                        
                        <div class="col-md-3 border-end">
                            <div class="serie-txt"><?php echo $t['id_motor']; ?></div>
                            <span class="badge bg-light text-dark border-0 p-0"><?php echo strtoupper($t['tipo_motor_captura']); ?></span>
                            <span class="ms-2 badge bg-primary" style="font-size: 0.65rem;"><?php echo $t['cantidad_motores']; ?> UNIDADES</span>
                        </div>

                        <div class="col-md-3 border-end">
                            <small class="text-muted d-block fw-bold" style="font-size: 0.6rem;">DEFECTO</small>
                            <span class="fw-bold text-danger"><?php echo $t['nombre_defecto']; ?></span>
                        </div>

                        <div class="col-md-2 border-end text-center">
                            <span class="badge mb-1 <?php 
                                if($t['estado']=='Cerrado') echo 'bg-success';
                                elseif($t['estado']=='Cancelado') echo 'bg-danger';
                                else echo 'bg-warning text-dark';
                            ?>">
                                <?php echo strtoupper($t['estado']); ?>
                            </span>
                            <button class="btn btn-link btn-sm d-block w-100 fw-bold text-decoration-none py-0" type="button" data-bs-toggle="collapse" data-bs-target="#piezas-<?php echo $t['id']; ?>">
                                <i class="bi bi-list-ul"></i> Ver Piezas
                            </button>
                        </div>

                        <div class="col-md-2 text-center">
                            <small class="text-muted d-block fw-bold" style="font-size: 0.6rem;">OPERADOR</small>
                            <span class="small fw-bold"><?php echo $t['operador']; ?></span>
                        </div>
                    </div>

                    <div class="collapse" id="piezas-<?php echo $t['id']; ?>">
                        <div class="mt-2 bg-light rounded border">
                            <?php 
                            if (!empty($t['piezas_detalle'])) {
                                $lista = explode('||', $t['piezas_detalle']);
                                foreach ($lista as $p) {
                                    $partes = explode('x ', $p);
                                    echo "<div class='pieza-item'>
                                            <span class='cant-circle'>{$partes[0]}</span> <b>{$partes[1]}</b>
                                          </div>";
                                }
                            } else { echo "<div class='p-2 text-center text-muted small'>Sin piezas.</div>"; }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
