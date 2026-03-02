<?php
include('../config/conexion.php');
session_start();

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $id_usuario_cierre = $_SESSION['user_id'];
    
    try {
        $sql = "UPDATE tickets SET 
                estado = 'Cerrado', 
                id_usuario_cierre = :usuario, 
                fecha_cierre = NOW() 
                WHERE id = :id";
        
        $stmt = $pdo->prepare($sql);
        $resultado = $stmt->execute([
            'usuario' => $id_usuario_cierre,
            'id' => $id
        ]);

        if ($resultado) {
            header("Location: ../tickets_abiertos.php?success=cerrado");
            exit();
        } else {
            echo "Error: No se pudo actualizar el registro.";
        }
    } catch (PDOException $e) {
        die("Error en la base de datos: " . $e->getMessage());
    }
} else {
    header("Location: ../tickets_abiertos.php");
    exit();
}
?>