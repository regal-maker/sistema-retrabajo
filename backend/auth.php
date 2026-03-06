<?php
require_once '../config/conexion.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = $_POST['codigo_credencial'];
    $pass = $_POST['password'];

    // Buscamos al usuario en la base de datos
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE codigo_credencial = ?");
    $stmt->execute([$codigo]);
    $usuario = $stmt->fetch();

    if ($usuario && $pass === $usuario['password']) {
        // Asignamos las variables de sesión usando la configuración de conexion.php
        $_SESSION['user_id'] = $usuario['id'];
        $_SESSION['user_nombre'] = $usuario['nombre'];
        $_SESSION['user_rol'] = $usuario['rol'];

        // Redirigimos al dashboard
        header("Location: ../dashboard.php");
        exit();
    } else {
        // Error de credenciales
        header("Location: ../index.php?error=1");
        exit();
    }
}
?>

