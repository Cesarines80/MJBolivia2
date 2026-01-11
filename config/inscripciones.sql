-- Tabla para sistema de inscripciones
-- Agregar a la base de datos web_institucional

USE `web_institucional`;

-- --------------------------------------------------------

-- Estructura de tabla para la tabla `inscripciones`
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

-- --------------------------------------------------------

-- Estructura de tabla para la tabla `configuracion_inscripciones`
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcado de datos para la tabla `configuracion_inscripciones`
INSERT INTO `configuracion_inscripciones` (`id`, `monto_inscripcion`, `monto_alojamiento`, `fecha_inicio`, `fecha_fin`, `instrucciones_pago`) VALUES
(1, 150.00, 50.00, '2026-01-01', '2026-12-31', '1. Efectivo: Pago directo en secretaría\n2. QR: Escanea el código proporcionado\n3. Depósito: Cuenta bancaria 1234567890\n4. Beca: Aplican restricciones');

-- --------------------------------------------------------

-- Estructura de tabla para la tabla `grupos_inscripcion`
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

COMMIT;