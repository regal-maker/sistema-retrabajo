<?php
session_start();
require_once '../config/conexion.php'; 

// Verificamos que vengan los datos necesarios por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_ticket'], $_POST['conclusion'])) {
    
    $id_ticket = $_POST['id_ticket'];
    $conclusion = $_POST['conclusion'];
    $user_id = $_SESSION['user_id'] ?? null;

    try {
        // Iniciamos una transacción para asegurar que los datos se guarden bien
        $pdo->beginTransaction();

        // Preparamos la consulta. 
        // IMPORTANTE: Asegúrate que los nombres de las columnas coincidan con tu tabla
        $sql = "UPDATE tickets SET 
                estado = 'Cerrado', 
                conclusion_cierre = :conclusion, 
                fecha_cierre = NOW() 
                WHERE id = :id";
        
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            ':conclusion' => $conclusion,
            ':id'         => $id_ticket
        ]);

        if ($result && $stmt->rowCount() > 0) {
            $pdo->commit();
            // Redirigir con éxito
            header("Location: ../tickets_abiertos.php?msg=success_cierre");
        } else {
            // Si no se actualizó ninguna fila, algo anda mal con el ID
            $pdo->rollBack();
            header("Location: ../tickets_abiertos.php?msg=error_id_no_encontrado");
        }
        exit();

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        // Esto te ayudará a ver el error exacto si falla la base de datos
        die("Error crítico en la base de datos: " . $e->getMessage());
    }
} else {
    // Si alguien intenta entrar sin enviar el formulario
    header("Location: ../tickets_abiertos.php?msg=acceso_denegado");
    exit();
}
