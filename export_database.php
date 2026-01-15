<?php

require_once 'config/config.php';

$db = getDB();

echo "<h1>Exportando Base de Datos Completa</h1>";

// Obtener todas las tablas
$tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

$sql = "-- Base de datos completa actualizada\n";
$sql .= "-- Exportado el " . date('Y-m-d H:i:s') . "\n\n";
$sql .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
$sql .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";
$sql .= "START TRANSACTION;\n\n";
$sql .= "USE `" . DB_NAME . "`;\n\n";

foreach ($tables as $table) {
    // Estructura de la tabla
    $sql .= "-- Estructura de la tabla `$table`\n";
    $sql .= "DROP TABLE IF EXISTS `$table`;\n";

    $createTable = $db->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_ASSOC);
    $sql .= $createTable['Create Table'] . ";\n\n";

    // Datos de la tabla
    $rows = $db->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($rows)) {
        $sql .= "-- Volcado de datos para la tabla `$table`\n";

        $columns = array_keys($rows[0]);
        $columnNames = '`' . implode('`, `', $columns) . '`';

        foreach ($rows as $row) {
            $values = array_map(function ($value) use ($db) {
                if ($value === null) return 'NULL';
                return $db->quote($value);
            }, $row);

            $sql .= "INSERT INTO `$table` ($columnNames) VALUES (" . implode(', ', $values) . ");\n";
        }

        $sql .= "\n";
    }
}

$sql .= "SET FOREIGN_KEY_CHECKS = 1;\n\n";
$sql .= "COMMIT;\n";

file_put_contents('database_completa_actualizada.sql', $sql);

echo "<p>Base de datos exportada a <strong>database_completa_actualizada.sql</strong></p>";
echo "<p>Tamaño del archivo: " . filesize('database_completa_actualizada.sql') . " bytes</p>";
