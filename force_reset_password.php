<?php
require_once 'config/config.php';

// --- CONFIGURACIÓN ---
$usernameToUpdate = 'superadmin';
$newPassword = 'superadminpass'; // Define la nueva contraseña aquí

// --- LÓGICA DEL SCRIPT ---
echo "<!DOCTYPE html><html lang='es'><head><meta charset='UTF-8'><title>Resetear Contraseña</title>";
echo "<style>body { font-family: Arial, sans-serif; max-width: 800px; margin: 40px auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background: #f9f9f9; } h1 { color: #333; } .success { color: #155724; background: #d4edda; padding: 15px; border-radius: 5px; border-left: 5px solid #28a745; } .error { color: #721c24; background: #f8d7da; padding: 15px; border-radius: 5px; border-left: 5px solid #dc3545; } code { background: #eee; padding: 3px 6px; border-radius: 4px; }</style>";
echo "</head><body>";
echo "<h1>Restablecimiento de Contraseña para Super Administrador</h1>";

try {
    // 1. Conexión a la base de datos
    $db = getDB();
    echo "<p>✅ Conexión a la base de datos exitosa.</p>";

    // 2. Hash de la nueva contraseña
    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
    echo "<p>✅ Nueva contraseña hasheada correctamente.</p>";

    // 3. Actualizar la base de datos
    $stmt = $db->prepare("UPDATE usuarios SET password = :password WHERE username = :username");
    $stmt->execute([
        ':password' => $hashedPassword,
        ':username' => $usernameToUpdate
    ]);

    // 4. Verificar si la actualización fue exitosa
    if ($stmt->rowCount() > 0) {
        echo "<div class='success'>";
        echo "<h2>¡Éxito! La contraseña ha sido restablecida.</h2>";
        echo "<p>El usuario <code>" . htmlspecialchars($usernameToUpdate) . "</code> ahora tiene una nueva contraseña.</p>";
        echo "<p><strong>Nueva Contraseña:</strong> <code>" . htmlspecialchars($newPassword) . "</code></p>";
        echo "<hr>";
        echo "<p><strong>Próximos pasos:</strong></p>";
        echo "<ol>";
        echo "<li>Ve a la página de <a href='admin/login.php'>login</a>.</li>";
        echo "<li>Usa el usuario <code>" . htmlspecialchars($usernameToUpdate) . "</code>.</li>";
        echo "<li>Usa la nueva contraseña <code>" . htmlspecialchars($newPassword) . "</code> para acceder.</li>";
        echo "</ol>";
        echo "</div>";
    } else {
        echo "<div class='error'>";
        echo "<h2>Error al actualizar.</h2>";
        echo "<p>No se pudo actualizar la contraseña para el usuario <code>" . htmlspecialchars($usernameToUpdate) . "</code>.</p>";
        echo "<p><strong>Posible causa:</strong> El usuario no fue encontrado en la base de datos.</p>";
        echo "<p>Verifica que el usuario exista en la tabla <code>usuarios</code>.</p>";
        echo "</div>";
    }

} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<h2>❌ Ocurrió un error catastrófico.</h2>";
    echo "<p><strong>Mensaje:</strong> " . $e->getMessage() . "</p>";
    echo "</div>";
}

echo "</body></html>";

?>
