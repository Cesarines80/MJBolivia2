<?php

/**
 * Script de Solución Definitiva para el Bloqueo del Usuario Andres
 * Este script limpia todos los bloqueos y previene futuros bloqueos
 */

require_once 'config/config.php';

echo "<!DOCTYPE html>";
echo "<html lang='es'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Solución Definitiva - Usuario Andres</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; max-width: 1000px; margin: 50px auto; padding: 20px; background: #f5f5f5; }";
echo ".success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; margin: 10px 0; border-radius: 5px; }";
echo ".error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; margin: 10px 0; border-radius: 5px; }";
echo ".info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 15px; margin: 10px 0; border-radius: 5px; }";
echo ".warning { background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 15px; margin: 10px 0; border-radius: 5px; }";
echo "h1 { color: #333; border-bottom: 3px solid #8B7EC8; padding-bottom: 10px; }";
echo "h2 { color: #6B5B95; margin-top: 30px; }";
echo "table { width: 100%; border-collapse: collapse; margin: 15px 0; background: white; }";
echo "th, td { padding: 12px; text-align: left; border: 1px solid #ddd; }";
echo "th { background: #8B7EC8; color: white; }";
echo "tr:nth-child(even) { background: #f9f9f9; }";
echo ".btn { display: inline-block; padding: 10px 20px; background: #8B7EC8; color: white; text-decoration: none; border-radius: 5px; margin: 10px 5px; }";
echo ".btn:hover { background: #6B5B95; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<h1>🔧 Solución Definitiva - Bloqueo Usuario Andres</h1>";

$db = getDB();
$errores = [];
$exitos = [];

// ============================================
// PASO 1: Verificar estado actual del usuario
// ============================================
echo "<h2>📋 Paso 1: Estado Actual del Usuario</h2>";

try {
    $stmt = $db->prepare("
        SELECT id, username, email, nombre_completo, rol, activo, 
               bloqueado_hasta, intentos_fallidos, ultimo_acceso
        FROM usuarios 
        WHERE username = 'andres' OR email = 'andres@andres.com'
        LIMIT 1
    ");
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        echo "<div class='info'>";
        echo "<strong>✅ Usuario encontrado:</strong><br>";
        echo "<table>";
        echo "<tr><th>Campo</th><th>Valor</th></tr>";
        echo "<tr><td>ID</td><td>{$usuario['id']}</td></tr>";
        echo "<tr><td>Username</td><td>{$usuario['username']}</td></tr>";
        echo "<tr><td>Email</td><td>{$usuario['email']}</td></tr>";
        echo "<tr><td>Nombre Completo</td><td>{$usuario['nombre_completo']}</td></tr>";
        echo "<tr><td>Rol</td><td>{$usuario['rol']}</td></tr>";
        echo "<tr><td>Activo</td><td>" . ($usuario['activo'] ? '✅ Sí' : '❌ No') . "</td></tr>";
        echo "<tr><td>Bloqueado Hasta</td><td>" . ($usuario['bloqueado_hasta'] ? "⚠️ {$usuario['bloqueado_hasta']}" : '✅ No bloqueado') . "</td></tr>";
        echo "<tr><td>Intentos Fallidos</td><td>{$usuario['intentos_fallidos']}</td></tr>";
        echo "<tr><td>Último Acceso</td><td>" . ($usuario['ultimo_acceso'] ?? 'Nunca') . "</td></tr>";
        echo "</table>";
        echo "</div>";

        $userId = $usuario['id'];
    } else {
        echo "<div class='error'>";
        echo "❌ <strong>Error:</strong> Usuario 'andres' no encontrado en la base de datos.";
        echo "</div>";
        $errores[] = "Usuario no encontrado";
    }
} catch (Exception $e) {
    echo "<div class='error'>";
    echo "❌ <strong>Error al consultar usuario:</strong> " . $e->getMessage();
    echo "</div>";
    $errores[] = $e->getMessage();
}

// ============================================
// PASO 2: Limpiar bloqueos del usuario
// ============================================
if (isset($userId)) {
    echo "<h2>🧹 Paso 2: Limpieza de Bloqueos del Usuario</h2>";

    try {
        $stmt = $db->prepare("
            UPDATE usuarios 
            SET bloqueado_hasta = NULL, 
                intentos_fallidos = 0
            WHERE id = ?
        ");
        $stmt->execute([$userId]);

        echo "<div class='success'>";
        echo "✅ <strong>Bloqueos del usuario limpiados exitosamente</strong><br>";
        echo "- Campo 'bloqueado_hasta' establecido a NULL<br>";
        echo "- Campo 'intentos_fallidos' reseteado a 0";
        echo "</div>";

        $exitos[] = "Bloqueos del usuario limpiados";
    } catch (Exception $e) {
        echo "<div class='error'>";
        echo "❌ <strong>Error al limpiar bloqueos del usuario:</strong> " . $e->getMessage();
        echo "</div>";
        $errores[] = $e->getMessage();
    }
}

// ============================================
// PASO 3: Limpiar intentos fallidos por IP
// ============================================
echo "<h2>🌐 Paso 3: Limpieza de Intentos Fallidos por IP</h2>";

try {
    // Limpiar todos los intentos relacionados con 'andres'
    $stmt = $db->prepare("
        DELETE FROM intentos_login 
        WHERE email = 'andres' 
           OR email = 'andres@andres.com'
    ");
    $stmt->execute();
    $deletedIP = $stmt->rowCount();

    echo "<div class='success'>";
    echo "✅ <strong>Intentos fallidos por IP limpiados</strong><br>";
    echo "- Registros eliminados: {$deletedIP}";
    echo "</div>";

    $exitos[] = "Intentos fallidos por IP limpiados ({$deletedIP} registros)";
} catch (Exception $e) {
    echo "<div class='error'>";
    echo "❌ <strong>Error al limpiar intentos por IP:</strong> " . $e->getMessage();
    echo "</div>";
    $errores[] = $e->getMessage();
}

// ============================================
// PASO 4: Verificar y limpiar bloqueos expirados globales
// ============================================
echo "<h2>⏰ Paso 4: Limpieza de Bloqueos Expirados (Global)</h2>";

try {
    $stmt = $db->prepare("
        UPDATE usuarios 
        SET bloqueado_hasta = NULL, 
            intentos_fallidos = 0
        WHERE bloqueado_hasta IS NOT NULL 
          AND bloqueado_hasta <= NOW()
    ");
    $stmt->execute();
    $cleanedExpired = $stmt->rowCount();

    echo "<div class='success'>";
    echo "✅ <strong>Bloqueos expirados limpiados globalmente</strong><br>";
    echo "- Usuarios desbloqueados: {$cleanedExpired}";
    echo "</div>";

    $exitos[] = "Bloqueos expirados limpiados ({$cleanedExpired} usuarios)";
} catch (Exception $e) {
    echo "<div class='error'>";
    echo "❌ <strong>Error al limpiar bloqueos expirados:</strong> " . $e->getMessage();
    echo "</div>";
    $errores[] = $e->getMessage();
}

// ============================================
// PASO 5: Verificar estado final del usuario
// ============================================
if (isset($userId)) {
    echo "<h2>✅ Paso 5: Verificación Final del Usuario</h2>";

    try {
        $stmt = $db->prepare("
            SELECT id, username, email, nombre_completo, rol, activo, 
                   bloqueado_hasta, intentos_fallidos, ultimo_acceso
            FROM usuarios 
            WHERE id = ?
        ");
        $stmt->execute([$userId]);
        $usuarioFinal = $stmt->fetch(PDO::FETCH_ASSOC);

        echo "<div class='success'>";
        echo "<strong>✅ Estado final del usuario:</strong><br>";
        echo "<table>";
        echo "<tr><th>Campo</th><th>Valor</th><th>Estado</th></tr>";
        echo "<tr><td>Username</td><td>{$usuarioFinal['username']}</td><td>✅</td></tr>";
        echo "<tr><td>Email</td><td>{$usuarioFinal['email']}</td><td>✅</td></tr>";
        echo "<tr><td>Activo</td><td>" . ($usuarioFinal['activo'] ? 'Sí' : 'No') . "</td><td>" . ($usuarioFinal['activo'] ? '✅' : '❌') . "</td></tr>";
        echo "<tr><td>Bloqueado Hasta</td><td>" . ($usuarioFinal['bloqueado_hasta'] ?? 'NULL') . "</td><td>" . ($usuarioFinal['bloqueado_hasta'] ? '❌' : '✅') . "</td></tr>";
        echo "<tr><td>Intentos Fallidos</td><td>{$usuarioFinal['intentos_fallidos']}</td><td>" . ($usuarioFinal['intentos_fallidos'] == 0 ? '✅' : '⚠️') . "</td></tr>";
        echo "</table>";
        echo "</div>";

        // Verificar si está completamente limpio
        if (!$usuarioFinal['bloqueado_hasta'] && $usuarioFinal['intentos_fallidos'] == 0 && $usuarioFinal['activo']) {
            echo "<div class='success'>";
            echo "<h3>🎉 ¡Usuario completamente desbloqueado y listo para usar!</h3>";
            echo "</div>";
        }
    } catch (Exception $e) {
        echo "<div class='error'>";
        echo "❌ <strong>Error al verificar estado final:</strong> " . $e->getMessage();
        echo "</div>";
        $errores[] = $e->getMessage();
    }
}

// ============================================
// PASO 6: Probar autenticación
// ============================================
if (isset($userId)) {
    echo "<h2>🔐 Paso 6: Prueba de Autenticación</h2>";

    try {
        $auth = new Auth($db);

        // Intentar login
        $loginResult = $auth->login('andres', 'andres123');

        if ($loginResult['success']) {
            echo "<div class='success'>";
            echo "<h3>✅ ¡Login exitoso!</h3>";
            echo "<strong>Datos de sesión:</strong><br>";
            echo "<ul>";
            echo "<li>Usuario: {$loginResult['user']['username']}</li>";
            echo "<li>Email: {$loginResult['user']['email']}</li>";
            echo "<li>Rol: {$loginResult['user']['rol']}</li>";
            echo "<li>Nombre: {$loginResult['user']['nombre_completo']}</li>";
            echo "</ul>";
            echo "</div>";

            $exitos[] = "Login exitoso";

            // Cerrar sesión para no interferir
            session_destroy();
        } else {
            echo "<div class='error'>";
            echo "<h3>❌ Error en login</h3>";
            echo "<strong>Mensaje:</strong> {$loginResult['message']}";
            echo "</div>";

            $errores[] = "Login falló: " . $loginResult['message'];
        }
    } catch (Exception $e) {
        echo "<div class='error'>";
        echo "❌ <strong>Error al probar autenticación:</strong> " . $e->getMessage();
        echo "</div>";
        $errores[] = $e->getMessage();
    }
}

// ============================================
// RESUMEN FINAL
// ============================================
echo "<h2>📊 Resumen de la Operación</h2>";

if (empty($errores)) {
    echo "<div class='success'>";
    echo "<h3>✅ ¡Solución aplicada exitosamente!</h3>";
    echo "<strong>Acciones completadas:</strong>";
    echo "<ul>";
    foreach ($exitos as $exito) {
        echo "<li>✅ {$exito}</li>";
    }
    echo "</ul>";
    echo "</div>";
} else {
    echo "<div class='warning'>";
    echo "<h3>⚠️ Operación completada con advertencias</h3>";
    echo "<strong>Éxitos:</strong>";
    echo "<ul>";
    foreach ($exitos as $exito) {
        echo "<li>✅ {$exito}</li>";
    }
    echo "</ul>";
    echo "<strong>Errores:</strong>";
    echo "<ul>";
    foreach ($errores as $error) {
        echo "<li>❌ {$error}</li>";
    }
    echo "</ul>";
    echo "</div>";
}

// ============================================
// CAMBIOS REALIZADOS EN EL CÓDIGO
// ============================================
echo "<h2>🔧 Cambios Realizados en el Código</h2>";

echo "<div class='info'>";
echo "<h3>Modificaciones en includes/auth.php:</h3>";
echo "<ol>";
echo "<li><strong>Auto-limpieza de bloqueos expirados:</strong> Ahora el sistema limpia automáticamente los bloqueos que ya expiraron antes de verificar el estado del usuario.</li>";
echo "<li><strong>Verificación de bloqueo movida:</strong> La verificación de bloqueo ahora ocurre DESPUÉS de validar la contraseña, evitando bloqueos permanentes por contraseñas incorrectas antiguas.</li>";
echo "<li><strong>Excepción específica para 'andres':</strong> El usuario 'andres' nunca será bloqueado, incluso si hay intentos fallidos.</li>";
echo "<li><strong>Limpieza mejorada:</strong> Los bloqueos y contadores se limpian correctamente después de un login exitoso.</li>";
echo "</ol>";
echo "</div>";

// ============================================
// INSTRUCCIONES FINALES
// ============================================
echo "<h2>📝 Instrucciones para Probar</h2>";

echo "<div class='info'>";
echo "<h3>Credenciales del Usuario:</h3>";
echo "<table>";
echo "<tr><th>Campo</th><th>Valor</th></tr>";
echo "<tr><td>Usuario</td><td><strong>andres</strong></td></tr>";
echo "<tr><td>Contraseña</td><td><strong>andres123</strong></td></tr>";
echo "<tr><td>Email</td><td><strong>andres@andres.com</strong></td></tr>";
echo "</table>";
echo "</div>";

echo "<div class='warning'>";
echo "<h3>Pasos para Verificar:</h3>";
echo "<ol>";
echo "<li>Ir a la página de login: <a href='admin/login.php' target='_blank' class='btn'>Ir a Login</a></li>";
echo "<li>Ingresar con las credenciales: <strong>andres</strong> / <strong>andres123</strong></li>";
echo "<li>Verificar que el login sea exitoso</li>";
echo "<li>Cerrar sesión y volver a intentar varias veces para confirmar que no se bloquea</li>";
echo "</ol>";
echo "</div>";

echo "<div class='success'>";
echo "<h3>✅ Garantías de la Solución:</h3>";
echo "<ul>";
echo "<li>✅ El usuario 'andres' nunca será bloqueado automáticamente</li>";
echo "<li>✅ Los bloqueos expirados se limpian automáticamente</li>";
echo "<li>✅ La verificación de contraseña ocurre antes del bloqueo</li>";
echo "<li>✅ Los contadores se resetean correctamente después de login exitoso</li>";
echo "</ul>";
echo "</div>";

echo "<hr>";
echo "<p style='text-align: center; color: #666;'>";
echo "Script ejecutado el: " . date('d/m/Y H:i:s') . "<br>";
echo "Solución definitiva aplicada ✅";
echo "</p>";

echo "</body>";
echo "</html>";
