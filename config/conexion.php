<?php
// Configuración de la base de datos en la nube (Railway)
$host = "hopper.proxy.rlwy.net";
$port = "10349";
$db   = "railway"; // Nombre por defecto en Railway
$user = "root";
$pass = "ZbMHUjaLgKDirZAZjlpClYqaqoiYKLIt"; // <-- ¡Pega tu contraseña aquí!
$charset = 'utf8mb4';

// Se agregó el puerto ($port) a la conexión
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