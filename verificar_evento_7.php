<?php
require_once 'config/config.php';

echo "<h1>Verificación de Fechas - Evento 7</h1>";
echo "<hr>";

$db = getDB();

// Obtener información del evento 7
$stmt = $db->prepare("SELECT * FROM eventos WHERE id = 7 LIMIT 1");
$stmt->execute();
$evento = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$evento) {
    echo "<p style='color: red;'>❌ Evento 7 no encontrado</p>";
    exit;
}

echo "<h2>Información del Evento</h2>";
echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
echo "<tr><th>Campo</th><th>Valor</th></tr>";
echo "<tr><td>ID</td><td>{$evento['id']}</td></tr>";
echo "<tr><td>Nombre</td><td>{$evento['nombre']}</td></tr>";
echo "<tr><td>Estado</td><td>{$evento['estado']}</td></tr>";
echo "</table>";

echo "<h2>Fechas del Evento</h2>";
echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
echo "<tr><th>Tipo de Fecha</th><th>Valor</th></tr>";
echo "<tr><td>Fecha Inicio Evento</td><td>{$evento['fecha_inicio']}</td></tr>";
echo "<tr><td>Fecha Fin Evento</td><td>{$evento['fecha_fin']}</td></tr>";
echo "<tr><td><strong>Fecha Inicio Inscripción</strong></td><td><strong>{$evento['fecha_inicio_inscripcion']}</strong></td></tr>";
echo "<tr><td><strong>Fecha Fin Inscripción</strong></td><td><strong>{$evento['fecha_fin_inscripcion']}</strong></td></tr>";
echo "</table>";

echo "<h2>Análisis de Fechas</h2>";

// Fecha actual del servidor
$fechaActual = date('Y-m-d');
$fechaInicioInscripcion = $evento['fecha_inicio_inscripcion'];
$fechaFinInscripcion = $evento['fecha_fin_inscripcion'];

echo "<div style='background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h3>Fecha Actual del Servidor:</h3>";
echo "<p style='font-size: 1.5em; color: #1976d2;'><strong>{$fechaActual}</strong></p>";
echo "<p><small>Formato: YYYY-MM-DD</small></p>";
echo "</div>";

echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h3>Período de Inscripción:</h3>";
echo "<p><strong>Inicio:</strong> {$fechaInicioInscripcion}</p>";
echo "<p><strong>Fin:</strong> {$fechaFinInscripcion}</p>";
echo "</div>";

echo "<h2>Validación de Inscripciones</h2>";

// Lógica de validación (igual que en eventos/inscribir.php)
$inscripcionAbierta = ($fechaActual >= $fechaInicioInscripcion && $fechaActual <= $fechaFinInscripcion);

echo "<div style='background: " . ($inscripcionAbierta ? '#d4edda' : '#f8d7da') . "; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h3>Estado de Inscripciones:</h3>";

if ($inscripcionAbierta) {
    echo "<p style='color: #155724; font-size: 1.2em;'><strong>✅ INSCRIPCIONES ABIERTAS</strong></p>";
} else {
    echo "<p style='color: #721c24; font-size: 1.2em;'><strong>❌ INSCRIPCIONES CERRADAS</strong></p>";
}

echo "</div>";

echo "<h3>Detalles de la Validación:</h3>";
echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
echo "<tr><th>Condición</th><th>Resultado</th><th>Estado</th></tr>";

$cond1 = $fechaActual >= $fechaInicioInscripcion;
$cond2 = $fechaActual <= $fechaFinInscripcion;

echo "<tr>";
echo "<td>Fecha actual >= Fecha inicio inscripción</td>";
echo "<td>{$fechaActual} >= {$fechaInicioInscripcion}</td>";
echo "<td style='color: " . ($cond1 ? 'green' : 'red') . ";'><strong>" . ($cond1 ? '✅ TRUE' : '❌ FALSE') . "</strong></td>";
echo "</tr>";

echo "<tr>";
echo "<td>Fecha actual <= Fecha fin inscripción</td>";
echo "<td>{$fechaActual} <= {$fechaFinInscripcion}</td>";
echo "<td style='color: " . ($cond2 ? 'green' : 'red') . ";'><strong>" . ($cond2 ? '✅ TRUE' : '❌ FALSE') . "</strong></td>";
echo "</tr>";

echo "<tr>";
echo "<td><strong>Ambas condiciones (AND)</strong></td>";
echo "<td colspan='2' style='color: " . ($inscripcionAbierta ? 'green' : 'red') . ";'><strong>" . ($inscripcionAbierta ? '✅ INSCRIPCIONES ABIERTAS' : '❌ INSCRIPCIONES CERRADAS') . "</strong></td>";
echo "</tr>";

echo "</table>";

// Diagnóstico
echo "<h2>🔍 Diagnóstico</h2>";

if (!$inscripcionAbierta) {
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px;'>";
    echo "<h3>⚠️ Problema Identificado:</h3>";

    if ($fechaActual < $fechaInicioInscripcion) {
        $diasFaltantes = (strtotime($fechaInicioInscripcion) - strtotime($fechaActual)) / 86400;
        echo "<p><strong>Las inscripciones aún no han comenzado.</strong></p>";
        echo "<p>Faltan <strong>" . ceil($diasFaltantes) . " días</strong> para que inicien las inscripciones.</p>";
        echo "<p>Las inscripciones comenzarán el: <strong>{$fechaInicioInscripcion}</strong></p>";
    } elseif ($fechaActual > $fechaFinInscripcion) {
        $diasPasados = (strtotime($fechaActual) - strtotime($fechaFinInscripcion)) / 86400;
        echo "<p><strong>Las inscripciones ya finalizaron.</strong></p>";
        echo "<p>Han pasado <strong>" . ceil($diasPasados) . " días</strong> desde el cierre de inscripciones.</p>";
        echo "<p>Las inscripciones cerraron el: <strong>{$fechaFinInscripcion}</strong></p>";
    }

    echo "</div>";

    echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 5px; margin-top: 15px;'>";
    echo "<h3>💡 Solución:</h3>";
    echo "<p>Para abrir las inscripciones, necesitas actualizar las fechas del evento 7:</p>";
    echo "<ol>";
    echo "<li>Ir al panel de administración</li>";
    echo "<li>Editar el evento 7</li>";
    echo "<li>Cambiar las fechas de inscripción para que incluyan la fecha actual ({$fechaActual})</li>";
    echo "</ol>";
    echo "<p><strong>Sugerencia de fechas:</strong></p>";
    echo "<ul>";
    echo "<li>Fecha Inicio Inscripción: <strong>{$fechaActual}</strong> (hoy)</li>";
    echo "<li>Fecha Fin Inscripción: <strong>" . date('Y-m-d', strtotime('+30 days')) . "</strong> (en 30 días)</li>";
    echo "</ul>";
    echo "</div>";
} else {
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px;'>";
    echo "<h3>✅ Todo está correcto</h3>";
    echo "<p>Las inscripciones están abiertas y funcionando correctamente.</p>";
    echo "</div>";
}

echo "<hr>";
echo "<p style='text-align: center; color: #666;'>Verificación completada - " . date('d/m/Y H:i:s') . "</p>";
