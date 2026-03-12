<?php
session_start();
require_once '../config/conexion.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_ticket'])) {
    $id = $_POST['id_ticket'];
    $motor = $_POST['motor'];
    $tipo = $_POST['tipo'];

    try {
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
        die("Error al modificar el ticket: " . $e->getMessage());
    }
}
