<?php
session_start();
require_once '../config/conexion.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_ticket'])) {
    $id = $_POST['id_ticket'];
    $motor = trim($_POST['motor']);
    $tipo = $_POST['tipo'];

    try {
        // Actualizamos id_motor y tipo_motor_captura que son los datos clave del ticket
        $stmt = $pdo->prepare("UPDATE tickets SET 
                                id_motor = :motor, 
                                tipo_motor_captura = :tipo 
                                WHERE id = :id");
        
        $stmt->execute([
            ':motor' => $motor,
            ':tipo'  => $tipo,
            ':id'    => $id
        ]);

        header("Location: ../tickets_abiertos.php?msg=success_edit");
        exit();

    } catch (PDOException $e) {
        // Log del error para depuración
        error_log("Error en modificar_ticket: " . $e->getMessage());
        header("Location: ../tickets_abiertos.php?msg=error_edit");
        exit();
    }
} else {
    header("Location: ../tickets_abiertos.php");
    exit();
}
