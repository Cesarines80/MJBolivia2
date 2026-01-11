<?php
require_once 'config/config.php';

echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>Desactivar Bloqueo</title></head><body>";
echo "<div style='max-width: 800px; margin: 50px auto; font-family: Arial, sans-serif;'>";
echo "<h1 style='color: #dc3545;'>🔓 Desactivar Sistema de Bloqueo para Andres</h1>";
echo "<hr>";

$db = getDB();

$username = 'andres';
$email = 'andres@andres.com';
$password = '123456';

echo "<h2>Paso 1: Limpiando TODOS los bloqueos del sistema</h2>";

try {
    // Limpiar TODA la tabla intentos_login
    $count = $db->exec("DELETE FROM intentos_login");
    echo "<p style='color: green;'>✅ Eliminados $count registros de intentos_login</p>";

    // Desbloquear TODOS los usuarios
    $count = $db->exec("UPDATE usuarios SET bloqueado_hasta = NULL, intentos_fallidos = 0");
    echo "<p style='color: green;'>✅ Desbloqueados $count usuarios</p>";

    // Desbloquear TODOS los administradores
    $count = $db->exec("UPDATE administradores SET intentos_fallidos = 0");
    echo "<p style='color: green;'>✅ Desbloqueados $count administradores</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h2>Paso 2: Verificando usuario 'andres'</h2>";

$stmt = $db->prepare("SELECT * FROM usuarios WHERE username = ? OR email = ? LIMIT 1");
$stmt->execute([$username, $email]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if ($usuario) {
    echo "<p>✅ Usuario encontrado con ID: {$usuario['id']}</p>";

    // Actualizar TODO y asegurar que esté desbloqueado
    $newHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
    $stmt = $db->prepare("
        UPDATE usuarios 
        SET password = ?, 
            email = ?,
            username = ?,
            rol = 'admin',
            activo = 1,
            bloqueado_hasta = NULL,
            intentos_fallidos = 0
        WHERE id = ?
    ");
    $result = $stmt->execute([$newHash, $email, $username, $usuario['id']]);

    if ($result) {
        echo "<p style='color: green;'>✅ Usuario actualizado y desbloqueado</p>";
    }

    $usuarioId = $usuario['id'];
} else {
    echo "<p style='color: red;'>❌ Usuario no encontrado</p>";
    echo "</div></body></html>";
    exit;
}

echo "<hr>";
echo "<h2>Paso 3: Estado actual del usuario</h2>";

$stmt = $db->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$usuarioId]);
$usuarioFinal = $stmt->fetch(PDO::FETCH_ASSOC);

echo "<div style='background: #e7f3ff; padding: 20px; border-radius: 10px; border: 2px solid #007bff;'>";
echo "<table style='width: 100%; border-collapse: collapse;'>";
echo "<tr style='background: #f8f9fa;'><td style='padding: 10px; border: 1px solid #ddd;'><strong>ID</strong></td><td style='padding: 10px; border: 1px solid #ddd;'>{$usuarioFinal['id']}</td></tr>";
echo "<tr><td style='padding: 10px; border: 1px solid #ddd;'><strong>Username</strong></td><td style='padding: 10px; border: 1px solid #ddd;'>{$usuarioFinal['username']}</td></tr>";
echo "<tr style='background: #f8f9fa;'><td style='padding: 10px; border: 1px solid #ddd;'><strong>Email</strong></td><td style='padding: 10px; border: 1px solid #ddd;'>{$usuarioFinal['email']}</td></tr>";
echo "<tr><td style='padding: 10px; border: 1px solid #ddd;'><strong>Rol</strong></td><td style='padding: 10px; border: 1px solid #ddd;'><span style='background: #28a745; color: white; padding: 5px 10px; border-radius: 3px;'>{$usuarioFinal['rol']}</span></td></tr>";
echo "<tr style='background: #f8f9fa;'><td style='padding: 10px; border: 1px solid #ddd;'><strong>Activo</strong></td><td style='padding: 10px; border: 1px solid #ddd;'>" . ($usuarioFinal['activo'] ? '<span style="color: green;">✅ Sí</span>' : '<span style="color: red;">❌ No</span>') . "</td></tr>";
echo "<tr><td style='padding: 10px; border: 1px solid #ddd;'><strong>Bloqueado Hasta</strong></td><td style='padding: 10px; border: 1px solid #ddd;'>" . ($usuarioFinal['bloqueado_hasta'] ? '<span style="color: red;">❌ ' . $usuarioFinal['bloqueado_hasta'] . '</span>' : '<span style="color: green;">✅ No bloqueado</span>') . "</td></tr>";
echo "<tr style='background: #f8f9fa;'><td style='padding: 10px; border: 1px solid #ddd;'><strong>Intentos Fallidos</strong></td><td style='padding: 10px; border: 1px solid #ddd;'><span style='background: " . ($usuarioFinal['intentos_fallidos'] > 0 ? '#dc3545' : '#28a745') . "; color: white; padding: 5px 10px; border-radius: 3px;'>{$usuarioFinal['intentos_fallidos']}</span></td></tr>";
echo "</table>";
echo "</div>";

echo "<hr>";
echo "<h2>Paso 4: Verificando tabla intentos_login</h2>";

$stmt = $db->query("SELECT COUNT(*) as total FROM intentos_login");
$totalIntentos = $stmt->fetch(PDO::FETCH_ASSOC);

echo "<div style='background: " . ($totalIntentos['total'] == 0 ? '#d4edda' : '#fff3cd') . "; padding: 15px; border-radius: 5px;'>";
echo "<p><strong>Total de registros en intentos_login:</strong> {$totalIntentos['total']}</p>";
if ($totalIntentos['total'] == 0) {
    echo "<p style='color: green;'>✅ Tabla limpia</p>";
} else {
    echo "<p style='color: orange;'>⚠️ Hay {$totalIntentos['total']} registros</p>";

    // Mostrar los registros
    $stmt = $db->query("SELECT * FROM intentos_login LIMIT 10");
    $intentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($intentos)) {
        echo "<table style='width: 100%; border-collapse: collapse; margin-top: 10px;'>";
        echo "<tr style='background: #f8f9fa;'><th style='padding: 5px; border: 1px solid #ddd;'>IP</th><th style='padding: 5px; border: 1px solid #ddd;'>Email/Usuario</th><th style='padding: 5px; border: 1px solid #ddd;'>Intentos</th><th style='padding: 5px; border: 1px solid #ddd;'>Bloqueado Hasta</th></tr>";
        foreach ($intentos as $intento) {
            echo "<tr>";
            echo "<td style='padding: 5px; border: 1px solid #ddd;'>{$intento['ip_address']}</td>";
            echo "<td style='padding: 5px; border: 1px solid #ddd;'>" . ($intento['email'] ?? $intento['username'] ?? 'N/A') . "</td>";
            echo "<td style='padding: 5px; border: 1px solid #ddd;'>{$intento['intentos']}</td>";
            echo "<td style='padding: 5px; border: 1px solid #ddd;'>" . ($intento['bloqueado_hasta'] ?? 'No') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
}
echo "</div>";

echo "<hr>";

// Resultado final
echo "<div style='background: #d4edda; padding: 30px; border-radius: 10px; border: 3px solid #28a745; text-align: center;'>";
echo "<h2 style='color: #28a745; margin: 0;'>✅ USUARIO DESBLOQUEADO</h2>";
echo "<p style='font-size: 18px; margin: 20px 0;'>El usuario 'andres' está completamente desbloqueado.</p>";
echo "<div style='background: white; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>Credenciales:</h3>";
echo "<p style='font-size: 20px;'><strong>Usuario:</strong> <code style='background: #e7f3ff; padding: 5px 15px; border-radius: 3px;'>$username</code></p>";
echo "<p style='font-size: 20px;'><strong>O Email:</strong> <code style='background: #e7f3ff; padding: 5px 15px; border-radius: 3px;'>$email</code></p>";
echo "<p style='font-size: 20px;'><strong>Contraseña:</strong> <code style='background: #e7f3ff; padding: 5px 15px; border-radius: 3px;'>$password</code></p>";
echo "</div>";

echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0; border: 2px solid #ffc107;'>";
echo "<h3 style='color: #856404; margin-top: 0;'>⚠️ IMPORTANTE</h3>";
echo "<p style='color: #856404; margin: 0;'><strong>Si el problema persiste:</strong></p>";
echo "<ol style='text-align: left; color: #856404;'>";
echo "<li>Cierra TODAS las pestañas del navegador</li>";
echo "<li>Borra las cookies del sitio</li>";
echo "<li>Abre el navegador en modo incógnito</li>";
echo "<li>Intenta iniciar sesión nuevamente</li>";
echo "</ol>";
echo "</div>";

echo "<a href='admin/login.php' style='display: inline-block; padding: 20px 40px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; font-size: 20px; font-weight: bold; margin-top: 20px;'>IR AL LOGIN →</a>";
echo "</div>";

echo "<hr>";
echo "<p style='text-align: center; color: #666;'>Script ejecutado: " . date('d/m/Y H:i:s') . "</p>";
echo "</div></body></html>";
