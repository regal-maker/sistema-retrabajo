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
        .card-horizontal { border: none; border-radius: 8px; background: white; box-shadow: 0 2px 6px rgba(0,0,0,0.05); border-left: 5px solid var(--regal-blue); margin-bottom: 8px; transition: 0.3s; }
        .serie-txt { font-size: 1.2rem; font-weight: 800; color: #212529; line-height: 1.2; }
        .folio-badge { background: #e9ecef; color: var(--regal-blue); font-weight: bold; font-size: 0.7rem; padding: 2px 8px; border-radius: 4px; }
        .filter-box { background: white; border-radius: 10px; padding: 12px; margin-bottom: 15px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .cant-circle { background: var(--regal-blue); color: white; width: 20px; height: 20px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 0.65rem; margin-right: 8px; font-weight: bold; }
        .pieza-item { display: flex; justify-content: space-between; padding: 5px 12px; border-bottom: 1px solid #edf0f2; font-size: 0.8rem; }
        .no-tickets-container { padding: 60px 20px; text-align: center; background: white; border-radius: 12px; border: 2px dashed #dee2e6; }
        
        /* Clases de Semaforización */
        .border-alerta { border-left: 8px solid #ffc107 !important; } 
        .border-critico { border-left: 8px solid #dc3545 !important; animation: blink-red 2s infinite; } 
        
        @keyframes blink-red {
            0% { box-shadow: 0 0 0px rgba(220, 53, 69, 0); }
            50% { box-shadow: 0 0 10px rgba(220, 53, 69, 0.5); }
            100% { box-shadow: 0 0 0px rgba(220, 53, 69, 0); }
        }
    </style>
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="container-fluid px-4">
    <div class="filter-box">
        </div>

    <div class="row" id="contenedor">
        <?php if (empty($tickets)): ?>
            <?php else: ?>
            <?php foreach($tickets as $t): 
                // 1. LÓGICA DE TIEMPO
                $fecha_inicio = new DateTime($t['fecha_apertura']);
                $fecha_actual = new DateTime();
                $diferencia = $fecha_actual->diff($fecha_inicio);
                
                // Calculamos el total de minutos para el semáforo
                $minutos_total = ($diferencia->days * 24 * 60) + ($diferencia->h * 60) + $diferencia->i;

                // 2. FORMATEO A HORAS Y MINUTOS
                $horas = floor($minutos_total / 60);
                $minutos_restantes = $minutos_total % 60;
                // Formato final (ej. 1:43)
                $tiempo_formateado = ($horas > 0) ? "{$horas}:" . str_pad($minutos_restantes, 2, "0", STR_PAD_LEFT) : "{$minutos_restantes} min";

                // ASIGNACIÓN DE CLASES PARA EL SEMÁFORO
                $clase_semaforo = "";
                $texto_tiempo = "text-muted";
                if ($minutos_total >= 60 && $minutos_total < 120) {
                    $clase_semaforo = "border-alerta";
                    $texto_tiempo = "text-warning fw-bold";
                } elseif ($minutos_total >= 120) {
                    $clase_semaforo = "border-critico";
                    $texto_tiempo = "text-danger fw-bold";
                }
            ?>
            <div class="col-lg-12">
                <div class="card card-horizontal <?php echo $clase_semaforo; ?>">
                    <div class="card-body p-2 px-3">
                        <div class="row align-items-center">
                            <div class="col-md-2 border-end text-center">
                                <span class="folio-badge mb-1 d-inline-block"><?php echo $t['folio']; ?></span><br>
                                <span class="badge bg-info text-dark w-100" style="font-size: 0.6rem;"><?php echo strtoupper($t['tipo_motor_captura']); ?></span>
                                <div class="mt-1 <?php echo $texto_tiempo; ?>" style="font-size: 0.75rem;">
                                    <i class="bi bi-clock"></i> <?php echo $tiempo_formateado; ?> hrs
                                </div>
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
function finalizar(id) { if(confirm('¿Confirmas el cierre de este folio?')) window.location.href = `backend/cerrar_ticket.php?id=${id}`; }
function cancelar(id) { if(confirm('¿Deseas CANCELAR este folio?')) window.location.href = `backend/cancelar_ticket.php?id=${id}`; }
</script>
</body>
</html>
