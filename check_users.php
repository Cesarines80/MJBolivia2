<?php
require 'config/config.php';

$db = getDB();

echo "=== TABLA ADMINISTRADORES ===" . PHP_EOL;
$stmt = $db->query("SELECT id, nombre, email, rol, estado FROM administradores");
$admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($admins as $admin) {
    echo "ID: " . $admin['id'] . PHP_EOL;
    echo "Nombre: " . $admin['nombre'] . PHP_EOL;
    echo "Email: " . $admin['email'] . PHP_EOL;
    echo "Rol: " . $admin['rol'] . PHP_EOL;
    echo "Estado: " . $admin['estado'] . PHP_EOL;
    echo "---" . PHP_EOL;
}

echo PHP_EOL . "=== TABLA USUARIOS ===" . PHP_EOL;
$stmt = $db->query("SELECT id, username, email, nombre_completo, rol, activo FROM usuarios");
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (count($usuarios) > 0) {
    foreach ($usuarios as $usuario) {
        echo "ID: " . $usuario['id'] . PHP_EOL;
        echo "Username: " . $usuario['username'] . PHP_EOL;
        echo "Email: " . $usuario['email'] . PHP_EOL;
        echo "Nombre: " . $usuario['nombre_completo'] . PHP_EOL;
        echo "Rol: " . $usuario['rol'] . PHP_EOL;
        echo "Activo: " . ($usuario['activo'] ? 'Si' : 'No') . PHP_EOL;
        echo "---" . PHP_EOL;
    }
} else {
    echo "No hay usuarios en la tabla usuarios" . PHP_EOL;
}
