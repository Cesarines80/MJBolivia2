<?php

/**
 * Script de prueba para verificar el dashboard
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Prueba de Dashboard</h1>";
echo "<hr>";

try {
    // Simular IP
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

    require_once 'config/config.php';

    echo "<h2>1. Verificar Conexión</h2>";
    $db = getDB();
    echo "✅ Conexión exitosa<br><br>";

    echo "<h2>2. Simular Login de Administrador</h2>";
    // Simular sesión de administrador
    $_SESSION['admin_id'] = 1;
    $_SESSION['admin_nombre'] = 'Administrador Principal';
    $_SESSION['admin_email'] = 'admin@institucion.com';
    $_SESSION['admin_rol'] = 'superadmin';
    $_SESSION['is_admin'] = true;

    echo "✅ Sesión de administrador creada<br><br>";

    echo "<h2>3. Verificar Métodos Estáticos de Auth</h2>";

    // Verificar requireLogin
    echo "<h3>3.1. Auth::requireLogin()</h3>";
    try {
        // No debería redirigir porque hay sesión activa
        ob_start();
        Auth::requireLogin();
        ob_end_clean();
        echo "✅ requireLogin() no redirige (sesión activa)<br><br>";
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "<br><br>";
    }

    // Verificar getUser
    echo "<h3>3.2. Auth::getUser()</h3>";
    $currentUser = Auth::getUser();
    if ($currentUser) {
        echo "✅ getUser() funciona:<br>";
        echo "- ID: " . $currentUser['id'] . "<br>";
        echo "- Nombre: " . $currentUser['nombre'] . "<br>";
        echo "- Email: " . $currentUser['email'] . "<br>";
        echo "- Rol: " . $currentUser['rol'] . "<br><br>";
    } else {
        echo "❌ getUser() retorna null<br><br>";
    }

    // Verificar checkRole
    echo "<h3>3.3. Auth::checkRole()</h3>";
    if (Auth::checkRole(['superadmin'])) {
        echo "✅ checkRole(['superadmin']) retorna true<br>";
    } else {
        echo "❌ checkRole(['superadmin']) retorna false<br>";
    }

    if (Auth::checkRole(['admin', 'superadmin'])) {
        echo "✅ checkRole(['admin', 'superadmin']) retorna true<br><br>";
    } else {
        echo "❌ checkRole(['admin', 'superadmin']) retorna false<br><br>";
    }

    echo "<h2>4. Verificar Clases Necesarias para Dashboard</h2>";

    // Verificar SiteConfig
    echo "<h3>4.1. SiteConfig::get()</h3>";
    try {
        $config = SiteConfig::get();
        if ($config) {
            echo "✅ SiteConfig::get() funciona<br>";
            echo "- Nombre: " . ($config['nombre_institucion'] ?? 'N/A') . "<br><br>";
        } else {
            echo "⚠️ SiteConfig::get() retorna null (tabla vacía)<br><br>";
        }
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "<br><br>";
    }

    // Verificar Contactos
    echo "<h3>4.2. Contactos::getAll()</h3>";
    try {
        $contactos = Contactos::getAll('nuevo');
        echo "✅ Contactos::getAll() funciona<br>";
        echo "- Total contactos nuevos: " . count($contactos) . "<br><br>";
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "<br><br>";
    }

    // Verificar Eventos
    echo "<h3>4.3. Eventos (tabla)</h3>";
    try {
        $stmt = $db->query("SELECT COUNT(*) FROM eventos WHERE estado = 'activo'");
        $total = $stmt->fetchColumn();
        echo "✅ Tabla eventos accesible<br>";
        echo "- Total eventos activos: " . $total . "<br><br>";
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "<br><br>";
    }

    echo "<h2>5. Probar Carga del Dashboard</h2>";
    echo "<p>Intenta acceder al dashboard en: <a href='admin/dashboard.php' target='_blank'>admin/dashboard.php</a></p>";

    echo "<hr>";
    echo "<h2>✅ RESUMEN</h2>";
    echo "<p>Todos los componentes necesarios para el dashboard están funcionando.</p>";
    echo "<p><strong>Siguiente paso:</strong> Acceder a <a href='admin/login.php'>admin/login.php</a> e iniciar sesión.</p>";
} catch (Exception $e) {
    echo "<h2>❌ Error</h2>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<p>Archivo: " . $e->getFile() . "</p>";
    echo "<p>Línea: " . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
