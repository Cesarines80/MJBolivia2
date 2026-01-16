<?php

/**
 * Test script to create an event with only accommodation option
 */

require_once 'config/config.php';

echo "<h1>Test Crear Evento con Solo Opcion de Alojamiento</h1>";
echo "<hr>";

$db = getDB();
$eventosManager = new EventosManager($db);

// Simulate session
$_SESSION['user_id'] = 1; // Assuming user ID 1 exists

$data = [
    'titulo' => 'Evento de Prueba',
    'descripcion' => 'Descripción de prueba',
    'fecha_inicio' => '2026-12-01',
    'fecha_fin' => '2026-12-02',
    'fecha_inicio_inscripcion' => '2026-11-01',
    'fecha_fin_inscripcion' => '2026-11-30',
    'lugar' => 'Lugar de Prueba',
    'costo_inscripcion' => 100.00,
    'costo_alojamiento' => 50.00,
    'alojamiento_opcion1_desc' => 'Habitación Individual',
    'alojamiento_opcion1_costo' => 50.00,
    'estado' => 'activo'
];

echo "<h2>Datos del Evento</h2>";
echo "<pre>" . print_r($data, true) . "</pre>";
echo "<hr>";

$result = $eventosManager->create($data);

if ($result['success']) {
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; border: 1px solid #c3e6cb;'>";
    echo "<h3>✅ Evento creado exitosamente</h3>";
    echo "<p>ID del evento: " . $result['evento_id'] . "</p>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; border: 1px solid #f5c6cb;'>";
    echo "<h3>❌ Error al crear el evento</h3>";
    echo "<p>" . ($result['message'] ?? 'Error desconocido') . "</p>";
    echo "</div>";
}

echo "<hr>";
echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 5px; border: 1px solid #bee5eb;'>";
echo "<h3>📝 Resultado del Test</h3>";
if ($result['success']) {
    echo "<p>El problema del HTTP 500 al crear evento con solo opción de alojamiento ha sido solucionado.</p>";
} else {
    echo "<p>Aún hay problemas. Revisar logs de error.</p>";
}
echo "</div>";
