<?php
// IMPORTANTE: NO poner session_start() aquí.
require_once '../config/conexion.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = $_POST['codigo_credencial'] ?? '';
    $pass = $_POST['password'] ?? '';

    if (empty($codigo) || empty($pass)) {
        header("Location: ../index.php?error=vacio");
        exit();
    }

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE codigo_credencial = ?");
    $stmt->execute([$codigo]);
    $usuario = $stmt->fetch();

    // Verificación de contraseña (ajusta si usas password_hash en el futuro)
    if ($usuario && $pass === $usuario['password']) {
        // Guardamos los datos en la sesión configurada por conexion.php
        $_SESSION['user_id'] = $usuario['id'];
        $_SESSION['user_nombre'] = $usuario['nombre'];
        $_SESSION['user_rol'] = $usuario['rol'];

        header("Location: ../dashboard.php");
        exit();
    } else {
        header("Location: ../index.php?error=1");
        exit();
    }
}
