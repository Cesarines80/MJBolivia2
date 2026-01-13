<?php
require_once 'config/config.php';

$db = getDB();

echo "<h1>Desbloquear Usuarios con Rol 'usuario'</h1>";

// Desbloquear todos los usuarios con rol 'usuario'
$stmt = $db->prepare("
    UPDATE usuarios
    SET bloqueado_hasta = NULL,
        intentos_fallidos = 0
    WHERE rol = 'usuario'
");

$result = $stmt->execute();

if ($result) {
    echo "<p>✅ Todos los usuarios con rol 'usuario' han sido desbloqueados.</p>";
} else {
    echo "<p>❌ Error al desbloquear usuarios.</p>";
}

// Limpiar intentos fallidos de IP para usuarios 'usuario'
$stmt = $db->prepare("
    DELETE FROM intentos_login
    WHERE email IN (
        SELECT email FROM usuarios WHERE rol = 'usuario'
    )
");

$result2 = $stmt->execute();

if ($result2) {
    echo "<p>✅ Intentos fallidos limpiados para usuarios 'usuario'.</p>";
} else {
    echo "<p>❌ Error al limpiar intentos fallidos.</p>";
}

echo "<p><a href='admin/usuarios.php'>Volver a Gestión de Usuarios</a></p>";
