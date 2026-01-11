<?php

/**
 * Script para cambiar el símbolo de moneda de $ a Bs. en todo el sistema
 */

echo "<h1>Cambio de Moneda: $ → Bs.</h1>";
echo "<hr>";

$archivos = [
    'inscripciones/reportes.php',
    'inscripciones/index.php',
    'eventos/inscribir.php',
    'admin/inscripciones.php',
    'admin/reportes-evento.php',
    'admin/mis-eventos.php',
    'admin/inscripciones-evento.php',
    'admin/eventos.php'
];

$cambiosRealizados = 0;
$archivosModificados = [];

foreach ($archivos as $archivo) {
    if (!file_exists($archivo)) {
        echo "<p style='color: orange;'>⚠️ Archivo no encontrado: {$archivo}</p>";
        continue;
    }

    $contenido = file_get_contents($archivo);
    $contenidoOriginal = $contenido;

    // Reemplazar $<?php con Bs. <?php
    $contenido = preg_replace('/\$<\?php/', 'Bs. <?php', $contenido);

    // Reemplazar $0.00 con Bs. 0.00
    $contenido = preg_replace('/\$(\d+\.?\d*)/', 'Bs. $1', $contenido);

    // Reemplazar "Total: $" con "Total: Bs."
    $contenido = str_replace('Total: $', 'Total: Bs. ', $contenido);

    // Reemplazar ícono de dólar con ícono de moneda
    $contenido = str_replace('fa-dollar-sign', 'fa-money-bill', $contenido);

    if ($contenido !== $contenidoOriginal) {
        file_put_contents($archivo, $contenido);
        $cambiosRealizados++;
        $archivosModificados[] = $archivo;
        echo "<p style='color: green;'>✅ Modificado: {$archivo}</p>";
    } else {
        echo "<p style='color: gray;'>➖ Sin cambios: {$archivo}</p>";
    }
}

echo "<hr>";
echo "<h2>Resumen</h2>";
echo "<p><strong>Archivos modificados:</strong> {$cambiosRealizados}</p>";

if (!empty($archivosModificados)) {
    echo "<h3>Lista de archivos modificados:</h3>";
    echo "<ul>";
    foreach ($archivosModificados as $archivo) {
        echo "<li>{$archivo}</li>";
    }
    echo "</ul>";
}

echo "<hr>";
echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; border: 1px solid #c3e6cb;'>";
echo "<h3>✅ Cambio de moneda completado</h3>";
echo "<p>Todos los símbolos $ han sido reemplazados por Bs. (Bolivianos)</p>";
echo "<p><strong>Nota:</strong> Verifica que los cambios se vean correctamente en el sistema.</p>";
echo "</div>";
