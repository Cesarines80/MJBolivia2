<?php
require_once 'config/config.php';

echo "<h1>Verificación y Actualización de Contraseña - Usuario Andres</h1>";
echo "<hr>";

$db = getDB();

// Obtener información del usuario
$stmt = $db->prepare("SELECT * FROM usuarios WHERE username = 'andres' LIMIT 1");
$stmt->execute();
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if ($usuario) {
    echo "<h2>Usuario Encontrado:</h2>";
    echo "<p><strong>ID:</strong> {$usuario['id']}</p>";
    echo "<p><strong>Username:</strong> {$usuario['username']}</p>";
    echo "<p><strong>Email:</strong> {$usuario['email']}</p>";
    echo "<p><strong>Nombre:</strong> {$usuario['nombre_completo']}</p>";
    echo "<p><strong>Rol:</strong> {$usuario['rol']}</p>";
    echo "<hr>";

    // Probar diferentes contraseñas comunes
    $passwordsToTest = ['andres123', 'andres', '123456', 'admin123'];

    echo "<h2>Probando Contraseñas Comunes:</h2>";
    $passwordFound = false;

    foreach ($passwordsToTest as $testPassword) {
        if (password_verify($testPassword, $usuario['password'])) {
            echo "<p style='color: green;'>✅ <strong>Contraseña encontrada:</strong> {$testPassword}</p>";
            $passwordFound = true;
            break;
        } else {
            echo "<p style='color: red;'>❌ No es: {$testPassword}</p>";
        }
    }

    if (!$passwordFound) {
        echo "<hr>";
        echo "<h2>Actualizando Contraseña a 'andres123'</h2>";

        // Actualizar la contraseña
        $newPassword = 'andres123';
        $newHash = password_hash($newPassword, PASSWORD_BCRYPT);

        $stmt = $db->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
        $result = $stmt->execute([$newHash, $usuario['id']]);

        if ($result) {
            echo "<p style='color: green;'>✅ <strong>Contraseña actualizada exitosamente</strong></p>";
            echo "<p><strong>Nueva contraseña:</strong> andres123</p>";

            // Verificar que funciona
            if (password_verify($newPassword, $newHash)) {
                echo "<p style='color: green;'>✅ Verificación exitosa: La contraseña funciona correctamente</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ Error al actualizar la contraseña</p>";
        }
    }

    echo "<hr>";
    echo "<h2>Credenciales Finales:</h2>";
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px;'>";
    echo "<p><strong>Usuario:</strong> andres</p>";
    echo "<p><strong>Contraseña:</strong> andres123</p>";
    echo "<p><strong>Email:</strong> andres@andres.com</p>";
    echo "</div>";
} else {
    echo "<p style='color: red;'>❌ Usuario 'andres' no encontrado</p>";
}
