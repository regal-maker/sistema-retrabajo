<?php
include('../config/conexion.php');
header('Content-Type: application/json');

if (!isset($_GET['id_ticket']) || !isset($_GET['tipo'])) {
    echo json_encode(['error' => 'Faltan parámetros']);
    exit;
}

$id_ticket = $_GET['id_ticket'];
$tipo = $_GET['tipo']; // 'Balero' o 'Buje'

try {
    // 1. Obtener catálogo filtrado por tipo
    $stmt1 = $pdo->prepare("SELECT id, descripcion FROM catalogo_piezas WHERE tipo = ? OR tipo = 'Ambos' ORDER BY descripcion ASC");
    $stmt1->execute([$tipo]);
    $catalogo = $stmt1->fetchAll(PDO::FETCH_ASSOC);

    // 2. Obtener piezas que ya tiene el ticket
    $stmt2 = $pdo->prepare("SELECT id_pieza, cantidad FROM ticket_piezas WHERE id_ticket = ?");
    $stmt2->execute([$id_ticket]);
    $asignadas = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatear asignadas para fácil búsqueda en JS: { id_pieza: cantidad }
    $asignadas_map = [];
    foreach($asignadas as $a) {
        $asignadas_map[$a['id_pieza']] = $a['cantidad'];
    }

    echo json_encode([
        'catalogo' => $catalogo, 
        'asignadas' => $asignadas_map
    ]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
