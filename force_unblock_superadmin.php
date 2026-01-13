<?php
require_once 'config/config.php';

echo "<!DOCTYPE html>";
echo "<html lang='es'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Forzar Desbloqueo Superadmin</title>";
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
echo ".warning { background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 15px; margin: 10px 0; border-radius: 5px; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<h1>🔧 Forzar Desbloqueo del Superadmin</h1>";
echo "<p><strong>Nota:</strong> Este script fuerza el desbloqueo ignorando la fecha del servidor.</p>";
echo "<hr>";

$db = getDB();

// Forzar desbloqueo directo en la base de datos
echo "<h2>🔓 Paso 1: Forzar Desbloqueo en Base de Datos</h2>";

try {
    // Establecer bloqueado_hasta a una fecha pasada (ayer)
    $ayer = date('Y-m-d H:i:s', strtotime('-1 day'));

    $stmt = $db->prepare("
        UPDATE usuarios
        SET bloqueado_hasta = ?,
            intentos_fallidos = 0,
            activo = 1
        WHERE username = 'superadmin'
    ");

    $result = $stmt->execute([$ayer]);

    if ($result) {
        echo "<div class='success'>";
        echo "<h3>✅ Usuario Forzado a Desbloquear</h3>";
        echo "<p>Campo 'bloqueado_hasta' establecido a: <strong>{$ayer}</strong></p>";
        echo "<p>Intentos fallidos reseteados a 0</p>";
        echo "<p>Usuario activado</p>";
        echo "</div>";
    } else {
        echo "<div class='error'>";
        echo "<h3>❌ Error al actualizar</h3>";
        echo "</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<h3>❌ Error en la base de datos</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}

// Limpiar intentos fallidos
echo "<h2>🧹 Paso 2: Limpiar Intentos Fallidos</h2>";

try {
    $stmt = $db->prepare("
        DELETE FROM intentos_login
        WHERE email = 'superadmin' OR email = 'superadmin@sistema.com'
    ");
    $stmt->execute();
    $deleted = $stmt->rowCount();

    echo "<div class='success'>";
    echo "<h3>✅ Intentos Fallidos Limpiados</h3>";
    echo "<p>Registros eliminados: <strong>{$deleted}</strong></p>";
    echo "</div>";
} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<p>Error al limpiar intentos: " . $e->getMessage() . "</p>";
    echo "</div>";
}

// Verificar estado final
echo "<h2>✅ Paso 3: Verificación Final</h2>";

$stmt = $db->prepare("SELECT * FROM usuarios WHERE username = 'superadmin' LIMIT 1");
$stmt->execute();
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if ($usuario) {
    echo "<div class='info'>";
    echo "<h3>Estado Final del Usuario:</h3>";
    echo "<table>";
    echo "<tr><th>Campo</th><th>Valor</th><th>Estado</th></tr>";
    echo "<tr><td>Username</td><td><strong>{$usuario['username']}</strong></td><td>✅</td></tr>";
    echo "<tr><td>Email</td><td>{$usuario['email']}</td><td>✅</td></tr>";
    echo "<tr><td>Rol</td><td><strong>{$usuario['rol']}</strong></td><td>✅</td></tr>";
    echo "<tr><td>Activo</td><td>" . ($usuario['activo'] ? 'Sí' : 'No') . "</td><td>" . ($usuario['activo'] ? '✅' : '❌') . "</td></tr>";
    echo "<tr><td>Bloqueado Hasta</td><td>{$usuario['bloqueado_hasta']}</td><td>" . (strtotime($usuario['bloqueado_hasta']) < time() ? '✅ LIBRE' : '❌ BLOQUEADO') . "</td></tr>";
    echo "<tr><td>Intentos Fallidos</td><td>{$usuario['intentos_fallidos']}</td><td>" . ($usuario['intentos_fallidos'] == 0 ? '✅' : '⚠️') . "</td></tr>";
    echo "</table>";
    echo "</div>";
}

// Probar login
echo "<h2>🔐 Paso 4: Prueba de Login</h2>";

try {
    $auth = new Auth($db);

    $loginResult = $auth->login('superadmin', 'superadmin123');

    if ($loginResult['success']) {
        echo "<div class='success'>";
        echo "<h3>✅ ¡Login Exitoso!</h3>";
        echo "<p>El usuario puede iniciar sesión correctamente.</p>";
        echo "</div>";

        // Cerrar sesión
        // session_destroy();
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

echo "<div class='warning'>";
echo "<h3>⚠️ IMPORTANTE: Problema de Fecha del Servidor</h3>";
echo "<p>El servidor muestra la fecha como 2026, lo cual es incorrecto.</p>";
echo "<p>Para una solución permanente, corrige la fecha del sistema operativo.</p>";
echo "<p>En Windows: Configuración > Hora e idioma > Fecha y hora</p>";
echo "</div>";

echo "<hr>";
echo "<p style='text-align: center; color: #666;'>";
echo "Script ejecutado - " . date('d/m/Y H:i:s') . "<br>";
echo "Usuario forzado a desbloquear";
echo "</p>";

echo "</body>";
echo "</html>";
