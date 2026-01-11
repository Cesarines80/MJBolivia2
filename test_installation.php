<?php

/**
 * Script de verificación de instalación
 */

echo "<h1>Verificación de Instalación - MJBolivia2</h1>";
echo "<hr>";

// Test 1: Verificar PHP
echo "<h2>1. Verificación de PHP</h2>";
echo "Versión de PHP: " . phpversion() . "<br>";
if (version_compare(phpversion(), '8.0.0', '>=')) {
    echo "✅ PHP 8.0+ detectado<br>";
} else {
    echo "❌ Se requiere PHP 8.0 o superior<br>";
}

// Test 2: Verificar extensiones
echo "<h2>2. Verificación de Extensiones PHP</h2>";
$extensions = ['pdo', 'pdo_mysql', 'gd', 'fileinfo', 'session'];
foreach ($extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "✅ Extensión $ext: Instalada<br>";
    } else {
        echo "❌ Extensión $ext: NO instalada<br>";
    }
}

// Test 3: Verificar directorios
echo "<h2>3. Verificación de Directorios</h2>";
$directories = [
    'assets/uploads' => 'Directorio de uploads',
    'logs' => 'Directorio de logs'
];

foreach ($directories as $dir => $desc) {
    if (is_dir($dir)) {
        echo "✅ $desc ($dir): Existe<br>";
        if (is_writable($dir)) {
            echo "✅ $desc: Tiene permisos de escritura<br>";
        } else {
            echo "⚠️ $desc: NO tiene permisos de escritura<br>";
        }
    } else {
        echo "❌ $desc ($dir): NO existe<br>";
    }
}

// Test 4: Verificar conexión a base de datos
echo "<h2>4. Verificación de Conexión a Base de Datos</h2>";
try {
    require_once 'config/config.php';
    $db = getDB();
    echo "✅ Conexión a base de datos: Exitosa<br>";

    // Verificar tablas
    echo "<h3>Tablas en la base de datos:</h3>";
    $stmt = $db->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>$table</li>";
    }
    echo "</ul>";

    // Verificar usuario administrador
    echo "<h3>Usuario Administrador:</h3>";
    $stmt = $db->query("SELECT id, nombre, email, rol, estado FROM administradores WHERE id = 1");
    $admin = $stmt->fetch();
    if ($admin) {
        echo "✅ Usuario administrador encontrado:<br>";
        echo "- ID: " . $admin['id'] . "<br>";
        echo "- Nombre: " . $admin['nombre'] . "<br>";
        echo "- Email: " . $admin['email'] . "<br>";
        echo "- Rol: " . $admin['rol'] . "<br>";
        echo "- Estado: " . $admin['estado'] . "<br>";
    } else {
        echo "❌ Usuario administrador NO encontrado<br>";
    }

    // Verificar configuración
    echo "<h3>Configuración del Sitio:</h3>";
    $stmt = $db->query("SELECT * FROM configuracion WHERE id = 1");
    $config = $stmt->fetch();
    if ($config) {
        echo "✅ Configuración encontrada:<br>";
        echo "- Nombre: " . $config['nombre_institucion'] . "<br>";
        echo "- Email: " . $config['email_contacto'] . "<br>";
        echo "- Teléfono: " . $config['telefono'] . "<br>";
    } else {
        echo "❌ Configuración NO encontrada<br>";
    }

    // Verificar configuración de inscripciones
    echo "<h3>Configuración de Inscripciones:</h3>";
    $stmt = $db->query("SELECT * FROM configuracion_inscripciones WHERE id = 1");
    $configInsc = $stmt->fetch();
    if ($configInsc) {
        echo "✅ Configuración de inscripciones encontrada:<br>";
        echo "- Monto inscripción: $" . $configInsc['monto_inscripcion'] . "<br>";
        echo "- Monto alojamiento: $" . $configInsc['monto_alojamiento'] . "<br>";
        echo "- Estado: " . $configInsc['estado'] . "<br>";
    } else {
        echo "❌ Configuración de inscripciones NO encontrada<br>";
    }
} catch (Exception $e) {
    echo "❌ Error de conexión: " . $e->getMessage() . "<br>";
}

// Test 5: Verificar archivos importantes
echo "<h2>5. Verificación de Archivos Importantes</h2>";
$files = [
    'index.php' => 'Página principal',
    'config/config.php' => 'Configuración',
    'includes/functions.php' => 'Funciones',
    'includes/auth.php' => 'Autenticación',
    'admin/login.php' => 'Login administrativo',
    'admin/dashboard.php' => 'Dashboard',
    'inscripciones/index.php' => 'Sistema de inscripciones'
];

foreach ($files as $file => $desc) {
    if (file_exists($file)) {
        echo "✅ $desc ($file): Existe<br>";
    } else {
        echo "❌ $desc ($file): NO existe<br>";
    }
}

// Resumen
echo "<hr>";
echo "<h2>Resumen de la Instalación</h2>";
echo "<p><strong>Credenciales de acceso:</strong></p>";
echo "<ul>";
echo "<li>URL del sitio: <a href='" . SITE_URL . "' target='_blank'>" . SITE_URL . "</a></li>";
echo "<li>URL admin: <a href='" . ADMIN_URL . "login.php' target='_blank'>" . ADMIN_URL . "login.php</a></li>";
echo "<li>Email: admin@institucion.com</li>";
echo "<li>Contraseña: admin123</li>";
echo "</ul>";

echo "<p><strong>⚠️ IMPORTANTE:</strong> Cambia la contraseña del administrador después del primer inicio de sesión.</p>";

echo "<hr>";
echo "<p>Instalación completada. Puedes eliminar este archivo (test_installation.php) por seguridad.</p>";
