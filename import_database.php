<?php

/**
 * Script para importar la base de datos
 */

echo "Importando base de datos...\n\n";

// Configuración
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'web_institucional';
$sqlFile = 'install_database.sql';

try {
    // Conectar sin seleccionar base de datos
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "✓ Conexión a MySQL exitosa\n";

    // Leer el archivo SQL
    if (!file_exists($sqlFile)) {
        throw new Exception("El archivo $sqlFile no existe");
    }

    $sql = file_get_contents($sqlFile);
    echo "✓ Archivo SQL leído correctamente\n";

    // Dividir en comandos individuales
    $commands = array_filter(
        array_map('trim', explode(';', $sql)),
        function ($cmd) {
            return !empty($cmd) &&
                !preg_match('/^--/', $cmd) &&
                !preg_match('/^\/\*/', $cmd);
        }
    );

    echo "✓ Total de comandos SQL: " . count($commands) . "\n\n";
    echo "Ejecutando comandos...\n";

    $executed = 0;
    $errors = 0;

    foreach ($commands as $command) {
        try {
            $pdo->exec($command);
            $executed++;

            // Mostrar progreso cada 10 comandos
            if ($executed % 10 == 0) {
                echo "  Ejecutados: $executed comandos\n";
            }
        } catch (PDOException $e) {
            $errors++;
            // Solo mostrar errores que no sean "tabla ya existe"
            if (strpos($e->getMessage(), 'already exists') === false) {
                echo "  ⚠ Error: " . $e->getMessage() . "\n";
            }
        }
    }

    echo "\n✓ Importación completada\n";
    echo "  - Comandos ejecutados: $executed\n";
    echo "  - Errores: $errors\n\n";

    // Verificar que la base de datos existe
    $pdo->exec("USE $dbname");
    echo "✓ Base de datos '$dbname' seleccionada\n";

    // Contar tablas
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "✓ Total de tablas creadas: " . count($tables) . "\n\n";

    echo "Tablas principales:\n";
    $mainTables = [
        'usuarios',
        'eventos',
        'inscripciones_eventos',
        'administradores',
        'configuracion',
        'inscripciones',
        'configuracion_inscripciones'
    ];

    foreach ($mainTables as $table) {
        if (in_array($table, $tables)) {
            echo "  ✓ $table\n";
        } else {
            echo "  ✗ $table (NO EXISTE)\n";
        }
    }

    echo "\n==============================================\n";
    echo "IMPORTACIÓN EXITOSA\n";
    echo "==============================================\n";
} catch (Exception $e) {
    echo "\n✗ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
