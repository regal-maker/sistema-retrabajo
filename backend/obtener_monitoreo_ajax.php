<?php
include('../config/conexion.php');
session_start();
if (!isset($_SESSION['user_id'])) { exit("Sesión no válida"); }

$rol = $_SESSION['user_rol']; // Asumiendo que guardas el rol en la sesión
$user_id = $_SESSION['user_id'];

$where_clauses = [];
$params = [];

// --- LÓGICA DE EXCEPCIÓN PARA ADMINISTRADORES / INGENIEROS ---
if ($rol !== 'Administrador' && $rol !== 'Ingeniero') {
    $where_clauses[] = "t.id_usuario_apertura = :user_id";
    $params['user_id'] = $user_id;
}

// Filtros provenientes de la URL (GET)
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
        ORDER BY t.fecha_apertura DESC LIMIT 100";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tickets = $stmt->fetchAll();

if (empty($tickets)) {
    echo '<div class="col-12 text-center py-5"><p class="text-muted">No se encontraron registros.</p></div>';
    exit;
}

foreach($tickets as $t): ?>
    <div class="col-lg-12">
        <div class="card card-historial shadow-sm mb-2" style="border-left: 5px solid var(--regal-blue);">
            <div class="card-body p-2 px-3">
                <div class="row align-items-center">
                    <div class="col-md-2 border-end text-center">
                        <span class="badge border text-primary"><?php echo $t['folio']; ?></span>
                        <div class="text-muted small mt-1"><?php echo date('d/m/y H:i', strtotime($t['fecha_apertura'])); ?></div>
                    </div>
                    <div class="col-md-3 border-end">
                        <div class="fw-bold"><?php echo $t['id_motor']; ?></div>
                        <span class="badge bg-light text-dark border"><?php echo strtoupper($t['tipo_motor_captura']); ?></span>
                    </div>
                    <div class="col-md-3 border-end">
                        <small class="text-muted d-block fw-bold" style="font-size: 0.6rem;">DEFECTO</small>
                        <span class="fw-bold text-danger"><?php echo $t['nombre_defecto']; ?></span>
                    </div>
                    <div class="col-md-2 border-end text-center">
                        <span class="badge <?php 
                            echo ($t['estado']=='Cerrado') ? 'bg-success' : (($t['estado']=='Cancelado') ? 'bg-danger' : 'bg-warning text-dark'); 
                        ?>"><?php echo strtoupper($t['estado']); ?></span>
                    </div>
                    <div class="col-md-2 text-center">
                        <small class="text-muted d-block fw-bold" style="font-size: 0.6rem;">OPERADOR</small>
                        <span class="small fw-bold"><?php echo $t['operador']; ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>
