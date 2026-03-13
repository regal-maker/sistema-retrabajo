<?php
include('../config/conexion.php');
header('Content-Type: application/json');

try {
    if (!isset($_GET['id_ticket']) || !isset($_GET['tipo'])) {
        throw new Exception("Faltan parámetros");
    }

    $id_ticket = $_GET['id_ticket'];
    $tipo = $_GET['tipo']; 

    // 1. Catálogo filtrado por tipo (Balero, Buje o Ambos)
    $stmt1 = $pdo->prepare("SELECT id, descripcion FROM catalogo_piezas WHERE tipo = ? OR tipo = 'Ambos' ORDER BY descripcion ASC");
    $stmt1->execute([$tipo]);
    $catalogo = $stmt1->fetchAll(PDO::FETCH_ASSOC);

    // 2. Piezas ya asignadas a este ticket
    $stmt2 = $pdo->prepare("SELECT id_pieza, cantidad FROM ticket_piezas WHERE id_ticket = ?");
    $stmt2->execute([$id_ticket]);
    $asignadas = $stmt2->fetchAll(PDO::FETCH_KEY_PAIR); // Retorna [id_pieza => cantidad]

    echo json_encode([
        'success' => true,
        'catalogo' => $catalogo, 
        'asignadas' => $asignadas ? $asignadas : (object)[]
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
