<?php
require_once 'config/config.php';

echo "Attempting to unblock superadmin...\n";

try {
    $db = getDB();
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Unblock user in 'usuarios' table
    $stmt1 = $db->prepare(
        "\n        UPDATE usuarios\n        SET bloqueado_hasta = NULL,\n            intentos_fallidos = 0,\n            activo = 1\n        WHERE email = :email\n    ");
    $stmt1->execute([':email' => 'superadmin@sistema.com']);
    $affected_rows1 = $stmt1->rowCount();
    echo "Unblocking user 'superadmin@sistema.com'. Rows affected: $affected_rows1\n";


    // Clear failed login attempts from 'intentos_login' table
    $stmt2 = $db->prepare(
        "\n        DELETE FROM intentos_login\n        WHERE email = :email\n    ");
    $stmt2->execute([':email' => 'superadmin@sistema.com']);
    $affected_rows2 = $stmt2->rowCount();
    echo "Clearing failed login attempts for 'superadmin@sistema.com'. Rows affected: $affected_rows2\n";


    if ($affected_rows1 > 0) {
        echo "Superadmin account successfully unblocked.\n";
    } else {
        echo "Superadmin account might not have been blocked or could not be found with that email.\n";
    }

} catch (Exception $e) {
    echo "An error occurred: " . $e->getMessage() . "\n";
}

