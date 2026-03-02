<?php
session_start();
// Damos un paso atrás para entrar a config/
require_once '../config/conexion.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = $_POST['codigo_credencial'];
    $pass = $_POST['password'];

    // Buscamos al usuario en la base de datos
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE codigo_credencial = ?");
    $stmt->execute([$codigo]);
    $usuario = $stmt->fetch();

    if ($usuario && $pass === $usuario['password']) {
        $_SESSION['user_id'] = $usuario['id'];
        $_SESSION['user_nombre'] = $usuario['nombre'];
        $_SESSION['user_rol'] = $usuario['rol'];

        // Redirigimos un nivel afuera hacia el dashboard
        header("Location: ../dashboard.php");
        exit();
    } else {
        // Redirigimos afuera con error
        header("Location: ../index.php?error=1");
        exit();
    }
}
?>