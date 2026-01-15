<?php

/**
 * Configuracion principal del sistema de Gestion de Eventos con Roles
 */

// Configuracion de la base de datos
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'web_institucional');

// Configuracion del sistema
define('SITE_NAME', 'Sistema de Gestion de Eventos');

// Detectar URL base dinámicamente
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
$dir = dirname($scriptName);
if ($dir == '/' || $dir == '\\' || $dir == '.') $dir = '';
define('SITE_URL', $protocol . '://' . $host . $dir . '/');

define('ADMIN_URL', SITE_URL . 'admin/');
define('INSCRIPCIONES_URL', SITE_URL . 'inscripciones/');
define('EVENTOS_URL', SITE_URL . 'eventos/');
define('UPLOADS_DIR', __DIR__ . '/../assets/uploads/');
define('UPLOADS_URL', '/assets/uploads/');

// Configuracion de roles y permisos
define('ROLES', [
    'super_admin' => 'Super Administrador',
    'admin' => 'Administrador',
    'usuario' => 'Usuario'
]);

// Configuracion de seguridad
define('SESSION_LIFETIME', 3600); // 1 hora en segundos
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_BLOCK_TIME', 900); // 15 minutos en segundos
define('PASSWORD_MIN_LENGTH', 8);
define('PASSWORD_HASH_ALGO', PASSWORD_BCRYPT);

// Configuracion de cookies
define('COOKIE_LIFETIME', 86400); // 1 dia en segundos
define('COOKIE_PATH', '/');
define('COOKIE_DOMAIN', '');
define('COOKIE_SECURE', false);
define('COOKIE_HTTPONLY', true);
define('COOKIE_SAMESITE', 'Lax');

// Configuracion de CSRF
define('CSRF_TOKEN_NAME', 'csrf_token');
define('CSRF_TOKEN_LENGTH', 32);

// Configuracion de errores
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');

// Incluir clases necesarias
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/eventos.php';
require_once __DIR__ . '/../includes/inscripciones.php';

// ============================================
// CONFIGURACIÓN DE ZONA HORARIA
// ============================================
// Configurar zona horaria para Bolivia
// Cambiar según tu ubicación si es necesario
// Zonas comunes: America/La_Paz (Bolivia), America/Lima (Perú), 
//                America/Bogota (Colombia), America/Argentina/Buenos_Aires (Argentina)
date_default_timezone_set('America/La_Paz');

// Iniciar sesion
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => COOKIE_LIFETIME,
        'path' => COOKIE_PATH,
        'domain' => COOKIE_DOMAIN,
        'secure' => COOKIE_SECURE,
        'httponly' => COOKIE_HTTPONLY,
        'samesite' => COOKIE_SAMESITE
    ]);
    session_start();
}

/**
 * Clase de conexion a base de datos
 */
class Database
{
    private static $instance = null;
    private $pdo;

    private function __construct()
    {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_general_ci"
            ];
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("Error de conexion a base de datos: " . $e->getMessage());
            throw new Exception("Error de conexion a la base de datos");
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->pdo;
    }
}

/**
 * Funcion para obtener la conexion a la base de datos
 */
function getDB()
{
    return Database::getInstance()->getConnection();
}

/**
 * Funcion para limpiar y validar entrada de datos
 */
function cleanInput($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Funcion para generar token CSRF
 */
function generateCSRFToken()
{
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(CSRF_TOKEN_LENGTH));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * Funcion para validar token CSRF
 */
function validateCSRFToken($token)
{
    if (empty($_SESSION[CSRF_TOKEN_NAME]) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

/**
 * Funcion para redirigir
 */
function redirect($url)
{
    header("Location: " . $url);
    exit;
}

/**
 * Funcion para mostrar mensajes de alerta
 */
function showAlert($message, $type = 'info')
{
    $types = ['info', 'success', 'warning', 'danger'];
    if (!in_array($type, $types)) {
        $type = 'info';
    }
    return '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">
                ' . htmlspecialchars($message) . '
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>';
}

/**
 * Funcion para formatear fechas
 */
function formatDate($date, $format = 'd/m/Y')
{
    if (empty($date)) return '';
    return date($format, strtotime($date));
}

/**
 * Funcion para limitar texto
 */
function limitText($text, $limit = 100)
{
    if (strlen($text) <= $limit) return $text;
    return substr($text, 0, $limit) . '...';
}

/**
 * Funcion para subir archivos
 */
function uploadFile($file, $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])
{
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Error al subir el archivo'];
    }

    if (!in_array($file['type'], $allowedTypes)) {
        return ['success' => false, 'error' => 'Tipo de archivo no permitido'];
    }

    // Limitar tamaño a 5MB
    if ($file['size'] > 5 * 1024 * 1024) {
        return ['success' => false, 'error' => 'El archivo excede el tamaño maximo permitido (5MB)'];
    }

    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $uploadPath = UPLOADS_DIR . $filename;

    if (!is_dir(UPLOADS_DIR)) {
        mkdir(UPLOADS_DIR, 0755, true);
    }

    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return ['success' => true, 'filename' => $filename, 'path' => $uploadPath];
    }

    return ['success' => false, 'error' => 'Error al mover el archivo'];
}

// Inicializar autenticacion
$db = getDB();
initializeAuth($db);
