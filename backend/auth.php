<?php
session_start();
require_once '../config/conexion.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Limpiamos espacios en blanco accidentales del escáner
    $codigo = trim($_POST['codigo_credencial']);
    $pass = trim($_POST['password']);

    // Sentencia preparada para evitar SQL Injection
    $stmt = $pdo->prepare("SELECT id, nombre, rol, password FROM usuarios WHERE codigo_credencial = ? LIMIT 1");
    $stmt->execute([$codigo]);
    $usuario = $stmt->fetch();

    // Verificación segura
    // Si usas texto plano: if ($usuario && $pass === $usuario['password'])
    // Si usas hash (Recomendado): if ($usuario && password_verify($pass, $usuario['password']))
    if ($usuario && $pass === $usuario['password']) {
        // Regenerar ID de sesión para evitar fijación de sesiones
        session_regenerate_id(true);
        
        $_SESSION['user_id'] = $usuario['id'];
        $_SESSION['user_nombre'] = $usuario['nombre'];
        $_SESSION['user_rol'] = $usuario['rol'];

        header("Location: ../dashboard.php");
        exit();
    } else {
        // Error genérico para no dar pistas a atacantes
        header("Location: ../index.php?error=1");
        exit();
    }
}
