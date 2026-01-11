<?php
require_once 'config/config.php';

echo "<h1>Crear/Verificar Usuario Andres</h1>";
echo "<hr>";

$db = getDB();
$auth = new Auth($db);

// Credenciales solicitadas
$username = 'andres';
$email = 'andres@andres.com';
$password = '123456';

echo "<h2>Paso 1: Verificar si el usuario existe</h2>";

// Buscar por username
$stmt = $db->prepare("SELECT * FROM usuarios WHERE username = ? OR email = ? LIMIT 1");
$stmt->execute([$username, $email]);
$usuarioExistente = $stmt->fetch(PDO::FETCH_ASSOC);

if ($usuarioExistente) {
    echo "⚠️ Usuario encontrado en la base de datos:<br>";
    echo "<pre>";
    print_r($usuarioExistente);
    echo "</pre>";

    echo "<h2>Paso 2: Actualizar contraseña</h2>";

    // Actualizar la contraseña
    $newHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
    $stmt = $db->prepare("UPDATE usuarios SET password = ?, email = ? WHERE id = ?");
    $result = $stmt->execute([$newHash, $email, $usuarioExistente['id']]);

    if ($result) {
        echo "✅ Contraseña actualizada exitosamente<br>";
    } else {
        echo "❌ Error al actualizar contraseña<br>";
    }

    $usuarioId = $usuarioExistente['id'];
} else {
    echo "ℹ️ Usuario no existe. Creando nuevo usuario...<br>";

    echo "<h2>Paso 2: Crear usuario</h2>";

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

// Obtener el usuario actualizado
echo "<h2>Paso 3: Verificar usuario final</h2>";

$stmt = $db->prepare("SELECT * FROM usuarios WHERE id = ? LIMIT 1");
$stmt->execute([$usuarioId]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if ($usuario) {
    echo "<div style='background: #d4edda; padding: 20px; border-radius: 5px; border: 1px solid #c3e6cb;'>";
    echo "<h3>✅ Usuario Configurado Correctamente</h3>";
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr><th>Campo</th><th>Valor</th></tr>";
    echo "<tr><td><strong>ID</strong></td><td>{$usuario['id']}</td></tr>";
    echo "<tr><td><strong>Username</strong></td><td>{$usuario['username']}</td></tr>";
    echo "<tr><td><strong>Email</strong></td><td>{$usuario['email']}</td></tr>";
    echo "<tr><td><strong>Nombre Completo</strong></td><td>{$usuario['nombre_completo']}</td></tr>";
    echo "<tr><td><strong>Rol</strong></td><td>{$usuario['rol']}</td></tr>";
    echo "<tr><td><strong>Estado</strong></td><td>" . ($usuario['activo'] ? 'Activo' : 'Inactivo') . "</td></tr>";
    echo "</table>";
    echo "</div>";
}

echo "<hr>";

// Probar login
echo "<h2>Paso 4: Probar Login</h2>";

$loginResult = $auth->login($username, $password);

if ($loginResult['success']) {
    echo "<div style='background: #d4edda; padding: 20px; border-radius: 5px; border: 1px solid #c3e6cb;'>";
    echo "<h3>✅ Login Exitoso</h3>";
    echo "<p>El usuario puede iniciar sesión correctamente.</p>";
    echo "</div>";

    // Limpiar sesión
    session_destroy();
} else {
    echo "<div style='background: #f8d7da; padding: 20px; border-radius: 5px; border: 1px solid #f5c6cb;'>";
    echo "<h3>❌ Error en Login</h3>";
    echo "<p>" . $loginResult['message'] . "</p>";
    echo "</div>";
}

echo "<hr>";

// Verificar eventos asignados
echo "<h2>Paso 5: Verificar Eventos Asignados</h2>";

$stmt = $db->prepare("
    SELECT ea.*, e.nombre as evento_nombre, e.estado
    FROM eventos_administradores ea
    INNER JOIN eventos e ON ea.evento_id = e.id
    WHERE ea.usuario_id = ? AND ea.activo = 1
");
$stmt->execute([$usuarioId]);
$eventosAsignados = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($eventosAsignados)) {
    echo "<div style='background: #fff3cd; padding: 20px; border-radius: 5px; border: 1px solid #ffeaa7;'>";
    echo "<h3>⚠️ Sin Eventos Asignados</h3>";
    echo "<p>El usuario no tiene eventos asignados todavía.</p>";
    echo "<p>Para asignar eventos:</p>";
    echo "<ol>";
    echo "<li>Inicia sesión como Super Admin</li>";
    echo "<li>Ve a 'Gestión de Usuarios'</li>";
    echo "<li>Haz clic en el icono de calendario junto al usuario 'andres'</li>";
    echo "<li>Selecciona un evento y haz clic en 'Asignar'</li>";
    echo "</ol>";
    echo "</div>";
} else {
    echo "<div style='background: #d4edda; padding: 20px; border-radius: 5px; border: 1px solid #c3e6cb;'>";
    echo "<h3>✅ Eventos Asignados</h3>";
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr><th>Evento</th><th>Estado</th><th>Fecha Asignación</th></tr>";
    foreach ($eventosAsignados as $evento) {
        echo "<tr>";
        echo "<td>{$evento['evento_nombre']}</td>";
        echo "<td>{$evento['estado']}</td>";
        echo "<td>" . date('d/m/Y H:i', strtotime($evento['fecha_asignacion'])) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
}

echo "<hr>";

// Resumen final
echo "<div style='background: #d1ecf1; padding: 20px; border-radius: 5px; border: 1px solid #bee5eb;'>";
echo "<h2>📋 Resumen Final</h2>";
echo "<h3>Credenciales para Login:</h3>";
echo "<ul style='font-size: 18px;'>";
echo "<li><strong>URL:</strong> <a href='admin/login.php' target='_blank'>admin/login.php</a></li>";
echo "<li><strong>Usuario:</strong> <code style='background: #fff; padding: 5px; border: 1px solid #ccc;'>$username</code></li>";
echo "<li><strong>Email:</strong> <code style='background: #fff; padding: 5px; border: 1px solid #ccc;'>$email</code></li>";
echo "<li><strong>Contraseña:</strong> <code style='background: #fff; padding: 5px; border: 1px solid #ccc;'>$password</code></li>";
echo "<li><strong>Rol:</strong> {$usuario['rol']}</li>";
echo "</ul>";

echo "<h3>Puedes iniciar sesión con:</h3>";
echo "<ul>";
echo "<li>Username: <strong>$username</strong></li>";
echo "<li>O Email: <strong>$email</strong></li>";
echo "</ul>";
echo "</div>";

echo "<br>";
echo "<div style='text-align: center; padding: 20px; background: #e7f3ff; border-radius: 5px;'>";
echo "<h3>🔗 Siguiente Paso</h3>";
echo "<a href='admin/login.php' style='display: inline-block; padding: 15px 30px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; font-size: 18px;'>Ir al Login</a>";
echo "</div>";

echo "<hr>";
echo "<p style='text-align: center; color: #666;'>Script ejecutado - " . date('d/m/Y H:i:s') . "</p>";
