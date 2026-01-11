<?php
require_once 'config/config.php';

echo "<!DOCTYPE html>";
echo "<html lang='es'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Crear Super Administrador</title>";
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

echo "<h1>🔐 Crear Usuario Super Administrador</h1>";
echo "<hr>";

$db = getDB();
$auth = new Auth($db);

// Datos del super administrador
$username = 'superadmin';
$password = 'superadmin123';
$email = 'superadmin@sistema.com';
$nombreCompleto = 'Super Administrador';
$rol = 'super_admin';

echo "<h2>📋 Paso 1: Verificar si el usuario ya existe</h2>";

// Verificar si ya existe
$stmt = $db->prepare("SELECT * FROM usuarios WHERE username = ? OR email = ? LIMIT 1");
$stmt->execute([$username, $email]);
$usuarioExistente = $stmt->fetch(PDO::FETCH_ASSOC);

if ($usuarioExistente) {
    echo "<div class='warning'>";
    echo "<h3>⚠️ Usuario ya existe</h3>";
    echo "<p>Se encontró un usuario con el username '<strong>{$username}</strong>' o email '<strong>{$email}</strong>'</p>";
    echo "<table>";
    echo "<tr><th>Campo</th><th>Valor</th></tr>";
    echo "<tr><td>ID</td><td>{$usuarioExistente['id']}</td></tr>";
    echo "<tr><td>Username</td><td>{$usuarioExistente['username']}</td></tr>";
    echo "<tr><td>Email</td><td>{$usuarioExistente['email']}</td></tr>";
    echo "<tr><td>Nombre</td><td>{$usuarioExistente['nombre_completo']}</td></tr>";
    echo "<tr><td>Rol Actual</td><td><strong>{$usuarioExistente['rol']}</strong></td></tr>";
    echo "<tr><td>Activo</td><td>" . ($usuarioExistente['activo'] ? '✅ Sí' : '❌ No') . "</td></tr>";
    echo "</table>";
    echo "</div>";

    // Actualizar el rol a super_admin si no lo es
    if ($usuarioExistente['rol'] !== 'super_admin') {
        echo "<h2>🔄 Paso 2: Actualizar rol a Super Administrador</h2>";

        try {
            $stmt = $db->prepare("
                UPDATE usuarios 
                SET rol = 'super_admin',
                    activo = 1,
                    bloqueado_hasta = NULL,
                    intentos_fallidos = 0
                WHERE id = ?
            ");
            $result = $stmt->execute([$usuarioExistente['id']]);

            if ($result) {
                echo "<div class='success'>";
                echo "<h3>✅ Rol actualizado exitosamente</h3>";
                echo "<p>El usuario '<strong>{$username}</strong>' ahora tiene rol de <strong>Super Administrador</strong></p>";
                echo "</div>";

                // Obtener datos actualizados
                $stmt = $db->prepare("SELECT * FROM usuarios WHERE id = ?");
                $stmt->execute([$usuarioExistente['id']]);
                $usuarioActualizado = $stmt->fetch(PDO::FETCH_ASSOC);

                echo "<h2>✅ Datos Finales del Usuario</h2>";
                echo "<table>";
                echo "<tr><th>Campo</th><th>Valor</th></tr>";
                echo "<tr><td>ID</td><td>{$usuarioActualizado['id']}</td></tr>";
                echo "<tr><td>Username</td><td><strong>{$usuarioActualizado['username']}</strong></td></tr>";
                echo "<tr><td>Email</td><td>{$usuarioActualizado['email']}</td></tr>";
                echo "<tr><td>Nombre</td><td>{$usuarioActualizado['nombre_completo']}</td></tr>";
                echo "<tr><td>Rol</td><td><strong style='color: green;'>{$usuarioActualizado['rol']}</strong></td></tr>";
                echo "<tr><td>Activo</td><td>✅ Sí</td></tr>";
                echo "</table>";
            }
        } catch (Exception $e) {
            echo "<div class='error'>";
            echo "<h3>❌ Error al actualizar</h3>";
            echo "<p>" . $e->getMessage() . "</p>";
            echo "</div>";
        }
    } else {
        echo "<div class='success'>";
        echo "<h3>✅ El usuario ya tiene rol de Super Administrador</h3>";
        echo "</div>";
    }
} else {
    echo "<div class='info'>";
    echo "<p>✅ No existe un usuario con ese username o email. Procediendo a crear...</p>";
    echo "</div>";

    echo "<h2>➕ Paso 2: Crear nuevo Super Administrador</h2>";

    try {
        // Crear el usuario usando el método register de Auth
        $result = $auth->register([
            'username' => $username,
            'password' => $password,
            'email' => $email,
            'nombre_completo' => $nombreCompleto,
            'rol' => $rol,
            'activo' => 1
        ]);

        if ($result['success']) {
            echo "<div class='success'>";
            echo "<h3>✅ Super Administrador creado exitosamente</h3>";
            echo "<p>El usuario ha sido creado con todos los permisos necesarios.</p>";
            echo "</div>";

            // Obtener datos del usuario creado
            $stmt = $db->prepare("SELECT * FROM usuarios WHERE id = ?");
            $stmt->execute([$result['user_id']]);
            $nuevoUsuario = $stmt->fetch(PDO::FETCH_ASSOC);

            echo "<h2>✅ Datos del Nuevo Usuario</h2>";
            echo "<table>";
            echo "<tr><th>Campo</th><th>Valor</th></tr>";
            echo "<tr><td>ID</td><td>{$nuevoUsuario['id']}</td></tr>";
            echo "<tr><td>Username</td><td><strong>{$nuevoUsuario['username']}</strong></td></tr>";
            echo "<tr><td>Email</td><td>{$nuevoUsuario['email']}</td></tr>";
            echo "<tr><td>Nombre</td><td>{$nuevoUsuario['nombre_completo']}</td></tr>";
            echo "<tr><td>Rol</td><td><strong style='color: green;'>{$nuevoUsuario['rol']}</strong></td></tr>";
            echo "<tr><td>Activo</td><td>✅ Sí</td></tr>";
            echo "<tr><td>Fecha Creación</td><td>{$nuevoUsuario['fecha_creacion']}</td></tr>";
            echo "</table>";
        } else {
            echo "<div class='error'>";
            echo "<h3>❌ Error al crear usuario</h3>";
            echo "<p>{$result['message']}</p>";
            echo "</div>";
        }
    } catch (Exception $e) {
        echo "<div class='error'>";
        echo "<h3>❌ Error</h3>";
        echo "<p>" . $e->getMessage() . "</p>";
        echo "</div>";
    }
}

// Mostrar todos los usuarios con rol super_admin
echo "<h2>👥 Paso 3: Usuarios con Rol Super Administrador</h2>";

try {
    $stmt = $db->query("SELECT * FROM usuarios WHERE rol = 'super_admin' ORDER BY id");
    $superadmins = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($superadmins)) {
        echo "<div class='warning'>";
        echo "<p>⚠️ No se encontraron usuarios con rol 'super_admin'</p>";
        echo "</div>";
    } else {
        echo "<div class='info'>";
        echo "<p>Se encontraron <strong>" . count($superadmins) . "</strong> usuario(s) con rol Super Administrador:</p>";
        echo "</div>";

        echo "<table>";
        echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Nombre</th><th>Activo</th><th>Fecha Creación</th></tr>";
        foreach ($superadmins as $sa) {
            echo "<tr>";
            echo "<td>{$sa['id']}</td>";
            echo "<td><strong>{$sa['username']}</strong></td>";
            echo "<td>{$sa['email']}</td>";
            echo "<td>{$sa['nombre_completo']}</td>";
            echo "<td>" . ($sa['activo'] ? '✅' : '❌') . "</td>";
            echo "<td>" . date('d/m/Y H:i', strtotime($sa['fecha_creacion'])) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<p>Error al consultar usuarios: " . $e->getMessage() . "</p>";
    echo "</div>";
}

// Credenciales finales
echo "<h2>🔑 Credenciales de Acceso</h2>";

echo "<div class='success'>";
echo "<h3>Credenciales del Super Administrador:</h3>";
echo "<table>";
echo "<tr><th>Campo</th><th>Valor</th></tr>";
echo "<tr><td>Usuario</td><td><strong style='font-size: 1.2em;'>superadmin</strong></td></tr>";
echo "<tr><td>Contraseña</td><td><strong style='font-size: 1.2em;'>superadmin123</strong></td></tr>";
echo "<tr><td>Email</td><td>superadmin@sistema.com</td></tr>";
echo "<tr><td>Rol</td><td><strong style='color: green;'>super_admin</strong></td></tr>";
echo "</table>";
echo "</div>";

echo "<div class='info'>";
echo "<h3>📝 Permisos del Super Administrador:</h3>";
echo "<ul>";
echo "<li>✅ Crear eventos</li>";
echo "<li>✅ Editar eventos</li>";
echo "<li>✅ <strong>Eliminar eventos</strong></li>";
echo "<li>✅ Gestionar usuarios</li>";
echo "<li>✅ Ver todos los eventos</li>";
echo "<li>✅ Gestionar inscripciones</li>";
echo "<li>✅ Acceso completo al sistema</li>";
echo "</ul>";
echo "</div>";

echo "<div class='warning'>";
echo "<h3>⚠️ Importante:</h3>";
echo "<ol>";
echo "<li>Guarda estas credenciales en un lugar seguro</li>";
echo "<li>Cambia la contraseña después del primer inicio de sesión</li>";
echo "<li>No compartas estas credenciales con usuarios no autorizados</li>";
echo "<li>El Super Administrador tiene acceso completo al sistema</li>";
echo "</ol>";
echo "</div>";

echo "<div class='info'>";
echo "<h3>🚀 Próximos Pasos:</h3>";
echo "<ol>";
echo "<li>Ir a: <a href='admin/login.php' target='_blank' style='color: #0c5460; font-weight: bold;'>admin/login.php</a></li>";
echo "<li>Iniciar sesión con: <strong>superadmin</strong> / <strong>superadmin123</strong></li>";
echo "<li>Ir a la sección de Eventos</li>";
echo "<li>Ahora podrás eliminar eventos sin restricciones</li>";
echo "</ol>";
echo "</div>";

echo "<hr>";
echo "<p style='text-align: center; color: #666;'>";
echo "Script ejecutado - " . date('d/m/Y H:i:s');
echo "</p>";

echo "</body>";
echo "</html>";
