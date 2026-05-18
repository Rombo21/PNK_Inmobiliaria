<?php
/**
 * PNK Inmobiliaria — Conexión a Base de Datos
 * Utiliza PDO con prepared statements para seguridad.
 * Lee credenciales desde variables de entorno o valores por defecto (WAMP/XAMPP local).
 */

// Cargar .env si existe (para producción en AWS)
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        list($key, $value) = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
        putenv(trim($key) . '=' . trim($value));
    }
}

// Configuración de la base de datos
$dbHost = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: 'localhost';
$dbPort = $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?: '3306';
$dbName = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'pnk_inmobiliaria';
$dbUser = $_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'root';
$dbPass = $_ENV['DB_PASS'] ?? getenv('DB_PASS') ?: 'Ejrs2003.';

// Charset
$dbCharset = 'utf8mb4';

// DSN
$dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset={$dbCharset}";

// Opciones de PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
];

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, $options);
} catch (PDOException $e) {
    // En producción: logear error y mostrar página de error genérica
    error_log('Error de conexión BD: ' . $e->getMessage());
    http_response_code(500);
    die('<h1>Error del servidor</h1><p>No se pudo conectar a la base de datos. Por favor, inténtelo más tarde.</p>');
}

// Configuración general del sitio
define('SITE_NAME', 'PNK Inmobiliaria');

// Autodetectar la URL correcta (AWS vs Local)
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$baseDir = ($host === 'localhost' || $host === '127.0.0.1') ? '/PNK_Inmobiliaria' : '';
define('SITE_URL', $_ENV['SITE_URL'] ?? getenv('SITE_URL') ?: $protocol . $host . $baseDir);

define('UPLOADS_DIR', __DIR__ . '/../uploads/');
define('UPLOADS_URL', SITE_URL . '/uploads/');

// Configuración Google Maps (API Key configurable)
define('GOOGLE_MAPS_API_KEY', $_ENV['GOOGLE_MAPS_KEY'] ?? getenv('GOOGLE_MAPS_KEY') ?: '');

// Iniciar sesión si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
