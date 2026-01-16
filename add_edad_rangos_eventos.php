<?php

/**
 * Script para agregar campos de rangos de edad con costos a la tabla eventos
 */

require_once 'config/config.php';

echo "<h1>Agregar Rangos de Edad con Costos a la Tabla Eventos</h1>";
echo "<hr>";

$db = getDB();

try {
    // Verificar columnas existentes
    $stmt = $db->query("SHOW COLUMNS FROM eventos");
    $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $columnasExistentes = array_column($columnas, 'Field');

    echo "<h2>Columnas Actuales en 'eventos'</h2>";
    echo "<ul>";
    foreach ($columnasExistentes as $col) {
        echo "<li>$col</li>";
    }
    echo "</ul><hr>";

    // Columnas a agregar para los rangos de edad con costos
    $columnasAAgregar = [
        'edad_rango1_min' => "ALTER TABLE eventos ADD COLUMN edad_rango1_min INT DEFAULT NULL AFTER alojamiento_opcion3_costo",
        'edad_rango1_max' => "ALTER TABLE eventos ADD COLUMN edad_rango1_max INT DEFAULT NULL AFTER edad_rango1_min",
        'costo_rango1' => "ALTER TABLE eventos ADD COLUMN costo_rango1 DECIMAL(10,2) DEFAULT 0.00 AFTER edad_rango1_max",
        'edad_rango2_min' => "ALTER TABLE eventos ADD COLUMN edad_rango2_min INT DEFAULT NULL AFTER costo_rango1",
        'edad_rango2_max' => "ALTER TABLE eventos ADD COLUMN edad_rango2_max INT DEFAULT NULL AFTER edad_rango2_min",
        'costo_rango2' => "ALTER TABLE eventos ADD COLUMN costo_rango2 DECIMAL(10,2) DEFAULT 0.00 AFTER edad_rango2_max"
    ];

    // Agregar columnas faltantes
    foreach ($columnasAAgregar as $columna => $sql) {
        if (!in_array($columna, $columnasExistentes)) {
            echo "<p>Agregando columna '$columna'...</p>";
            $db->exec($sql);
            echo "<p style='color: green;'>✅ Columna '$columna' agregada exitosamente</p>";
        } else {
            echo "<p style='color: blue;'>ℹ️ Columna '$columna' ya existe</p>";
        }
    }

    echo "<hr>";
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; border: 1px solid #c3e6cb;'>";
    echo "<h3>✅ Tabla 'eventos' actualizada exitosamente</h3>";
    echo "<p>Ahora incluye las columnas para los dos rangos de edad con costos.</p>";
    echo "</div>";

    // Mostrar estructura final
    echo "<hr>";
    echo "<h2>Estructura Final de la Tabla 'eventos'</h2>";
    $stmt = $db->query("SHOW COLUMNS FROM eventos");
    $columnasFinal = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Predeterminado</th></tr>";
    foreach ($columnasFinal as $col) {
        echo "<tr>";
        echo "<td><strong>{$col['Field']}</strong></td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>" . ($col['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
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
echo "<li>Actualizar los métodos create y update en includes/eventos.php</li>";
echo "<li>Modificar el formulario de creación de eventos en admin/eventos.php</li>";
echo "<li>Probar la funcionalidad</li>";
echo "</ol>";
echo "</div>";
