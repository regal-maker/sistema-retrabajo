<?php
session_start();
require_once '../config/conexion.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Limpiamos los datos de entrada para evitar espacios accidentales del escáner
    $codigo = isset($_POST['codigo_credencial']) ? trim($_POST['codigo_credencial']) : '';
    $pass = isset($_POST['password']) ? trim($_POST['password']) : '';

    if (empty($codigo) || empty($pass)) {
        header("Location: ../index.php?error=1");
        exit();
    }

    try {
        // Uso de Sentencias Preparadas (Placeholder "?") para seguridad total
        $stmt = $pdo->prepare("SELECT id, nombre, rol, password FROM usuarios WHERE codigo_credencial = ? LIMIT 1");
        $stmt->execute([$codigo]);
        $usuario = $stmt->fetch();

        // Verificación de credenciales
        // NOTA: Si usas hashes (password_hash), cambia el IF por: if ($usuario && password_verify($pass, $usuario['password']))
        if ($usuario && $pass === $usuario['password']) {
            
            // Seguridad: Regenerar ID de sesión para prevenir "Session Fixation"
            session_regenerate_id(true);

            $_SESSION['user_id'] = $usuario['id'];
            $_SESSION['user_nombre'] = $usuario['nombre'];
            $_SESSION['user_rol'] = $usuario['rol'];

            // Redirigir al dashboard
            header("Location: ../dashboard.php");
            exit();
        } else {
            // Error: Datos no coinciden
            header("Location: ../index.php?error=1");
            exit();
        }

    } catch (PDOException $e) {
        // Error de base de datos
        error_log("Error en Login: " . $e->getMessage());
        header("Location: ../index.php?error=server");
        exit();
    }
} else {
    // Si intentan entrar por GET a este archivo, los mandamos fuera
    header("Location: ../index.php");
    exit();
}
