<?php
include('../config/conexion.php');
session_start();

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $id_user = $_SESSION['user_id'];
    
    $sql = "UPDATE tickets SET estado = 'Cancelado', id_usuario_cierre = ?, fecha_cierre = NOW() WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$id_user, $id])) {
        header("Location: ../tickets_abiertos.php?msg=cancelado");
    } else {
        echo "Error al cancelar.";
    }
}
?>