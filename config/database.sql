-- Base de datos para Sitio Web Institucional
-- Base de datos: web_institucional

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET FOREIGN_KEY_CHECKS = 0;

START TRANSACTION;

SET time_zone = "+00:00";

-- Usar base de datos
USE `web_institucional`;

-- --------------------------------------------------------

-- Estructura de tabla para la tabla `administradores`
DROP TABLE IF EXISTS `administradores`;
CREATE TABLE `administradores` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `nombre` varchar(100) NOT NULL,
    `email` varchar(100) NOT NULL,
    `password` varchar(255) NOT NULL,
    `rol` enum(
        'superadmin',
        'admin',
        'editor'
    ) DEFAULT 'admin',
    `estado` enum('activo', 'inactivo') DEFAULT 'activo',
    `ultimo_acceso` datetime DEFAULT NULL,
    `intentos_fallidos` int(11) DEFAULT 0,
    `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `email` (`email`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- Volcado de datos para la tabla `administradores`
INSERT INTO
    `administradores` (
        `id`,
        `nombre`,
        `email`,
        `password`,
        `rol`,
        `estado`,
        `fecha_creacion`
    )
VALUES (
        1,
        'Administrador Principal',
        'admin@institucion.com',
        '$2y$12$YiLM5.aCDsKGL20awx2Ps.G6gLJSmspGe8MWounSPk2HylzrecO0G',
        'superadmin',
        'activo',
        NOW()
    );

-- --------------------------------------------------------

-- Estructura de tabla para la tabla `configuracion`
DROP TABLE IF EXISTS `configuracion`;
CREATE TABLE `configuracion` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `nombre_institucion` varchar(200) NOT NULL,
    `descripcion` text DEFAULT NULL,
    `logo` varchar(255) DEFAULT NULL,
    `favicon` varchar(255) DEFAULT NULL,
    `email_contacto` varchar(100) DEFAULT NULL,
    `telefono` varchar(50) DEFAULT NULL,
    `direccion` text DEFAULT NULL,
    `facebook` varchar(255) DEFAULT NULL,
    `twitter` varchar(255) DEFAULT NULL,
    `instagram` varchar(255) DEFAULT NULL,
    `youtube` varchar(255) DEFAULT NULL,
    `color_primario` varchar(7) DEFAULT '#8B7EC8',
    `color_secundario` varchar(7) DEFAULT '#B8B3D8',
    `color_acento` varchar(7) DEFAULT '#6B5B95',
    `metadescription` text DEFAULT NULL,
    `metakeywords` text DEFAULT NULL,
    `analytics_id` varchar(50) DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

INSERT INTO
    `configuracion` (
        `id`,
        `nombre_institucion`,
        `descripcion`,
        `logo`,
        `email_contacto`,
        `telefono`,
        `direccion`,
        `color_primario`,
        `color_secundario`,
        `color_acento`
    )
VALUES (
        1,
        'Nombre de tu Institución',
        'Descripción de tu institución',
        NULL,
        'contacto@institucion.com',
        '+1 234 567 8900',
        'Dirección de la institución',
        '#8B7EC8',
        '#B8B3D8',
        '#6B5B95'
    );

-- --------------------------------------------------------

-- Estructura de tabla para la tabla `mision_vision`
DROP TABLE IF EXISTS `mision_vision`;
CREATE TABLE `mision_vision` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `tipo` enum(
        'mision',
        'vision',
        'valores',
        'historia'
    ) NOT NULL,
    `titulo` varchar(200) NOT NULL,
    `contenido` text NOT NULL,
    `imagen` varchar(255) DEFAULT NULL,
    `estado` enum('activo', 'inactivo') DEFAULT 'activo',
    `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

INSERT INTO
    `mision_vision` (
        `id`,
        `tipo`,
        `titulo`,
        `contenido`
    )
VALUES (
        1,
        'mision',
        'Nuestra Misión',
        'Proporcionar educación de alta calidad y servicios excepcionales a nuestra comunidad, fomentando el desarrollo integral de cada individuo.'
    ),
    (
        2,
        'vision',
        'Nuestra Visión',
        'Ser reconocidos como una institución líder en innovación educativa, formando ciudadanos comprometidos con el cambio social positivo.'
    ),
    (
        3,
        'valores',
        'Nuestros Valores',
        'Excelencia, Integridad, Compromiso, Innovación, Responsabilidad Social'
    ),
    (
        4,
        'historia',
        'Nuestra Historia',
        'Fundada en 1990, nuestra institución ha crecido para convertirse en un referente de excelencia educativa en la región.'
    );

-- --------------------------------------------------------

-- Estructura de tabla para la tabla `carrusel`
DROP TABLE IF EXISTS `carrusel`;
CREATE TABLE `carrusel` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `titulo` varchar(200) NOT NULL,
    `descripcion` text DEFAULT NULL,
    `imagen` varchar(255) NOT NULL,
    `tipo` enum('imagen', 'video') DEFAULT 'imagen',
    `url` varchar(255) DEFAULT NULL,
    `orden` int(11) DEFAULT 0,
    `estado` enum('activo', 'inactivo') DEFAULT 'activo',
    `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- --------------------------------------------------------

-- Estructura de tabla para la tabla `eventos`
DROP TABLE IF EXISTS `eventos`;
CREATE TABLE `eventos` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `titulo` varchar(200) NOT NULL,
    `descripcion` text NOT NULL,
    `fecha_evento` date NOT NULL,
    `hora_evento` time DEFAULT NULL,
    `lugar` varchar(200) DEFAULT NULL,
    `imagen` varchar(255) DEFAULT NULL,
    `estado` enum(
        'activo',
        'inactivo',
        'finalizado'
    ) DEFAULT 'activo',
    `destacado` enum('si', 'no') DEFAULT 'no',
    `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- --------------------------------------------------------

-- Estructura de tabla para la tabla `galeria`
DROP TABLE IF EXISTS `galeria`;
CREATE TABLE `galeria` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `titulo` varchar(200) NOT NULL,
    `descripcion` text DEFAULT NULL,
    `imagen` varchar(255) NOT NULL,
    `categoria` varchar(100) DEFAULT 'general',
    `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- --------------------------------------------------------

-- Estructura de tabla para la tabla `contactos`
DROP TABLE IF EXISTS `contactos`;
CREATE TABLE `contactos` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `nombre` varchar(100) NOT NULL,
    `email` varchar(100) NOT NULL,
    `telefono` varchar(50) DEFAULT NULL,
    `asunto` varchar(200) NOT NULL,
    `mensaje` text NOT NULL,
    `estado` enum(
        'nuevo',
        'leido',
        'respondido'
    ) DEFAULT 'nuevo',
    `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- --------------------------------------------------------

-- Estructura de tabla para la tabla `paginas`
DROP TABLE IF EXISTS `paginas`;
CREATE TABLE `paginas` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `titulo` varchar(200) NOT NULL,
    `slug` varchar(200) NOT NULL,
    `contenido` text NOT NULL,
    `imagen_destacada` varchar(255) DEFAULT NULL,
    `estado` enum('publicado', 'borrador') DEFAULT 'publicado',
    `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
    `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `slug` (`slug`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- --------------------------------------------------------

-- Estructura de tabla para la tabla `sesiones_admin`
DROP TABLE IF EXISTS `sesiones_admin`;
CREATE TABLE `sesiones_admin` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `admin_id` int(11) NOT NULL,
    `token` varchar(255) NOT NULL,
    `ip_address` varchar(45) DEFAULT NULL,
    `user_agent` text DEFAULT NULL,
    `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
    `fecha_expiracion` datetime NOT NULL,
    `activa` enum('si', 'no') DEFAULT 'si',
    PRIMARY KEY (`id`),
    KEY `admin_id` (`admin_id`),
    CONSTRAINT `sesiones_admin_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `administradores` (`id`) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- --------------------------------------------------------

-- Estructura de tabla para la tabla `intentos_login`
DROP TABLE IF EXISTS `intentos_login`;
CREATE TABLE `intentos_login` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `email` varchar(100) NOT NULL,
    `ip_address` varchar(45) NOT NULL,
    `intentos` int(11) DEFAULT 1,
    `ultimo_intento` timestamp NOT NULL DEFAULT current_timestamp(),
    `bloqueado_hasta` datetime DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `email` (`email`),
    KEY `ip_address` (`ip_address`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

SET FOREIGN_KEY_CHECKS = 1;

COMMIT;
