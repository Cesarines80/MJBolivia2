<?php
session_start();
session_destroy();
session_start();

require_once 'config/config.php';

echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>Logout y Reparación</title></head><body>";
echo "<div style='max-width: 800px; margin: 50px auto; font-family: Arial, sans-serif;'>";
echo "<h1 style='color: #007bff;'>🔄 Cerrar Sesión y Reparar</h1>";
echo "<hr>";

$db = getDB();

$username = 'andres';
$email = 'andres@andres.com';
$password = '123456';

echo "<h2>Paso 1: Cerrando todas las sesiones</h2>";

// Destruir sesión actual
session_destroy();
echo "<p style='color: green;'>✅ Sesión actual cerrada</p>";

// Limpiar todas las sesiones de la base de datos
try {
    $db->exec("DELETE FROM sesiones");
    echo "<p style='color: green;'>✅ Todas las sesiones eliminadas de la base de datos</p>";
} catch (Exception $e) {
    echo "<p style='color: orange;'>⚠️ No se pudo limpiar sesiones: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h2>Paso 2: Limpiando bloqueos</h2>";

try {
    $db->exec("DELETE FROM intentos_login");
    echo "<p style='color: green;'>✅ Intentos de login limpiados</p>";

    $db->exec("UPDATE usuarios SET bloqueado_hasta = NULL, intentos_fallidos = 0");
    echo "<p style='color: green;'>✅ Usuarios desbloqueados</p>";

    $db->exec("UPDATE administradores SET intentos_fallidos = 0");
    echo "<p style='color: green;'>✅ Administradores desbloqueados</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h2>Paso 3: Configurando usuario 'andres'</h2>";

$stmt = $db->prepare("SELECT * FROM usuarios WHERE username = ? OR email = ? LIMIT 1");
$stmt->execute([$username, $email]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if ($usuario) {
    echo "<p>✅ Usuario encontrado con ID: {$usuario['id']}</p>";

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
echo "<h2>Paso 4: Verificando configuración del usuario</h2>";

$stmt = $db->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$usuarioId]);
$usuarioFinal = $stmt->fetch(PDO::FETCH_ASSOC);

echo "<div style='background: #e7f3ff; padding: 20px; border-radius: 10px; border: 2px solid #007bff;'>";
echo "<h3>Datos del Usuario:</h3>";
echo "<table style='width: 100%; border-collapse: collapse;'>";
echo "<tr style='background: #f8f9fa;'><td style='padding: 10px; border: 1px solid #ddd;'><strong>ID</strong></td><td style='padding: 10px; border: 1px solid #ddd;'>{$usuarioFinal['id']}</td></tr>";
echo "<tr><td style='padding: 10px; border: 1px solid #ddd;'><strong>Username</strong></td><td style='padding: 10px; border: 1px solid #ddd;'>{$usuarioFinal['username']}</td></tr>";
echo "<tr style='background: #f8f9fa;'><td style='padding: 10px; border: 1px solid #ddd;'><strong>Email</strong></td><td style='padding: 10px; border: 1px solid #ddd;'>{$usuarioFinal['email']}</td></tr>";
echo "<tr><td style='padding: 10px; border: 1px solid #ddd;'><strong>Nombre Completo</strong></td><td style='padding: 10px; border: 1px solid #ddd;'>{$usuarioFinal['nombre_completo']}</td></tr>";
echo "<tr style='background: #f8f9fa;'><td style='padding: 10px; border: 1px solid #ddd;'><strong>Rol</strong></td><td style='padding: 10px; border: 1px solid #ddd;'><span style='background: #28a745; color: white; padding: 5px 10px; border-radius: 3px;'>{$usuarioFinal['rol']}</span></td></tr>";
echo "<tr><td style='padding: 10px; border: 1px solid #ddd;'><strong>Activo</strong></td><td style='padding: 10px; border: 1px solid #ddd;'>" . ($usuarioFinal['activo'] ? '✅ Sí' : '❌ No') . "</td></tr>";
echo "<tr style='background: #f8f9fa;'><td style='padding: 10px; border: 1px solid #ddd;'><strong>Bloqueado</strong></td><td style='padding: 10px; border: 1px solid #ddd;'>" . ($usuarioFinal['bloqueado_hasta'] ? '❌ ' . $usuarioFinal['bloqueado_hasta'] : '✅ No') . "</td></tr>";
echo "<tr><td style='padding: 10px; border: 1px solid #ddd;'><strong>Intentos Fallidos</strong></td><td style='padding: 10px; border: 1px solid #ddd;'>{$usuarioFinal['intentos_fallidos']}</td></tr>";
echo "</table>";
echo "</div>";

echo "<hr>";

// Verificar que el rol sea 'admin'
if ($usuarioFinal['rol'] !== 'admin') {
    echo "<div style='background: #f8d7da; padding: 20px; border-radius: 10px; border: 2px solid #dc3545;'>";
    echo "<h3>❌ ERROR: Rol Incorrecto</h3>";
    echo "<p>El rol del usuario es '<strong>{$usuarioFinal['rol']}</strong>' pero debería ser '<strong>admin</strong>'</p>";
    echo "<p>Corrigiendo...</p>";

    $stmt = $db->prepare("UPDATE usuarios SET rol = 'admin' WHERE id = ?");
    $stmt->execute([$usuarioId]);

    echo "<p style='color: green;'>✅ Rol corregido a 'admin'</p>";
    echo "</div>";
    echo "<hr>";
}

echo "<h2>Paso 5: Probando Login con Nueva Sesión</h2>";

// Iniciar nueva sesión
session_start();

$auth = new Auth($db);
$loginResult = $auth->login($username, $password);

if ($loginResult['success']) {
    echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; border: 2px solid #28a745;'>";
    echo "<h3>✅ Login Exitoso</h3>";
    echo "<p>Datos de la sesión creada:</p>";
    echo "<ul>";
    echo "<li><strong>user_id:</strong> " . ($_SESSION['user_id'] ?? 'NO DEFINIDO') . "</li>";
    echo "<li><strong>username:</strong> " . ($_SESSION['username'] ?? 'NO DEFINIDO') . "</li>";
    echo "<li><strong>rol:</strong> " . ($_SESSION['rol'] ?? 'NO DEFINIDO') . "</li>";
    echo "<li><strong>nombre_completo:</strong> " . ($_SESSION['nombre_completo'] ?? 'NO DEFINIDO') . "</li>";
    echo "<li><strong>email:</strong> " . ($_SESSION['email'] ?? 'NO DEFINIDO') . "</li>";
    echo "</ul>";

    // Verificar Auth::checkRole
    echo "<p><strong>Verificación de Auth::checkRole(['admin']):</strong> ";
    if (Auth::checkRole(['admin'])) {
        echo "<span style='color: green; font-weight: bold;'>✅ PASA</span>";
    } else {
        echo "<span style='color: red; font-weight: bold;'>❌ FALLA</span>";
    }
    echo "</p>";

    echo "<p><strong>Verificación de Auth::checkRole(['superadmin', 'admin', 'super_admin']):</strong> ";
    if (Auth::checkRole(['superadmin', 'admin', 'super_admin'])) {
        echo "<span style='color: green; font-weight: bold;'>✅ PASA</span>";
    } else {
        echo "<span style='color: red; font-weight: bold;'>❌ FALLA</span>";
    }
    echo "</p>";

    echo "</div>";

    // Cerrar sesión de prueba
    session_destroy();
} else {
    echo "<div style='background: #f8d7da; padding: 20px; border-radius: 10px; border: 2px solid #dc3545;'>";
    echo "<h3>❌ Error en Login</h3>";
    echo "<p><strong>Mensaje:</strong> " . $loginResult['message'] . "</p>";
    echo "</div>";
}

echo "<hr>";

// Resultado final
echo "<div style='background: #d1ecf1; padding: 30px; border-radius: 10px; border: 3px solid #17a2b8; text-align: center;'>";
echo "<h2 style='color: #0c5460; margin: 0;'>✅ TODO CONFIGURADO</h2>";
echo "<p style='font-size: 18px; margin: 20px 0;'>El usuario está listo. Ahora puedes iniciar sesión.</p>";
echo "<div style='background: white; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>Credenciales:</h3>";
echo "<p style='font-size: 20px;'><strong>Usuario:</strong> <code style='background: #e7f3ff; padding: 5px 15px; border-radius: 3px;'>$username</code></p>";
echo "<p style='font-size: 20px;'><strong>O Email:</strong> <code style='background: #e7f3ff; padding: 5px 15px; border-radius: 3px;'>$email</code></p>";
echo "<p style='font-size: 20px;'><strong>Contraseña:</strong> <code style='background: #e7f3ff; padding: 5px 15px; border-radius: 3px;'>$password</code></p>";
echo "</div>";
echo "<p style='color: #856404; background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<strong>⚠️ IMPORTANTE:</strong> Asegúrate de cerrar todas las pestañas del navegador antes de iniciar sesión nuevamente.";
echo "</p>";
echo "<a href='admin/login.php' style='display: inline-block; padding: 20px 40px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; font-size: 20px; font-weight: bold; margin-top: 20px;'>IR AL LOGIN AHORA →</a>";
echo "</div>";

echo "<hr>";
echo "<p style='text-align: center; color: #666;'>Script ejecutado: " . date('d/m/Y H:i:s') . "</p>";
echo "</div></body></html>";
