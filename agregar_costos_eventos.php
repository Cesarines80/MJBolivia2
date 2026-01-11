<?php

/**
 * Script para agregar campos de costos a la tabla eventos
 */

require_once 'config/config.php';

echo "<h1>Agregar Campos de Costos a Eventos</h1>";
echo "<hr>";

$db = getDB();

try {
    // Verificar si los campos ya existen
    $stmt = $db->query("SHOW COLUMNS FROM eventos LIKE 'costo_inscripcion'");
    $existeCostoInscripcion = $stmt->fetch();

    $stmt = $db->query("SHOW COLUMNS FROM eventos LIKE 'costo_alojamiento'");
    $existeCostoAlojamiento = $stmt->fetch();

    if ($existeCostoInscripcion && $existeCostoAlojamiento) {
        echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; border: 1px solid #ffeaa7;'>";
        echo "<h3>⚠️ Los campos ya existen</h3>";
        echo "<p>Los campos 'costo_inscripcion' y 'costo_alojamiento' ya están en la tabla eventos.</p>";
        echo "</div>";
    } else {
        // Agregar campos si no existen
        if (!$existeCostoInscripcion) {
            $db->exec("ALTER TABLE eventos ADD COLUMN costo_inscripcion DECIMAL(10,2) DEFAULT 0.00 AFTER lugar");
            echo "<p style='color: green;'>✅ Campo 'costo_inscripcion' agregado exitosamente</p>";
        }

        if (!$existeCostoAlojamiento) {
            $db->exec("ALTER TABLE eventos ADD COLUMN costo_alojamiento DECIMAL(10,2) DEFAULT 0.00 AFTER costo_inscripcion");
            echo "<p style='color: green;'>✅ Campo 'costo_alojamiento' agregado exitosamente</p>";
        }

        echo "<hr>";
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; border: 1px solid #c3e6cb;'>";
        echo "<h3>✅ Campos agregados exitosamente</h3>";
        echo "<p>La tabla 'eventos' ahora tiene los siguientes campos nuevos:</p>";
        echo "<ul>";
        echo "<li><strong>costo_inscripcion</strong> - DECIMAL(10,2) - Costo base de inscripción al evento</li>";
        echo "<li><strong>costo_alojamiento</strong> - DECIMAL(10,2) - Costo del alojamiento</li>";
        echo "</ul>";
        echo "</div>";
    }

    // Mostrar estructura actual de la tabla
    echo "<hr>";
    echo "<h2>Estructura Actual de la Tabla 'eventos'</h2>";
    $stmt = $db->query("SHOW COLUMNS FROM eventos");
    $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Predeterminado</th></tr>";
    foreach ($columnas as $col) {
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
echo "<li>Actualizar el formulario de creación/edición de eventos en <code>admin/eventos.php</code></li>";
echo "<li>Agregar campos de entrada para 'Costo de Inscripción' y 'Costo de Alojamiento'</li>";
echo "<li>Actualizar la lógica de inscripciones para usar estos costos específicos del evento</li>";
echo "</ol>";
echo "</div>";
