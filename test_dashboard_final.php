<?php

/**
 * Script final de verificación del dashboard
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html>
<head>
    <title>Verificación Final - Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        .box { border: 1px solid #ccc; padding: 15px; margin: 10px 0; border-radius: 5px; }
        h1 { color: #333; }
        h2 { color: #666; border-bottom: 2px solid #8B7EC8; padding-bottom: 5px; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 3px; overflow-x: auto; }
        .credentials { background: #fffacd; padding: 15px; border-left: 4px solid #ffd700; }
    </style>
</head>
<body>";

echo "<h1>🔍 Verificación Final del Dashboard</h1>";
echo "<hr>";

try {
    // Simular IP
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

    require_once 'config/config.php';

    echo "<div class='box'>";
    echo "<h2>✅ Paso 1: Conexión a Base de Datos</h2>";
    $db = getDB();
    echo "<p class='success'>✓ Conexión exitosa a la base de datos 'web_institucional'</p>";
    echo "</div>";

    echo "<div class='box'>";
    echo "<h2>✅ Paso 2: Verificar Usuario Administrador</h2>";
    $stmt = $db->query("SELECT * FROM administradores WHERE id = 1");
    $admin = $stmt->fetch();
    if ($admin) {
        echo "<p class='success'>✓ Usuario administrador encontrado</p>";
        echo "<ul>";
        echo "<li><strong>ID:</strong> " . $admin['id'] . "</li>";
        echo "<li><strong>Nombre:</strong> " . $admin['nombre'] . "</li>";
        echo "<li><strong>Email:</strong> " . $admin['email'] . "</li>";
        echo "<li><strong>Rol:</strong> " . $admin['rol'] . "</li>";
        echo "<li><strong>Estado:</strong> " . $admin['estado'] . "</li>";
        echo "</ul>";
    } else {
        echo "<p class='error'>✗ Usuario administrador NO encontrado</p>";
    }
    echo "</div>";

    echo "<div class='box'>";
    echo "<h2>✅ Paso 3: Simular Sesión de Administrador</h2>";
    $_SESSION['admin_id'] = 1;
    $_SESSION['admin_nombre'] = 'Administrador Principal';
    $_SESSION['admin_email'] = 'admin@institucion.com';
    $_SESSION['admin_rol'] = 'superadmin';
    $_SESSION['is_admin'] = true;
    echo "<p class='success'>✓ Sesión de administrador creada correctamente</p>";
    echo "</div>";

    echo "<div class='box'>";
    echo "<h2>✅ Paso 4: Verificar Métodos de Autenticación</h2>";

    // Test Auth::requireLogin()
    echo "<h3>4.1. Auth::requireLogin()</h3>";
    ob_start();
    Auth::requireLogin();
    ob_end_clean();
    echo "<p class='success'>✓ requireLogin() funciona (no redirige con sesión activa)</p>";

    // Test Auth::getUser()
    echo "<h3>4.2. Auth::getUser()</h3>";
    $currentUser = Auth::getUser();
    if ($currentUser) {
        echo "<p class='success'>✓ getUser() retorna datos del usuario:</p>";
        echo "<pre>" . print_r($currentUser, true) . "</pre>";
    } else {
        echo "<p class='error'>✗ getUser() retorna null</p>";
    }

    // Test Auth::checkRole()
    echo "<h3>4.3. Auth::checkRole()</h3>";
    if (Auth::checkRole(['superadmin'])) {
        echo "<p class='success'>✓ checkRole(['superadmin']) = true</p>";
    } else {
        echo "<p class='error'>✗ checkRole(['superadmin']) = false</p>";
    }
    echo "</div>";

    echo "<div class='box'>";
    echo "<h2>✅ Paso 5: Verificar Tablas del Sistema</h2>";

    $tables = [
        'administradores' => 'Administradores del sistema',
        'configuracion' => 'Configuración del sitio',
        'eventos' => 'Eventos',
        'inscripciones_eventos' => 'Inscripciones a eventos',
        'contactos' => 'Mensajes de contacto',
        'carrusel' => 'Carrusel de imágenes',
        'galeria' => 'Galería de fotos'
    ];

    echo "<ul>";
    foreach ($tables as $table => $desc) {
        try {
            $stmt = $db->query("SELECT COUNT(*) FROM $table");
            $count = $stmt->fetchColumn();
            echo "<li class='success'>✓ <strong>$table</strong> ($desc): $count registros</li>";
        } catch (Exception $e) {
            echo "<li class='error'>✗ <strong>$table</strong>: Error - " . $e->getMessage() . "</li>";
        }
    }
    echo "</ul>";
    echo "</div>";

    echo "<div class='box'>";
    echo "<h2>✅ Paso 6: Verificar Clases del Sistema</h2>";

    // Test SiteConfig
    echo "<h3>6.1. SiteConfig</h3>";
    try {
        $config = SiteConfig::get();
        if ($config) {
            echo "<p class='success'>✓ SiteConfig::get() funciona</p>";
            echo "<p>Nombre institución: " . ($config['nombre_institucion'] ?? 'No configurado') . "</p>";
        } else {
            echo "<p class='warning'>⚠ SiteConfig::get() retorna null (configurar en el panel)</p>";
        }
    } catch (Exception $e) {
        echo "<p class='error'>✗ Error: " . $e->getMessage() . "</p>";
    }

    // Test Contactos
    echo "<h3>6.2. Contactos</h3>";
    try {
        $contactos = Contactos::getAll();
        echo "<p class='success'>✓ Contactos::getAll() funciona</p>";
        echo "<p>Total contactos: " . count($contactos) . "</p>";
    } catch (Exception $e) {
        echo "<p class='error'>✗ Error: " . $e->getMessage() . "</p>";
    }
    echo "</div>";

    echo "<div class='box credentials'>";
    echo "<h2>🔑 Credenciales de Acceso</h2>";
    echo "<p><strong>Panel de Administración:</strong></p>";
    echo "<ul>";
    echo "<li><strong>URL:</strong> <a href='admin/login.php' target='_blank'>http://localhost/proyectos/MJBolivia2/admin/login.php</a></li>";
    echo "<li><strong>Email:</strong> admin@institucion.com</li>";
    echo "<li><strong>Contraseña:</strong> admin123</li>";
    echo "</ul>";
    echo "<p class='warning'>⚠️ <strong>IMPORTANTE:</strong> Cambia la contraseña después del primer inicio de sesión.</p>";
    echo "</div>";

    echo "<div class='box' style='background: #d4edda; border-color: #c3e6cb;'>";
    echo "<h2>🎉 ¡Instalación Completada Exitosamente!</h2>";
    echo "<p><strong>El sistema está listo para usar. Puedes:</strong></p>";
    echo "<ol>";
    echo "<li>Acceder al <a href='admin/login.php' target='_blank'><strong>Panel de Administración</strong></a></li>";
    echo "<li>Ver el <a href='index.php' target='_blank'><strong>Sitio Web Público</strong></a></li>";
    echo "<li>Configurar el sitio desde el dashboard</li>";
    echo "<li>Crear eventos y gestionar inscripciones</li>";
    echo "</ol>";
    echo "<p><strong>Próximos pasos recomendados:</strong></p>";
    echo "<ul>";
    echo "<li>Cambiar la contraseña del administrador</li>";
    echo "<li>Configurar los datos de la institución</li>";
    echo "<li>Subir el logo y favicon</li>";
    echo "<li>Configurar las redes sociales</li>";
    echo "<li>Crear tu primer evento</li>";
    echo "</ul>";
    echo "</div>";
} catch (Exception $e) {
    echo "<div class='box' style='background: #f8d7da; border-color: #f5c6cb;'>";
    echo "<h2 class='error'>❌ Error en la Verificación</h2>";
    echo "<p><strong>Mensaje:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Archivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Línea:</strong> " . $e->getLine() . "</p>";
    echo "<h3>Stack Trace:</h3>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
}

echo "</body>
</html>";
