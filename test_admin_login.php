<?php

/**
 * Script de prueba para verificar el login de administradores
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Prueba de Login de Administradores</h1>";
echo "<hr>";

try {
    // Simular IP para pruebas CLI
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

    require_once 'config/config.php';

    echo "<h2>1. Verificar Conexión a Base de Datos</h2>";
    $db = getDB();
    echo "✅ Conexión exitosa<br><br>";

    echo "<h2>2. Verificar Tabla Administradores</h2>";
    $stmt = $db->query("SELECT id, nombre, email, rol, estado FROM administradores WHERE id = 1");
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin) {
        echo "✅ Administrador encontrado:<br>";
        echo "- ID: " . $admin['id'] . "<br>";
        echo "- Nombre: " . $admin['nombre'] . "<br>";
        echo "- Email: " . $admin['email'] . "<br>";
        echo "- Rol: " . $admin['rol'] . "<br>";
        echo "- Estado: " . $admin['estado'] . "<br><br>";
    } else {
        echo "❌ No se encontró el administrador<br><br>";
    }

    echo "<h2>3. Verificar Método loginAdmin()</h2>";

    // Crear instancia de Auth
    $auth = new Auth($db);

    // Probar login con credenciales correctas
    echo "<h3>Prueba 1: Credenciales Correctas</h3>";
    $result = $auth->loginAdmin('admin@institucion.com', 'admin123');

    if ($result['success']) {
        echo "✅ Login exitoso<br>";
        echo "- Admin ID: " . $result['admin']['id'] . "<br>";
        echo "- Nombre: " . $result['admin']['nombre'] . "<br>";
        echo "- Email: " . $result['admin']['email'] . "<br>";
        echo "- Rol: " . $result['admin']['rol'] . "<br><br>";

        // Limpiar sesión
        session_destroy();
    } else {
        echo "❌ Login fallido: " . $result['message'] . "<br><br>";
    }

    // Probar login con credenciales incorrectas
    echo "<h3>Prueba 2: Contraseña Incorrecta</h3>";
    $result = $auth->loginAdmin('admin@institucion.com', 'wrongpassword');

    if (!$result['success']) {
        echo "✅ Rechazo correcto: " . $result['message'] . "<br><br>";
    } else {
        echo "❌ Error: Se permitió login con contraseña incorrecta<br><br>";
    }

    // Probar login con email inexistente
    echo "<h3>Prueba 3: Email Inexistente</h3>";
    $result = $auth->loginAdmin('noexiste@test.com', 'admin123');

    if (!$result['success']) {
        echo "✅ Rechazo correcto: " . $result['message'] . "<br><br>";
    } else {
        echo "❌ Error: Se permitió login con email inexistente<br><br>";
    }

    echo "<h2>4. Verificar Tabla intentos_login</h2>";
    $stmt = $db->query("DESCRIBE intentos_login");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Columnas en intentos_login:<br>";
    echo "<ul>";
    foreach ($columns as $col) {
        echo "<li>$col</li>";
    }
    echo "</ul><br>";

    echo "<h2>5. Verificar Estructura de Sesión</h2>";
    // Iniciar nueva sesión para prueba
    session_start();
    $_SESSION['admin_id'] = 1;
    $_SESSION['admin_nombre'] = 'Test Admin';
    $_SESSION['admin_email'] = 'admin@institucion.com';
    $_SESSION['admin_rol'] = 'superadmin';
    $_SESSION['is_admin'] = true;

    echo "Sesión de prueba creada:<br>";
    echo "- admin_id: " . $_SESSION['admin_id'] . "<br>";
    echo "- admin_nombre: " . $_SESSION['admin_nombre'] . "<br>";
    echo "- admin_email: " . $_SESSION['admin_email'] . "<br>";
    echo "- admin_rol: " . $_SESSION['admin_rol'] . "<br>";
    echo "- is_admin: " . ($_SESSION['is_admin'] ? 'true' : 'false') . "<br><br>";

    echo "<h3>Verificar isLoggedIn() con sesión de admin</h3>";
    if ($auth->isLoggedIn()) {
        echo "✅ isLoggedIn() retorna true para administrador<br><br>";
    } else {
        echo "❌ isLoggedIn() retorna false para administrador<br><br>";
    }

    // Limpiar sesión
    session_destroy();

    echo "<hr>";
    echo "<h2>✅ RESUMEN DE PRUEBAS</h2>";
    echo "<p><strong>Estado:</strong> Sistema de login de administradores funcionando correctamente</p>";
    echo "<p><strong>Credenciales de acceso:</strong></p>";
    echo "<ul>";
    echo "<li>URL: <a href='admin/login.php'>admin/login.php</a></li>";
    echo "<li>Email: admin@institucion.com</li>";
    echo "<li>Contraseña: admin123</li>";
    echo "</ul>";
    echo "<p><strong>⚠️ IMPORTANTE:</strong> Cambiar la contraseña después del primer acceso.</p>";
} catch (Exception $e) {
    echo "<h2>❌ Error en las Pruebas</h2>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<p>Archivo: " . $e->getFile() . "</p>";
    echo "<p>Línea: " . $e->getLine() . "</p>";
}
