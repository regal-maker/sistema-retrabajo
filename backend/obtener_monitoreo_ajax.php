<?php
include('../config/conexion.php');
session_start();
if (!isset($_SESSION['user_id'])) { exit("Sesión no válida"); }

date_default_timezone_set('America/Mexico_City');

$user_id = $_SESSION['user_id'];
$where_clauses = ["t.estado = 'Abierto'", "t.id_usuario_apertura = :user_id"];
$params = ['user_id' => $user_id];

$sql = "SELECT t.*, d.nombre_defecto, u.nombre as operador,
        (SELECT GROUP_CONCAT(CONCAT(tp.cantidad, 'x ', cp.descripcion) SEPARATOR '||') 
         FROM ticket_piezas tp JOIN catalogo_piezas cp ON tp.id_pieza = cp.id 
         WHERE tp.id_ticket = t.id) as piezas_detalle
        FROM tickets t
        LEFT JOIN catalogo_defectos d ON t.id_defecto = d.id
        LEFT JOIN usuarios u ON t.id_usuario_apertura = u.id
        WHERE " . implode(" AND ", $where_clauses) . "
        ORDER BY t.fecha_apertura DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tickets = $stmt->fetchAll();

if (empty($tickets)) {
    echo '<div class="col-12 text-center py-5"><p class="text-muted">No tienes tickets abiertos.</p></div>';
    exit;
}

foreach($tickets as $t):
    $fecha_inicio = new DateTime($t['fecha_apertura']);
    $fecha_actual = new DateTime();
    if ($fecha_actual < $fecha_inicio) $fecha_actual = $fecha_inicio;
    $diff = $fecha_actual->diff($fecha_inicio);
    $minutos_total = ($diff->days * 24 * 60) + ($diff->h * 60) + $diff->i;

    $clase_semaforo = "";
    if ($minutos_total >= 60 && $minutos_total < 120) $clase_semaforo = "border-alerta";
    elseif ($minutos_total >= 120) $clase_semaforo = "border-critico";
?>
    <div class="col-lg-12">
        <div class="card card-horizontal <?php echo $clase_semaforo; ?>">
            <div class="card-body p-2 px-3">
                <div class="row align-items-center">
                    <div class="col-md-2 border-end text-center">
                        <span class="folio-badge mb-1 d-inline-block"><?php echo $t['folio']; ?></span><br>
                        <span class="badge bg-info text-dark w-100" style="font-size: 0.6rem;"><?php echo strtoupper($t['tipo_motor_captura']); ?></span>
                        <div class="mt-1 small"><i class="bi bi-clock"></i> <?php echo $minutos_total; ?> min</div>
                    </div>
                    <div class="col-md-3 border-end">
                        <small class="text-muted fw-bold d-block" style="font-size: 0.6rem;">MOTOR / MODELO</small>
                        <div class="serie-txt" style="font-size: 1.1rem; font-weight: 800;"><?php echo $t['id_motor']; ?></div>
                    </div>
                    <div class="col-md-3 border-end text-center">
                        <button class="btn btn-link btn-sm py-0" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?php echo $t['id']; ?>">
                            Ver Piezas Scrap
                        </button>
                        <div class="text-muted small">Defecto: <?php echo $t['nombre_defecto']; ?></div>
                    </div>
                    <div class="col-md-2 border-end text-center">
                        <small class="text-muted d-block" style="font-size: 0.6rem;">OPERADOR</small>
                        <span class="fw-bold small"><?php echo $t['operador']; ?></span>
                    </div>
                    // ... (Dentro de tu foreach de tickets, en la sección de botones) ...
<div class="col-md-2 text-center d-flex flex-column gap-2">
    <button class="btn btn-success btn-sm fw-bold shadow-sm" onclick="finalizar(<?php echo $t['id']; ?>)">
        <i class="bi bi-check-circle"></i> CERRAR
    </button>
    
    <button class="btn btn-outline-danger btn-sm fw-bold" onclick="cancelarTicket(<?php echo $t['id']; ?>)">
        <i class="bi bi-x-circle"></i> CANCELAR
    </button>
</div>
                        
                </div>
                <div class="collapse" id="collapse-<?php echo $t['id']; ?>">
                    <div class="mt-2 bg-light p-2 small border rounded">
                        <?php 
                        if(!empty($t['piezas_detalle'])){
                            $piezas = explode('||', $t['piezas_detalle']);
                            foreach($piezas as $p) echo "<div>• $p</div>";
                        } else { echo "Sin piezas."; }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>
