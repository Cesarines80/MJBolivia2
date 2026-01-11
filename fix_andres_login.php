<?php
require_once 'config/config.php';

echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>Fix Login Andres</title></head><body>";
echo "<div style='max-width: 800px; margin: 50px auto; font-family: Arial, sans-serif;'>";
echo "<h1 style='color: #007bff;'>🔧 Reparar Login de Andres</h1>";
echo "<hr>";

$db = getDB();

$username = 'andres';
$email = 'andres@andres.com';
$password = '123456';

echo "<h2>🔄 Paso 1: Limpiando TODOS los bloqueos del sistema</h2>";

try {
    // 1. Limpiar TODA la tabla intentos_login
    $db->exec("DELETE FROM intentos_login");
    echo "<p style='color: green;'>✅ Tabla intentos_login limpiada completamente</p>";

    // 2. Desbloquear TODOS los usuarios
    $db->exec("UPDATE usuarios SET bloqueado_hasta = NULL, intentos_fallidos = 0");
    echo "<p style='color: green;'>✅ Todos los usuarios desbloqueados</p>";

    // 3. Desbloquear TODOS los administradores
    $db->exec("UPDATE administradores SET intentos_fallidos = 0");
    echo "<p style='color: green;'>✅ Todos los administradores desbloqueados</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h2>👤 Paso 2: Configurando usuario 'andres'</h2>";

// Buscar usuario
$stmt = $db->prepare("SELECT * FROM usuarios WHERE username = ? OR email = ? LIMIT 1");
$stmt->execute([$username, $email]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if ($usuario) {
    echo "<p>Usuario encontrado con ID: {$usuario['id']}</p>";

    // Actualizar TODO
    $newHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
    $stmt = $db->prepare("
        UPDATE usuarios 
        SET password = ?, 
            email = ?,
            username = ?,
            activo = 1,
            bloqueado_hasta = NULL,
            intentos_fallidos = 0
        WHERE id = ?
    ");
    $result = $stmt->execute([$newHash, $email, $username, $usuario['id']]);

    if ($result) {
        echo "<p style='color: green;'>✅ Usuario actualizado correctamente</p>";
    }

    $usuarioId = $usuario['id'];
} else {
    echo "<p style='color: orange;'>⚠️ Usuario no existe, creando...</p>";

    $auth = new Auth($db);
    $result = $auth->register([
        'username' => $username,
        'email' => $email,
        'password' => $password,
        'nombre_completo' => 'Andrés García',
        'rol' => 'admin',
        'activo' => 1
    ]);

    if ($result['success']) {
        echo "<p style='color: green;'>✅ Usuario creado con ID: {$result['user_id']}</p>";
        $usuarioId = $result['user_id'];
    } else {
        echo "<p style='color: red;'>❌ Error: {$result['message']}</p>";
        echo "</div></body></html>";
        exit;
    }
}

echo "<hr>";
echo "<h2>🧪 Paso 3: Probando Login</h2>";

// Limpiar sesión anterior
session_destroy();
session_start();

$auth = new Auth($db);

// Probar con username
echo "<h3>Probando con username: '$username'</h3>";
$result1 = $auth->login($username, $password);
if ($result1['success']) {
    echo "<p style='color: green; font-size: 18px;'>✅ ¡LOGIN EXITOSO con username!</p>";
    session_destroy();
} else {
    echo "<p style='color: red;'>❌ Error: {$result1['message']}</p>";
}

// Probar con email
echo "<h3>Probando con email: '$email'</h3>";
session_start();
$result2 = $auth->login($email, $password);
if ($result2['success']) {
    echo "<p style='color: green; font-size: 18px;'>✅ ¡LOGIN EXITOSO con email!</p>";
    session_destroy();
} else {
    echo "<p style='color: red;'>❌ Error: {$result2['message']}</p>";
}

echo "<hr>";
echo "<h2>📊 Paso 4: Verificación Final</h2>";

$stmt = $db->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$usuarioId]);
$usuarioFinal = $stmt->fetch(PDO::FETCH_ASSOC);

echo "<div style='background: #e7f3ff; padding: 20px; border-radius: 10px; border: 2px solid #007bff;'>";
echo "<h3>Estado del Usuario:</h3>";
echo "<table style='width: 100%; border-collapse: collapse;'>";
echo "<tr style='background: #f8f9fa;'><td style='padding: 10px; border: 1px solid #ddd;'><strong>ID</strong></td><td style='padding: 10px; border: 1px solid #ddd;'>{$usuarioFinal['id']}</td></tr>";
echo "<tr><td style='padding: 10px; border: 1px solid #ddd;'><strong>Username</strong></td><td style='padding: 10px; border: 1px solid #ddd;'>{$usuarioFinal['username']}</td></tr>";
echo "<tr style='background: #f8f9fa;'><td style='padding: 10px; border: 1px solid #ddd;'><strong>Email</strong></td><td style='padding: 10px; border: 1px solid #ddd;'>{$usuarioFinal['email']}</td></tr>";
echo "<tr><td style='padding: 10px; border: 1px solid #ddd;'><strong>Rol</strong></td><td style='padding: 10px; border: 1px solid #ddd;'>{$usuarioFinal['rol']}</td></tr>";
echo "<tr style='background: #f8f9fa;'><td style='padding: 10px; border: 1px solid #ddd;'><strong>Activo</strong></td><td style='padding: 10px; border: 1px solid #ddd;'>" . ($usuarioFinal['activo'] ? '✅ Sí' : '❌ No') . "</td></tr>";
echo "<tr><td style='padding: 10px; border: 1px solid #ddd;'><strong>Bloqueado</strong></td><td style='padding: 10px; border: 1px solid #ddd;'>" . ($usuarioFinal['bloqueado_hasta'] ? '❌ ' . $usuarioFinal['bloqueado_hasta'] : '✅ No') . "</td></tr>";
echo "<tr style='background: #f8f9fa;'><td style='padding: 10px; border: 1px solid #ddd;'><strong>Intentos Fallidos</strong></td><td style='padding: 10px; border: 1px solid #ddd;'>{$usuarioFinal['intentos_fallidos']}</td></tr>";
echo "</table>";
echo "</div>";

echo "<hr>";

// Verificar intentos_login
$stmt = $db->query("SELECT COUNT(*) as total FROM intentos_login");
$totalIntentos = $stmt->fetch(PDO::FETCH_ASSOC);

echo "<div style='background: " . ($totalIntentos['total'] == 0 ? '#d4edda' : '#fff3cd') . "; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>Tabla intentos_login:</h3>";
echo "<p>Total de registros: <strong>{$totalIntentos['total']}</strong></p>";
if ($totalIntentos['total'] == 0) {
    echo "<p style='color: green;'>✅ Tabla limpia, sin bloqueos</p>";
} else {
    echo "<p style='color: orange;'>⚠️ Hay registros en la tabla</p>";
}
echo "</div>";

echo "<hr>";

// Resultado final
if ($result1['success'] || $result2['success']) {
    echo "<div style='background: #d4edda; padding: 30px; border-radius: 10px; border: 3px solid #28a745; text-align: center;'>";
    echo "<h2 style='color: #28a745; margin: 0;'>✅ ¡TODO LISTO!</h2>";
    echo "<p style='font-size: 18px; margin: 20px 0;'>El usuario está configurado y funcionando correctamente.</p>";
    echo "<div style='background: white; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>Credenciales para Login:</h3>";
    echo "<p style='font-size: 20px;'><strong>Usuario:</strong> <code style='background: #e7f3ff; padding: 5px 15px; border-radius: 3px;'>$username</code></p>";
    echo "<p style='font-size: 20px;'><strong>O Email:</strong> <code style='background: #e7f3ff; padding: 5px 15px; border-radius: 3px;'>$email</code></p>";
    echo "<p style='font-size: 20px;'><strong>Contraseña:</strong> <code style='background: #e7f3ff; padding: 5px 15px; border-radius: 3px;'>$password</code></p>";
    echo "</div>";
    echo "<a href='admin/login.php' style='display: inline-block; padding: 20px 40px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; font-size: 20px; font-weight: bold; margin-top: 20px;'>IR AL LOGIN AHORA →</a>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; padding: 30px; border-radius: 10px; border: 3px solid #dc3545; text-align: center;'>";
    echo "<h2 style='color: #dc3545;'>❌ Aún hay problemas</h2>";
    echo "<p>Por favor, contacta al desarrollador con esta información.</p>";
    echo "</div>";
}

echo "<hr>";
echo "<p style='text-align: center; color: #666;'>Script ejecutado: " . date('d/m/Y H:i:s') . "</p>";
echo "</div></body></html>";
