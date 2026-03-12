<?php
session_start();
require_once '../config/conexion.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_ticket'])) {
    $id_ticket = $_POST['id_ticket'];

    try {
        // Actualizamos el estado a 'Cancelado'
        $stmt = $pdo->prepare("UPDATE tickets SET 
                                estado = 'Cancelado', 
                                comentarios_cierre = 'CANCELADO POR ERROR', 
                                fecha_cierre = NOW() 
                                WHERE id = ?");
        
        $stmt->execute([$id_ticket]);

        header("Location: ../tickets_abiertos.php?msg=success_cancel");
        exit();

    } catch (PDOException $e) {
        die("Error al cancelar el folio: " . $e->getMessage());
    }
} else {
    header("Location: ../tickets_abiertos.php");
    exit();
}
