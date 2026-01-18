<?php
// Archivo de prueba simple para verificar el servidor
echo "✅ Servidor funcionando correctamente<br>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Fecha/Hora: " . date('Y-m-d H:i:s') . "<br>";
echo "Zona horaria: " . date_default_timezone_get() . "<br>";

// Probar conexión a base de datos
try {
    require_once 'config/config.php';
    $db = getDB();
    echo "✅ Conexión a base de datos exitosa<br>";
} catch (Exception $e) {
    echo "❌ Error de base de datos: " . $e->getMessage() . "<br>";
}
