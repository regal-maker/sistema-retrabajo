<?php
// 1. Configuración del volumen persistente en Railway
$sessionPath = '/tmp/sessions';
if (!is_dir($sessionPath)) {
    mkdir($sessionPath, 0777, true);
}

// Forzar a PHP a usar el almacenamiento por archivos en la ruta del volumen
ini_set('session.save_handler', 'files');
session_save_path($sessionPath);

// 2. Configuración de Cookies para Producción (Railway usa HTTPS)
ini_set('session.cookie_lifetime', 2592000); // 30 días
ini_set('session.gc_maxlifetime', 2592000);

session_set_cookie_params([
    'lifetime' => 2592000,
    'path' => '/',
    'domain' => '', // Se ajusta solo al dominio de tu app
    'secure' => true, // IMPORTANTE: Railway requiere esto por ser HTTPS
    'httponly' => true,
    'samesite' => 'Lax'
]);

// 3. Iniciar sesión si no existe
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 4. Conexión PDO a la Base de Datos
$host = "hopper.proxy.rlwy.net";
$port = "10349";
$db   = "railway"; 
$user = "root";
$pass = "ZbMHUjaLgKDirZAZjlpClYqaqoiYKLIt"; 
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";

try {
     $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
     ]);
     $pdo->exec("SET time_zone = '-06:00';"); 
} catch (\PDOException $e) {
     die("Error de conexión: " . $e->getMessage());
}

// Asegurar el guardado físico de la sesión al terminar el script
register_shutdown_function('session_write_close');
