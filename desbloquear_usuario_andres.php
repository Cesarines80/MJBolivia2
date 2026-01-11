<?php
require_once 'config/config.php';

echo "<h1>Desbloquear Usuario Andres</h1>";
echo "<hr>";

$db = getDB();

// Credenciales
$username = 'andres';
$email = 'andres@andres.com';
$password = '123456';

echo "<h2>Paso 1: Limpiar Bloqueos</h2>";

// Limpiar intentos fallidos de la tabla intentos_login
$stmt = $db->prepare("DELETE FROM intentos_login WHERE ip_address = ? OR username = ? OR email = ?");
$stmt->execute([$_SERVER['REMOTE_ADDR'], $username, $email]);
echo "✅ Intentos fallidos eliminados de la tabla intentos_login<br>";

// Desbloquear usuario en la tabla usuarios
$stmt = $db->prepare("
    UPDATE usuarios 
    SET bloqueado_hasta = NULL, 
        intentos_fallidos = 0 
    WHERE username = ? OR email = ?
");
$stmt->execute([$username, $email]);
echo "✅ Usuario desbloqueado en la tabla usuarios<br>";

echo "<hr>";

// Verificar/Crear usuario
echo "<h2>Paso 2: Verificar/Crear Usuario</h2>";

$stmt = $db->prepare("SELECT * FROM usuarios WHERE username = ? OR email = ? LIMIT 1");
$stmt->execute([$username, $email]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if ($usuario) {
    echo "✅ Usuario encontrado<br>";

    // Actualizar contraseña y asegurar que esté activo
    $newHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
    $stmt = $db->prepare("
        UPDATE usuarios 
        SET password = ?, 
            email = ?, 
            activo = 1,
            bloqueado_hasta = NULL,
            intentos_fallidos = 0
        WHERE id = ?
    ");
    $stmt->execute([$newHash, $email, $usuario['id']]);
    echo "✅ Contraseña actualizada y usuario activado<br>";

    $usuarioId = $usuario['id'];
} else {
    echo "⚠️ Usuario no existe. Creando...<br>";

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
        echo "✅ Usuario creado exitosamente<br>";
        $usuarioId = $result['user_id'];
    } else {
        echo "❌ Error al crear usuario: " . $result['message'] . "<br>";
        exit;
    }
}

echo "<hr>";

// Verificar estado final
echo "<h2>Paso 3: Verificar Estado Final</h2>";

$stmt = $db->prepare("SELECT * FROM usuarios WHERE id = ? LIMIT 1");
$stmt->execute([$usuarioId]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

echo "<div style='background: #d4edda; padding: 20px; border-radius: 5px; border: 1px solid #c3e6cb;'>";
echo "<h3>✅ Usuario Configurado</h3>";
echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
echo "<tr><th>Campo</th><th>Valor</th></tr>";
echo "<tr><td><strong>ID</strong></td><td>{$usuario['id']}</td></tr>";
echo "<tr><td><strong>Username</strong></td><td>{$usuario['username']}</td></tr>";
echo "<tr><td><strong>Email</strong></td><td>{$usuario['email']}</td></tr>";
echo "<tr><td><strong>Nombre</strong></td><td>{$usuario['nombre_completo']}</td></tr>";
echo "<tr><td><strong>Rol</strong></td><td>{$usuario['rol']}</td></tr>";
echo "<tr><td><strong>Activo</strong></td><td>" . ($usuario['activo'] ? '✅ Sí' : '❌ No') . "</td></tr>";
echo "<tr><td><strong>Bloqueado Hasta</strong></td><td>" . ($usuario['bloqueado_hasta'] ? $usuario['bloqueado_hasta'] : '✅ No bloqueado') . "</td></tr>";
echo "<tr><td><strong>Intentos Fallidos</strong></td><td>{$usuario['intentos_fallidos']}</td></tr>";
echo "</table>";
echo "</div>";

echo "<hr>";

// Probar login
echo "<h2>Paso 4: Probar Login</h2>";

$auth = new Auth($db);
$loginResult = $auth->login($username, $password);

if ($loginResult['success']) {
    echo "<div style='background: #d4edda; padding: 20px; border-radius: 5px; border: 1px solid #c3e6cb;'>";
    echo "<h3>✅ Login Exitoso</h3>";
    echo "<p>El usuario puede iniciar sesión correctamente.</p>";
    echo "<ul>";
    echo "<li><strong>Usuario:</strong> {$loginResult['user']['username']}</li>";
    echo "<li><strong>Email:</strong> {$loginResult['user']['email']}</li>";
    echo "<li><strong>Rol:</strong> {$loginResult['user']['rol']}</li>";
    echo "</ul>";
    echo "</div>";

    // Limpiar sesión
    session_destroy();
} else {
    echo "<div style='background: #f8d7da; padding: 20px; border-radius: 5px; border: 1px solid #f5c6cb;'>";
    echo "<h3>❌ Error en Login</h3>";
    echo "<p><strong>Mensaje:</strong> " . $loginResult['message'] . "</p>";
    echo "</div>";
}

echo "<hr>";

// Verificar tabla intentos_login
echo "<h2>Paso 5: Verificar Tabla de Intentos</h2>";

$stmt = $db->prepare("SELECT * FROM intentos_login WHERE username = ? OR email = ?");
$stmt->execute([$username, $email]);
$intentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($intentos)) {
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px;'>";
    echo "✅ No hay intentos fallidos registrados";
    echo "</div>";
} else {
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px;'>";
    echo "⚠️ Intentos fallidos encontrados:<br>";
    echo "<pre>";
    print_r($intentos);
    echo "</pre>";
    echo "</div>";
}

echo "<hr>";

// Resumen final
echo "<div style='background: #d1ecf1; padding: 20px; border-radius: 5px; border: 1px solid #bee5eb;'>";
echo "<h2>📋 Resumen - Usuario Desbloqueado</h2>";
echo "<h3>✅ Ahora puedes iniciar sesión con:</h3>";
echo "<div style='background: white; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<ul style='font-size: 18px; list-style: none;'>";
echo "<li>🔗 <strong>URL:</strong> <a href='admin/login.php' target='_blank'>admin/login.php</a></li>";
echo "<li>👤 <strong>Usuario:</strong> <code style='background: #e7f3ff; padding: 5px 10px; border-radius: 3px;'>$username</code></li>";
echo "<li>📧 <strong>Email:</strong> <code style='background: #e7f3ff; padding: 5px 10px; border-radius: 3px;'>$email</code></li>";
echo "<li>🔑 <strong>Contraseña:</strong> <code style='background: #e7f3ff; padding: 5px 10px; border-radius: 3px;'>$password</code></li>";
echo "</ul>";
echo "</div>";
echo "<p><strong>Nota:</strong> Puedes usar el username o el email para iniciar sesión.</p>";
echo "</div>";

echo "<br>";
echo "<div style='text-align: center; padding: 20px; background: #e7f3ff; border-radius: 5px;'>";
echo "<h3>🔗 Ir al Login</h3>";
echo "<a href='admin/login.php' style='display: inline-block; padding: 15px 30px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; font-size: 18px; font-weight: bold;'>INICIAR SESIÓN AHORA</a>";
echo "</div>";

echo "<hr>";
echo "<p style='text-align: center; color: #666;'>Usuario desbloqueado - " . date('d/m/Y H:i:s') . "</p>";
