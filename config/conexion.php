<?php
// 1. Configurar ruta de sesiones en el volumen persistente de Railway
$sessionPath = '/tmp/sessions';

// Crear el directorio si no existe (asegura permisos en el volumen)
if (!is_dir($sessionPath)) {
    mkdir($sessionPath, 0777, true);
}

// Forzar el manejador de archivos y la ruta del volumen
ini_set('session.save_handler', 'files');
session_save_path($sessionPath);

// 2. Configuración robusta de la Cookie (Indispensable para Railway con HTTPS)
ini_set('session.cookie_lifetime', 2592000); // 30 días
ini_set('session.gc_maxlifetime', 2592000);

session_set_cookie_params([
    'lifetime' => 2592000,
    'path' => '/',
    'domain' => '', // Se ajusta automáticamente al dominio de Railway
    'secure' => true, // Obligatorio para HTTPS
    'httponly' => true, // Protege contra XSS
    'samesite' => 'Lax'
]);

// Iniciar la sesión solo si no existe
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 3. Datos de conexión a la base de datos
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

// Asegurar que la sesión se escriba físicamente antes de cerrar el script
register_shutdown_function('session_write_close');
?>
