<?php
require_once 'config/config.php';

$db = getDB();
$stmt = $db->prepare('SELECT id, nombre, fecha_inicio_inscripcion, fecha_fin_inscripcion, estado FROM eventos WHERE id = 5');
$stmt->execute();
$evento = $stmt->fetch(PDO::FETCH_ASSOC);

echo "<h1>Verificación Evento ID: 5</h1>";
echo "<hr>";

if ($evento) {
    echo "<h2>Datos del Evento:</h2>";
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>Campo</th><th>Valor</th></tr>";
    echo "<tr><td>ID</td><td>{$evento['id']}</td></tr>";
    echo "<tr><td>Nombre</td><td>{$evento['nombre']}</td></tr>";
    echo "<tr><td>Fecha Inicio Inscripción</td><td>{$evento['fecha_inicio_inscripcion']}</td></tr>";
    echo "<tr><td>Fecha Fin Inscripción</td><td>{$evento['fecha_fin_inscripcion']}</td></tr>";
    echo "<tr><td>Estado</td><td>{$evento['estado']}</td></tr>";
    echo "</table>";

    echo "<hr>";
    echo "<h2>Validación de Fechas:</h2>";

    $hoy = date('Y-m-d');
    echo "<p><strong>Fecha Actual:</strong> {$hoy}</p>";

    $inicioOK = ($hoy >= $evento['fecha_inicio_inscripcion']);
    $finOK = ($hoy <= $evento['fecha_fin_inscripcion']);

    echo "<p><strong>Inicio de inscripciones:</strong> " . ($inicioOK ? '✅ OK' : '❌ NO') . "</p>";
    echo "<p><strong>Fin de inscripciones:</strong> " . ($finOK ? '✅ OK' : '❌ NO') . "</p>";

    $inscripcionAbierta = $inicioOK && $finOK && $evento['estado'] === 'activo';

    echo "<hr>";
    echo "<h2>Resultado:</h2>";
    if ($inscripcionAbierta) {
        echo "<p style='color: green; font-size: 20px;'>✅ <strong>Inscripciones ABIERTAS</strong></p>";
    } else {
        echo "<p style='color: red; font-size: 20px;'>❌ <strong>Inscripciones CERRADAS</strong></p>";
        echo "<p>Razones:</p>";
        echo "<ul>";
        if (!$inicioOK) echo "<li>Aún no ha iniciado el periodo de inscripción</li>";
        if (!$finOK) echo "<li>El periodo de inscripción ha finalizado</li>";
        if ($evento['estado'] !== 'activo') echo "<li>El evento no está activo (Estado: {$evento['estado']})</li>";
        echo "</ul>";
    }

    echo "<hr>";
    echo "<h2>Comparación Detallada:</h2>";
    echo "<pre>";
    echo "Fecha actual:              {$hoy}\n";
    echo "Fecha inicio inscripción:  {$evento['fecha_inicio_inscripcion']}\n";
    echo "Fecha fin inscripción:     {$evento['fecha_fin_inscripcion']}\n";
    echo "\n";
    echo "¿Hoy >= Inicio? " . ($hoy >= $evento['fecha_inicio_inscripcion'] ? 'SÍ' : 'NO') . "\n";
    echo "¿Hoy <= Fin?    " . ($hoy <= $evento['fecha_fin_inscripcion'] ? 'SÍ' : 'NO') . "\n";
    echo "</pre>";
} else {
    echo "<p style='color: red;'>❌ Evento no encontrado</p>";
}
