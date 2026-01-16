<?php

/**
 * Script para renombrar la columna 'nombre' a 'titulo' en la tabla eventos
 */

require_once 'config/config.php';

echo "<h1>Renombrar Columna 'nombre' a 'titulo' en Tabla Eventos</h1>";
echo "<hr>";

$db = getDB();

try {
    // Verificar si existe la columna 'nombre'
    $stmt = $db->query("SHOW COLUMNS FROM eventos LIKE 'nombre'");
    $columnaNombre = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($columnaNombre) {
        echo "<p>✅ Columna 'nombre' encontrada. Procediendo a renombrar...</p>";

        // Renombrar columna
        $db->exec("ALTER TABLE eventos CHANGE nombre titulo VARCHAR(200) NOT NULL");

        echo "<p style='color: green;'>✅ Columna renombrada exitosamente de 'nombre' a 'titulo'</p>";
    } else {
        // Verificar si ya existe 'titulo'
        $stmt = $db->query("SHOW COLUMNS FROM eventos LIKE 'titulo'");
        $columnaTitulo = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($columnaTitulo) {
            echo "<p style='color: blue;'>ℹ️ La columna ya se llama 'titulo'</p>";
        } else {
            echo "<p style='color: red;'>❌ No se encontró ni 'nombre' ni 'titulo'. Verificar estructura de tabla.</p>";
        }
    }

    // Mostrar estructura final
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
echo "<li>Probar la creación de eventos en <code>admin/eventos.php</code></li>";
echo "<li>Verificar que no haya errores HTTP 500</li>";
echo "</ol>";
echo "</div>";
