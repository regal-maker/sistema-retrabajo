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

    $horas_t = floor($minutos_total / 60);
    $minutos_t = $minutos_total % 60;
    $tiempo_display = ($horas_t > 0) ? "{$horas_t}h {$minutos_t}m" : "{$minutos_t}m";

    $clase_semaforo = "";
    if ($minutos_total >= 60 && $minutos_total < 120) $clase_semaforo = "border-alerta";
    elseif ($minutos_total >= 120) $clase_semaforo = "border-critico";
?>
    <div class="col-lg-12">
        <div class="card card-ticket <?php echo $clase_semaforo; ?> mb-2 shadow-sm">
            <div class="card-body p-2 px-3">
                <div class="row align-items-center">
                    <div class="col-md-2 border-end text-center">
                        <span class="folio-badge mb-1 d-inline-block">#<?php echo $t['folio']; ?></span><br>
                        <span class="badge bg-info text-dark w-100" style="font-size: 0.6rem;"><?php echo strtoupper($t['tipo_motor_captura']); ?></span>
                        <div class="mt-1 fw-bold" style="font-size: 0.9rem;"><i class="bi bi-clock-history"></i> <?php echo $tiempo_display; ?></div>
                    </div>
                    
                    <div class="col-md-3 border-end">
                        <small class="text-muted fw-bold d-block" style="font-size: 0.6rem;">MOTOR / MODELO</small>
                        <div class="serie-txt" style="font-size: 1.1rem; font-weight: 800;"><?php echo $t['id_motor']; ?></div>
                    </div>

                    <div class="col-md-3 border-end text-center">
                        <button class="btn btn-outline-primary btn-sm py-1 px-3 fw-bold rounded-pill" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?php echo $t['id']; ?>" style="font-size: 0.75rem;">
                            <i class="bi bi-box-seam"></i> VER PIEZAS SCRAP
                        </button>
                        <div class="text-muted small mt-1">Defecto: <?php echo $t['nombre_defecto']; ?></div>
                    </div>

                    <div class="col-md-2 border-end text-center">
                        <small class="text-muted d-block" style="font-size: 0.6rem;">OPERADOR</small>
                        <span class="fw-bold small"><?php echo $t['operador']; ?></span>
                    </div>

                    <div class="col-md-2 text-center d-flex flex-column gap-2">
                        <button class="btn btn-success btn-sm fw-bold shadow-sm" onclick="finalizar(<?php echo $t['id']; ?>)">
                            <i class="bi bi-check-circle"></i> CERRAR
                        </button>
                        
                        <button class="btn btn-outline-primary btn-sm fw-bold" 
                            onclick="editarTicket(<?php echo $t['id']; ?>, '<?php echo addslashes($t['id_motor']); ?>', '<?php echo addslashes($t['tipo_motor_captura']); ?>')">
                            <i class="bi bi-pencil-square"></i> EDITAR
                        </button>

                        <button class="btn btn-outline-danger btn-sm fw-bold" onclick="cancelarTicket(<?php echo $t['id']; ?>)">
                            <i class="bi bi-x-circle"></i> CANCELAR
                        </button>
                    </div>
                </div>

                <div class="collapse" id="collapse-<?php echo $t['id']; ?>">
                    <div class="mt-2 bg-light p-3 border rounded shadow-inner">
                        <small class="d-block text-muted mb-2 fw-bold" style="font-size: 0.65rem; letter-spacing: 0.5px;">MATERIALES REGISTRADOS:</small>
                        <div class="d-flex flex-wrap gap-2">
                            <?php 
                            if(!empty($t['piezas_detalle'])){
                                $piezas = explode('||', $t['piezas_detalle']);
                                foreach($piezas as $p): ?>
                                    <span class="pieza-tag">
                                        <i class="bi bi-gear-fill me-1" style="font-size: 0.7rem;"></i> <?php echo $p; ?>
                                    </span>
                                <?php endforeach;
                            } else { 
                                echo "<span class='text-muted small'>Sin piezas registradas.</span>"; 
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>
