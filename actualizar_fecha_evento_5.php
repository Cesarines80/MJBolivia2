<?php
require_once 'config/config.php';

echo "<h1>Actualizar Fecha de Inicio de Inscripción - Evento 5</h1>";
echo "<hr>";

$db = getDB();

// Obtener datos actuales
$stmt = $db->prepare('SELECT id, nombre, fecha_inicio_inscripcion, fecha_fin_inscripcion FROM eventos WHERE id = 5');
$stmt->execute();
$evento = $stmt->fetch(PDO::FETCH_ASSOC);

if ($evento) {
    echo "<h2>Datos Actuales:</h2>";
    echo "<p><strong>Evento:</strong> {$evento['nombre']}</p>";
    echo "<p><strong>Fecha Inicio Inscripción:</strong> {$evento['fecha_inicio_inscripcion']}</p>";
    echo "<p><strong>Fecha Fin Inscripción:</strong> {$evento['fecha_fin_inscripcion']}</p>";

    echo "<hr>";

    // Actualizar fecha de inicio a hoy o antes
    $nuevaFechaInicio = '2026-01-10';

    $stmt = $db->prepare('UPDATE eventos SET fecha_inicio_inscripcion = ? WHERE id = 5');
    $result = $stmt->execute([$nuevaFechaInicio]);

    if ($result) {
        echo "<p style='color: green;'>✅ <strong>Fecha actualizada exitosamente</strong></p>";
        echo "<p><strong>Nueva Fecha Inicio:</strong> {$nuevaFechaInicio}</p>";

        // Verificar actualización
        $stmt = $db->prepare('SELECT fecha_inicio_inscripcion, fecha_fin_inscripcion FROM eventos WHERE id = 5');
        $stmt->execute();
        $eventoActualizado = $stmt->fetch(PDO::FETCH_ASSOC);

        echo "<hr>";
        echo "<h2>Verificación:</h2>";
        echo "<p><strong>Fecha Inicio:</strong> {$eventoActualizado['fecha_inicio_inscripcion']}</p>";
        echo "<p><strong>Fecha Fin:</strong> {$eventoActualizado['fecha_fin_inscripcion']}</p>";

        $hoy = date('Y-m-d');
        $inscripcionAbierta = ($hoy >= $eventoActualizado['fecha_inicio_inscripcion'] && $hoy <= $eventoActualizado['fecha_fin_inscripcion']);

        echo "<hr>";
        echo "<h2>Estado de Inscripciones:</h2>";
        if ($inscripcionAbierta) {
            echo "<p style='color: green; font-size: 20px;'>✅ <strong>INSCRIPCIONES ABIERTAS</strong></p>";
        } else {
            echo "<p style='color: red; font-size: 20px;'>❌ <strong>INSCRIPCIONES CERRADAS</strong></p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Error al actualizar la fecha</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Evento no encontrado</p>";
}
