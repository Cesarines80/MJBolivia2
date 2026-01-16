<?php

/**
 * Script para eliminar las restricciones de clave foránea problemáticas
 */

require_once 'config/config.php';

echo "<h1>Eliminar Restricciones de Clave Foránea Problemáticas</h1>";
echo "<hr>";

$db = getDB();

try {
    // Eliminar foreign keys que causan problemas con ids de admin
    $foreignKeys = [
        "ALTER TABLE eventos DROP FOREIGN KEY fk_eventos_creado_por",
        "ALTER TABLE eventos_administradores DROP FOREIGN KEY eventos_administradores_ibfk_1",
        "ALTER TABLE eventos_administradores DROP FOREIGN KEY eventos_administradores_ibfk_2",
        "ALTER TABLE log_actividades DROP FOREIGN KEY log_actividades_ibfk_1",
        "ALTER TABLE log_actividades DROP FOREIGN KEY log_actividades_ibfk_2"
    ];

    foreach ($foreignKeys as $sql) {
        try {
            $db->exec($sql);
            echo "<p style='color: green;'>✅ Foreign key eliminada: " . substr($sql, 0, 50) . "...</p>";
        } catch (Exception $e) {
            echo "<p style='color: blue;'>ℹ️ Foreign key ya no existe o no se pudo eliminar: " . $e->getMessage() . "</p>";
        }
    }

    echo "<hr>";
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; border: 1px solid #c3e6cb;'>";
    echo "<h3>✅ Restricciones de clave foránea eliminadas</h3>";
    echo "<p>Ahora el sistema puede usar ids de administradores y usuarios indistintamente.</p>";
    echo "</div>";
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; border: 1px solid #f5c6cb;'>";
    echo "<h3>❌ Error</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}

echo "<hr>";
echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 5px; border: 1px solid #bee5eb;'>";
echo "<h3>📝 Próximos Pasos</h3>";
echo "<ol>";
echo "<li>Probar la creación de eventos nuevamente</li>";
echo "<li>Verificar que no haya errores de integridad referencial</li>";
echo "</ol>";
echo "</div>";
