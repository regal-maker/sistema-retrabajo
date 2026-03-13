<?php
include('../config/conexion.php');
header('Content-Type: application/json');

try {
    // Obtenemos los defectos del catálogo
    $sql = "SELECT id, nombre_defecto FROM catalogo_defectos ORDER BY nombre_defecto ASC";
    $stmt = $pdo->query($sql);
    $defectos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($defectos);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
