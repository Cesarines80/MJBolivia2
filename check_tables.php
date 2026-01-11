<?php
require 'config/config.php';

$db = getDB();
$stmt = $db->query('SHOW TABLES');

echo "Tablas en la base de datos:" . PHP_EOL;
echo "============================" . PHP_EOL;
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
foreach ($tables as $table) {
    echo $table . PHP_EOL;
}
echo PHP_EOL . "Total: " . count($tables) . " tablas" . PHP_EOL;
