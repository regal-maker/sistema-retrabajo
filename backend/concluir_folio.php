<?php
session_start();
require_once '../config/conexion.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    // Recibimos el ID y la conclusión del formulario dinámico
    $id_ticket = $_POST['id_ticket'];
    $conclusion = $_POST['conclusion'];

    try {
        // Actualizamos usando el ID para mayor precisión
        $stmt = $pdo->prepare("UPDATE tickets SET 
                                estado = 'Cerrado', 
                                comentarios_cierre = ?, 
                                fecha_cierre = NOW() 
                                WHERE id = ?");
        
        $stmt->execute([$conclusion, $id_ticket]);

        // Redirigir con mensaje de éxito
        header("Location: ../tickets_abiertos.php?msg=success_cierre");
        exit();

    } catch (PDOException $e) {
        // En producción, es mejor loguear el error y mostrar un mensaje genérico
        die("Error al concluir el folio: " . $e->getMessage());
    }
} else {
    // Si intentan entrar directo al archivo sin POST
    header("Location: ../tickets_abiertos.php");
    exit();
}
?>
