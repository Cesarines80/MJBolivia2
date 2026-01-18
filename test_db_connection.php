<?php
// Archivo de prueba para verificar la conexión a la base de datos
require_once 'config/config.php';

try {
    $db = getDB();
    echo "✅ Conexión a la base de datos exitosa!<br>";
    echo "Host: " . DB_HOST . "<br>";
    echo "Base de datos: " . DB_NAME . "<br>";
    echo "Usuario: " . DB_USER . "<br>";

    // Verificar si la base de datos existe
    $stmt = $db->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Tablas encontradas: " . count($tables) . "<br>";
    if (count($tables) > 0) {
        echo "Primeras 5 tablas: " . implode(', ', array_slice($tables, 0, 5)) . "<br>";
    }
} catch (Exception $e) {
    echo "❌ Error de conexión: " . $e->getMessage() . "<br>";
    echo "Revisa las configuraciones en config/config.php<br>";
    echo "Host actual: " . DB_HOST . "<br>";
    echo "Usuario actual: " . DB_USER . "<br>";
    echo "Base de datos actual: " . DB_NAME . "<br>";
}
