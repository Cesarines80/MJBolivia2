<?php

/**
 * Prueba de Login Superadmin
 */

require_once 'config/config.php';

// Simular IP para CLI
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

echo "<!DOCTYPE html>";
echo "<html lang='es'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<title>Prueba Login Superadmin</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }";
echo ".success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; margin: 10px 0; border-radius: 5px; }";
echo ".error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; margin: 10px 0; border-radius: 5px; }";
echo "h1 { color: #333; border-bottom: 3px solid #8B7EC8; padding-bottom: 10px; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<h1>🧪 Prueba de Login Superadmin</h1>";

$db = getDB();
$auth = new Auth($db);

echo "<h2>Prueba de Login</h2>";
echo "<p><strong>Usuario:</strong> superadmin</p>";
echo "<p><strong>Contraseña:</strong> superadmin123</p>";

$result = $auth->login('superadmin', 'superadmin123');

if ($result['success']) {
    echo "<div class='success'>";
    echo "<h3>✅ ¡Login Exitoso!</h3>";
    echo "<p>El superadmin puede iniciar sesión correctamente.</p>";
    echo "<ul>";
    echo "<li>ID: {$result['user']['id']}</li>";
    echo "<li>Username: {$result['user']['username']}</li>";
    echo "<li>Rol: {$result['user']['rol']}</li>";
    echo "</ul>";
    echo "</div>";

    // Cerrar sesión
    session_destroy();
} else {
    echo "<div class='error'>";
    echo "<h3>❌ Error en Login</h3>";
    echo "<p><strong>Mensaje:</strong> {$result['message']}</p>";
    echo "</div>";
}

echo "<h2>Estado del Usuario</h2>";

$stmt = $db->prepare("
    SELECT username, email, activo, bloqueado_hasta, intentos_fallidos
    FROM usuarios
    WHERE username = 'superadmin'
");
$stmt->execute();
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

echo "<div class='success'>";
echo "<p><strong>Estado:</strong></p>";
echo "<ul>";
echo "<li>Activo: " . ($usuario['activo'] ? '✅ Sí' : '❌ No') . "</li>";
echo "<li>Bloqueado hasta: " . ($usuario['bloqueado_hasta'] ?? '✅ No bloqueado') . "</li>";
echo "<li>Intentos fallidos: {$usuario['intentos_fallidos']}</li>";
echo "</ul>";
echo "</div>";

echo "<p style='text-align: center; color: #666; margin-top: 30px;'>";
echo "Prueba ejecutada el: " . date('d/m/Y H:i:s');
echo "</p>";

echo "</body>";
echo "</html>";
