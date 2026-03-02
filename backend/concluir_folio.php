<?php
session_start();
require_once '../config/conexion.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $folio = $_POST['folio'];
    $conclusion = $_POST['conclusion'];

    try {
        $stmt = $pdo->prepare("UPDATE tickets SET 
                                estado = 'Cerrado', 
                                comentarios_cierre = ?, 
                                fecha_cierre = NOW() 
                                WHERE folio = ?");
        $stmt->execute([$conclusion, $folio]);

        header("Location: ../tickets_abiertos.php?msg=success_cierre");
        exit();
    } catch (PDOException $e) {
        die("Error al concluir el folio: " . $e->getMessage());
    }
}
?>