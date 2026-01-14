-- Crear tabla para imágenes adicionales de galería
USE `web_institucional`;

DROP TABLE IF EXISTS `galeria_imagenes`;

CREATE TABLE `galeria_imagenes` (
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