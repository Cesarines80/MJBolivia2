<?php
require_once 'config/config.php';

echo "<h1>Agregar Campo para Código de Pago</h1>";
echo "<hr>";

$db = getDB();

try {
    // Verificar si el campo ya existe
    $stmt = $db->query("SHOW COLUMNS FROM inscripciones_eventos LIKE 'codigo_pago'");
    $existe = $stmt->fetch();

    if ($existe) {
        echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px;'>";
        echo "<h3>⚠️ El campo ya existe</h3>";
        echo "<p>El campo 'codigo_pago' ya está en la tabla inscripciones_eventos.</p>";
        echo "</div>";
    } else {
        // Agregar campo
        $db->exec("ALTER TABLE inscripciones_eventos ADD COLUMN codigo_pago VARCHAR(100) DEFAULT NULL AFTER monto_pagado");

        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px;'>";
        echo "<h3>✅ Campo agregado exitosamente</h3>";
        echo "<p>El campo 'codigo_pago' ha sido agregado a la tabla inscripciones_eventos.</p>";
        echo "<ul>";
        echo "<li><strong>Nombre:</strong> codigo_pago</li>";
        echo "<li><strong>Tipo:</strong> VARCHAR(100)</li>";
        echo "<li><strong>Uso:</strong> Almacenar código de depósito o QR</li>";
        echo "</ul>";
        echo "</div>";
    }

    // Mostrar estructura actualizada
    echo "<hr>";
    echo "<h2>Estructura de la Tabla inscripciones_eventos</h2>";
    $stmt = $db->query("SHOW COLUMNS FROM inscripciones_eventos");
    $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Predeterminado</th></tr>";
    foreach ($columnas as $col) {
        $highlight = ($col['Field'] == 'codigo_pago') ? "style='background: #d4edda;'" : "";
        echo "<tr {$highlight}>";
        echo "<td><strong>{$col['Field']}</strong></td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>" . ($col['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px;'>";
    echo "<h3>❌ Error</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}

echo "<hr>";
echo "<p style='color: green;'>✅ Listo para actualizar el formulario de inscripción</p>";
