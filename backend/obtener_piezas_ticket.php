<?php
include('../config/conexion.php');
header('Content-Type: application/json');

$id_ticket = $_GET['id_ticket'];
$tipo = $_GET['tipo']; // Balero o Buje

try {
    // 1. Catálogo completo según el tipo
    $stmt1 = $pdo->prepare("SELECT id, descripcion FROM catalogo_piezas WHERE tipo = ? OR tipo = 'Ambos' ORDER BY descripcion ASC");
    $stmt1->execute([$tipo]);
    $catalogo = $stmt1->fetchAll(PDO::FETCH_ASSOC);

    // 2. Piezas ya guardadas en este ticket
    $stmt2 = $pdo->prepare("SELECT id_pieza, cantidad FROM ticket_piezas WHERE id_ticket = ?");
    $stmt2->execute([$id_ticket]);
    $asignadas = $stmt2->fetchAll(PDO::FETCH_KEY_PAIR); // Retorna [id_pieza => cantidad]

    echo json_encode(['catalogo' => $catalogo, 'asignadas' => $asignadas]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
