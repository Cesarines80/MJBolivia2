<?php

/**
 * Prueba Final de Login - Usuario Andres
 * Verifica que el usuario puede iniciar sesión sin bloqueos
 */

require_once 'config/config.php';

echo "<!DOCTYPE html>";
echo "<html lang='es'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<title>Prueba Final - Login Usuario Andres</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }";
echo ".success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; margin: 10px 0; border-radius: 5px; }";
echo ".error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; margin: 10px 0; border-radius: 5px; }";
echo ".info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 15px; margin: 10px 0; border-radius: 5px; }";
echo "h1 { color: #333; border-bottom: 3px solid #8B7EC8; padding-bottom: 10px; }";
echo "h2 { color: #6B5B95; margin-top: 30px; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<h1>🧪 Prueba Final - Login Usuario Andres</h1>";

$db = getDB();
$auth = new Auth($db);

// Prueba 1: Login con contraseña correcta (123456)
echo "<h2>Prueba 1: Login con Contraseña Correcta</h2>";
echo "<p><strong>Usuario:</strong> andres</p>";
echo "<p><strong>Contraseña:</strong> 123456</p>";

$result1 = $auth->login('andres', '123456');

if ($result1['success']) {
    echo "<div class='success'>";
    echo "<h3>✅ ¡Login Exitoso!</h3>";
    echo "<p><strong>Datos del usuario:</strong></p>";
    echo "<ul>";
    echo "<li>ID: {$result1['user']['id']}</li>";
    echo "<li>Username: {$result1['user']['username']}</li>";
    echo "<li>Email: {$result1['user']['email']}</li>";
    echo "<li>Nombre: {$result1['user']['nombre_completo']}</li>";
    echo "<li>Rol: {$result1['user']['rol']}</li>";
    echo "</ul>";
    echo "</div>";

    // Cerrar sesión
    session_destroy();
} else {
    echo "<div class='error'>";
    echo "<h3>❌ Error en Login</h3>";
    echo "<p><strong>Mensaje:</strong> {$result1['message']}</p>";
    echo "</div>";
}

echo "<hr>";

// Prueba 2: Verificar que no hay bloqueos
echo "<h2>Prueba 2: Verificar Estado del Usuario</h2>";

$stmt = $db->prepare("
    SELECT username, email, activo, bloqueado_hasta, intentos_fallidos 
    FROM usuarios 
    WHERE username = 'andres'
");
$stmt->execute();
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

echo "<div class='info'>";
echo "<p><strong>Estado actual:</strong></p>";
echo "<ul>";
echo "<li>Activo: " . ($usuario['activo'] ? '✅ Sí' : '❌ No') . "</li>";
echo "<li>Bloqueado hasta: " . ($usuario['bloqueado_hasta'] ?? '✅ No bloqueado') . "</li>";
echo "<li>Intentos fallidos: {$usuario['intentos_fallidos']}</li>";
echo "</ul>";
echo "</div>";

if (!$usuario['bloqueado_hasta'] && $usuario['intentos_fallidos'] == 0 && $usuario['activo']) {
    echo "<div class='success'>";
    echo "<h3>✅ Usuario en estado óptimo</h3>";
    echo "<p>El usuario está completamente desbloqueado y listo para usar.</p>";
    echo "</div>";
}

echo "<hr>";

// Prueba 3: Múltiples intentos de login
echo "<h2>Prueba 3: Múltiples Intentos de Login (Verificar No Bloqueo)</h2>";

$intentosExitosos = 0;
$intentosFallidos = 0;

for ($i = 1; $i <= 3; $i++) {
    echo "<p><strong>Intento #{$i}:</strong> ";

    // Limpiar sesión antes de cada intento
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
    session_start();

    $resultado = $auth->login('andres', '123456');

    if ($resultado['success']) {
        echo "<span style='color: green;'>✅ Exitoso</span></p>";
        $intentosExitosos++;
        session_destroy();
    } else {
        echo "<span style='color: red;'>❌ Falló: {$resultado['message']}</span></p>";
        $intentosFallidos++;
    }
}

echo "<div class='info'>";
echo "<p><strong>Resumen de intentos:</strong></p>";
echo "<ul>";
echo "<li>Exitosos: {$intentosExitosos}/3</li>";
echo "<li>Fallidos: {$intentosFallidos}/3</li>";
echo "</ul>";
echo "</div>";

if ($intentosExitosos == 3) {
    echo "<div class='success'>";
    echo "<h3>✅ ¡Todos los intentos fueron exitosos!</h3>";
    echo "<p>El usuario puede iniciar sesión múltiples veces sin problemas de bloqueo.</p>";
    echo "</div>";
}

echo "<hr>";

// Verificar estado final
echo "<h2>Prueba 4: Estado Final del Usuario</h2>";

$stmt = $db->prepare("
    SELECT username, email, activo, bloqueado_hasta, intentos_fallidos 
    FROM usuarios 
    WHERE username = 'andres'
");
$stmt->execute();
$usuarioFinal = $stmt->fetch(PDO::FETCH_ASSOC);

echo "<div class='info'>";
echo "<p><strong>Estado después de las pruebas:</strong></p>";
echo "<ul>";
echo "<li>Activo: " . ($usuarioFinal['activo'] ? '✅ Sí' : '❌ No') . "</li>";
echo "<li>Bloqueado hasta: " . ($usuarioFinal['bloqueado_hasta'] ?? '✅ No bloqueado') . "</li>";
echo "<li>Intentos fallidos: {$usuarioFinal['intentos_fallidos']}</li>";
echo "</ul>";
echo "</div>";

// Resumen final
echo "<hr>";
echo "<h2>📊 Resumen Final</h2>";

if ($result1['success'] && $intentosExitosos == 3 && !$usuarioFinal['bloqueado_hasta']) {
    echo "<div class='success'>";
    echo "<h3>🎉 ¡TODAS LAS PRUEBAS PASARON EXITOSAMENTE!</h3>";
    echo "<p><strong>Conclusión:</strong></p>";
    echo "<ul>";
    echo "<li>✅ El usuario 'andres' puede iniciar sesión correctamente</li>";
    echo "<li>✅ No hay bloqueos activos</li>";
    echo "<li>✅ Múltiples intentos de login funcionan sin problemas</li>";
    echo "<li>✅ El sistema no bloquea al usuario después de logins exitosos</li>";
    echo "</ul>";
    echo "<p><strong>El problema ha sido resuelto definitivamente.</strong></p>";
    echo "</div>";
} else {
    echo "<div class='error'>";
    echo "<h3>⚠️ Algunas pruebas fallaron</h3>";
    echo "<p>Por favor, revisa los resultados anteriores para más detalles.</p>";
    echo "</div>";
}

echo "<hr>";
echo "<div class='info'>";
echo "<h3>📝 Instrucciones para el Usuario</h3>";
echo "<p>Para iniciar sesión en el sistema:</p>";
echo "<ol>";
echo "<li>Ir a: <a href='admin/login.php' target='_blank'>admin/login.php</a></li>";
echo "<li>Usuario: <strong>andres</strong></li>";
echo "<li>Contraseña: <strong>123456</strong></li>";
echo "<li>Hacer clic en 'Iniciar Sesión'</li>";
echo "</ol>";
echo "<p><strong>Nota:</strong> Si deseas cambiar la contraseña, puedes hacerlo desde la configuración de tu perfil una vez que hayas iniciado sesión.</p>";
echo "</div>";

echo "<p style='text-align: center; color: #666; margin-top: 30px;'>";
echo "Prueba ejecutada el: " . date('d/m/Y H:i:s');
echo "</p>";

echo "</body>";
echo "</html>";
