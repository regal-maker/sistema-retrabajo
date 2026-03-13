<?php
session_start();
require_once '../config/conexion.php'; 

// Verificamos que lleguen los datos con los nombres exactos del preConfirm de Swal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_ticket'])) {
    
    $id = $_POST['id_ticket'];
    $motor = trim($_POST['motor']);
    $cantidad = $_POST['cantidad'];
    $tipo = $_POST['tipo'];
    $severidad = $_POST['severidad'];
    $id_defecto = $_POST['id_defecto'];

    try {
        $sql = "UPDATE tickets SET 
                id_motor = :motor, 
                cantidad_motores = :cantidad,
                tipo_motor_captura = :tipo,
                severidad = :severidad,
                id_defecto = :id_defecto
                WHERE id = :id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':motor'      => $motor,
            ':cantidad'   => $cantidad,
            ':tipo'       => $tipo,
            ':severidad'  => $severidad,
            ':id_defecto' => $id_defecto,
            ':id'         => $id
        ]);

        header("Location: ../tickets_abiertos.php?msg=success_edit");
        exit();

    } catch (PDOException $e) {
        die("Error al actualizar: " . $e->getMessage());
    }
} else {
    header("Location: ../tickets_abiertos.php");
    exit();
}
