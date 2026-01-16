<?php
require_once 'config/config.php';

$db = getDB();
$stmt = $db->query('DESCRIBE eventos');
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Estructura de la tabla eventos:\n\n";
foreach ($columns as $col) {
    echo $col['Field'] . ' - ' . $col['Type'] . "\n";
}
