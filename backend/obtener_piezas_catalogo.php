<?php
include('../config/conexion.php');
$tipo = $_GET['tipo'] ?? 'Balero';

$stmt = $pdo->prepare("SELECT id, descripcion FROM catalogo_piezas WHERE tipo_compatibilidad = ? OR tipo_compatibilidad = 'Ambos' ORDER BY descripcion ASC");
$stmt->execute([$tipo]);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>