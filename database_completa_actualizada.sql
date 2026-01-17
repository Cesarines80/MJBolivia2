-- Base de datos completa actualizada
-- Exportado el 2026-01-17 15:17:19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET FOREIGN_KEY_CHECKS = 0;

START TRANSACTION;

USE `web_institucional`;

-- Estructura de la tabla `administradores`
DROP TABLE IF EXISTS `administradores`;
CREATE TABLE `administradores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('superadmin','admin','editor') DEFAULT 'admin',
  `estado` enum('activo','inactivo') DEFAULT 'activo',
  `ultimo_acceso` datetime DEFAULT NULL,
  `intentos_fallidos` int(11) DEFAULT 0,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcado de datos para la tabla `administradores`
INSERT INTO `administradores` (`id`, `nombre`, `email`, `password`, `rol`, `estado`, `ultimo_acceso`, `intentos_fallidos`, `fecha_creacion`) VALUES ('1', 'Administrador Principal', 'admin@institucion.com', '$2y$12$YiLM5.aCDsKGL20awx2Ps.G6gLJSmspGe8MWounSPk2HylzrecO0G', 'superadmin', 'activo', '2026-01-14 12:26:32', '0', '2026-01-10 18:33:31');

-- Estructura de la tabla `carrusel`
DROP TABLE IF EXISTS `carrusel`;
CREATE TABLE `carrusel` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(200) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `imagen` varchar(255) NOT NULL,
  `tipo` enum('imagen','video') DEFAULT 'imagen',
  `url` varchar(255) DEFAULT NULL,
  `orden` int(11) DEFAULT 0,
  `estado` enum('activo','inactivo') DEFAULT 'activo',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcado de datos para la tabla `carrusel`
INSERT INTO `carrusel` (`id`, `titulo`, `descripcion`, `imagen`, `tipo`, `url`, `orden`, `estado`, `fecha_creacion`) VALUES ('6', 'Test Debug 1768089174', 'Descripción de prueba', '6962e8f9d31e9_1768089849.jpg', 'imagen', '', '0', 'activo', '2026-01-10 19:52:54');
INSERT INTO `carrusel` (`id`, `titulo`, `descripcion`, `imagen`, `tipo`, `url`, `orden`, `estado`, `fecha_creacion`) VALUES ('11', 'Liderazgo Espiritual', 'fghfg hfghfg hfgh hfgh hfgh hfg', '6967c3f074a60_1768408048.jpeg', 'imagen', '', '1', 'activo', '2026-01-14 12:27:28');
INSERT INTO `carrusel` (`id`, `titulo`, `descripcion`, `imagen`, `tipo`, `url`, `orden`, `estado`, `fecha_creacion`) VALUES ('12', 'vnbnvb', 'vbnvfghfgh hfgh', '6968d6f45cb9b_1768478452.jpeg', 'imagen', '', '0', 'activo', '2026-01-15 08:00:52');

-- Estructura de la tabla `configuracion`
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcado de datos para la tabla `configuracion`
INSERT INTO `configuracion` (`id`, `nombre_institucion`, `descripcion`, `logo`, `favicon`, `email_contacto`, `telefono`, `direccion`, `facebook`, `twitter`, `instagram`, `youtube`, `color_primario`, `color_secundario`, `color_acento`, `metadescription`, `metakeywords`, `analytics_id`) VALUES ('1', 'MJ-Bolivia', 'Ministerio de Jovenes de Bolivia', '6962f06430530_1768091748.jpeg', NULL, 'idp.bolivia.mj@gmail.com', '+1 591 75401107', 'Bolivia', 'https://www.facebook.com/mjboliviaoficial?locale=es_LA', '', '', 'https://www.youtube.com/@ministeriodejovenesbolivia', '#7c3fee', '#b8b3d8', '#d7bde5', '', '', '');

-- Estructura de la tabla `configuracion_eventos`
DROP TABLE IF EXISTS `configuracion_eventos`;
CREATE TABLE `configuracion_eventos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `evento_id` int(11) NOT NULL,
  `precio_base` decimal(10,2) DEFAULT 0.00,
  `precio_alojamiento` decimal(10,2) DEFAULT 0.00,
  `max_participantes` int(11) DEFAULT 200,
  `requiere_aprobacion` tinyint(1) DEFAULT 0,
  `instrucciones_pago` text DEFAULT NULL,
  `campos_extra` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`campos_extra`)),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `descuento_fecha1` date DEFAULT NULL,
  `descuento_costo1` decimal(10,2) DEFAULT 0.00,
  `descuento_fecha2` date DEFAULT NULL,
  `descuento_costo2` decimal(10,2) DEFAULT 0.00,
  `descuento_fecha3` date DEFAULT NULL,
  `descuento_costo3` decimal(10,2) DEFAULT 0.00,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_evento` (`evento_id`),
  CONSTRAINT `configuracion_eventos_ibfk_1` FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcado de datos para la tabla `configuracion_eventos`
INSERT INTO `configuracion_eventos` (`id`, `evento_id`, `precio_base`, `precio_alojamiento`, `max_participantes`, `requiere_aprobacion`, `instrucciones_pago`, `campos_extra`, `fecha_actualizacion`, `descuento_fecha1`, `descuento_costo1`, `descuento_fecha2`, `descuento_costo2`, `descuento_fecha3`, `descuento_costo3`) VALUES ('1', '2', '100.00', '50.00', '200', '0', 'Realizar el pago en la cuenta bancaria proporcionada y enviar el comprobante.', NULL, '2026-01-10 20:14:25', NULL, '0.00', NULL, '0.00', NULL, '0.00');
INSERT INTO `configuracion_eventos` (`id`, `evento_id`, `precio_base`, `precio_alojamiento`, `max_participantes`, `requiere_aprobacion`, `instrucciones_pago`, `campos_extra`, `fecha_actualizacion`, `descuento_fecha1`, `descuento_costo1`, `descuento_fecha2`, `descuento_costo2`, `descuento_fecha3`, `descuento_costo3`) VALUES ('4', '5', '100.00', '50.00', '200', '0', 'Realizar el pago en la cuenta bancaria proporcionada y enviar el comprobante.', NULL, '2026-01-11 00:22:29', NULL, '0.00', NULL, '0.00', NULL, '0.00');
INSERT INTO `configuracion_eventos` (`id`, `evento_id`, `precio_base`, `precio_alojamiento`, `max_participantes`, `requiere_aprobacion`, `instrucciones_pago`, `campos_extra`, `fecha_actualizacion`, `descuento_fecha1`, `descuento_costo1`, `descuento_fecha2`, `descuento_costo2`, `descuento_fecha3`, `descuento_costo3`) VALUES ('11', '12', '100.00', '50.00', '200', '0', 'Realizar el pago en la cuenta bancaria proporcionada y enviar el comprobante.', NULL, '2026-01-15 21:56:34', NULL, '0.00', NULL, '0.00', NULL, '0.00');
INSERT INTO `configuracion_eventos` (`id`, `evento_id`, `precio_base`, `precio_alojamiento`, `max_participantes`, `requiere_aprobacion`, `instrucciones_pago`, `campos_extra`, `fecha_actualizacion`, `descuento_fecha1`, `descuento_costo1`, `descuento_fecha2`, `descuento_costo2`, `descuento_fecha3`, `descuento_costo3`) VALUES ('12', '13', '100.00', '50.00', '200', '0', 'Realizar el pago en la cuenta bancaria proporcionada y enviar el comprobante.', NULL, '2026-01-15 22:04:47', NULL, '0.00', NULL, '0.00', NULL, '0.00');
INSERT INTO `configuracion_eventos` (`id`, `evento_id`, `precio_base`, `precio_alojamiento`, `max_participantes`, `requiere_aprobacion`, `instrucciones_pago`, `campos_extra`, `fecha_actualizacion`, `descuento_fecha1`, `descuento_costo1`, `descuento_fecha2`, `descuento_costo2`, `descuento_fecha3`, `descuento_costo3`) VALUES ('13', '14', '100.00', '50.00', '200', '0', 'Realizar el pago en la cuenta bancaria proporcionada y enviar el comprobante.', NULL, '2026-01-16 07:12:02', NULL, '0.00', NULL, '0.00', NULL, '0.00');
INSERT INTO `configuracion_eventos` (`id`, `evento_id`, `precio_base`, `precio_alojamiento`, `max_participantes`, `requiere_aprobacion`, `instrucciones_pago`, `campos_extra`, `fecha_actualizacion`, `descuento_fecha1`, `descuento_costo1`, `descuento_fecha2`, `descuento_costo2`, `descuento_fecha3`, `descuento_costo3`) VALUES ('14', '15', '100.00', '50.00', '200', '0', 'Realizar el pago en la cuenta bancaria proporcionada y enviar el comprobante.', NULL, '2026-01-16 23:34:34', NULL, '0.00', NULL, '0.00', NULL, '0.00');
INSERT INTO `configuracion_eventos` (`id`, `evento_id`, `precio_base`, `precio_alojamiento`, `max_participantes`, `requiere_aprobacion`, `instrucciones_pago`, `campos_extra`, `fecha_actualizacion`, `descuento_fecha1`, `descuento_costo1`, `descuento_fecha2`, `descuento_costo2`, `descuento_fecha3`, `descuento_costo3`) VALUES ('16', '17', '0.00', '0.00', '200', '0', '', NULL, '2026-01-17 00:30:14', '2026-01-26', '200.00', NULL, '0.00', NULL, '0.00');
INSERT INTO `configuracion_eventos` (`id`, `evento_id`, `precio_base`, `precio_alojamiento`, `max_participantes`, `requiere_aprobacion`, `instrucciones_pago`, `campos_extra`, `fecha_actualizacion`, `descuento_fecha1`, `descuento_costo1`, `descuento_fecha2`, `descuento_costo2`, `descuento_fecha3`, `descuento_costo3`) VALUES ('18', '18', '100.00', '50.00', '200', '0', 'Realizar el pago en la cuenta bancaria proporcionada y enviar el comprobante.', NULL, '2026-01-17 13:12:00', NULL, '0.00', NULL, '0.00', NULL, '0.00');

-- Estructura de la tabla `configuracion_global`
DROP TABLE IF EXISTS `configuracion_global`;
CREATE TABLE `configuracion_global` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clave` varchar(100) NOT NULL,
  `valor` text DEFAULT NULL,
  `tipo` varchar(20) DEFAULT 'string',
  `descripcion` text DEFAULT NULL,
  `editable` tinyint(1) DEFAULT 1,
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `clave` (`clave`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcado de datos para la tabla `configuracion_global`
INSERT INTO `configuracion_global` (`id`, `clave`, `valor`, `tipo`, `descripcion`, `editable`, `fecha_actualizacion`) VALUES ('1', 'nombre_sistema', 'Sistema de Gesti├│n de Eventos', 'string', 'Nombre del sistema', '1', '2026-01-10 18:33:31');
INSERT INTO `configuracion_global` (`id`, `clave`, `valor`, `tipo`, `descripcion`, `editable`, `fecha_actualizacion`) VALUES ('2', 'correo_contacto', 'contacto@example.com', 'string', 'Correo de contacto', '1', '2026-01-10 18:33:31');
INSERT INTO `configuracion_global` (`id`, `clave`, `valor`, `tipo`, `descripcion`, `editable`, `fecha_actualizacion`) VALUES ('3', 'telefono_contacto', '+1-234-567-8900', 'string', 'Tel├®fono de contacto', '1', '2026-01-10 18:33:31');
INSERT INTO `configuracion_global` (`id`, `clave`, `valor`, `tipo`, `descripcion`, `editable`, `fecha_actualizacion`) VALUES ('4', 'direccion_contacto', 'Calle Principal 123, Ciudad', 'string', 'Direcci├│n f├¡sica', '1', '2026-01-10 18:33:31');
INSERT INTO `configuracion_global` (`id`, `clave`, `valor`, `tipo`, `descripcion`, `editable`, `fecha_actualizacion`) VALUES ('5', 'max_intentos_login', '5', 'integer', 'M├íximo de intentos de login antes de bloqueo', '1', '2026-01-10 18:33:31');
INSERT INTO `configuracion_global` (`id`, `clave`, `valor`, `tipo`, `descripcion`, `editable`, `fecha_actualizacion`) VALUES ('6', 'tiempo_bloqueo_minutos', '30', 'integer', 'Tiempo de bloqueo en minutos', '1', '2026-01-10 18:33:31');
INSERT INTO `configuracion_global` (`id`, `clave`, `valor`, `tipo`, `descripcion`, `editable`, `fecha_actualizacion`) VALUES ('7', 'formato_fecha', 'd/m/Y', 'string', 'Formato de fecha para el sistema', '1', '2026-01-10 18:33:31');
INSERT INTO `configuracion_global` (`id`, `clave`, `valor`, `tipo`, `descripcion`, `editable`, `fecha_actualizacion`) VALUES ('8', 'moneda_predeterminada', 'USD', 'string', 'Moneda predeterminada', '1', '2026-01-10 18:33:31');

-- Estructura de la tabla `configuracion_inscripciones`
DROP TABLE IF EXISTS `configuracion_inscripciones`;
CREATE TABLE `configuracion_inscripciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `monto_inscripcion` decimal(10,2) NOT NULL DEFAULT 0.00,
  `monto_alojamiento` decimal(10,2) NOT NULL DEFAULT 0.00,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `limite_inscripciones` int(11) DEFAULT NULL,
  `instrucciones_pago` text DEFAULT NULL,
  `estado` enum('activo','inactivo') DEFAULT 'activo',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcado de datos para la tabla `configuracion_inscripciones`
INSERT INTO `configuracion_inscripciones` (`id`, `monto_inscripcion`, `monto_alojamiento`, `fecha_inicio`, `fecha_fin`, `limite_inscripciones`, `instrucciones_pago`, `estado`, `fecha_creacion`) VALUES ('1', '150.00', '50.00', '2026-01-01', '2026-12-31', NULL, '1. Efectivo: Pago directo en secretar├¡a\n2. QR: Escanea el c├│digo proporcionado\n3. Dep├│sito: Cuenta bancaria 1234567890\n4. Beca: Aplican restricciones', 'activo', '2026-01-10 18:33:31');

-- Estructura de la tabla `contactos`
DROP TABLE IF EXISTS `contactos`;
CREATE TABLE `contactos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `asunto` varchar(200) NOT NULL,
  `mensaje` text NOT NULL,
  `estado` enum('nuevo','leido','respondido') DEFAULT 'nuevo',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Estructura de la tabla `eventos`
DROP TABLE IF EXISTS `eventos`;
CREATE TABLE `eventos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(200) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_evento` date DEFAULT NULL,
  `hora_evento` time DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `fecha_inicio_inscripcion` date DEFAULT NULL,
  `fecha_fin_inscripcion` date DEFAULT NULL,
  `lugar` varchar(200) DEFAULT NULL,
  `imagen_portada` varchar(255) DEFAULT NULL,
  `costo_inscripcion` decimal(10,2) DEFAULT 0.00,
  `costo_alojamiento` decimal(10,2) DEFAULT 0.00,
  `alojamiento_opcion1_desc` varchar(255) DEFAULT NULL,
  `alojamiento_opcion1_costo` decimal(10,2) DEFAULT 0.00,
  `alojamiento_opcion2_desc` varchar(255) DEFAULT NULL,
  `alojamiento_opcion2_costo` decimal(10,2) DEFAULT 0.00,
  `alojamiento_opcion3_desc` varchar(255) DEFAULT NULL,
  `alojamiento_opcion3_costo` decimal(10,2) DEFAULT 0.00,
  `edad_rango1_min` int(11) DEFAULT NULL,
  `edad_rango1_max` int(11) DEFAULT NULL,
  `costo_rango1` decimal(10,2) DEFAULT 0.00,
  `edad_rango2_min` int(11) DEFAULT NULL,
  `edad_rango2_max` int(11) DEFAULT NULL,
  `costo_rango2` decimal(10,2) DEFAULT 0.00,
  `imagen` varchar(255) DEFAULT NULL,
  `estado` enum('activo','inactivo','finalizado') DEFAULT 'activo',
  `color` varchar(7) DEFAULT NULL,
  `destacado` enum('si','no') DEFAULT 'no',
  `creado_por` int(11) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_fechas` (`fecha_inicio_inscripcion`,`fecha_fin_inscripcion`),
  KEY `idx_estado` (`estado`),
  KEY `idx_creado_por` (`creado_por`),
  CONSTRAINT `eventos_ibfk_1` FOREIGN KEY (`creado_por`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcado de datos para la tabla `eventos`
INSERT INTO `eventos` (`id`, `titulo`, `descripcion`, `fecha_inicio`, `fecha_evento`, `hora_evento`, `fecha_fin`, `fecha_inicio_inscripcion`, `fecha_fin_inscripcion`, `lugar`, `imagen_portada`, `costo_inscripcion`, `costo_alojamiento`, `alojamiento_opcion1_desc`, `alojamiento_opcion1_costo`, `alojamiento_opcion2_desc`, `alojamiento_opcion2_costo`, `alojamiento_opcion3_desc`, `alojamiento_opcion3_costo`, `edad_rango1_min`, `edad_rango1_max`, `costo_rango1`, `edad_rango2_min`, `edad_rango2_max`, `costo_rango2`, `imagen`, `estado`, `color`, `destacado`, `creado_por`, `fecha_creacion`) VALUES ('2', 'Campamento', 'Tiquipaya', NULL, '2026-01-19', NULL, '2026-01-25', '2026-01-10', '2026-01-19', 'cbba', NULL, '100.00', '30.00', NULL, '0.00', NULL, '0.00', NULL, '0.00', NULL, NULL, '0.00', NULL, NULL, '0.00', NULL, 'inactivo', NULL, 'no', '1', '2026-01-10 20:14:25');
INSERT INTO `eventos` (`id`, `titulo`, `descripcion`, `fecha_inicio`, `fecha_evento`, `hora_evento`, `fecha_fin`, `fecha_inicio_inscripcion`, `fecha_fin_inscripcion`, `lugar`, `imagen_portada`, `costo_inscripcion`, `costo_alojamiento`, `alojamiento_opcion1_desc`, `alojamiento_opcion1_costo`, `alojamiento_opcion2_desc`, `alojamiento_opcion2_costo`, `alojamiento_opcion3_desc`, `alojamiento_opcion3_costo`, `edad_rango1_min`, `edad_rango1_max`, `costo_rango1`, `edad_rango2_min`, `edad_rango2_max`, `costo_rango2`, `imagen`, `estado`, `color`, `destacado`, `creado_por`, `fecha_creacion`) VALUES ('5', 'Retiro Nacional', 'Departamento de La Paz', NULL, '2026-01-19', NULL, '2026-01-25', '2026-01-10', '2026-01-19', 'La Paz', NULL, '200.00', '50.00', NULL, '0.00', NULL, '0.00', NULL, '0.00', NULL, NULL, '0.00', NULL, NULL, '0.00', '69632585671d3_1768105349.jpeg', 'inactivo', NULL, 'no', '1', '2026-01-11 00:22:29');
INSERT INTO `eventos` (`id`, `titulo`, `descripcion`, `fecha_inicio`, `fecha_evento`, `hora_evento`, `fecha_fin`, `fecha_inicio_inscripcion`, `fecha_fin_inscripcion`, `lugar`, `imagen_portada`, `costo_inscripcion`, `costo_alojamiento`, `alojamiento_opcion1_desc`, `alojamiento_opcion1_costo`, `alojamiento_opcion2_desc`, `alojamiento_opcion2_costo`, `alojamiento_opcion3_desc`, `alojamiento_opcion3_costo`, `edad_rango1_min`, `edad_rango1_max`, `costo_rango1`, `edad_rango2_min`, `edad_rango2_max`, `costo_rango2`, `imagen`, `estado`, `color`, `destacado`, `creado_por`, `fecha_creacion`) VALUES ('12', 'isaacar', 'añañañao', '2026-02-02', '2026-02-10', NULL, '2026-02-08', '2026-01-15', '2026-02-02', 'Santa Cruz', '69699ad2386d4_1768528594.jpeg', '200.00', '10.00', NULL, '0.00', NULL, '0.00', NULL, '0.00', NULL, NULL, '0.00', NULL, NULL, '0.00', NULL, 'activo', '#45B7D1', 'no', '5', '2026-01-15 21:56:34');
INSERT INTO `eventos` (`id`, `titulo`, `descripcion`, `fecha_inicio`, `fecha_evento`, `hora_evento`, `fecha_fin`, `fecha_inicio_inscripcion`, `fecha_fin_inscripcion`, `lugar`, `imagen_portada`, `costo_inscripcion`, `costo_alojamiento`, `alojamiento_opcion1_desc`, `alojamiento_opcion1_costo`, `alojamiento_opcion2_desc`, `alojamiento_opcion2_costo`, `alojamiento_opcion3_desc`, `alojamiento_opcion3_costo`, `edad_rango1_min`, `edad_rango1_max`, `costo_rango1`, `edad_rango2_min`, `edad_rango2_max`, `costo_rango2`, `imagen`, `estado`, `color`, `destacado`, `creado_por`, `fecha_creacion`) VALUES ('13', 'Nuestra Mision', 'ghfgh hfgh hfgh hfgh', '2026-02-02', '2026-02-15', NULL, '2026-02-08', '2026-01-15', '2026-02-02', 'Santa Cruz', '69699cbf74135_1768529087.jpg', '200.00', '30.00', NULL, '0.00', NULL, '0.00', NULL, '0.00', NULL, NULL, '0.00', NULL, NULL, '0.00', NULL, 'activo', '#BB8FCE', 'no', '5', '2026-01-15 22:04:47');
INSERT INTO `eventos` (`id`, `titulo`, `descripcion`, `fecha_inicio`, `fecha_evento`, `hora_evento`, `fecha_fin`, `fecha_inicio_inscripcion`, `fecha_fin_inscripcion`, `lugar`, `imagen_portada`, `costo_inscripcion`, `costo_alojamiento`, `alojamiento_opcion1_desc`, `alojamiento_opcion1_costo`, `alojamiento_opcion2_desc`, `alojamiento_opcion2_costo`, `alojamiento_opcion3_desc`, `alojamiento_opcion3_costo`, `edad_rango1_min`, `edad_rango1_max`, `costo_rango1`, `edad_rango2_min`, `edad_rango2_max`, `costo_rango2`, `imagen`, `estado`, `color`, `destacado`, `creado_por`, `fecha_creacion`) VALUES ('14', 'isaacar', 'hfghf hfgh hfg', '2026-02-02', '2026-02-02', NULL, '2026-02-08', '2026-01-16', '2026-02-02', 'cbba', '696a1d0211a73_1768561922.jpeg', '100.00', '30.00', 'Habitacion con Ducha', '40.00', 'Habitacion con Ducha y Wifi', '60.00', 'algo', '10.00', NULL, NULL, '0.00', NULL, NULL, '0.00', NULL, 'activo', '#D7BDE2', 'no', '5', '2026-01-16 07:12:02');
INSERT INTO `eventos` (`id`, `titulo`, `descripcion`, `fecha_inicio`, `fecha_evento`, `hora_evento`, `fecha_fin`, `fecha_inicio_inscripcion`, `fecha_fin_inscripcion`, `lugar`, `imagen_portada`, `costo_inscripcion`, `costo_alojamiento`, `alojamiento_opcion1_desc`, `alojamiento_opcion1_costo`, `alojamiento_opcion2_desc`, `alojamiento_opcion2_costo`, `alojamiento_opcion3_desc`, `alojamiento_opcion3_costo`, `edad_rango1_min`, `edad_rango1_max`, `costo_rango1`, `edad_rango2_min`, `edad_rango2_max`, `costo_rango2`, `imagen`, `estado`, `color`, `destacado`, `creado_por`, `fecha_creacion`) VALUES ('15', 'Campamento oruro', 'Lugar del Evento Colcapirua', '2026-02-02', '2026-02-02', NULL, '2026-02-08', '2026-01-16', '2026-02-02', 'Cochabamba', '696b034ad97b7_1768620874.webp', '210.00', '0.00', 'Habitacion + Alimentacion+Polera', '40.00', '', '0.00', '', '0.00', NULL, NULL, '0.00', NULL, NULL, '0.00', NULL, 'activo', '#FF6B6B', 'no', '5', '2026-01-16 23:34:34');
INSERT INTO `eventos` (`id`, `titulo`, `descripcion`, `fecha_inicio`, `fecha_evento`, `hora_evento`, `fecha_fin`, `fecha_inicio_inscripcion`, `fecha_fin_inscripcion`, `lugar`, `imagen_portada`, `costo_inscripcion`, `costo_alojamiento`, `alojamiento_opcion1_desc`, `alojamiento_opcion1_costo`, `alojamiento_opcion2_desc`, `alojamiento_opcion2_costo`, `alojamiento_opcion3_desc`, `alojamiento_opcion3_costo`, `edad_rango1_min`, `edad_rango1_max`, `costo_rango1`, `edad_rango2_min`, `edad_rango2_max`, `costo_rango2`, `imagen`, `estado`, `color`, `destacado`, `creado_por`, `fecha_creacion`) VALUES ('17', 'Desenderate', 'Hogar Militar de Colcaphirua', '2026-02-02', '2026-02-02', NULL, '2026-02-08', '2026-01-17', '2026-02-02', 'Cochabamba', '696b1056046c8_1768624214.jpg', '210.00', '0.00', 'Habitacion + Alimentacion+Polera', '40.00', '', '0.00', '', '0.00', NULL, NULL, '0.00', NULL, NULL, '0.00', NULL, 'activo', '#85C1E9', 'no', '5', '2026-01-17 00:30:14');
INSERT INTO `eventos` (`id`, `titulo`, `descripcion`, `fecha_inicio`, `fecha_evento`, `hora_evento`, `fecha_fin`, `fecha_inicio_inscripcion`, `fecha_fin_inscripcion`, `lugar`, `imagen_portada`, `costo_inscripcion`, `costo_alojamiento`, `alojamiento_opcion1_desc`, `alojamiento_opcion1_costo`, `alojamiento_opcion2_desc`, `alojamiento_opcion2_costo`, `alojamiento_opcion3_desc`, `alojamiento_opcion3_costo`, `edad_rango1_min`, `edad_rango1_max`, `costo_rango1`, `edad_rango2_min`, `edad_rango2_max`, `costo_rango2`, `imagen`, `estado`, `color`, `destacado`, `creado_por`, `fecha_creacion`) VALUES ('18', 'Desenderate  2.0', 'complejo militar', '2026-02-02', '2026-02-02', NULL, '2026-02-08', '2026-01-17', '2026-02-02', 'Cochabamba', '696bc2e094b84_1768669920.jpg', '210.00', '0.00', 'Habitacion + Alimentacion+Polera', '40.00', '', '0.00', '', '0.00', '5', '12', '100.00', NULL, NULL, '0.00', NULL, 'activo', '#85C1E9', 'no', '5', '2026-01-17 13:12:00');

-- Estructura de la tabla `eventos_administradores`
DROP TABLE IF EXISTS `eventos_administradores`;
CREATE TABLE `eventos_administradores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `evento_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `asignado_por` int(11) NOT NULL,
  `fecha_asignacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `activo` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_asignacion` (`evento_id`,`usuario_id`),
  KEY `asignado_por` (`asignado_por`),
  KEY `idx_usuario_evento` (`usuario_id`,`evento_id`),
  CONSTRAINT `eventos_administradores_ibfk_3` FOREIGN KEY (`asignado_por`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcado de datos para la tabla `eventos_administradores`
INSERT INTO `eventos_administradores` (`id`, `evento_id`, `usuario_id`, `asignado_por`, `fecha_asignacion`, `activo`) VALUES ('1', '2', '1', '1', '2026-01-10 20:14:25', '1');
INSERT INTO `eventos_administradores` (`id`, `evento_id`, `usuario_id`, `asignado_por`, `fecha_asignacion`, `activo`) VALUES ('4', '2', '2', '1', '2026-01-10 21:13:02', '1');
INSERT INTO `eventos_administradores` (`id`, `evento_id`, `usuario_id`, `asignado_por`, `fecha_asignacion`, `activo`) VALUES ('5', '5', '1', '1', '2026-01-11 00:22:29', '1');
INSERT INTO `eventos_administradores` (`id`, `evento_id`, `usuario_id`, `asignado_por`, `fecha_asignacion`, `activo`) VALUES ('6', '5', '3', '1', '2026-01-11 00:24:28', '1');
INSERT INTO `eventos_administradores` (`id`, `evento_id`, `usuario_id`, `asignado_por`, `fecha_asignacion`, `activo`) VALUES ('16', '10', '5', '5', '2026-01-13 13:34:08', '1');
INSERT INTO `eventos_administradores` (`id`, `evento_id`, `usuario_id`, `asignado_por`, `fecha_asignacion`, `activo`) VALUES ('17', '10', '9', '5', '2026-01-13 13:34:53', '1');
INSERT INTO `eventos_administradores` (`id`, `evento_id`, `usuario_id`, `asignado_por`, `fecha_asignacion`, `activo`) VALUES ('19', '12', '5', '5', '2026-01-15 21:56:34', '1');
INSERT INTO `eventos_administradores` (`id`, `evento_id`, `usuario_id`, `asignado_por`, `fecha_asignacion`, `activo`) VALUES ('20', '13', '5', '5', '2026-01-15 22:04:47', '1');
INSERT INTO `eventos_administradores` (`id`, `evento_id`, `usuario_id`, `asignado_por`, `fecha_asignacion`, `activo`) VALUES ('21', '14', '5', '5', '2026-01-16 07:12:02', '1');
INSERT INTO `eventos_administradores` (`id`, `evento_id`, `usuario_id`, `asignado_por`, `fecha_asignacion`, `activo`) VALUES ('22', '14', '9', '5', '2026-01-16 23:19:03', '0');
INSERT INTO `eventos_administradores` (`id`, `evento_id`, `usuario_id`, `asignado_por`, `fecha_asignacion`, `activo`) VALUES ('23', '15', '5', '5', '2026-01-16 23:34:34', '1');
INSERT INTO `eventos_administradores` (`id`, `evento_id`, `usuario_id`, `asignado_por`, `fecha_asignacion`, `activo`) VALUES ('24', '15', '9', '5', '2026-01-16 23:35:29', '0');
INSERT INTO `eventos_administradores` (`id`, `evento_id`, `usuario_id`, `asignado_por`, `fecha_asignacion`, `activo`) VALUES ('25', '16', '5', '5', '2026-01-17 00:23:27', '1');
INSERT INTO `eventos_administradores` (`id`, `evento_id`, `usuario_id`, `asignado_por`, `fecha_asignacion`, `activo`) VALUES ('26', '17', '5', '5', '2026-01-17 00:30:14', '1');
INSERT INTO `eventos_administradores` (`id`, `evento_id`, `usuario_id`, `asignado_por`, `fecha_asignacion`, `activo`) VALUES ('27', '17', '9', '5', '2026-01-17 00:53:36', '1');
INSERT INTO `eventos_administradores` (`id`, `evento_id`, `usuario_id`, `asignado_por`, `fecha_asignacion`, `activo`) VALUES ('28', '18', '5', '5', '2026-01-17 13:12:00', '1');

-- Estructura de la tabla `eventos_galeria`
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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcado de datos para la tabla `eventos_galeria`
INSERT INTO `eventos_galeria` (`id`, `evento_id`, `titulo`, `descripcion`, `imagen`, `orden`, `fecha_creacion`) VALUES ('1', '17', '', '', '696b105607b3e_1768624214.jpg', '0', '2026-01-17 00:30:14');
INSERT INTO `eventos_galeria` (`id`, `evento_id`, `titulo`, `descripcion`, `imagen`, `orden`, `fecha_creacion`) VALUES ('2', '17', '', '', '696b1056087bb_1768624214.jpg', '2', '2026-01-17 00:30:14');
INSERT INTO `eventos_galeria` (`id`, `evento_id`, `titulo`, `descripcion`, `imagen`, `orden`, `fecha_creacion`) VALUES ('3', '17', '', '', '696b10560905d_1768624214.jpg', '3', '2026-01-17 00:30:14');
INSERT INTO `eventos_galeria` (`id`, `evento_id`, `titulo`, `descripcion`, `imagen`, `orden`, `fecha_creacion`) VALUES ('4', '18', '', '', '696bc2e099ffa_1768669920.jpg', '0', '2026-01-17 13:12:00');
INSERT INTO `eventos_galeria` (`id`, `evento_id`, `titulo`, `descripcion`, `imagen`, `orden`, `fecha_creacion`) VALUES ('5', '18', '', '', '696bc2e09a82f_1768669920.jpg', '2', '2026-01-17 13:12:00');
INSERT INTO `eventos_galeria` (`id`, `evento_id`, `titulo`, `descripcion`, `imagen`, `orden`, `fecha_creacion`) VALUES ('6', '18', '', '', '696bc2e09b224_1768669920.jpg', '3', '2026-01-17 13:12:00');

-- Estructura de la tabla `galeria`
DROP TABLE IF EXISTS `galeria`;
CREATE TABLE `galeria` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(200) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `imagen` varchar(255) NOT NULL,
  `categoria` varchar(100) DEFAULT 'general',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcado de datos para la tabla `galeria`
INSERT INTO `galeria` (`id`, `titulo`, `descripcion`, `imagen`, `categoria`, `fecha_creacion`) VALUES ('6', 'isaacar', 'dfgdfg gdfg gdf', '6967c409295ef_1768408073.jpeg', 'general', '2026-01-14 12:27:53');
INSERT INTO `galeria` (`id`, `titulo`, `descripcion`, `imagen`, `categoria`, `fecha_creacion`) VALUES ('7', 'isaacar', 'ghjgh jghj jgh', '6968d704e2097_1768478468.jpeg', 'general', '2026-01-15 08:01:08');
INSERT INTO `galeria` (`id`, `titulo`, `descripcion`, `imagen`, `categoria`, `fecha_creacion`) VALUES ('8', 'isaacar', 'fbfdghsd gdfg gdfg', '6969190d0521a_1768495373.jpg', 'general', '2026-01-15 12:42:53');

-- Estructura de la tabla `galeria_imagenes`
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcado de datos para la tabla `galeria_imagenes`
INSERT INTO `galeria_imagenes` (`id`, `galeria_id`, `titulo`, `descripcion`, `imagen`, `orden`, `fecha_creacion`) VALUES ('1', '6', 'isaacar', 'si', '6967ca202fffe_1768409632.jpeg', '0', '2026-01-14 12:53:52');

-- Estructura de la tabla `grupos_eventos`
DROP TABLE IF EXISTS `grupos_eventos`;
CREATE TABLE `grupos_eventos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `evento_id` int(11) NOT NULL,
  `numero_grupo` int(11) NOT NULL,
  `nombre_grupo` varchar(100) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `capacidad_maxima` int(11) DEFAULT 0,
  `lider_grupo` varchar(100) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_grupo_evento` (`evento_id`,`numero_grupo`),
  KEY `idx_evento_grupo` (`evento_id`,`numero_grupo`),
  CONSTRAINT `grupos_eventos_ibfk_1` FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de la tabla `grupos_inscripcion`
DROP TABLE IF EXISTS `grupos_inscripcion`;
CREATE TABLE `grupos_inscripcion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `numero_grupo` int(11) NOT NULL,
  `nombre_grupo` varchar(100) DEFAULT NULL,
  `color` varchar(7) DEFAULT NULL,
  `total_participantes` int(11) DEFAULT 0,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `numero_grupo` (`numero_grupo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Estructura de la tabla `inscripciones`
DROP TABLE IF EXISTS `inscripciones`;
CREATE TABLE `inscripciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `iglesia` varchar(200) DEFAULT NULL,
  `departamento` varchar(100) DEFAULT NULL,
  `sexo` enum('Masculino','Femenino') NOT NULL,
  `tipo_inscripcion` enum('efectivo','qr','deposito','beca') NOT NULL,
  `monto_pagado` decimal(10,2) DEFAULT 0.00,
  `monto_total` decimal(10,2) DEFAULT 0.00,
  `alojamiento` enum('Si','No') NOT NULL DEFAULT 'No',
  `grupo` int(11) DEFAULT NULL,
  `fecha_inscripcion` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado_pago` enum('pendiente','pagado','beca') DEFAULT 'pendiente',
  `estado` enum('activo','inactivo') DEFAULT 'activo',
  `observaciones` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sexo` (`sexo`),
  KEY `tipo_inscripcion` (`tipo_inscripcion`),
  KEY `estado_pago` (`estado_pago`),
  KEY `grupo` (`grupo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Estructura de la tabla `inscripciones_eventos`
DROP TABLE IF EXISTS `inscripciones_eventos`;
CREATE TABLE `inscripciones_eventos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `evento_id` int(11) NOT NULL,
  `codigo_inscripcion` varchar(20) NOT NULL,
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `fecha_nacimiento` date NOT NULL,
  `iglesia` varchar(150) DEFAULT NULL,
  `departamento` varchar(100) DEFAULT NULL,
  `sexo` enum('Masculino','Femenino') NOT NULL,
  `tipo_inscripcion` enum('Efectivo','QR','Deposito','Beca') NOT NULL,
  `monto_pagado` decimal(10,2) DEFAULT 0.00,
  `codigo_pago` varchar(100) DEFAULT NULL,
  `monto_total` decimal(10,2) NOT NULL,
  `alojamiento` enum('Si','No') DEFAULT 'No',
  `grupo` int(11) DEFAULT NULL,
  `estado_pago` enum('pendiente','parcial','completo','beca') DEFAULT 'pendiente',
  `aprobado` tinyint(1) DEFAULT 0,
  `fecha_inscripcion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo_inscripcion` (`codigo_inscripcion`),
  KEY `idx_evento` (`evento_id`),
  KEY `idx_sexo` (`sexo`),
  KEY `idx_grupo` (`grupo`),
  KEY `idx_estado_pago` (`estado_pago`),
  KEY `idx_codigo` (`codigo_inscripcion`),
  CONSTRAINT `inscripciones_eventos_ibfk_1` FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcado de datos para la tabla `inscripciones_eventos`
INSERT INTO `inscripciones_eventos` (`id`, `evento_id`, `codigo_inscripcion`, `nombres`, `apellidos`, `email`, `telefono`, `fecha_nacimiento`, `iglesia`, `departamento`, `sexo`, `tipo_inscripcion`, `monto_pagado`, `codigo_pago`, `monto_total`, `alojamiento`, `grupo`, `estado_pago`, `aprobado`, `fecha_inscripcion`, `fecha_actualizacion`) VALUES ('1', '2', 'INS20266473B8', 'Amaia Alexia', 'Quispe Quispe', 'amaia@amaia.com', '73847578', '2009-07-25', 'Oruro Norte', 'ORURO', 'Femenino', 'Efectivo', '150.00', NULL, '100.00', 'No', '2', 'completo', '1', '2026-01-10 23:50:30', '2026-01-10 23:59:33');
INSERT INTO `inscripciones_eventos` (`id`, `evento_id`, `codigo_inscripcion`, `nombres`, `apellidos`, `email`, `telefono`, `fecha_nacimiento`, `iglesia`, `departamento`, `sexo`, `tipo_inscripcion`, `monto_pagado`, `codigo_pago`, `monto_total`, `alojamiento`, `grupo`, `estado_pago`, `aprobado`, `fecha_inscripcion`, `fecha_actualizacion`) VALUES ('2', '2', 'INS2026B124A3', 'Jaasiel Raquel', 'Quispe Quispe', 'Jaasiel@Jaasiel.com', '73847578', '2012-08-12', 'Oruro Norte', 'ORURO', 'Femenino', 'Efectivo', '150.00', NULL, '100.00', 'No', '1', 'completo', '1', '2026-01-10 23:52:11', '2026-01-10 23:59:33');
INSERT INTO `inscripciones_eventos` (`id`, `evento_id`, `codigo_inscripcion`, `nombres`, `apellidos`, `email`, `telefono`, `fecha_nacimiento`, `iglesia`, `departamento`, `sexo`, `tipo_inscripcion`, `monto_pagado`, `codigo_pago`, `monto_total`, `alojamiento`, `grupo`, `estado_pago`, `aprobado`, `fecha_inscripcion`, `fecha_actualizacion`) VALUES ('3', '5', 'INS202648ADD1', 'cesar', 'quispe', 'admin@institucion.com', '73847578', '1998-02-11', 'Oruro Norte', 'ORURO', 'Masculino', 'Beca', '0.00', '', '150.00', 'Si', NULL, 'beca', '1', '2026-01-11 01:02:28', '2026-01-11 01:02:28');
INSERT INTO `inscripciones_eventos` (`id`, `evento_id`, `codigo_inscripcion`, `nombres`, `apellidos`, `email`, `telefono`, `fecha_nacimiento`, `iglesia`, `departamento`, `sexo`, `tipo_inscripcion`, `monto_pagado`, `codigo_pago`, `monto_total`, `alojamiento`, `grupo`, `estado_pago`, `aprobado`, `fecha_inscripcion`, `fecha_actualizacion`) VALUES ('4', '2', 'INS20264E7AF1', 'weimar', 'Quispe mamani', 'admin@institucion.com', '+590 73847578', '2011-02-12', 'Oruro Norte', 'ORURO', 'Femenino', 'Beca', '0.00', '', '150.00', 'Si', NULL, 'beca', '1', '2026-01-12 23:21:40', '2026-01-12 23:21:40');
INSERT INTO `inscripciones_eventos` (`id`, `evento_id`, `codigo_inscripcion`, `nombres`, `apellidos`, `email`, `telefono`, `fecha_nacimiento`, `iglesia`, `departamento`, `sexo`, `tipo_inscripcion`, `monto_pagado`, `codigo_pago`, `monto_total`, `alojamiento`, `grupo`, `estado_pago`, `aprobado`, `fecha_inscripcion`, `fecha_actualizacion`) VALUES ('5', '14', 'INS202676BB2F', 'weimar', 'Quispe mamani', 'admin@institucion.com', '+590 73847578', '1980-02-12', 'Amachuma Catuyo', 'ORURO', 'Masculino', 'Efectivo', '140.00', '', '140.00', '', NULL, 'completo', '1', '2026-01-16 07:29:43', '2026-01-16 07:29:43');
INSERT INTO `inscripciones_eventos` (`id`, `evento_id`, `codigo_inscripcion`, `nombres`, `apellidos`, `email`, `telefono`, `fecha_nacimiento`, `iglesia`, `departamento`, `sexo`, `tipo_inscripcion`, `monto_pagado`, `codigo_pago`, `monto_total`, `alojamiento`, `grupo`, `estado_pago`, `aprobado`, `fecha_inscripcion`, `fecha_actualizacion`) VALUES ('6', '14', 'INS20260AA3DD', 'carlos', 'mamani', '', '', '1980-09-12', 'oruro', 'oruro', 'Masculino', 'Efectivo', '160.00', '', '160.00', '', NULL, 'completo', '1', '2026-01-16 23:28:00', '2026-01-16 23:28:00');
INSERT INTO `inscripciones_eventos` (`id`, `evento_id`, `codigo_inscripcion`, `nombres`, `apellidos`, `email`, `telefono`, `fecha_nacimiento`, `iglesia`, `departamento`, `sexo`, `tipo_inscripcion`, `monto_pagado`, `codigo_pago`, `monto_total`, `alojamiento`, `grupo`, `estado_pago`, `aprobado`, `fecha_inscripcion`, `fecha_actualizacion`) VALUES ('7', '15', 'INS20265C8036', 'Andres', 'mamani', '', '', '1988-12-12', 'oruro', 'Oruro', 'Masculino', 'Efectivo', '250.00', '', '250.00', '', NULL, 'completo', '1', '2026-01-16 23:37:41', '2026-01-16 23:37:41');
INSERT INTO `inscripciones_eventos` (`id`, `evento_id`, `codigo_inscripcion`, `nombres`, `apellidos`, `email`, `telefono`, `fecha_nacimiento`, `iglesia`, `departamento`, `sexo`, `tipo_inscripcion`, `monto_pagado`, `codigo_pago`, `monto_total`, `alojamiento`, `grupo`, `estado_pago`, `aprobado`, `fecha_inscripcion`, `fecha_actualizacion`) VALUES ('8', '18', 'INS2026C5DEDB', 'Shirley Angelica', 'Quispe mamani', 'admin@institucion.com', '+590 73847578', '2020-02-06', 'Amachuma Catuyo', 'ORURO', 'Masculino', 'Efectivo', '140.00', '', '140.00', '', NULL, 'completo', '1', '2026-01-17 14:25:16', '2026-01-17 14:25:16');

-- Estructura de la tabla `intentos_login`
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
) ENGINE=InnoDB AUTO_INCREMENT=91 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Estructura de la tabla `log_actividades`
DROP TABLE IF EXISTS `log_actividades`;
CREATE TABLE `log_actividades` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) DEFAULT NULL,
  `evento_id` int(11) DEFAULT NULL,
  `accion` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `fecha_hora` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_usuario` (`usuario_id`),
  KEY `idx_evento_log` (`evento_id`),
  KEY `idx_fecha` (`fecha_hora`)
) ENGINE=InnoDB AUTO_INCREMENT=209 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcado de datos para la tabla `log_actividades`
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('1', '1', NULL, 'UPDATE_CONFIG', 'Configuración general actualizada', '::1', '2026-01-10 19:47:53');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('2', '1', NULL, 'UPDATE_CARRUSEL', 'ID: 4, Título: Test Debug 1768089149', '::1', '2026-01-10 19:59:55');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('3', '1', NULL, 'UPDATE_CARRUSEL', 'ID: 5, Título: Test Formulario 1768089149', '::1', '2026-01-10 20:00:02');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('4', '1', NULL, 'UPDATE_CARRUSEL', 'ID: 7, Título: Test Formulario 1768089174', '::1', '2026-01-10 20:00:08');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('5', '1', NULL, 'UPDATE_CARRUSEL', 'ID: 7, Título: Test Formulario 1768089174', '::1', '2026-01-10 20:00:15');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('6', '1', NULL, 'UPDATE_CARRUSEL', 'ID: 6, Título: Test Debug 1768089174', '::1', '2026-01-10 20:04:09');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('7', '1', NULL, 'CREATE_CARRUSEL', 'Título: Campamento', '::1', '2026-01-10 20:05:24');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('8', '1', NULL, 'CREATE_GALERIA', 'Título: Campamento', '::1', '2026-01-10 20:06:05');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('9', '1', NULL, 'UPDATE_GALERIA', 'ID: 4, Título: Campamento', '::1', '2026-01-10 20:06:11');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('10', '1', '2', 'evento_creado', 'Evento creado: Campamento', '::1', '2026-01-10 20:14:25');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('11', '1', NULL, 'evento_creado', 'Evento creado: Campamento2', '::1', '2026-01-10 20:15:12');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('12', '1', NULL, 'UPDATE_CONFIG', 'Configuración general actualizada', '::1', '2026-01-10 20:35:48');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('13', '1', NULL, 'evento_creado', 'Evento creado: Retiro', '::1', '2026-01-10 20:50:42');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('14', '1', NULL, 'UPDATE_MISION_VISION', 'ID: 1, Tipo: mision', '::1', '2026-01-10 20:57:07');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('15', '1', NULL, 'UPDATE_MISION_VISION', 'ID: 2, Tipo: vision', '::1', '2026-01-10 20:57:20');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('16', '1', NULL, 'UPDATE_CONFIG', 'Configuración general actualizada', '::1', '2026-01-10 20:59:49');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('17', '1', NULL, 'UPDATE_CONFIG', 'Configuración general actualizada', '::1', '2026-01-10 21:00:34');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('18', '2', NULL, 'usuario_creado', 'Usuario creado: Andres', '::1', '2026-01-10 21:12:47');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('19', '2', NULL, 'logout', 'Cierre de sesion', '::1', '2026-01-10 23:26:00');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('20', '2', NULL, 'logout', 'Cierre de sesion', '::1', '2026-01-10 23:32:16');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('21', '2', NULL, 'logout', 'Cierre de sesion', '::1', '2026-01-10 23:47:57');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('22', '2', NULL, 'login', 'Inicio de sesion exitoso', '::1', '2026-01-10 23:48:28');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('23', '2', '2', 'inscripcion_creada', 'Inscripcion creada: INS20266473B8', '::1', '2026-01-10 23:50:30');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('24', '2', '2', 'inscripcion_creada', 'Inscripcion creada: INS2026B124A3', '::1', '2026-01-10 23:52:11');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('25', '2', '2', 'grupos_formados', 'Se formaron 2 grupos con 2 participantes', '::1', '2026-01-10 23:59:33');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('26', '2', NULL, 'logout', 'Cierre de sesion', '::1', '2026-01-11 00:20:38');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('27', '1', '5', 'evento_creado', 'Evento creado: Retiro Nacional', '::1', '2026-01-11 00:22:29');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('28', '3', NULL, 'usuario_creado', 'Usuario creado: jaasiel', '::1', '2026-01-11 00:23:51');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('29', '3', NULL, 'login', 'Inicio de sesion exitoso', '::1', '2026-01-11 00:25:08');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('30', '3', '5', 'inscripcion_creada', 'Inscripcion creada: INS202648ADD1', '::1', '2026-01-11 01:02:28');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('31', '3', NULL, 'logout', 'Cierre de sesion', '::1', '2026-01-11 01:10:17');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('32', '1', NULL, 'evento_creado', 'Evento creado: Los Valientes', '::1', '2026-01-11 01:12:06');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('33', '1', NULL, 'evento_actualizado', 'Evento actualizado: Los Valientes', '::1', '2026-01-11 01:15:26');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('34', '1', NULL, 'evento_actualizado', 'Evento actualizado: Los Valientes', '::1', '2026-01-11 01:15:58');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('35', '1', NULL, 'evento_creado', 'Evento creado: Los Guerreros', '::1', '2026-01-11 01:17:10');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('36', '4', NULL, 'usuario_creado', 'Usuario creado: mamani', '::1', '2026-01-11 01:18:50');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('37', '4', NULL, 'login', 'Inicio de sesion exitoso', '::1', '2026-01-11 01:19:22');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('38', '4', NULL, 'logout', 'Cierre de sesion', '::1', '2026-01-11 01:25:54');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('39', '5', NULL, 'usuario_creado', 'Usuario creado: superadmin', NULL, '2026-01-11 01:31:03');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('40', '5', NULL, 'login', 'Inicio de sesion exitoso', NULL, '2026-01-11 01:38:05');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('41', '5', NULL, 'login', 'Inicio de sesion exitoso', NULL, '2026-01-11 01:54:11');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('42', '5', NULL, 'login', 'Inicio de sesion exitoso', NULL, '2026-01-11 01:59:11');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('43', '2', NULL, 'login', 'Inicio de sesion exitoso', '::1', '2026-01-11 08:14:07');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('44', '2', NULL, 'logout', 'Cierre de sesion', '::1', '2026-01-11 08:14:13');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('45', '1', NULL, 'UPDATE_CONFIG', 'Configuración general actualizada', '::1', '2026-01-11 08:15:20');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('46', '1', NULL, 'UPDATE_GALERIA', 'ID: 1, Título: Imagen de Prueba 1768088320', '::1', '2026-01-11 08:16:09');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('47', '5', NULL, 'login', 'Inicio de sesion exitoso', '::1', '2026-01-11 08:23:31');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('48', '5', NULL, 'login', 'Inicio de sesion exitoso', '::1', '2026-01-11 08:37:38');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('49', '5', NULL, 'login', 'Inicio de sesion exitoso', '::1', '2026-01-11 08:37:41');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('50', '5', NULL, 'login', 'Inicio de sesion exitoso', '::1', '2026-01-11 08:37:43');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('51', '5', NULL, 'login', 'Inicio de sesion exitoso', '::1', '2026-01-11 08:45:06');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('52', '5', NULL, 'login', 'Inicio de sesion exitoso', '::1', '2026-01-11 08:45:08');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('53', '5', NULL, 'login', 'Inicio de sesion exitoso', '::1', '2026-01-11 08:45:09');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('54', '5', NULL, 'login', 'Inicio de sesion exitoso', '::1', '2026-01-11 08:45:10');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('55', '5', NULL, 'login', 'Inicio de sesion exitoso', '::1', '2026-01-11 08:45:11');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('56', '5', NULL, 'login', 'Inicio de sesion exitoso', '::1', '2026-01-11 08:45:11');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('57', '5', NULL, 'login', 'Inicio de sesion exitoso', '::1', '2026-01-12 17:51:22');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('58', '5', NULL, 'logout', 'Cierre de sesion', '::1', '2026-01-12 17:51:40');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('59', '2', NULL, 'login', 'Inicio de sesion exitoso', '::1', '2026-01-12 18:04:15');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('60', '5', NULL, 'login', 'Inicio de sesion exitoso', '::1', '2026-01-12 18:06:50');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('61', '5', NULL, 'login', 'Inicio de sesion exitoso', '::1', '2026-01-12 18:07:27');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('62', '5', NULL, 'login', 'Inicio de sesion exitoso', '::1', '2026-01-12 18:08:26');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('63', '5', NULL, 'login', 'Inicio de sesion exitoso', '::1', '2026-01-12 18:08:27');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('64', '5', NULL, 'login', 'Inicio de sesion exitoso', '::1', '2026-01-12 18:08:29');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('65', '5', NULL, 'login', 'Inicio de sesion exitoso', '::1', '2026-01-12 18:08:30');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('66', '5', NULL, 'login', 'Inicio de sesion exitoso', '::1', '2026-01-12 18:08:31');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('67', '5', NULL, 'login', 'Inicio de sesion exitoso', '::1', '2026-01-12 18:08:32');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('68', '2', NULL, 'login', 'Inicio de sesion exitoso', '::1', '2026-01-12 18:09:48');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('69', '2', NULL, 'logout', 'Cierre de sesion', '::1', '2026-01-12 18:09:51');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('70', '3', NULL, 'login', 'Inicio de sesion exitoso', '::1', '2026-01-12 18:21:34');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('71', '3', NULL, 'logout', 'Cierre de sesion', '::1', '2026-01-12 18:21:40');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('72', '5', NULL, 'login', 'Inicio de sesion exitoso', '::1', '2026-01-12 18:21:42');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('73', '6', NULL, 'usuario_creado', 'Usuario creado: amor', '::1', '2026-01-12 21:56:12');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('74', '6', NULL, 'login', 'Inicio de sesion exitoso', '::1', '2026-01-12 21:58:58');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('75', '6', NULL, 'logout', 'Cierre de sesion', '::1', '2026-01-12 21:59:08');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('76', '5', NULL, 'login', 'Inicio de sesion exitoso', '127.0.0.1', '2026-01-12 22:50:13');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('77', '6', NULL, 'login', 'Inicio de sesion exitoso', '::1', '2026-01-12 22:59:01');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('78', '5', NULL, 'login', 'Inicio de sesion exitoso', '127.0.0.1', '2026-01-12 22:59:14');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('79', '5', NULL, 'login', 'Inicio de sesion exitoso', '127.0.0.1', '2026-01-12 23:00:14');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('80', '6', NULL, 'logout', 'Cierre de sesion', '::1', '2026-01-12 23:01:29');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('81', '5', NULL, 'login', 'Inicio de sesion exitoso', '::1', '2026-01-12 23:02:44');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('85', '5', NULL, 'evento_eliminado', 'Evento eliminado ID: 6', '::1', '2026-01-12 23:07:59');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('86', '5', NULL, 'UPDATE_CONFIG', 'Configuración general actualizada', '::1', '2026-01-12 23:12:21');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('87', '5', NULL, 'evento_creado', 'Evento creado: Retiro Oruro', '::1', '2026-01-12 23:15:31');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('88', '7', NULL, 'usuario_creado', 'Usuario creado: rox', '::1', '2026-01-12 23:16:29');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('89', '5', NULL, 'logout', 'Cierre de sesion', '::1', '2026-01-12 23:16:52');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('90', '2', NULL, 'login', 'Inicio de sesion exitoso', '::1', '2026-01-12 23:17:38');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('91', '2', NULL, 'logout', 'Cierre de sesion', '::1', '2026-01-12 23:17:41');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('92', '2', NULL, 'login', 'Inicio de sesion exitoso', '::1', '2026-01-12 23:18:47');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('93', '2', '2', 'inscripcion_creada', 'Inscripcion creada: INS20264E7AF1', '::1', '2026-01-12 23:21:40');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('94', '2', NULL, 'logout', 'Cierre de sesion', '::1', '2026-01-12 23:24:52');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('95', '5', NULL, 'login', 'Inicio de sesion exitoso', '::1', '2026-01-12 23:25:52');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('96', '5', NULL, 'evento_eliminado', 'Evento eliminado ID: 8', '::1', '2026-01-12 23:26:01');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('97', '5', NULL, 'evento_eliminado', 'Evento eliminado ID: 7', '::1', '2026-01-12 23:26:05');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('98', '5', NULL, 'logout', 'Cierre de sesion', '::1', '2026-01-12 23:31:04');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('99', '5', NULL, 'login', 'Inicio de sesion exitoso', '::1', '2026-01-12 23:31:26');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('100', '5', NULL, 'evento_creado', 'Evento creado: Campamento Oruro', '::1', '2026-01-12 23:33:31');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('101', '5', NULL, 'logout', 'Cierre de sesion', '::1', '2026-01-12 23:34:35');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('102', '5', NULL, 'login', 'Inicio de sesion exitoso', '::1', '2026-01-12 23:36:23');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('103', '8', NULL, 'usuario_creado', 'Usuario creado: ces', '::1', '2026-01-12 23:36:57');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('104', '5', NULL, 'logout', 'Cierre de sesion', '::1', '2026-01-12 23:37:11');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('105', '8', NULL, 'login', 'Inicio de sesion exitoso', '::1', '2026-01-12 23:41:05');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('106', '8', NULL, 'logout', 'Cierre de sesion', '::1', '2026-01-12 23:48:41');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('107', '2', NULL, 'login', 'Inicio de sesion exitoso', '::1', '2026-01-12 23:48:52');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('108', '2', NULL, 'logout', 'Cierre de sesion', '::1', '2026-01-12 23:49:06');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('109', '7', NULL, 'login', 'Inicio de sesion exitoso', '::1', '2026-01-12 23:49:17');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('110', '7', NULL, 'logout', 'Cierre de sesion', '::1', '2026-01-12 23:56:35');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('111', '8', NULL, 'login', 'Inicio de sesion exitoso', '::1', '2026-01-12 23:56:44');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('112', '8', NULL, 'logout', 'Cierre de sesion', '::1', '2026-01-12 23:57:11');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('113', '5', NULL, 'login', 'Inicio de sesion exitoso', '::1', '2026-01-12 23:57:26');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('114', '5', NULL, 'CREATE_CARRUSEL', 'Título: Liderazgo Espiritual', '::1', '2026-01-12 23:58:14');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('115', '5', NULL, 'CREATE_GALERIA', 'Título: Liderazgo Espiritual', '::1', '2026-01-12 23:58:51');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('116', '5', NULL, 'logout', 'Cierre de sesion', '::1', '2026-01-13 00:02:51');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('117', '5', NULL, 'login', 'Inicio de sesion exitoso', '::1', '2026-01-13 00:03:07');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('118', '5', NULL, 'logout', 'Cierre de sesion', '::1', '2026-01-13 00:04:55');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('119', '2', NULL, 'login', 'Inicio de sesion exitoso', '::1', '2026-01-13 00:05:32');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('120', '2', NULL, 'logout', 'Cierre de sesion', '::1', '2026-01-13 00:13:28');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('121', '5', NULL, 'login', 'Inicio de sesion exitoso', '::1', '2026-01-13 00:13:52');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('122', '5', NULL, 'UPDATE_MISION_VISION', 'ID: 1, Tipo: mision', '::1', '2026-01-13 00:15:35');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('123', '5', NULL, 'UPDATE_MISION_VISION', 'ID: 2, Tipo: vision', '::1', '2026-01-13 00:15:48');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('124', '5', NULL, 'logout', 'Cierre de sesion', '::1', '2026-01-13 00:18:32');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('125', '5', NULL, 'login', 'Inicio de sesion exitoso', '::1', '2026-01-13 00:18:48');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('126', '5', NULL, 'UPDATE_MISION_VISION', 'ID: 1, Tipo: mision', '::1', '2026-01-13 00:22:31');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('127', '5', NULL, 'UPDATE_MISION_VISION', 'ID: 2, Tipo: vision', '::1', '2026-01-13 00:23:37');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('128', '5', NULL, 'UPDATE_MISION_VISION', 'ID: 3, Tipo: valores', '::1', '2026-01-13 00:24:48');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('129', '5', NULL, 'UPDATE_CONFIG', 'Configuración general actualizada', '::1', '2026-01-13 00:25:58');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('130', '5', NULL, 'logout', 'Cierre de sesion', '::1', '2026-01-13 00:27:27');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('131', '1', NULL, 'UPDATE_GALERIA', 'ID: 5, Título: Liderazgo Espiritual', '::1', '2026-01-13 00:28:10');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('132', '5', NULL, 'login', 'Inicio de sesion exitoso', '::1', '2026-01-13 00:29:55');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('133', '5', NULL, 'DELETE_CARRUSEL', 'ID: 4', '::1', '2026-01-13 00:32:44');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('134', '5', NULL, 'DELETE_CARRUSEL', 'ID: 5', '::1', '2026-01-13 00:32:51');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('135', '5', NULL, 'DELETE_CARRUSEL', 'ID: 7', '::1', '2026-01-13 00:32:54');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('136', '5', NULL, 'DELETE_GALERIA', 'ID: 4', '::1', '2026-01-13 00:33:00');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('137', '5', NULL, 'UPDATE_CONFIG', 'Configuración general actualizada', '::1', '2026-01-13 00:42:24');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('138', '5', NULL, 'logout', 'Cierre de sesion', '::1', '2026-01-13 13:24:43');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('139', '8', NULL, 'logout', 'Cierre de sesion', '::1', '2026-01-13 13:25:06');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('140', '5', NULL, 'logout', 'Cierre de sesion', '::1', '2026-01-13 13:26:18');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('141', '1', NULL, 'DELETE_CARRUSEL', 'ID: 9', '::1', '2026-01-13 13:26:42');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('142', '5', NULL, 'DELETE_CARRUSEL', 'ID: 10', '::1', '2026-01-13 13:27:29');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('143', '5', NULL, 'evento_eliminado', 'Evento eliminado ID: 9', '::1', '2026-01-13 13:27:39');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('144', '5', NULL, 'logout', 'Cierre de sesion', '::1', '2026-01-13 13:27:52');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('145', '7', NULL, 'logout', 'Cierre de sesion', '::1', '2026-01-13 13:29:22');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('146', '2', NULL, 'logout', 'Cierre de sesion', '::1', '2026-01-13 13:29:42');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('147', '5', NULL, 'login', 'Inicio de sesion exitoso', '::1', '2026-01-13 13:32:31');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('148', '5', NULL, 'logout', 'Cierre de sesion', '::1', '2026-01-13 13:32:42');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('149', '7', NULL, 'login', 'Inicio de sesion exitoso', '::1', '2026-01-13 13:32:54');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('150', '7', NULL, 'logout', 'Cierre de sesion', '::1', '2026-01-13 13:32:57');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('151', '5', NULL, 'login', 'Inicio de sesion exitoso', '::1', '2026-01-13 13:33:10');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('152', '5', '10', 'evento_creado', 'Evento creado: Campamento', '::1', '2026-01-13 13:34:08');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('153', '9', NULL, 'usuario_creado', 'Usuario creado: ami', '::1', '2026-01-13 13:34:41');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('154', '5', NULL, 'logout', 'Cierre de sesion', '::1', '2026-01-13 13:34:59');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('155', '9', NULL, 'login', 'Inicio de sesion exitoso', '::1', '2026-01-13 13:35:31');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('156', '9', NULL, 'logout', 'Cierre de sesion', '::1', '2026-01-13 13:46:48');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('157', '5', NULL, 'login', 'Inicio de sesion exitoso', '::1', '2026-01-13 23:27:32');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('158', '5', NULL, 'UPDATE_CONFIG', 'Configuración general actualizada', '::1', '2026-01-13 23:29:22');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('159', '5', NULL, 'UPDATE_MISION_VISION', 'ID: 4, Tipo: historia', '::1', '2026-01-13 23:48:53');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('160', '5', NULL, 'UPDATE_MISION_VISION', 'ID: 1, Tipo: mision', '::1', '2026-01-13 23:50:02');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('161', '5', NULL, 'UPDATE_MISION_VISION', 'ID: 2, Tipo: vision', '::1', '2026-01-13 23:50:14');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('162', '5', NULL, 'UPDATE_MISION_VISION', 'ID: 3, Tipo: valores', '::1', '2026-01-13 23:50:23');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('163', '5', NULL, 'logout', 'Cierre de sesion', '::1', '2026-01-14 12:25:47');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('164', '1', NULL, 'DELETE_GALERIA', 'ID: 1', '::1', '2026-01-14 12:27:07');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('165', '1', NULL, 'CREATE_CARRUSEL', 'Título: Liderazgo Espiritual', '::1', '2026-01-14 12:27:28');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('166', '1', NULL, 'CREATE_GALERIA', 'Título: isaacar', '::1', '2026-01-14 12:27:53');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('167', '1', NULL, 'DELETE_GALERIA', 'ID: 5', '::1', '2026-01-14 12:35:26');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('168', '1', NULL, 'UPDATE_MISION_VISION', 'ID: 3, Tipo: valores', '::1', '2026-01-14 13:15:24');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('169', '1', NULL, 'evento_creado', 'Evento creado: isaacar', '::1', '2026-01-15 08:00:11');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('170', '1', NULL, 'CREATE_CARRUSEL', 'Título: vnbnvb', '::1', '2026-01-15 08:00:52');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('171', '1', NULL, 'CREATE_GALERIA', 'Título: isaacar', '::1', '2026-01-15 08:01:08');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('172', '5', NULL, 'login', 'Inicio de sesion exitoso', '::1', '2026-01-15 12:42:36');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('173', '5', NULL, 'CREATE_GALERIA', 'Título: isaacar', '::1', '2026-01-15 12:42:53');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('174', '5', NULL, 'evento_eliminado', 'Evento eliminado ID: 11', '::1', '2026-01-15 13:44:09');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('175', '5', '12', 'evento_creado', 'Evento creado: isaacar', '::1', '2026-01-15 21:56:34');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('176', '5', '13', 'evento_creado', 'Evento creado: Nuestra Mision', '::1', '2026-01-15 22:04:47');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('177', '5', '5', 'evento_desactivado', 'Evento desactivado manualmente', '::1', '2026-01-15 22:05:24');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('178', '5', '2', 'evento_desactivado', 'Evento desactivado manualmente', '::1', '2026-01-15 22:14:23');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('179', '5', '13', 'evento_actualizado', 'Evento actualizado: Nuestra Mision', '::1', '2026-01-15 22:23:51');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('180', '5', '5', 'evento_desactivado', 'Evento desactivado manualmente', '::1', '2026-01-15 22:30:15');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('181', '5', '2', 'evento_desactivado', 'Evento desactivado manualmente', '::1', '2026-01-15 22:30:18');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('182', '5', NULL, 'UPDATE_CONFIG', 'Configuración general actualizada', '::1', '2026-01-16 00:39:49');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('183', '5', '10', 'evento_eliminado', 'Evento eliminado ID: 10', '::1', '2026-01-16 06:48:14');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('184', '5', '14', 'evento_creado', 'Evento creado: isaacar', '::1', '2026-01-16 07:12:02');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('185', '5', '14', 'inscripcion_creada', 'Inscripcion creada: INS202676BB2F', '::1', '2026-01-16 07:29:43');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('186', '5', NULL, 'login', 'Inicio de sesion exitoso', '::1', '2026-01-16 23:18:42');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('187', '5', NULL, 'logout', 'Cierre de sesion', '::1', '2026-01-16 23:19:21');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('188', '9', NULL, 'login', 'Inicio de sesion exitoso', '::1', '2026-01-16 23:19:35');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('189', '9', '14', 'inscripcion_creada', 'Inscripcion creada: INS20260AA3DD', '::1', '2026-01-16 23:28:00');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('190', '9', NULL, 'logout', 'Cierre de sesion', '::1', '2026-01-16 23:28:42');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('191', '5', NULL, 'login', 'Inicio de sesion exitoso', '::1', '2026-01-16 23:28:45');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('192', '5', '15', 'evento_creado', 'Evento creado: Campamento oruro', '::1', '2026-01-16 23:34:34');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('193', '5', NULL, 'logout', 'Cierre de sesion', '::1', '2026-01-16 23:35:38');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('194', '9', NULL, 'login', 'Inicio de sesion exitoso', '::1', '2026-01-16 23:35:45');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('195', '9', '15', 'inscripcion_creada', 'Inscripcion creada: INS20265C8036', '::1', '2026-01-16 23:37:41');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('196', '9', NULL, 'logout', 'Cierre de sesion', '::1', '2026-01-17 00:10:43');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('197', '5', NULL, 'login', 'Inicio de sesion exitoso', '::1', '2026-01-17 00:10:46');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('198', '5', '16', 'evento_creado', 'Evento creado: Desenderate', '::1', '2026-01-17 00:23:27');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('199', '5', '16', 'evento_eliminado', 'Evento eliminado ID: 16', '::1', '2026-01-17 00:29:01');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('200', '5', '17', 'evento_creado', 'Evento creado: Desenderate', '::1', '2026-01-17 00:30:14');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('201', '5', '17', 'evento_configurado', 'Evento configurado', '::1', '2026-01-17 00:30:14');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('202', '5', NULL, 'logout', 'Cierre de sesion', '::1', '2026-01-17 00:53:48');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('203', '9', NULL, 'login', 'Inicio de sesion exitoso', '::1', '2026-01-17 00:53:58');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('204', '9', NULL, 'logout', 'Cierre de sesion', '::1', '2026-01-17 13:08:49');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('205', '5', NULL, 'login', 'Inicio de sesion exitoso', '::1', '2026-01-17 13:08:51');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('206', '5', '18', 'evento_creado', 'Evento creado: Desenderate  2.0', '::1', '2026-01-17 13:12:00');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('207', '5', NULL, 'login', 'Inicio de sesion exitoso', '::1', '2026-01-17 14:17:51');
INSERT INTO `log_actividades` (`id`, `usuario_id`, `evento_id`, `accion`, `descripcion`, `ip_address`, `fecha_hora`) VALUES ('208', '5', '18', 'inscripcion_creada', 'Inscripcion creada: INS2026C5DEDB', '::1', '2026-01-17 14:25:16');

-- Estructura de la tabla `mision_vision`
DROP TABLE IF EXISTS `mision_vision`;
CREATE TABLE `mision_vision` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipo` enum('mision','vision','valores','historia') NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `contenido` text NOT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `estado` enum('activo','inactivo') DEFAULT 'activo',
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcado de datos para la tabla `mision_vision`
INSERT INTO `mision_vision` (`id`, `tipo`, `titulo`, `contenido`, `imagen`, `estado`, `fecha_actualizacion`) VALUES ('1', 'mision', 'Nuestra Mision', 'Equipar y capacitar a cada joven para ser un discípulo y mentor comprometidos\r\ncon su fe y llamados a ser discípulos de Jesucristo, a través de un discipulado\r\nsólido y un programa de mentoreo integral. Buscamos empoderar a una\r\ngeneración de líderes transformadores que sirvan con amor, integridad y\r\nexcelencia, reflejando los valores del Reino de Dios en cada ámbito de su vida y\r\ncomunidad.', '6967126adec45_1768362602.jpeg', 'activo', '2026-01-13 23:50:02');
INSERT INTO `mision_vision` (`id`, `tipo`, `titulo`, `contenido`, `imagen`, `estado`, `fecha_actualizacion`) VALUES ('2', 'vision', 'Nuestra Vision', 'Ser un ministerio juvenil de discípulos de Cristo que, fundamentados en el\r\nconocimiento bíblico y la guía del Espíritu Santo, impacten su entorno con el\r\npoder del evangelio. En la próxima década, con líderes comprometidos, maduros\r\nen la fe y capacitados para discipular y mentorear a otros, asegurando así la\r\nexpansión y continuidad del ministerio en la Iglesia de Dios de la Profecía y más\r\nallá.', '6967127653a47_1768362614.jpeg', 'activo', '2026-01-13 23:50:14');
INSERT INTO `mision_vision` (`id`, `tipo`, `titulo`, `contenido`, `imagen`, `estado`, `fecha_actualizacion`) VALUES ('3', 'valores', 'Nuestros Valores', 'A. DISCIPULADO INTEG RAL:\r\nB. MENTOREO ACTI VO:\r\nC. SERVICIO Y COMPASIÓN:\r\nD. EMPODERAMIENTO :\r\nE. COMPROMISO CON LA PALABRA:', '6967127fb386d_1768362623.jpeg', 'activo', '2026-01-14 13:15:24');
INSERT INTO `mision_vision` (`id`, `tipo`, `titulo`, `contenido`, `imagen`, `estado`, `fecha_actualizacion`) VALUES ('4', 'historia', 'Nuestra Historia', 'Fundada en 1990, nuestra instituci├│n ha crecido para convertirse en un referente de excelencia educativa en la regi├│n.', '696712255a35b_1768362533.jpeg', 'activo', '2026-01-13 23:48:53');

-- Estructura de la tabla `paginas`
DROP TABLE IF EXISTS `paginas`;
CREATE TABLE `paginas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(200) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `contenido` text NOT NULL,
  `imagen_destacada` varchar(255) DEFAULT NULL,
  `estado` enum('publicado','borrador') DEFAULT 'publicado',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Estructura de la tabla `pagos_inscripciones`
DROP TABLE IF EXISTS `pagos_inscripciones`;
CREATE TABLE `pagos_inscripciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `inscripcion_id` int(11) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `tipo_pago` enum('Efectivo','QR','Deposito','Transferencia') NOT NULL,
  `referencia_pago` varchar(100) DEFAULT NULL,
  `fecha_pago` date NOT NULL,
  `confirmado_por` int(11) DEFAULT NULL,
  `notas` text DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `confirmado_por` (`confirmado_por`),
  KEY `idx_inscripcion` (`inscripcion_id`),
  CONSTRAINT `pagos_inscripciones_ibfk_1` FOREIGN KEY (`inscripcion_id`) REFERENCES `inscripciones_eventos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pagos_inscripciones_ibfk_2` FOREIGN KEY (`confirmado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estructura de la tabla `permisos`
DROP TABLE IF EXISTS `permisos`;
CREATE TABLE `permisos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rol` varchar(20) NOT NULL,
  `recurso` varchar(100) NOT NULL,
  `accion` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_permiso` (`rol`,`recurso`,`accion`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcado de datos para la tabla `permisos`
INSERT INTO `permisos` (`id`, `rol`, `recurso`, `accion`, `descripcion`) VALUES ('1', 'super_admin', 'eventos', 'crear', 'Crear nuevos eventos');
INSERT INTO `permisos` (`id`, `rol`, `recurso`, `accion`, `descripcion`) VALUES ('2', 'super_admin', 'eventos', 'editar', 'Editar cualquier evento');
INSERT INTO `permisos` (`id`, `rol`, `recurso`, `accion`, `descripcion`) VALUES ('3', 'super_admin', 'eventos', 'eliminar', 'Eliminar eventos');
INSERT INTO `permisos` (`id`, `rol`, `recurso`, `accion`, `descripcion`) VALUES ('4', 'super_admin', 'eventos', 'configurar', 'Configurar eventos');
INSERT INTO `permisos` (`id`, `rol`, `recurso`, `accion`, `descripcion`) VALUES ('5', 'super_admin', 'usuarios', 'crear', 'Crear usuarios');
INSERT INTO `permisos` (`id`, `rol`, `recurso`, `accion`, `descripcion`) VALUES ('6', 'super_admin', 'usuarios', 'editar', 'Editar usuarios');
INSERT INTO `permisos` (`id`, `rol`, `recurso`, `accion`, `descripcion`) VALUES ('7', 'super_admin', 'usuarios', 'eliminar', 'Eliminar usuarios');
INSERT INTO `permisos` (`id`, `rol`, `recurso`, `accion`, `descripcion`) VALUES ('8', 'super_admin', 'configuracion', 'acceder', 'Acceder a configuraci├│n global');
INSERT INTO `permisos` (`id`, `rol`, `recurso`, `accion`, `descripcion`) VALUES ('9', 'super_admin', 'todos_eventos', 'acceder', 'Acceder a todos los eventos');
INSERT INTO `permisos` (`id`, `rol`, `recurso`, `accion`, `descripcion`) VALUES ('10', 'admin', 'eventos_asignados', 'acceder', 'Acceder solo a eventos asignados');
INSERT INTO `permisos` (`id`, `rol`, `recurso`, `accion`, `descripcion`) VALUES ('11', 'admin', 'inscripciones', 'crear', 'Crear inscripciones');
INSERT INTO `permisos` (`id`, `rol`, `recurso`, `accion`, `descripcion`) VALUES ('12', 'admin', 'inscripciones', 'editar', 'Editar inscripciones');
INSERT INTO `permisos` (`id`, `rol`, `recurso`, `accion`, `descripcion`) VALUES ('13', 'admin', 'inscripciones', 'eliminar', 'Eliminar inscripciones');
INSERT INTO `permisos` (`id`, `rol`, `recurso`, `accion`, `descripcion`) VALUES ('14', 'admin', 'inscripciones', 'aprobar', 'Aprobar inscripciones');
INSERT INTO `permisos` (`id`, `rol`, `recurso`, `accion`, `descripcion`) VALUES ('15', 'admin', 'grupos', 'formar', 'Formar grupos');
INSERT INTO `permisos` (`id`, `rol`, `recurso`, `accion`, `descripcion`) VALUES ('16', 'admin', 'reportes', 'ver', 'Ver reportes');
INSERT INTO `permisos` (`id`, `rol`, `recurso`, `accion`, `descripcion`) VALUES ('17', 'admin', 'pagos', 'registrar', 'Registrar pagos');
INSERT INTO `permisos` (`id`, `rol`, `recurso`, `accion`, `descripcion`) VALUES ('18', 'usuario', 'inscripciones', 'crear_propia', 'Crear inscripci├│n propia');
INSERT INTO `permisos` (`id`, `rol`, `recurso`, `accion`, `descripcion`) VALUES ('19', 'usuario', 'inscripciones', 'ver_propia', 'Ver inscripci├│n propia');

-- Estructura de la tabla `sesiones`
DROP TABLE IF EXISTS `sesiones`;
CREATE TABLE `sesiones` (
  `id` varchar(128) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `datos` text DEFAULT NULL,
  `ultima_actividad` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `ultima_actividad` (`ultima_actividad`),
  CONSTRAINT `sesiones_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcado de datos para la tabla `sesiones`
INSERT INTO `sesiones` (`id`, `usuario_id`, `ip_address`, `user_agent`, `datos`, `ultima_actividad`) VALUES ('5lnhdr5i2aqf5v3thv0bqdcm0j', '5', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"login_time\":1768325551,\"role\":\"super_admin\"}', '2026-01-13 13:32:31');
INSERT INTO `sesiones` (`id`, `usuario_id`, `ip_address`, `user_agent`, `datos`, `ultima_actividad`) VALUES ('6m2ikogsbpf2qn0vm7tsn99221', '7', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"login_time\":1768325574,\"role\":\"usuario\"}', '2026-01-13 13:32:54');
INSERT INTO `sesiones` (`id`, `usuario_id`, `ip_address`, `user_agent`, `datos`, `ultima_actividad`) VALUES ('7mc44famq8mskom2kclugpir2v', '9', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"login_time\":1768325731,\"role\":\"usuario\"}', '2026-01-13 13:35:31');
INSERT INTO `sesiones` (`id`, `usuario_id`, `ip_address`, `user_agent`, `datos`, `ultima_actividad`) VALUES ('7tom1den74glps741mspv9j3bt', '5', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"login_time\":1768325590,\"role\":\"super_admin\"}', '2026-01-13 13:33:10');
INSERT INTO `sesiones` (`id`, `usuario_id`, `ip_address`, `user_agent`, `datos`, `ultima_actividad`) VALUES ('a6p5qphp2hka8dpalfnklljosd', '5', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"login_time\":1768361252,\"role\":\"super_admin\"}', '2026-01-13 23:27:32');
INSERT INTO `sesiones` (`id`, `usuario_id`, `ip_address`, `user_agent`, `datos`, `ultima_actividad`) VALUES ('hctka6q5gbp3f8m808niv1f5ng', '9', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '{\"login_time\":1768619975,\"role\":\"usuario\"}', '2026-01-16 23:19:35');
INSERT INTO `sesiones` (`id`, `usuario_id`, `ip_address`, `user_agent`, `datos`, `ultima_actividad`) VALUES ('hlsbov8vs5hjtdqqg6k5nieue5', '5', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '{\"login_time\":1768623046,\"role\":\"super_admin\"}', '2026-01-17 00:10:46');
INSERT INTO `sesiones` (`id`, `usuario_id`, `ip_address`, `user_agent`, `datos`, `ultima_actividad`) VALUES ('k8l7rnap8097bormhpdm3osruo', '5', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '{\"login_time\":1768619922,\"role\":\"super_admin\"}', '2026-01-16 23:18:42');
INSERT INTO `sesiones` (`id`, `usuario_id`, `ip_address`, `user_agent`, `datos`, `ultima_actividad`) VALUES ('m3mlp5a1juuq5nlbjr4acc7hrh', '9', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '{\"login_time\":1768625638,\"role\":\"usuario\"}', '2026-01-17 00:53:58');
INSERT INTO `sesiones` (`id`, `usuario_id`, `ip_address`, `user_agent`, `datos`, `ultima_actividad`) VALUES ('mbe6ctd6qt2v56cbfbamsqtf33', '5', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '{\"login_time\":1768673871,\"role\":\"super_admin\"}', '2026-01-17 14:17:51');
INSERT INTO `sesiones` (`id`, `usuario_id`, `ip_address`, `user_agent`, `datos`, `ultima_actividad`) VALUES ('mbgok9hcbd4bbj3lgkue765pfe', '5', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '{\"login_time\":1768669731,\"role\":\"super_admin\"}', '2026-01-17 13:08:51');
INSERT INTO `sesiones` (`id`, `usuario_id`, `ip_address`, `user_agent`, `datos`, `ultima_actividad`) VALUES ('n2hppcpb0839rj7l5bq1mdij7q', '5', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"login_time\":1768495356,\"role\":\"super_admin\"}', '2026-01-15 12:42:36');
INSERT INTO `sesiones` (`id`, `usuario_id`, `ip_address`, `user_agent`, `datos`, `ultima_actividad`) VALUES ('q11mf9b80cq4m49fo2ifpmu0cc', '5', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '{\"login_time\":1768620525,\"role\":\"super_admin\"}', '2026-01-16 23:28:45');
INSERT INTO `sesiones` (`id`, `usuario_id`, `ip_address`, `user_agent`, `datos`, `ultima_actividad`) VALUES ('rvp05gi6np8sboudt11pn88mp3', '9', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '{\"login_time\":1768620945,\"role\":\"usuario\"}', '2026-01-16 23:35:45');

-- Estructura de la tabla `sesiones_admin`
DROP TABLE IF EXISTS `sesiones_admin`;
CREATE TABLE `sesiones_admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_expiracion` datetime NOT NULL,
  `activa` enum('si','no') DEFAULT 'si',
  PRIMARY KEY (`id`),
  KEY `admin_id` (`admin_id`),
  CONSTRAINT `sesiones_admin_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `administradores` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Estructura de la tabla `usuarios`
DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `nombre_completo` varchar(100) NOT NULL,
  `rol` enum('super_admin','admin','usuario') DEFAULT 'usuario',
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `ultimo_acceso` timestamp NULL DEFAULT NULL,
  `intentos_fallidos` int(11) DEFAULT 0,
  `bloqueado_hasta` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_username` (`username`),
  KEY `idx_rol` (`rol`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcado de datos para la tabla `usuarios`
INSERT INTO `usuarios` (`id`, `username`, `password`, `email`, `nombre_completo`, `rol`, `activo`, `fecha_creacion`, `ultimo_acceso`, `intentos_fallidos`, `bloqueado_hasta`) VALUES ('1', 'admin', '$2y$12$YiLM5.aCDsKGL20awx2Ps.G6gLJSmspGe8MWounSPk2HylzrecO0G', 'admin@example.com', 'Administrador Principal', 'super_admin', '1', '2026-01-10 18:33:31', NULL, '1', '2026-01-12 23:13:09');
INSERT INTO `usuarios` (`id`, `username`, `password`, `email`, `nombre_completo`, `rol`, `activo`, `fecha_creacion`, `ultimo_acceso`, `intentos_fallidos`, `bloqueado_hasta`) VALUES ('2', 'andres', '$2y$10$/VVLr/sTGaZt.jyCkYZkRu5lwqq8TMhWlTpuLVLofApZ/Sz1OO7KW', 'andres@andres.com', 'Andres marca', 'admin', '1', '2026-01-10 21:12:47', '2026-01-13 13:29:33', '0', NULL);
INSERT INTO `usuarios` (`id`, `username`, `password`, `email`, `nombre_completo`, `rol`, `activo`, `fecha_creacion`, `ultimo_acceso`, `intentos_fallidos`, `bloqueado_hasta`) VALUES ('3', 'jaasiel', '$2y$10$Vb4cdu/.lBouu5LQGhz35.iIdZIuWC/PNujTH/JQsnFGXFiGgShyC', 'jaasiel@jaasiel.com', 'Jaasiel Raquel', 'admin', '1', '2026-01-11 00:23:51', '2026-01-12 18:21:34', '0', NULL);
INSERT INTO `usuarios` (`id`, `username`, `password`, `email`, `nombre_completo`, `rol`, `activo`, `fecha_creacion`, `ultimo_acceso`, `intentos_fallidos`, `bloqueado_hasta`) VALUES ('4', 'mamani', '$2y$10$qKAJrff/vz0pPTaVkU5BnOAIquEVe7JSd9gv1ODFdQ8JuF5DMGOmO', 'mamani@mamani.com', 'Mamani Quispe', 'admin', '1', '2026-01-11 01:18:50', '2026-01-11 01:19:22', '0', NULL);
INSERT INTO `usuarios` (`id`, `username`, `password`, `email`, `nombre_completo`, `rol`, `activo`, `fecha_creacion`, `ultimo_acceso`, `intentos_fallidos`, `bloqueado_hasta`) VALUES ('5', 'superadmin', '$2y$10$t.WZhijNEfbB0jhFvUDG9eQY.eqJy.N9ttGbOgiAFZYgeFp1vPUXa', 'superadmin@sistema.com', 'Super Administrador', 'super_admin', '1', '2026-01-11 01:31:03', '2026-01-17 14:17:51', '0', NULL);
INSERT INTO `usuarios` (`id`, `username`, `password`, `email`, `nombre_completo`, `rol`, `activo`, `fecha_creacion`, `ultimo_acceso`, `intentos_fallidos`, `bloqueado_hasta`) VALUES ('6', 'amor', '$2y$10$hm9T4hzob3/LGLty/8uR.uttiZ3O.uV0i2Zfj5baH49NLrKXKCnU2', 'amor@amor.com', 'amores', 'admin', '1', '2026-01-12 21:56:12', '2026-01-12 22:59:00', '0', NULL);
INSERT INTO `usuarios` (`id`, `username`, `password`, `email`, `nombre_completo`, `rol`, `activo`, `fecha_creacion`, `ultimo_acceso`, `intentos_fallidos`, `bloqueado_hasta`) VALUES ('7', 'rox', '$2y$10$fSjAY/fGYCdY13vpM1VHQObOrHlPHBgCxhe2mt3uGDSfMie3YO4Mu', 'rox@rox.com', 'roxana', 'usuario', '1', '2026-01-12 23:16:29', '2026-01-13 13:32:54', '0', NULL);
INSERT INTO `usuarios` (`id`, `username`, `password`, `email`, `nombre_completo`, `rol`, `activo`, `fecha_creacion`, `ultimo_acceso`, `intentos_fallidos`, `bloqueado_hasta`) VALUES ('8', 'ces', '$2y$10$LWgY6RI..meFx7LIPhYzjOh0lXP5Ul59xGBeZYYSH4JOVlJY3556K', 'ces@ces.com', 'cesar', 'usuario', '1', '2026-01-12 23:36:57', '2026-01-13 13:24:54', '0', NULL);
INSERT INTO `usuarios` (`id`, `username`, `password`, `email`, `nombre_completo`, `rol`, `activo`, `fecha_creacion`, `ultimo_acceso`, `intentos_fallidos`, `bloqueado_hasta`) VALUES ('9', 'ami', '$2y$10$O7RSvgZQH0ld9WymFA/jAuU2Gkql6.55KhvRf7ZCvHYiSBPS4U8Bm', 'ami@ami', 'ami', 'usuario', '1', '2026-01-13 13:34:41', '2026-01-17 00:53:58', '0', NULL);

SET FOREIGN_KEY_CHECKS = 1;

COMMIT;
