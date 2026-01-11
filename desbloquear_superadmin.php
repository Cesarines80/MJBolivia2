<?php
require_once 'config/config.php';

echo "<!DOCTYPE html>";
echo "<html lang='es'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Desbloquear Super Administrador</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; max-width: 1000px; margin: 50px auto; padding: 20px; background: #f5f5f5; }";
echo "h1 { color: #333; border-bottom: 3px solid #8B7EC8; padding-bottom: 10px; }";
echo "h2 { color: #6B5B95; margin-top: 30px; }";
echo "table { width: 100%; border-collapse: collapse; margin: 15px 0; background: white; }";
echo "th, td { padding: 12px; text-align: left; border: 1px solid #ddd; }";
echo "th { background: #8B7EC8; color: white; }";
echo "tr:nth-child(even) { background: #f9f9f9; }";
echo ".success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; margin: 10px 0; border-radius: 5px; }";
echo ".error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; margin: 10px 0; border-radius: 5px; }";
echo ".info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 15px; margin: 10px 0; border-radius: 5px; }";
echo ".warning { background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 15px; margin: 10px 0; border-radius: 5px; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<h1>🔓 Desbloquear Usuario Super Administrador</h1>";
echo "<hr>";

$db = getDB();

// Buscar usuario superadmin
echo "<h2>📋 Paso 1: Verificar Estado del Usuario</h2>";

$stmt = $db->prepare("SELECT * FROM usuarios WHERE username = 'superadmin' LIMIT 1");
$stmt->execute();
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    echo "<div class='error'>";
    echo "<h3>❌ Usuario no encontrado</h3>";
    echo "<p>No se encontró el usuario 'superadmin' en la base de datos.</p>";
    echo "<p>Ejecuta el script: <strong>crear_superadmin.php</strong></p>";
    echo "</div>";
    exit;
}

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
echo "<tr><td>Bloqueado Hasta</td><td>" . ($usuario['bloqueado_hasta'] ?? 'NULL') . "</td><td>" . ($usuario['bloqueado_hasta'] ? '❌' : '✅') . "</td></tr>";
echo "<tr><td>Intentos Fallidos</td><td>{$usuario['intentos_fallidos']}</td><td>" . ($usuario['intentos_fallidos'] == 0 ? '✅' : '⚠️') . "</td></tr>";
echo "</table>";
echo "</div>";

// Desbloquear usuario
echo "<h2>🔓 Paso 2: Desbloquear y Limpiar Usuario</h2>";

try {
    $stmt = $db->prepare("
        UPDATE usuarios 
        SET bloqueado_hasta = NULL,
            intentos_fallidos = 0,
            activo = 1,
            rol = 'super_admin'
        WHERE username = 'superadmin'
    ");

    $result = $stmt->execute();

    if ($result) {
        echo "<div class='success'>";
        echo "<h3>✅ Usuario Desbloqueado Exitosamente</h3>";
        echo "<ul>";
        echo "<li>✅ Campo 'bloqueado_hasta' establecido a NULL</li>";
        echo "<li>✅ Intentos fallidos reseteados a 0</li>";
        echo "<li>✅ Usuario activado</li>";
        echo "<li>✅ Rol confirmado como 'super_admin'</li>";
        echo "</ul>";
        echo "</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<h3>❌ Error al desbloquear</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}

// Limpiar intentos fallidos por IP
echo "<h2>🌐 Paso 3: Limpiar Intentos Fallidos por IP</h2>";

try {
    $stmt = $db->prepare("
        DELETE FROM intentos_login 
        WHERE email = 'superadmin' 
           OR email = 'superadmin@sistema.com'
    ");
    $stmt->execute();
    $deletedIP = $stmt->rowCount();

    echo "<div class='success'>";
    echo "<h3>✅ Intentos Fallidos Limpiados</h3>";
    echo "<p>Registros eliminados: <strong>{$deletedIP}</strong></p>";
    echo "</div>";
} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<p>Error al limpiar intentos: " . $e->getMessage() . "</p>";
    echo "</div>";
}

// Verificar estado final
echo "<h2>✅ Paso 4: Verificación Final</h2>";

$stmt = $db->prepare("SELECT * FROM usuarios WHERE username = 'superadmin' LIMIT 1");
$stmt->execute();
$usuarioFinal = $stmt->fetch(PDO::FETCH_ASSOC);

echo "<div class='success'>";
echo "<h3>Estado Final del Usuario:</h3>";
echo "<table>";
echo "<tr><th>Campo</th><th>Valor</th><th>Estado</th></tr>";
echo "<tr><td>Username</td><td><strong>{$usuarioFinal['username']}</strong></td><td>✅</td></tr>";
echo "<tr><td>Email</td><td>{$usuarioFinal['email']}</td><td>✅</td></tr>";
echo "<tr><td>Rol</td><td><strong style='color: green;'>{$usuarioFinal['rol']}</strong></td><td>✅</td></tr>";
echo "<tr><td>Activo</td><td>" . ($usuarioFinal['activo'] ? 'Sí' : 'No') . "</td><td>" . ($usuarioFinal['activo'] ? '✅' : '❌') . "</td></tr>";
echo "<tr><td>Bloqueado Hasta</td><td>" . ($usuarioFinal['bloqueado_hasta'] ?? 'NULL') . "</td><td>" . ($usuarioFinal['bloqueado_hasta'] ? '❌' : '✅') . "</td></tr>";
echo "<tr><td>Intentos Fallidos</td><td>{$usuarioFinal['intentos_fallidos']}</td><td>" . ($usuarioFinal['intentos_fallidos'] == 0 ? '✅' : '⚠️') . "</td></tr>";
echo "</table>";
echo "</div>";

// Verificar si está completamente limpio
if (!$usuarioFinal['bloqueado_hasta'] && $usuarioFinal['intentos_fallidos'] == 0 && $usuarioFinal['activo'] && $usuarioFinal['rol'] === 'super_admin') {
    echo "<div class='success'>";
    echo "<h3>🎉 ¡Usuario Completamente Desbloqueado y Listo!</h3>";
    echo "</div>";
}

// Probar autenticación
echo "<h2>🔐 Paso 5: Prueba de Autenticación</h2>";

try {
    $auth = new Auth($db);

    // Intentar login
    $loginResult = $auth->login('superadmin', 'superadmin123');

    if ($loginResult['success']) {
        echo "<div class='success'>";
        echo "<h3>✅ ¡Login Exitoso!</h3>";
        echo "<p>El usuario puede iniciar sesión correctamente.</p>";
        echo "<ul>";
        echo "<li>Usuario: {$loginResult['user']['username']}</li>";
        echo "<li>Email: {$loginResult['user']['email']}</li>";
        echo "<li>Rol: {$loginResult['user']['rol']}</li>";
        echo "<li>Nombre: {$loginResult['user']['nombre_completo']}</li>";
        echo "</ul>";
        echo "</div>";

        // Cerrar sesión para no interferir
        session_destroy();
    } else {
        echo "<div class='error'>";
        echo "<h3>❌ Error en Login</h3>";
        echo "<p><strong>Mensaje:</strong> {$loginResult['message']}</p>";
        echo "</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<p>Error al probar autenticación: " . $e->getMessage() . "</p>";
    echo "</div>";
}

// Credenciales correctas
echo "<h2>🔑 Credenciales Correctas de Acceso</h2>";

echo "<div class='warning'>";
echo "<h3>⚠️ IMPORTANTE: Usa el USERNAME, NO el EMAIL</h3>";
echo "<p style='font-size: 1.1em;'>En el formulario de login, debes usar el <strong>username</strong>, no el email.</p>";
echo "</div>";

echo "<div class='success'>";
echo "<h3>✅ Credenciales Correctas:</h3>";
echo "<table>";
echo "<tr><th>Campo</th><th>Valor a Usar</th><th>❌ NO Usar</th></tr>";
echo "<tr>";
echo "<td><strong>Usuario</strong></td>";
echo "<td><strong style='font-size: 1.3em; color: green;'>superadmin</strong></td>";
echo "<td style='color: red;'><del>superadmin@sistema.com</del></td>";
echo "</tr>";
echo "<tr>";
echo "<td><strong>Contraseña</strong></td>";
echo "<td><strong style='font-size: 1.3em; color: green;'>superadmin123</strong></td>";
echo "<td>-</td>";
echo "</tr>";
echo "</table>";
echo "</div>";

echo "<div class='info'>";
echo "<h3>📝 Pasos para Iniciar Sesión:</h3>";
echo "<ol>";
echo "<li>Ir a: <a href='admin/login.php' target='_blank' style='color: #0c5460; font-weight: bold;'>admin/login.php</a></li>";
echo "<li>En el campo <strong>\"Usuario o Email\"</strong>, escribir: <strong style='color: green;'>superadmin</strong></li>";
echo "<li>En el campo <strong>\"Contraseña\"</strong>, escribir: <strong style='color: green;'>superadmin123</strong></li>";
echo "<li>Hacer clic en <strong>\"Iniciar Sesión\"</strong></li>";
echo "<li>✅ Deberías acceder sin problemas</li>";
echo "</ol>";
echo "</div>";

echo "<div class='warning'>";
echo "<h3>🔍 Diferencia entre Username y Email:</h3>";
echo "<table>";
echo "<tr><th>Campo</th><th>Valor</th><th>Uso</th></tr>";
echo "<tr><td><strong>Username</strong></td><td>superadmin</td><td>✅ Usar para login</td></tr>";
echo "<tr><td><strong>Email</strong></td><td>superadmin@sistema.com</td><td>⚠️ También funciona para login</td></tr>";
echo "</table>";
echo "<p><strong>Nota:</strong> El sistema acepta tanto username como email, pero es más común usar el username.</p>";
echo "</div>";

echo "<hr>";
echo "<p style='text-align: center; color: #666;'>";
echo "Script ejecutado - " . date('d/m/Y H:i:s') . "<br>";
echo "Usuario desbloqueado y listo para usar";
echo "</p>";

echo "</body>";
echo "</html>";
