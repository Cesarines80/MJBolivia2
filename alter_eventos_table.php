<?php

/**
 * Script para alterar la tabla eventos y agregar columnas faltantes
 */

require_once 'config/config.php';

echo "<h1>Alterar Tabla Eventos - Agregar Columnas Faltantes</h1>";
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

    // Columnas a agregar
    $columnasAAgregar = [
        'fecha_inicio' => "ALTER TABLE eventos ADD COLUMN fecha_inicio DATE AFTER descripcion",
        'fecha_fin' => "ALTER TABLE eventos ADD COLUMN fecha_fin DATE AFTER fecha_inicio",
        'fecha_inicio_inscripcion' => "ALTER TABLE eventos ADD COLUMN fecha_inicio_inscripcion DATE AFTER fecha_fin",
        'fecha_fin_inscripcion' => "ALTER TABLE eventos ADD COLUMN fecha_fin_inscripcion DATE AFTER fecha_inicio_inscripcion",
        'imagen_portada' => "ALTER TABLE eventos ADD COLUMN imagen_portada VARCHAR(255) AFTER lugar",
        'creado_por' => "ALTER TABLE eventos ADD COLUMN creado_por INT AFTER estado",
        'costo_inscripcion' => "ALTER TABLE eventos ADD COLUMN costo_inscripcion DECIMAL(10,2) DEFAULT 0.00 AFTER imagen_portada",
        'costo_alojamiento' => "ALTER TABLE eventos ADD COLUMN costo_alojamiento DECIMAL(10,2) DEFAULT 0.00 AFTER costo_inscripcion"
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

    // Agregar foreign key para creado_por si no existe
    try {
        $db->exec("ALTER TABLE eventos ADD CONSTRAINT fk_eventos_creado_por FOREIGN KEY (creado_por) REFERENCES usuarios(id) ON DELETE RESTRICT");
        echo "<p style='color: green;'>✅ Foreign key para 'creado_por' agregada</p>";
    } catch (Exception $e) {
        echo "<p style='color: blue;'>ℹ️ Foreign key ya existe o no se pudo agregar: " . $e->getMessage() . "</p>";
    }

    // Agregar índices
    $indices = [
        "ALTER TABLE eventos ADD INDEX idx_fechas (fecha_inicio_inscripcion, fecha_fin_inscripcion)",
        "ALTER TABLE eventos ADD INDEX idx_creado_por (creado_por)"
    ];

    foreach ($indices as $sql) {
        try {
            $db->exec($sql);
            echo "<p style='color: green;'>✅ Índice agregado</p>";
        } catch (Exception $e) {
            echo "<p style='color: blue;'>ℹ️ Índice ya existe o no se pudo agregar</p>";
        }
    }

    echo "<hr>";
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; border: 1px solid #c3e6cb;'>";
    echo "<h3>✅ Tabla 'eventos' actualizada exitosamente</h3>";
    echo "<p>La tabla ahora tiene todas las columnas necesarias para el sistema de eventos.</p>";
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
echo "<li>Probar la creación de eventos en <code>admin/eventos.php</code></li>";
echo "<li>Verificar que no haya errores HTTP 500</li>";
echo "</ol>";
echo "</div>";
