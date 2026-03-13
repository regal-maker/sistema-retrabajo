<?php
session_start();
require_once '../config/conexion.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_ticket'])) {
    $id = $_POST['id_ticket'];
    $motor = trim($_POST['motor']);
    $tipo = $_POST['tipo'];
    $cantidad = $_POST['cantidad'];
    $severidad = $_POST['severidad'];
    $id_defecto = $_POST['id_defecto'];

    try {
        // Actualizamos todos los campos clave
        $stmt = $pdo->prepare("UPDATE tickets SET 
                                id_motor = :motor, 
                                tipo_motor_captura = :tipo,
                                cantidad_motores = :cantidad,
                                severidad = :severidad,
                                id_defecto = :id_defecto
                                WHERE id = :id");
        
        $stmt->execute([
            ':motor'     => $motor,
            ':tipo'      => $tipo,
            ':cantidad'  => $cantidad,
            ':severidad' => $severidad,
            ':id_defecto'=> $id_defecto,
            ':id'        => $id
        ]);

        header("Location: ../tickets_abiertos.php?msg=success_edit");
        exit();

    } catch (PDOException $e) {
        error_log("Error en modificar_ticket: " . $e->getMessage());
        header("Location: ../tickets_abiertos.php?msg=error_edit");
        exit();
    }
} else {
    header("Location: ../tickets_abiertos.php");
    exit();
}
