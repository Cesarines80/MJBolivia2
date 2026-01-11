<?php

/**
 * Script para crear la tabla sesiones faltante
 */

require_once 'config/config.php';

echo "<h1>Crear Tabla Sesiones</h1>";
echo "<hr>";

$db = getDB();

try {
    // Crear tabla sesiones
    $sql = "CREATE TABLE IF NOT EXISTS `sesiones` (
        `id` varchar(128) NOT NULL,
        `usuario_id` int(11) DEFAULT NULL,
        `ip_address` varchar(45) DEFAULT NULL,
        `user_agent` varchar(255) DEFAULT NULL,
        `datos` text DEFAULT NULL,
        `ultima_actividad` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        PRIMARY KEY (`id`),
        KEY `usuario_id` (`usuario_id`),
        KEY `ultima_actividad` (`ultima_actividad`),
        CONSTRAINT `sesiones_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

    $db->exec($sql);

    echo "<p style='color: green;'>✅ <strong>Tabla 'sesiones' creada exitosamente</strong></p>";

    // Verificar que la tabla existe
    $stmt = $db->query("SHOW TABLES LIKE 'sesiones'");
    $exists = $stmt->fetch();

    if ($exists) {
        echo "<p style='color: green;'>✅ Verificación: La tabla 'sesiones' existe en la base de datos</p>";

        // Mostrar estructura de la tabla
        $stmt = $db->query("DESCRIBE sesiones");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "<h2>Estructura de la tabla 'sesiones':</h2>";
        echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        foreach ($columns as $column) {
            echo "<tr>";
            echo "<td>{$column['Field']}</td>";
            echo "<td>{$column['Type']}</td>";
            echo "<td>{$column['Null']}</td>";
            echo "<td>{$column['Key']}</td>";
            echo "<td>" . ($column['Default'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }

    echo "<hr>";
    echo "<h2>✅ Proceso Completado</h2>";
    echo "<p>La tabla 'sesiones' ha sido creada correctamente. Ahora puedes intentar hacer login nuevamente.</p>";
    echo "<p><a href='admin/login.php'>Ir a Login</a></p>";
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ <strong>Error:</strong> " . $e->getMessage() . "</p>";
}
