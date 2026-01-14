-- Crear tabla para galería de eventos
USE `web_institucional`;

DROP TABLE IF EXISTS `eventos_galeria`;
CREATE TABLE `eventos_galeria` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `evento_id` int(11) NOT NULL,
    `titulo` varchar(200) DEFAULT NULL,
    `descripcion` text DEFAULT NULL,
    `imagen` varchar(255) NOT NULL,
    `orden` int(11) DEFAULT 0,
    `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `evento_id` (`evento_id`),
    CONSTRAINT `eventos_galeria_ibfk_1` FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;
