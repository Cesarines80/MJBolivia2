<?php
require 'config/config.php';

$db = getDB();
$stmt = $db->query('DESCRIBE intentos_login');

echo "Estructura de la tabla intentos_login:\n";
echo "=====================================\n";
while ($row = $stmt->fetch()) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}
