<?php
// 1. Configurar ruta de sesiones en el volumen persistente de Railway
$sessionPath = '/tmp/sessions';

// Crear el directorio si no existe (asegura permisos en el volumen)
if (!is_dir($sessionPath)) {
    mkdir($sessionPath, 0777, true);
}

// Establecer la ruta donde PHP guardará los archivos de sesión de forma persistente
session_save_path($sessionPath);

// 2. Configuración de parámetros de la cookie de sesión (30 días)
ini_set('session.cookie_lifetime', 2592000); 
ini_set('session.gc_maxlifetime', 2592000);
session_set_cookie_params(2592000);

// Iniciar la sesión antes de cualquier salida de texto
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 3. Datos de conexión a la base de datos (Railway)
$host = "hopper.proxy.rlwy.net";
$port = "10349";
$db   = "railway"; 
$user = "root";
$pass = "ZbMHUjaLgKDirZAZjlpClYqaqoiYKLIt"; 
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
     $pdo->exec("SET time_zone = '-06:00';"); 
} catch (\PDOException $e) {
     die("Error de conexión: " . $e->getMessage());
}
?>
