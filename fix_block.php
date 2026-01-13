<?php
require_once 'config/config.php';

echo "<!DOCTYPE html><html><head><title>Unblock Superadmin</title>";
echo "<style>body{font-family:Arial, sans-serif; margin: 20px;} .success{color:green;} .error{color:red;}</style>";
echo "</head><body>";
echo "<h1>Unblocking superadmin@sistema.com</h1>";

try {
    $db = getDB();
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Unblock user in 'usuarios' table
    echo "<h2>1. Updating 'usuarios' table...</h2>";
    $stmt1 = $db->prepare(
        "UPDATE usuarios
         SET bloqueado_hasta = NULL,
             intentos_fallidos = 0,
             activo = 1
         WHERE email = :email"
    );
    $stmt1->execute([':email' => 'superadmin@sistema.com']);
    $affected_rows1 = $stmt1->rowCount();

    if ($affected_rows1 > 0) {
        echo "<p class='success'>SUCCESS: User 'superadmin@sistema.com' was unblocked. ($affected_rows1 row affected)</p>";
    } else {
        echo "<p class='error'>NOTE: User 'superadmin@sistema.com' was not found or was not blocked in the 'usuarios' table. ($affected_rows1 rows affected)</p>";
    }

    // 2. Clear failed login attempts from 'intentos_login' table
    echo "<h2>2. Deleting from 'intentos_login' table...</h2>";
    $stmt2 = $db->prepare(
        "DELETE FROM intentos_login
         WHERE email = :email"
    );
    $stmt2->execute([':email' => 'superadmin@sistema.com']);
    $affected_rows2 = $stmt2->rowCount();

    if ($affected_rows2 > 0) {
        echo "<p class='success'>SUCCESS: Removed login attempt logs. ($affected_rows2 rows affected)</p>";
    } else {
        echo "<p>NOTE: No login attempt logs found for this user.</p>";
    }

    echo "<h2>Verification</h2>";
    $stmt3 = $db->prepare("SELECT username, email, activo, bloqueado_hasta, intentos_fallidos FROM usuarios WHERE email = :email");
    $stmt3->execute([':email' => 'superadmin@sistema.com']);
    $user = $stmt3->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        echo "<pre>" . print_r($user, true) . "</pre>";
        echo "<p class='success'>Verification complete. The user should be able to log in now.</p>";
    } else {
        echo "<p class='error'>Could not verify user status.</p>";
    }


} catch (Exception $e) {
    echo "<p class='error'>An error occurred: " . $e->getMessage() . "</p>";
}

echo "</body></html>";
