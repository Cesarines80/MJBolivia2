<?php
require_once 'config/config.php';

echo "<!DOCTYPE html>";
echo "<html lang='es'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Verificar Estado Superadmin</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; background: #f5f5f5; }";
echo "h1 { color: #333; border-bottom: 3px solid #8B7EC8; padding-bottom: 10px; }";
echo "table { width: 100%; border-collapse: collapse; margin: 15px 0; background: white; }";
echo "th, td { padding: 12px; text-align: left; border: 1px solid #ddd; }";
echo "th { background: #8B7EC8; color: white; }";
echo "tr:nth-child(even) { background: #f9f9f9; }";
echo ".success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; margin: 10px 0; border-radius: 5px; }";
echo ".error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; margin: 10px 0; border-radius: 5px; }";
echo ".info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 15px; margin: 10px 0; border-radius: 5px; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<h1>🔍 Verificar Estado del Usuario Superadmin</h1>";
echo "<hr>";

$db = getDB();

// Verificar usuario en tabla usuarios
echo "<h2>👤 Estado en Tabla 'usuarios'</h2>";

$stmt = $db->prepare("SELECT * FROM usuarios WHERE username = 'superadmin' LIMIT 1");
$stmt->execute();
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    echo "<div class='error'>";
    echo "<h3>❌ Usuario no encontrado</h3>";
    echo "<p>No se encontró el usuario 'superadmin' en la tabla usuarios.</p>";
    echo "</div>";
} else {
    echo "<div class='info'>";
    echo "<h3>✅ Usuario Encontrado</h3>";
    echo "<table>";
    echo "<tr><th>Campo</th><th>Valor</th><th>Estado</th></tr>";
    echo "<tr><td>ID</td><td>{$usuario['id']}</td><td>✅</td></tr>";
    echo "<tr><td>Username</td><td><strong>{$usuario['username']}</strong></td><td>✅</td></tr>";
    echo "<tr><td>Email</td><td>{$usuario['email']}</td><td>✅</td></tr>";
    echo "<tr><td>Nombre</td><td>{$usuario['nombre_completo']}</td><td>✅</td></tr>";
    echo "<tr><td>Rol</td><td><strong>{$usuario['rol']}</strong></td><td>" . ($usuario['rol'] === 'super_admin' ? '✅' : '⚠️') . "</td></tr>";
    echo "<tr><td>Activo</td><td>" . ($usuario['activo'] ? 'Sí' : 'No') . "</td><td>" . ($usuario['activo'] ? '✅' : '❌') . "</td></tr>";
    echo "<tr><td>Bloqueado Hasta</td><td>" . ($usuario['bloqueado_hasta'] ?? 'NULL') . "</td><td>" . ($usuario['bloqueado_hasta'] ? '❌ BLOQUEADO' : '✅ LIBRE') . "</td></tr>";
    echo "<tr><td>Intentos Fallidos</td><td>{$usuario['intentos_fallidos']}</td><td>" . ($usuario['intentos_fallidos'] == 0 ? '✅' : '⚠️') . "</td></tr>";
    echo "<tr><td>Último Acceso</td><td>" . ($usuario['ultimo_acceso'] ?? 'Nunca') . "</td><td>ℹ️</td></tr>";
    echo "</table>";
    echo "</div>";
}

// Verificar intentos de login por IP
echo "<h2>🌐 Intentos de Login por IP</h2>";

$currentIP = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

$stmt = $db->prepare("
    SELECT ip_address, email, intentos, ultimo_intento, bloqueado_hasta
    FROM intentos_login
    WHERE ip_address = ? OR email = 'superadmin' OR email = 'superadmin@sistema.com'
    ORDER BY ultimo_intento DESC
");
$stmt->execute([$currentIP]);

$intentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($intentos)) {
    echo "<div class='success'>";
    echo "<h3>✅ No hay registros de intentos fallidos</h3>";
    echo "<p>Tu IP actual: <strong>{$currentIP}</strong></p>";
    echo "</div>";
} else {
    echo "<div class='warning'>";
    echo "<h3>⚠️ Registros de Intentos Fallidos Encontrados</h3>";
    echo "<p>Tu IP actual: <strong>{$currentIP}</strong></p>";
    echo "<table>";
    echo "<tr><th>IP</th><th>Email</th><th>Intentos</th><th>Último Intento</th><th>Bloqueado Hasta</th><th>Estado</th></tr>";

    foreach ($intentos as $intento) {
        $estado = '✅ Libre';
        if ($intento['bloqueado_hasta'] && strtotime($intento['bloqueado_hasta']) > time()) {
            $estado = '❌ BLOQUEADO';
        }

        echo "<tr>";
        echo "<td>{$intento['ip_address']}</td>";
        echo "<td>{$intento['email']}</td>";
        echo "<td>{$intento['intentos']}</td>";
        echo "<td>{$intento['ultimo_intento']}</td>";
        echo "<td>" . ($intento['bloqueado_hasta'] ?? 'NULL') . "</td>";
        echo "<td>{$estado}</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
}

// Verificar si hay bloqueo por IP actual
echo "<h2>🔒 Verificación de Bloqueo por IP</h2>";

$stmt = $db->prepare("
    SELECT bloqueado_hasta
    FROM intentos_login
    WHERE ip_address = ? AND bloqueado_hasta IS NOT NULL AND bloqueado_hasta > NOW()
    LIMIT 1
");
$stmt->execute([$currentIP]);

$bloqueoIP = $stmt->fetch(PDO::FETCH_ASSOC);

if ($bloqueoIP) {
    echo "<div class='error'>";
    echo "<h3>❌ Tu IP está bloqueada</h3>";
    echo "<p>IP: <strong>{$currentIP}</strong></p>";
    echo "<p>Bloqueada hasta: <strong>{$bloqueoIP['bloqueado_hasta']}</strong></p>";
    echo "<p>Esto explica por qué sigues viendo 'Usuario bloqueado temporalmente'.</p>";
    echo "</div>";
} else {
    echo "<div class='success'>";
    echo "<h3>✅ Tu IP no está bloqueada</h3>";
    echo "<p>IP: <strong>{$currentIP}</strong></p>";
    echo "</div>";
}

// Probar login
echo "<h2>🔐 Prueba de Login</h2>";

try {
    $auth = new Auth($db);

    $loginResult = $auth->login('superadmin', 'superadmin123');

    if ($loginResult['success']) {
        echo "<div class='success'>";
        echo "<h3>✅ ¡Login Exitoso!</h3>";
        echo "<p>El usuario puede iniciar sesión correctamente.</p>";
        echo "</div>";

        // Cerrar sesión
        session_destroy();
    } else {
        echo "<div class='error'>";
        echo "<h3>❌ Error en Login</h3>";
        echo "<p><strong>Mensaje:</strong> {$loginResult['message']}</p>";
        echo "<p>Esto confirma que el problema persiste.</p>";
        echo "</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<p>Error al probar autenticación: " . $e->getMessage() . "</p>";
    echo "</div>";
}

echo "<hr>";
echo "<p style='text-align: center; color: #666;'>";
echo "Verificación ejecutada - " . date('d/m/Y H:i:s') . "<br>";
echo "Estado del superadmin";
echo "</p>";

echo "</body>";
echo "</html>";
