<?php
require_once 'config/config.php';

try {
    $db = getDB();

    // SQL para crear la tabla galeria_imagenes
    $sql = "
    CREATE TABLE IF NOT EXISTS `galeria_imagenes` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `galeria_id` int(11) NOT NULL,
        `titulo` varchar(200) DEFAULT NULL,
        `descripcion` text DEFAULT NULL,
        `imagen` varchar(255) NOT NULL,
        `orden` int(11) DEFAULT 0,
        `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`),
        KEY `galeria_id` (`galeria_id`),
        CONSTRAINT `galeria_imagenes_ibfk_1` FOREIGN KEY (`galeria_id`) REFERENCES `galeria` (`id`) ON DELETE CASCADE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;
    ";

    $stmt = $db->prepare($sql);
    $result = $stmt->execute();

    if ($result) {
        echo "Tabla 'galeria_imagenes' creada exitosamente.\n";
    } else {
        echo "Error al crear la tabla.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
