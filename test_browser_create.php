<?php

/**
 * Script para simular la creación de elementos desde el navegador
 */

// Simular sesión de administrador
session_start();
$_SESSION['admin_id'] = 1;
$_SESSION['admin_nombre'] = 'Administrador Principal';
$_SESSION['admin_email'] = 'admin@institucion.com';
$_SESSION['admin_rol'] = 'superadmin';
$_SESSION['is_admin'] = true;

// Incluir configuración
require_once 'config/config.php';

echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>Prueba Creación Navegador</title>";
echo "<style>body{font-family:Arial;margin:20px;}.success{color:green;}.error{color:red;}.section{border:1px solid #ccc;padding:15px;margin:10px 0;border-radius:5px;}h2{color:#8B7EC8;}</style></head><body>";

echo "<h1>🧪 Prueba de Creación desde Navegador</h1><hr>";

// Test 1: Verificar sesión
echo "<div class='section'><h2>1. Verificar Sesión</h2>";
if (isset($_SESSION['admin_id'])) {
    echo "<p class='success'>✅ Sesión activa</p>";
    echo "<ul>";
    echo "<li>ID: " . $_SESSION['admin_id'] . "</li>";
    echo "<li>Nombre: " . $_SESSION['admin_nombre'] . "</li>";
    echo "<li>Email: " . $_SESSION['admin_email'] . "</li>";
    echo "<li>Rol: " . $_SESSION['admin_rol'] . "</li>";
    echo "</ul>";
} else {
    echo "<p class='error'>❌ No hay sesión activa</p>";
}
echo "</div>";

// Test 2: Simular POST de Carrusel
echo "<div class='section'><h2>2. Simular Creación de Carrusel (sin imagen)</h2>";
try {
    // Simular datos POST
    $_POST = [
        'action' => 'add',
        'csrf_token' => generateCSRFToken(),
        'titulo' => 'Elemento de Prueba Browser',
        'descripcion' => 'Descripción de prueba',
        'tipo' => 'imagen',
        'url' => '',
        'orden' => 0,
        'estado' => 'activo'
    ];

    // Simular que no hay archivo
    $_FILES = [];

    $data = [
        'titulo' => $_POST['titulo'],
        'descripcion' => $_POST['descripcion'] ?? '',
        'tipo' => $_POST['tipo'] ?? 'imagen',
        'url' => $_POST['url'] ?? '',
        'orden' => (int)($_POST['orden'] ?? 0),
        'estado' => $_POST['estado'] ?? 'activo',
        'imagen' => '' // Sin imagen
    ];

    if (Carrusel::create($data)) {
        echo "<p class='success'>✅ Elemento de carrusel creado correctamente</p>";
        echo "<p>Datos enviados:</p><pre>" . print_r($data, true) . "</pre>";

        // Verificar que se creó
        $items = Carrusel::getAll(false);
        echo "<p>Total de elementos en carrusel: <strong>" . count($items) . "</strong></p>";
    } else {
        echo "<p class='error'>❌ Error al crear elemento de carrusel</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Excepción: " . $e->getMessage() . "</p>";
    echo "<p>Archivo: " . $e->getFile() . "</p>";
    echo "<p>Línea: " . $e->getLine() . "</p>";
}
echo "</div>";

// Test 3: Simular POST de Galería
echo "<div class='section'><h2>3. Simular Creación de Galería (sin imagen)</h2>";
try {
    $_POST = [
        'action' => 'add',
        'csrf_token' => generateCSRFToken(),
        'titulo' => 'Imagen de Prueba Browser',
        'descripcion' => 'Descripción de prueba',
        'categoria' => 'prueba'
    ];

    $_FILES = [];

    $data = [
        'titulo' => $_POST['titulo'],
        'descripcion' => $_POST['descripcion'] ?? '',
        'categoria' => $_POST['categoria'] ?? 'general',
        'imagen' => '' // Sin imagen
    ];

    if (Galeria::create($data)) {
        echo "<p class='success'>✅ Imagen de galería creada correctamente</p>";
        echo "<p>Datos enviados:</p><pre>" . print_r($data, true) . "</pre>";

        // Verificar que se creó
        $items = Galeria::getAll();
        echo "<p>Total de imágenes en galería: <strong>" . count($items) . "</strong></p>";
    } else {
        echo "<p class='error'>❌ Error al crear imagen de galería</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Excepción: " . $e->getMessage() . "</p>";
    echo "<p>Archivo: " . $e->getFile() . "</p>";
    echo "<p>Línea: " . $e->getLine() . "</p>";
}
echo "</div>";

// Test 4: Verificar función logActivity
echo "<div class='section'><h2>4. Verificar función logActivity()</h2>";
try {
    logActivity('TEST_BROWSER', 'Prueba desde navegador');
    echo "<p class='success'>✅ Función logActivity() ejecutada sin errores</p>";

    // Verificar en la base de datos
    $db = getDB();
    $stmt = $db->query("SELECT COUNT(*) as total FROM log_actividades WHERE accion = 'TEST_BROWSER'");
    $result = $stmt->fetch();
    echo "<p>Registros de actividad con acción 'TEST_BROWSER': <strong>" . $result['total'] . "</strong></p>";
} catch (Exception $e) {
    echo "<p class='error'>❌ Error en logActivity(): " . $e->getMessage() . "</p>";
}
echo "</div>";

// Test 5: Verificar permisos de directorios
echo "<div class='section'><h2>5. Verificar Permisos de Directorios</h2>";
$dirs = [
    'assets/uploads' => 'Directorio de uploads',
    'logs' => 'Directorio de logs'
];

foreach ($dirs as $dir => $desc) {
    if (is_dir($dir)) {
        echo "<p class='success'>✅ $desc existe</p>";
        if (is_writable($dir)) {
            echo "<p class='success'>✅ $desc tiene permisos de escritura</p>";
        } else {
            echo "<p class='error'>❌ $desc NO tiene permisos de escritura</p>";
        }
    } else {
        echo "<p class='error'>❌ $desc NO existe</p>";
    }
}
echo "</div>";

echo "<hr>";
echo "<div class='section' style='background:#e8f5e9;'>";
echo "<h2>✅ Resumen</h2>";
echo "<p>Las pruebas simulan exactamente lo que sucede cuando creas elementos desde el navegador.</p>";
echo "<p><strong>Próximo paso:</strong> Intenta crear un elemento desde el navegador y compara los resultados.</p>";
echo "<p><strong>URL para probar:</strong></p>";
echo "<ul>";
echo "<li><a href='admin/carrusel.php' target='_blank'>admin/carrusel.php</a></li>";
echo "<li><a href='admin/galeria.php' target='_blank'>admin/galeria.php</a></li>";
echo "</ul>";
echo "</div>";

echo "</body></html>";
