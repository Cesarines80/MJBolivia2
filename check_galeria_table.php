<?php
require 'config/config.php';
$db = getDB();
$stmt = $db->query('DESCRIBE galeria');
echo "Estructura de la tabla galeria:\n";
echo "=====================================\n";
while ($row = $stmt->fetch()) {
    echo $row['Field'] . ' - ' . $row['Type'] . "\n";
}
