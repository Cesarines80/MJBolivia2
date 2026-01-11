<?php
require_once 'config/config.php';

echo "<h1>Actualización de Fechas - Evento 7</h1>";
echo "<hr>";

$db = getDB();

// Obtener información actual del evento 7
$stmt = $db->prepare("SELECT * FROM eventos WHERE id = 7 LIMIT 1");
$stmt->execute();
$evento = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$evento) {
    echo "<p style='color: red;'>❌ Evento 7 no encontrado</p>";
    exit;
}

echo "<h2>Estado Actual del Evento 7</h2>";
echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
echo "<tr><th>Campo</th><th>Valor Actual</th></tr>";
echo "<tr><td>Nombre</td><td>{$evento['nombre']}</td></tr>";
echo "<tr><td>Fecha Inicio Inscripción</td><td><strong>{$evento['fecha_inicio_inscripcion']}</strong></td></tr>";
echo "<tr><td>Fecha Fin Inscripción</td><td><strong>{$evento['fecha_fin_inscripcion']}</strong></td></tr>";
echo "</table>";

$fechaActual = date('Y-m-d');
echo "<p><strong>Fecha Actual del Servidor:</strong> {$fechaActual}</p>";

echo "<hr>";
echo "<h2>Actualizando Fechas...</h2>";

// Nueva fecha de inicio: hoy
$nuevaFechaInicio = $fechaActual;
// Mantener la fecha de fin original
$nuevaFechaFin = $evento['fecha_fin_inscripcion'];

try {
    $stmt = $db->prepare("
        UPDATE eventos 
        SET fecha_inicio_inscripcion = ? 
        WHERE id = 7
    ");

    $result = $stmt->execute([$nuevaFechaInicio]);

    if ($result) {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h3>✅ Actualización Exitosa</h3>";
        echo "<p>Las fechas del evento 7 han sido actualizadas correctamente.</p>";
        echo "</div>";

        echo "<h2>Nuevas Fechas del Evento 7</h2>";
        echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
        echo "<tr><th>Campo</th><th>Valor Anterior</th><th>Valor Nuevo</th></tr>";
        echo "<tr><td>Fecha Inicio Inscripción</td><td>{$evento['fecha_inicio_inscripcion']}</td><td><strong style='color: green;'>{$nuevaFechaInicio}</strong></td></tr>";
        echo "<tr><td>Fecha Fin Inscripción</td><td>{$evento['fecha_fin_inscripcion']}</td><td><strong>{$nuevaFechaFin}</strong></td></tr>";
        echo "</table>";

        // Verificar que ahora las inscripciones estén abiertas
        $inscripcionAbierta = ($fechaActual >= $nuevaFechaInicio && $fechaActual <= $nuevaFechaFin);

        echo "<h2>Verificación Final</h2>";
        echo "<div style='background: " . ($inscripcionAbierta ? '#d4edda' : '#f8d7da') . "; padding: 15px; border-radius: 5px;'>";
        if ($inscripcionAbierta) {
            echo "<h3 style='color: #155724;'>✅ INSCRIPCIONES ABIERTAS</h3>";
            echo "<p>El evento 7 ahora acepta inscripciones.</p>";
            echo "<p><strong>Período de inscripción:</strong> {$nuevaFechaInicio} al {$nuevaFechaFin}</p>";
        } else {
            echo "<h3 style='color: #721c24;'>❌ INSCRIPCIONES CERRADAS</h3>";
            echo "<p>Aún hay un problema con las fechas.</p>";
        }
        echo "</div>";

        echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 5px; margin-top: 15px;'>";
        echo "<h3>📝 Próximos Pasos</h3>";
        echo "<ol>";
        echo "<li>Ir al formulario de inscripción: <a href='eventos/inscribir.php?evento=7' target='_blank'>eventos/inscribir.php?evento=7</a></li>";
        echo "<li>Verificar que el formulario esté disponible</li>";
        echo "<li>Realizar una inscripción de prueba</li>";
        echo "</ol>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px;'>";
        echo "<h3>❌ Error en la Actualización</h3>";
        echo "<p>No se pudo actualizar las fechas del evento.</p>";
        echo "</div>";
    }
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px;'>";
    echo "<h3>❌ Error</h3>";
    echo "<p>Error al actualizar: " . $e->getMessage() . "</p>";
    echo "</div>";
}

echo "<hr>";
echo "<p style='text-align: center; color: #666;'>Actualización completada - " . date('d/m/Y H:i:s') . "</p>";
