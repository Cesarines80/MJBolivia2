<?php
require_once 'config/config.php';

echo "=== Verificación de Tabla mision_vision ===\n\n";

$db = getDB();

// 1. Verificar si la tabla existe
try {
    $stmt = $db->query("SHOW TABLES LIKE 'mision_vision'");
    $exists = $stmt->fetch();

    if ($exists) {
        echo "✅ Tabla 'mision_vision' existe\n\n";

        // 2. Ver estructura de la tabla
        echo "Estructura de la tabla:\n";
        $stmt = $db->query("DESCRIBE mision_vision");
        $columns = $stmt->fetchAll();
        foreach ($columns as $col) {
            echo "  - {$col['Field']} ({$col['Type']})\n";
        }
        echo "\n";

        // 3. Contar registros
        $stmt = $db->query("SELECT COUNT(*) as total FROM mision_vision");
        $count = $stmt->fetch();
        echo "Total de registros: {$count['total']}\n\n";

        // 4. Mostrar todos los registros
        if ($count['total'] > 0) {
            echo "Registros existentes:\n";
            $stmt = $db->query("SELECT * FROM mision_vision");
            $items = $stmt->fetchAll();
            foreach ($items as $item) {
                echo "\n  ID: {$item['id']}\n";
                echo "  Tipo: {$item['tipo']}\n";
                echo "  Título: {$item['titulo']}\n";
                echo "  Contenido: " . substr($item['contenido'], 0, 50) . "...\n";
                echo "  Imagen: " . ($item['imagen'] ?? 'Sin imagen') . "\n";
                echo "  Estado: {$item['estado']}\n";
                echo "  ---\n";
            }
        } else {
            echo "⚠️ No hay registros en la tabla\n";
            echo "\nInsertando registros por defecto...\n";

            // Insertar registros por defecto
            $defaults = [
                [
                    'tipo' => 'mision',
                    'titulo' => 'Nuestra Misión',
                    'contenido' => 'Definir y comunicar claramente nuestra misión institucional.',
                    'estado' => 'activo'
                ],
                [
                    'tipo' => 'vision',
                    'titulo' => 'Nuestra Visión',
                    'contenido' => 'Proyectar el futuro que deseamos alcanzar como institución.',
                    'estado' => 'activo'
                ],
                [
                    'tipo' => 'valores',
                    'titulo' => 'Nuestros Valores',
                    'contenido' => 'Los principios que guían nuestro actuar diario.',
                    'estado' => 'activo'
                ],
                [
                    'tipo' => 'historia',
                    'titulo' => 'Nuestra Historia',
                    'contenido' => 'El camino recorrido y las experiencias que nos han formado.',
                    'estado' => 'activo'
                ]
            ];

            $stmt = $db->prepare("
                INSERT INTO mision_vision (tipo, titulo, contenido, estado) 
                VALUES (:tipo, :titulo, :contenido, :estado)
            ");

            foreach ($defaults as $item) {
                $stmt->execute($item);
                echo "  ✅ Insertado: {$item['tipo']}\n";
            }

            echo "\n✅ Registros por defecto insertados correctamente\n";
        }
    } else {
        echo "❌ Tabla 'mision_vision' NO existe\n";
        echo "\nCreando tabla...\n";

        $db->exec("
            CREATE TABLE `mision_vision` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `tipo` enum('mision','vision','valores','historia') NOT NULL,
                `titulo` varchar(200) NOT NULL,
                `contenido` text NOT NULL,
                `imagen` varchar(255) DEFAULT NULL,
                `estado` enum('activo','inactivo') DEFAULT 'activo',
                `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                PRIMARY KEY (`id`),
                UNIQUE KEY `tipo` (`tipo`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        echo "✅ Tabla creada\n\n";
        echo "Ejecuta este script nuevamente para insertar datos por defecto.\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== Fin de la verificación ===\n";
