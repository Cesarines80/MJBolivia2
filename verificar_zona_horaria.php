<?php
require_once 'config/config.php';

echo "<!DOCTYPE html>";
echo "<html lang='es'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Verificación de Zona Horaria</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; max-width: 1000px; margin: 50px auto; padding: 20px; background: #f5f5f5; }";
echo "h1 { color: #333; border-bottom: 3px solid #8B7EC8; padding-bottom: 10px; }";
echo "h2 { color: #6B5B95; margin-top: 30px; }";
echo "table { width: 100%; border-collapse: collapse; margin: 15px 0; background: white; }";
echo "th, td { padding: 12px; text-align: left; border: 1px solid #ddd; }";
echo "th { background: #8B7EC8; color: white; }";
echo "tr:nth-child(even) { background: #f9f9f9; }";
echo ".success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; margin: 10px 0; border-radius: 5px; }";
echo ".warning { background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 15px; margin: 10px 0; border-radius: 5px; }";
echo ".info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 15px; margin: 10px 0; border-radius: 5px; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<h1>🌍 Verificación de Zona Horaria</h1>";
echo "<hr>";

// Zona horaria configurada en PHP
echo "<h2>📍 Configuración de PHP</h2>";
$zonaActual = date_default_timezone_get();
echo "<div class='info'>";
echo "<p><strong>Zona Horaria Actual:</strong> <span style='font-size: 1.2em; color: #0c5460;'>{$zonaActual}</span></p>";
echo "</div>";

// Fecha y hora actual del servidor
echo "<h2>🕐 Fecha y Hora del Servidor</h2>";
echo "<table>";
echo "<tr><th>Concepto</th><th>Valor</th></tr>";
echo "<tr><td>Fecha (Y-m-d)</td><td><strong>" . date('Y-m-d') . "</strong></td></tr>";
echo "<tr><td>Hora (H:i:s)</td><td><strong>" . date('H:i:s') . "</strong></td></tr>";
echo "<tr><td>Fecha y Hora Completa</td><td><strong>" . date('Y-m-d H:i:s') . "</strong></td></tr>";
echo "<tr><td>Día de la Semana</td><td><strong>" . date('l, d F Y') . "</strong></td></tr>";
echo "<tr><td>Formato Latino</td><td><strong>" . date('d/m/Y H:i:s') . "</strong></td></tr>";
echo "</table>";

// Timestamp
echo "<h2>⏱️ Timestamp Unix</h2>";
echo "<div class='info'>";
echo "<p><strong>Timestamp:</strong> " . time() . "</p>";
echo "<p><small>Segundos desde el 1 de enero de 1970 00:00:00 UTC</small></p>";
echo "</div>";

// Información de zona horaria
echo "<h2>ℹ️ Información Detallada</h2>";
$timezone = new DateTimeZone($zonaActual);
$datetime = new DateTime('now', $timezone);
echo "<table>";
echo "<tr><th>Propiedad</th><th>Valor</th></tr>";
echo "<tr><td>Offset UTC</td><td><strong>" . $datetime->format('P') . "</strong></td></tr>";
echo "<tr><td>Nombre de Zona</td><td><strong>" . $timezone->getName() . "</strong></td></tr>";
echo "<tr><td>Abreviatura</td><td><strong>" . $datetime->format('T') . "</strong></td></tr>";
echo "</table>";

// Comparación con otras zonas
echo "<h2>🌎 Comparación con Otras Zonas Horarias</h2>";
echo "<table>";
echo "<tr><th>País/Región</th><th>Zona Horaria</th><th>Fecha y Hora</th><th>Diferencia</th></tr>";

$zonas = [
    'Bolivia' => 'America/La_Paz',
    'Perú' => 'America/Lima',
    'Colombia' => 'America/Bogota',
    'Argentina' => 'America/Argentina/Buenos_Aires',
    'Chile' => 'America/Santiago',
    'México' => 'America/Mexico_City',
    'España' => 'Europe/Madrid',
    'UTC' => 'UTC'
];

foreach ($zonas as $nombre => $zona) {
    $tz = new DateTimeZone($zona);
    $dt = new DateTime('now', $tz);
    $offset = $dt->format('P');

    $esActual = ($zona === $zonaActual);
    $estilo = $esActual ? "background: #d4edda; font-weight: bold;" : "";

    echo "<tr style='{$estilo}'>";
    echo "<td>{$nombre}" . ($esActual ? " ✅" : "") . "</td>";
    echo "<td>{$zona}</td>";
    echo "<td>" . $dt->format('Y-m-d H:i:s') . "</td>";
    echo "<td>{$offset}</td>";
    echo "</tr>";
}
echo "</table>";

// Verificar configuración de MySQL
echo "<h2>🗄️ Zona Horaria de MySQL</h2>";
try {
    $db = getDB();
    $stmt = $db->query("SELECT @@global.time_zone as global_tz, @@session.time_zone as session_tz, NOW() as mysql_time");
    $mysqlTz = $stmt->fetch(PDO::FETCH_ASSOC);

    echo "<table>";
    echo "<tr><th>Configuración</th><th>Valor</th></tr>";
    echo "<tr><td>Zona Horaria Global</td><td><strong>{$mysqlTz['global_tz']}</strong></td></tr>";
    echo "<tr><td>Zona Horaria de Sesión</td><td><strong>{$mysqlTz['session_tz']}</strong></td></tr>";
    echo "<tr><td>Hora Actual de MySQL</td><td><strong>{$mysqlTz['mysql_time']}</strong></td></tr>";
    echo "</table>";
} catch (Exception $e) {
    echo "<div class='warning'>";
    echo "<p>⚠️ No se pudo verificar la zona horaria de MySQL: " . $e->getMessage() . "</p>";
    echo "</div>";
}

// Recomendación
echo "<h2>🔧 Recomendación</h2>";
if ($zonaActual === 'America/La_Paz') {
    echo "<div class='success'>";
    echo "<h3>✅ Zona horaria configurada correctamente para Bolivia</h3>";
    echo "<p>Tu servidor PHP está configurado con la zona horaria correcta.</p>";
    echo "</div>";
} else {
    echo "<div class='warning'>";
    echo "<h3>⚠️ Zona horaria actual: {$zonaActual}</h3>";
    echo "<p>Para Bolivia, se recomienda configurar: <strong>America/La_Paz</strong></p>";
    echo "<p><strong>Cómo configurar:</strong></p>";
    echo "<ol>";
    echo "<li>Abrir el archivo: <code>config/config.php</code></li>";
    echo "<li>Agregar al inicio (después de <code><?php</code>):</li>";
    echo "<li><code>date_default_timezone_set('America/La_Paz');</code></li>";
    echo "<li>Guardar y recargar esta página</li>";
    echo "</ol>";
    echo "</div>";
}

// Información adicional
echo "<h2>📚 Información Adicional</h2>";
echo "<div class='info'>";
echo "<h3>Formatos de Fecha Comunes en PHP:</h3>";
echo "<table>";
echo "<tr><th>Formato</th><th>Código</th><th>Ejemplo</th></tr>";
echo "<tr><td>ISO 8601</td><td>date('Y-m-d')</td><td>" . date('Y-m-d') . "</td></tr>";
echo "<tr><td>Latino</td><td>date('d/m/Y')</td><td>" . date('d/m/Y') . "</td></tr>";
echo "<tr><td>USA</td><td>date('m/d/Y')</td><td>" . date('m/d/Y') . "</td></tr>";
echo "<tr><td>Con Hora</td><td>date('Y-m-d H:i:s')</td><td>" . date('Y-m-d H:i:s') . "</td></tr>";
echo "<tr><td>Completo</td><td>date('l, d F Y')</td><td>" . date('l, d F Y') . "</td></tr>";
echo "<tr><td>Latino con Hora</td><td>date('d/m/Y H:i:s')</td><td>" . date('d/m/Y H:i:s') . "</td></tr>";
echo "</table>";
echo "</div>";

echo "<hr>";
echo "<p style='text-align: center; color: #666;'>";
echo "Verificación completada - " . date('d/m/Y H:i:s') . "<br>";
echo "Para más información, consulta: <strong>CONFIGURACION_ZONA_HORARIA.md</strong>";
echo "</p>";

echo "</body>";
echo "</html>";
