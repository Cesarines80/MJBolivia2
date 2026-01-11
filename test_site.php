<?php

/**
 * Script para probar que el sitio carga sin errores
 */

echo "Probando carga del sitio...\n\n";

// Capturar errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Simular carga de index.php
ob_start();
try {
    include 'index.php';
    $output = ob_get_clean();

    if (empty($output)) {
        echo "❌ ERROR: La página no generó ninguna salida\n";
    } else {
        echo "✓ Página principal cargada exitosamente\n";
        echo "✓ Longitud del contenido: " . strlen($output) . " bytes\n";

        // Verificar que no haya errores PHP en la salida
        if (
            strpos($output, 'Fatal error') !== false ||
            strpos($output, 'Parse error') !== false ||
            strpos($output, 'Warning') !== false
        ) {
            echo "❌ Se detectaron errores en la salida\n";
        } else {
            echo "✓ No se detectaron errores PHP\n";
        }
    }
} catch (Exception $e) {
    ob_end_clean();
    echo "❌ ERROR al cargar la página: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}

echo "\n";
echo "Verificando log de errores...\n";
$errorLog = file_get_contents('logs/error.log');
if (empty(trim($errorLog))) {
    echo "✓ No hay errores en el log\n";
} else {
    echo "❌ Hay errores en el log:\n";
    echo $errorLog;
}

echo "\n==============================================\n";
echo "Prueba completada\n";
echo "==============================================\n";
