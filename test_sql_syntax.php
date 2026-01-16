<?php

require_once 'config/config.php';

echo "<h1>Prueba de Sintaxis del Archivo SQL</h1>";

$sqlFile = 'database_completa_actualizada.sql';

if (!file_exists($sqlFile)) {
    echo "<p style='color: red;'>Error: Archivo $sqlFile no encontrado.</p>";
    exit;
}

$content = file_get_contents($sqlFile);

if ($content === false) {
    echo "<p style='color: red;'>Error: No se pudo leer el archivo.</p>";
    exit;
}

echo "<p>Archivo leído correctamente. Tamaño: " . strlen($content) . " caracteres.</p>";

// Intentar parsear el SQL (básico)
$lines = explode("\n", $content);
$totalLines = count($lines);
$createCount = 0;
$insertCount = 0;

foreach ($lines as $line) {
    $line = trim($line);
    if (stripos($line, 'CREATE TABLE') === 0) {
        $createCount++;
    }
    if (stripos($line, 'INSERT INTO') === 0) {
        $insertCount++;
    }
}

echo "<p>Líneas totales: $totalLines</p>";
echo "<p>Tablas CREATE encontradas: $createCount</p>";
echo "<p>Instrucciones INSERT encontradas: $insertCount</p>";

// Verificar que no haya caracteres extraños
$invalidChars = preg_match('/[^\x20-\x7E\x0A\x0D\x09]/', $content);
if ($invalidChars) {
    echo "<p style='color: red;'>Advertencia: Se encontraron caracteres no ASCII en el archivo.</p>";
} else {
    echo "<p style='color: green;'>El archivo contiene solo caracteres válidos.</p>";
}

// Verificar que termine con COMMIT
if (stripos($content, 'COMMIT;') !== false) {
    echo "<p style='color: green;'>El archivo termina correctamente con COMMIT.</p>";
} else {
    echo "<p style='color: red;'>Error: El archivo no termina con COMMIT.</p>";
}

echo "<p style='color: green;'>Prueba de sintaxis básica completada exitosamente.</p>";
