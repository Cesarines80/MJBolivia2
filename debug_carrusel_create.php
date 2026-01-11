<?php

/**
 * Script de diagnóstico para creación de carrusel
 */

// Iniciar sesión
session_start();
$_SESSION['admin_id'] = 1;
$_SESSION['admin_nombre'] = 'Administrador Principal';
$_SESSION['admin_email'] = 'admin@institucion.com';
$_SESSION['admin_rol'] = 'superadmin';
$_SESSION['is_admin'] = true;

// Incluir configuración
require_once 'config/config.php';

echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>Debug Carrusel</title>";
echo "<style>body{font-family:Arial;margin:20px;}.success{color:green;}.error{color:red;}.info{color:blue;}.section{border:1px solid #ccc;padding:15px;margin:10px 0;border-radius:5px;}pre{background:#f4f4f4;padding:10px;border-radius:3px;}</style></head><body>";

echo "<h1>🔍 Diagnóstico de Creación de Carrusel</h1><hr>";

// Test 1: Verificar que la clase Carrusel existe
echo "<div class='section'><h2>1. Verificar Clase Carrusel</h2>";
if (class_exists('Carrusel')) {
    echo "<p class='success'>✅ Clase Carrusel existe</p>";

    // Verificar método create
    if (method_exists('Carrusel', 'create')) {
        echo "<p class='success'>✅ Método Carrusel::create() existe</p>";
    } else {
        echo "<p class='error'>❌ Método Carrusel::create() NO existe</p>";
    }
} else {
    echo "<p class='error'>❌ Clase Carrusel NO existe</p>";
}
echo "</div>";

// Test 2: Intentar crear un elemento SIN imagen
echo "<div class='section'><h2>2. Crear Elemento SIN Imagen</h2>";
try {
    $data = [
        'titulo' => 'Test Debug ' . time(),
        'descripcion' => 'Descripción de prueba',
        'tipo' => 'imagen',
        'url' => '',
        'orden' => 0,
        'estado' => 'activo',
        'imagen' => '' // Sin imagen
    ];

    echo "<p class='info'>Datos a insertar:</p>";
    echo "<pre>" . print_r($data, true) . "</pre>";

    $result = Carrusel::create($data);

    if ($result) {
        echo "<p class='success'>✅ Elemento creado exitosamente</p>";

        // Verificar en la base de datos
        $items = Carrusel::getAll(false);
        echo "<p>Total de elementos en carrusel: <strong>" . count($items) . "</strong></p>";

        // Mostrar el último elemento
        if (count($items) > 0) {
            $ultimo = end($items);
            echo "<p>Último elemento creado:</p>";
            echo "<pre>" . print_r($ultimo, true) . "</pre>";
        }
    } else {
        echo "<p class='error'>❌ Carrusel::create() retornó FALSE</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Excepción: " . $e->getMessage() . "</p>";
    echo "<p>Archivo: " . $e->getFile() . "</p>";
    echo "<p>Línea: " . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
echo "</div>";

// Test 3: Verificar estructura de la tabla carrusel
echo "<div class='section'><h2>3. Estructura de Tabla Carrusel</h2>";
try {
    $db = getDB();
    $stmt = $db->query("DESCRIBE carrusel");
    $columns = $stmt->fetchAll();

    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Default</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>" . $col['Field'] . "</td>";
        echo "<td>" . $col['Type'] . "</td>";
        echo "<td>" . $col['Null'] . "</td>";
        echo "<td>" . ($col['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} catch (Exception $e) {
    echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Test 4: Simular el proceso completo del formulario
echo "<div class='section'><h2>4. Simular Proceso del Formulario</h2>";
try {
    // Simular POST
    $_POST = [
        'action' => 'add',
        'csrf_token' => generateCSRFToken(),
        'titulo' => 'Test Formulario ' . time(),
        'descripcion' => 'Descripción desde formulario',
        'tipo' => 'imagen',
        'url' => '',
        'orden' => 0,
        'estado' => 'activo'
    ];

    // Simular que no hay archivo
    $_FILES = [];

    echo "<p class='info'>Simulando POST del formulario...</p>";
    echo "<pre>" . print_r($_POST, true) . "</pre>";

    // Validar CSRF
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        echo "<p class='error'>❌ Token CSRF inválido</p>";
    } else {
        echo "<p class='success'>✅ Token CSRF válido</p>";
    }

    // Preparar datos como lo hace carrusel.php
    $data = [
        'titulo' => cleanInput($_POST['titulo']),
        'descripcion' => cleanInput($_POST['descripcion'] ?? ''),
        'tipo' => cleanInput($_POST['tipo'] ?? 'imagen'),
        'url' => cleanInput($_POST['url'] ?? ''),
        'orden' => (int)($_POST['orden'] ?? 0),
        'estado' => cleanInput($_POST['estado'] ?? 'activo')
    ];

    // Verificar si hay imagen
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        echo "<p class='info'>Imagen detectada</p>";
    } else {
        echo "<p class='info'>Sin imagen - agregando cadena vacía</p>";
        $data['imagen'] = '';
    }

    echo "<p class='info'>Datos finales:</p>";
    echo "<pre>" . print_r($data, true) . "</pre>";

    // Intentar crear
    if (Carrusel::create($data)) {
        echo "<p class='success'>✅ Elemento creado desde simulación de formulario</p>";
    } else {
        echo "<p class='error'>❌ Error al crear desde simulación de formulario</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Excepción: " . $e->getMessage() . "</p>";
    echo "<p>Archivo: " . $e->getFile() . "</p>";
    echo "<p>Línea: " . $e->getLine() . "</p>";
}
echo "</div>";

// Test 5: Verificar logs de error
echo "<div class='section'><h2>5. Últimos Errores en Log</h2>";
$logFile = 'logs/error.log';
if (file_exists($logFile)) {
    $logContent = file_get_contents($logFile);
    $lines = explode("\n", $logContent);
    $lastLines = array_slice($lines, -20); // Últimas 20 líneas

    if (!empty(trim(implode('', $lastLines)))) {
        echo "<pre>" . htmlspecialchars(implode("\n", $lastLines)) . "</pre>";
    } else {
        echo "<p class='success'>✅ No hay errores recientes en el log</p>";
    }
} else {
    echo "<p class='info'>Archivo de log no existe</p>";
}
echo "</div>";

echo "<hr>";
echo "<div class='section' style='background:#fffacd;'>";
echo "<h2>📋 Instrucciones</h2>";
echo "<p>1. Ejecuta este script: <code>php debug_carrusel_create.php</code></p>";
echo "<p>2. Luego intenta crear un elemento desde el navegador en: <a href='admin/carrusel.php'>admin/carrusel.php</a></p>";
echo "<p>3. Si falla, revisa los logs de error arriba</p>";
echo "<p>4. Comparte el mensaje de error específico que ves en el navegador</p>";
echo "</div>";

echo "</body></html>";
