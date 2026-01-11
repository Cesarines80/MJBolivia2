-- Script de instalación completa de la base de datos
-- MJBolivia2 - Sistema de Gestión de Eventos

-- Crear base de datos si no existe
CREATE DATABASE IF NOT EXISTS `web_institucional` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `web_institucional`;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET FOREIGN_KEY_CHECKS = 0;
SET time_zone = "+00:00";

-- ============================================
-- SISTEMA DE GESTIÓN DE EVENTOS CON ROLES
-- ============================================

-- Tabla de usuarios con roles
DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE `usuarios` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `nombre_completo` VARCHAR(100) NOT NULL,
    `rol` ENUM('super_admin', 'admin', 'usuario') DEFAULT 'usuario',
    `activo` BOOLEAN DEFAULT TRUE,
    `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `ultimo_acceso` TIMESTAMP NULL,
    `intentos_fallidos` INT DEFAULT 0,
    `bloqueado_hasta` TIMESTAMP NULL,
    INDEX `idx_username` (`username`),
    INDEX `idx_rol` (`rol`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de eventos (cada evento es independiente)
DROP TABLE IF EXISTS `eventos`;
CREATE TABLE `eventos` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre` VARCHAR(200) NOT NULL,
    `descripcion` TEXT,
    `fecha_inicio` DATE NOT NULL,
    `fecha_fin` DATE NOT NULL,
    `fecha_inicio_inscripcion` DATE NOT NULL,
    `fecha_fin_inscripcion` DATE NOT NULL,
    `lugar` VARCHAR(200),
    `imagen_portada` VARCHAR(255),
    `estado` ENUM('activo', 'inactivo', 'finalizado') DEFAULT 'activo',
    `creado_por` INT NOT NULL,
    `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`creado_por`) REFERENCES `usuarios`(`id`) ON DELETE RESTRICT,
    INDEX `idx_fechas` (`fecha_inicio_inscripcion`, `fecha_fin_inscripcion`),
    INDEX `idx_estado` (`estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de configuración por evento
DROP TABLE IF EXISTS `configuracion_eventos`;
CREATE TABLE `configuracion_eventos` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `evento_id` INT NOT NULL,
    `precio_base` DECIMAL(10,2) DEFAULT 0.00,
    `precio_alojamiento` DECIMAL(10,2) DEFAULT 0.00,
    `max_participantes` INT DEFAULT 200,
    `requiere_aprobacion` BOOLEAN DEFAULT FALSE,
    `instrucciones_pago` TEXT,
    `campos_extra` JSON,
    `fecha_actualizacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`evento_id`) REFERENCES `eventos`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_evento` (`evento_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de inscripciones por evento
DROP TABLE IF EXISTS `inscripciones_eventos`;
CREATE TABLE `inscripciones_eventos` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `evento_id` INT NOT NULL,
    `codigo_inscripcion` VARCHAR(20) NOT NULL UNIQUE,
    `nombres` VARCHAR(100) NOT NULL,
    `apellidos` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100),
    `telefono` VARCHAR(20),
    `fecha_nacimiento` DATE NOT NULL,
    `iglesia` VARCHAR(150),
    `departamento` VARCHAR(100),
    `sexo` ENUM('Masculino', 'Femenino') NOT NULL,
    `tipo_inscripcion` ENUM('Efectivo', 'QR', 'Deposito', 'Beca') NOT NULL,
    `monto_pagado` DECIMAL(10,2) DEFAULT 0.00,
    `monto_total` DECIMAL(10,2) NOT NULL,
    `alojamiento` ENUM('Si', 'No') DEFAULT 'No',
    `grupo` INT DEFAULT NULL,
    `estado_pago` ENUM('pendiente', 'parcial', 'completo', 'beca') DEFAULT 'pendiente',
    `aprobado` BOOLEAN DEFAULT FALSE,
    `fecha_inscripcion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `fecha_actualizacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`evento_id`) REFERENCES `eventos`(`id`) ON DELETE CASCADE,
    INDEX `idx_evento` (`evento_id`),
    INDEX `idx_sexo` (`sexo`),
    INDEX `idx_grupo` (`grupo`),
    INDEX `idx_estado_pago` (`estado_pago`),
    INDEX `idx_codigo` (`codigo_inscripcion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de grupos por evento
DROP TABLE IF EXISTS `grupos_eventos`;
CREATE TABLE `grupos_eventos` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `evento_id` INT NOT NULL,
    `numero_grupo` INT NOT NULL,
    `nombre_grupo` VARCHAR(100),
    `descripcion` TEXT,
    `capacidad_maxima` INT DEFAULT 0,
    `lider_grupo` VARCHAR(100),
    `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`evento_id`) REFERENCES `eventos`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_grupo_evento` (`evento_id`, `numero_grupo`),
    INDEX `idx_evento_grupo` (`evento_id`, `numero_grupo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de pagos para seguimiento detallado
DROP TABLE IF EXISTS `pagos_inscripciones`;
CREATE TABLE `pagos_inscripciones` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `inscripcion_id` INT NOT NULL,
    `monto` DECIMAL(10,2) NOT NULL,
    `tipo_pago` ENUM('Efectivo', 'QR', 'Deposito', 'Transferencia') NOT NULL,
    `referencia_pago` VARCHAR(100),
    `fecha_pago` DATE NOT NULL,
    `confirmado_por` INT,
    `notas` TEXT,
    `fecha_registro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`inscripcion_id`) REFERENCES `inscripciones_eventos`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`confirmado_por`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL,
    INDEX `idx_inscripcion` (`inscripcion_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de log de actividades
DROP TABLE IF EXISTS `log_actividades`;
CREATE TABLE `log_actividades` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `usuario_id` INT,
    `evento_id` INT,
    `accion` VARCHAR(100) NOT NULL,
    `descripcion` TEXT,
    `ip_address` VARCHAR(45),
    `fecha_hora` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`evento_id`) REFERENCES `eventos`(`id`) ON DELETE SET NULL,
    INDEX `idx_usuario` (`usuario_id`),
    INDEX `idx_evento_log` (`evento_id`),
    INDEX `idx_fecha` (`fecha_hora`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de permisos por rol
DROP TABLE IF EXISTS `permisos`;
CREATE TABLE `permisos` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `rol` VARCHAR(20) NOT NULL,
    `recurso` VARCHAR(100) NOT NULL,
    `accion` VARCHAR(50) NOT NULL,
    `descripcion` TEXT,
    UNIQUE KEY `unique_permiso` (`rol`, `recurso`, `accion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de asignación de administradores a eventos
DROP TABLE IF EXISTS `eventos_administradores`;
CREATE TABLE `eventos_administradores` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `evento_id` INT NOT NULL,
    `usuario_id` INT NOT NULL,
    `asignado_por` INT NOT NULL,
    `fecha_asignacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `activo` BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (`evento_id`) REFERENCES `eventos`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`asignado_por`) REFERENCES `usuarios`(`id`) ON DELETE RESTRICT,
    UNIQUE KEY `unique_asignacion` (`evento_id`, `usuario_id`),
    INDEX `idx_usuario_evento` (`usuario_id`, `evento_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de configuración global del sistema
DROP TABLE IF EXISTS `configuracion_global`;
CREATE TABLE `configuracion_global` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `clave` VARCHAR(100) NOT NULL UNIQUE,
    `valor` TEXT,
    `tipo` VARCHAR(20) DEFAULT 'string',
    `descripcion` TEXT,
    `editable` BOOLEAN DEFAULT TRUE,
    `fecha_actualizacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SITIO WEB INSTITUCIONAL
-- ============================================

-- Tabla de administradores (para el sitio institucional)
DROP TABLE IF EXISTS `administradores`;
CREATE TABLE `administradores` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `nombre` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `rol` ENUM('superadmin', 'admin', 'editor') DEFAULT 'admin',
    `estado` ENUM('activo', 'inactivo') DEFAULT 'activo',
    `ultimo_acceso` DATETIME DEFAULT NULL,
    `intentos_fallidos` INT(11) DEFAULT 0,
    `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla de configuración del sitio
DROP TABLE IF EXISTS `configuracion`;
CREATE TABLE `configuracion` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `nombre_institucion` VARCHAR(200) NOT NULL,
    `descripcion` TEXT DEFAULT NULL,
    `logo` VARCHAR(255) DEFAULT NULL,
    `favicon` VARCHAR(255) DEFAULT NULL,
    `email_contacto` VARCHAR(100) DEFAULT NULL,
    `telefono` VARCHAR(50) DEFAULT NULL,
    `direccion` TEXT DEFAULT NULL,
    `facebook` VARCHAR(255) DEFAULT NULL,
    `twitter` VARCHAR(255) DEFAULT NULL,
    `instagram` VARCHAR(255) DEFAULT NULL,
    `youtube` VARCHAR(255) DEFAULT NULL,
    `color_primario` VARCHAR(7) DEFAULT '#8B7EC8',
    `color_secundario` VARCHAR(7) DEFAULT '#B8B3D8',
    `color_acento` VARCHAR(7) DEFAULT '#6B5B95',
    `metadescription` TEXT DEFAULT NULL,
    `metakeywords` TEXT DEFAULT NULL,
    `analytics_id` VARCHAR(50) DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla de misión y visión
DROP TABLE IF EXISTS `mision_vision`;
CREATE TABLE `mision_vision` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `tipo` ENUM('mision', 'vision', 'valores', 'historia') NOT NULL,
    `titulo` VARCHAR(200) NOT NULL,
    `contenido` TEXT NOT NULL,
    `imagen` VARCHAR(255) DEFAULT NULL,
    `estado` ENUM('activo', 'inactivo') DEFAULT 'activo',
    `fecha_actualizacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla de carrusel
DROP TABLE IF EXISTS `carrusel`;
CREATE TABLE `carrusel` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `titulo` VARCHAR(200) NOT NULL,
    `descripcion` TEXT DEFAULT NULL,
    `imagen` VARCHAR(255) NOT NULL,
    `tipo` ENUM('imagen', 'video') DEFAULT 'imagen',
    `url` VARCHAR(255) DEFAULT NULL,
    `orden` INT(11) DEFAULT 0,
    `estado` ENUM('activo', 'inactivo') DEFAULT 'activo',
    `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla de galería
DROP TABLE IF EXISTS `galeria`;
CREATE TABLE `galeria` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `titulo` VARCHAR(200) NOT NULL,
    `descripcion` TEXT DEFAULT NULL,
    `imagen` VARCHAR(255) NOT NULL,
    `categoria` VARCHAR(100) DEFAULT 'general',
    `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla de contactos
DROP TABLE IF EXISTS `contactos`;
CREATE TABLE `contactos` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `nombre` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `telefono` VARCHAR(50) DEFAULT NULL,
    `asunto` VARCHAR(200) NOT NULL,
    `mensaje` TEXT NOT NULL,
    `estado` ENUM('nuevo', 'leido', 'respondido') DEFAULT 'nuevo',
    `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla de páginas
DROP TABLE IF EXISTS `paginas`;
CREATE TABLE `paginas` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `titulo` VARCHAR(200) NOT NULL,
    `slug` VARCHAR(200) NOT NULL,
    `contenido` TEXT NOT NULL,
    `imagen_destacada` VARCHAR(255) DEFAULT NULL,
    `estado` ENUM('publicado', 'borrador') DEFAULT 'publicado',
    `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `fecha_actualizacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla de sesiones admin
DROP TABLE IF EXISTS `sesiones_admin`;
CREATE TABLE `sesiones_admin` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `admin_id` INT(11) NOT NULL,
    `token` VARCHAR(255) NOT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `user_agent` TEXT DEFAULT NULL,
    `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `fecha_expiracion` DATETIME NOT NULL,
    `activa` ENUM('si', 'no') DEFAULT 'si',
    PRIMARY KEY (`id`),
    KEY `admin_id` (`admin_id`),
    CONSTRAINT `sesiones_admin_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `administradores` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla de intentos de login
DROP TABLE IF EXISTS `intentos_login`;
CREATE TABLE `intentos_login` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `email` VARCHAR(100) NOT NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `intentos` INT(11) DEFAULT 1,
    `ultimo_intento` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `bloqueado_hasta` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `email` (`email`),
    KEY `ip_address` (`ip_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================
-- SISTEMA DE INSCRIPCIONES
-- ============================================

-- Tabla de inscripciones
DROP TABLE IF EXISTS `inscripciones`;
CREATE TABLE `inscripciones` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `nombres` VARCHAR(100) NOT NULL,
    `apellidos` VARCHAR(100) NOT NULL,
    `fecha_nacimiento` DATE NOT NULL,
    `iglesia` VARCHAR(200) DEFAULT NULL,
    `departamento` VARCHAR(100) DEFAULT NULL,
    `sexo` ENUM('Masculino','Femenino') NOT NULL,
    `tipo_inscripcion` ENUM('efectivo','qr','deposito','beca') NOT NULL,
    `monto_pagado` DECIMAL(10,2) DEFAULT 0.00,
    `monto_total` DECIMAL(10,2) DEFAULT 0.00,
    `alojamiento` ENUM('Si','No') NOT NULL DEFAULT 'No',
    `grupo` INT(11) DEFAULT NULL,
    `fecha_inscripcion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `estado_pago` ENUM('pendiente','pagado','beca') DEFAULT 'pendiente',
    `estado` ENUM('activo','inactivo') DEFAULT 'activo',
    `observaciones` TEXT DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `sexo` (`sexo`),
    KEY `tipo_inscripcion` (`tipo_inscripcion`),
    KEY `estado_pago` (`estado_pago`),
    KEY `grupo` (`grupo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla de configuración de inscripciones
DROP TABLE IF EXISTS `configuracion_inscripciones`;
CREATE TABLE `configuracion_inscripciones` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `monto_inscripcion` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `monto_alojamiento` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `fecha_inicio` DATE DEFAULT NULL,
    `fecha_fin` DATE DEFAULT NULL,
    `limite_inscripciones` INT(11) DEFAULT NULL,
    `instrucciones_pago` TEXT DEFAULT NULL,
    `estado` ENUM('activo','inactivo') DEFAULT 'activo',
    `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla de grupos de inscripción
DROP TABLE IF EXISTS `grupos_inscripcion`;
CREATE TABLE `grupos_inscripcion` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `numero_grupo` INT(11) NOT NULL,
    `nombre_grupo` VARCHAR(100) DEFAULT NULL,
    `color` VARCHAR(7) DEFAULT NULL,
    `total_participantes` INT(11) DEFAULT 0,
    `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `numero_grupo` (`numero_grupo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================
-- DATOS INICIALES
-- ============================================

-- Insertar usuario super administrador
INSERT INTO `usuarios` (`username`, `password`, `email`, `nombre_completo`, `rol`, `activo`) VALUES
('admin', '$2y$12$YiLM5.aCDsKGL20awx2Ps.G6gLJSmspGe8MWounSPk2HylzrecO0G', 'admin@example.com', 'Administrador Principal', 'super_admin', TRUE);

-- Insertar administrador del sitio institucional
INSERT INTO `administradores` (`id`, `nombre`, `email`, `password`, `rol`, `estado`, `fecha_creacion`) VALUES
(1, 'Administrador Principal', 'admin@institucion.com', '$2y$12$YiLM5.aCDsKGL20awx2Ps.G6gLJSmspGe8MWounSPk2HylzrecO0G', 'superadmin', 'activo', NOW());

-- Insertar configuración del sitio
INSERT INTO `configuracion` (`id`, `nombre_institucion`, `descripcion`, `logo`, `email_contacto`, `telefono`, `direccion`, `color_primario`, `color_secundario`, `color_acento`) VALUES
(1, 'Nombre de tu Institución', 'Descripción de tu institución', NULL, 'contacto@institucion.com', '+1 234 567 8900', 'Dirección de la institución', '#8B7EC8', '#B8B3D8', '#6B5B95');

-- Insertar misión y visión
INSERT INTO `mision_vision` (`id`, `tipo`, `titulo`, `contenido`) VALUES
(1, 'mision', 'Nuestra Misión', 'Proporcionar educación de alta calidad y servicios excepcionales a nuestra comunidad, fomentando el desarrollo integral de cada individuo.'),
(2, 'vision', 'Nuestra Visión', 'Ser reconocidos como una institución líder en innovación educativa, formando ciudadanos comprometidos con el cambio social positivo.'),
(3, 'valores', 'Nuestros Valores', 'Excelencia, Integridad, Compromiso, Innovación, Responsabilidad Social'),
(4, 'historia', 'Nuestra Historia', 'Fundada en 1990, nuestra institución ha crecido para convertirse en un referente de excelencia educativa en la región.');

-- Insertar configuración de inscripciones
INSERT INTO `configuracion_inscripciones` (`id`, `monto_inscripcion`, `monto_alojamiento`, `fecha_inicio`, `fecha_fin`, `instrucciones_pago`) VALUES
(1, 150.00, 50.00, '2026-01-01', '2026-12-31', '1. Efectivo: Pago directo en secretaría\n2. QR: Escanea el código proporcionado\n3. Depósito: Cuenta bancaria 1234567890\n4. Beca: Aplican restricciones');

-- Insertar permisos por defecto
INSERT INTO `permisos` (`rol`, `recurso`, `accion`, `descripcion`) VALUES
('super_admin', 'eventos', 'crear', 'Crear nuevos eventos'),
('super_admin', 'eventos', 'editar', 'Editar cualquier evento'),
('super_admin', 'eventos', 'eliminar', 'Eliminar eventos'),
('super_admin', 'eventos', 'configurar', 'Configurar eventos'),
('super_admin', 'usuarios', 'crear', 'Crear usuarios'),
('super_admin', 'usuarios', 'editar', 'Editar usuarios'),
('super_admin', 'usuarios', 'eliminar', 'Eliminar usuarios'),
('super_admin', 'configuracion', 'acceder', 'Acceder a configuración global'),
('super_admin', 'todos_eventos', 'acceder', 'Acceder a todos los eventos'),
('admin', 'eventos_asignados', 'acceder', 'Acceder solo a eventos asignados'),
('admin', 'inscripciones', 'crear', 'Crear inscripciones'),
('admin', 'inscripciones', 'editar', 'Editar inscripciones'),
('admin', 'inscripciones', 'eliminar', 'Eliminar inscripciones'),
('admin', 'inscripciones', 'aprobar', 'Aprobar inscripciones'),
('admin', 'grupos', 'formar', 'Formar grupos'),
('admin', 'reportes', 'ver', 'Ver reportes'),
('admin', 'pagos', 'registrar', 'Registrar pagos'),
('usuario', 'inscripciones', 'crear_propia', 'Crear inscripción propia'),
('usuario', 'inscripciones', 'ver_propia', 'Ver inscripción propia');

-- Insertar configuración global
INSERT INTO `configuracion_global` (`clave`, `valor`, `tipo`, `descripcion`) VALUES
('nombre_sistema', 'Sistema de Gestión de Eventos', 'string', 'Nombre del sistema'),
('correo_contacto', 'contacto@example.com', 'string', 'Correo de contacto'),
('telefono_contacto', '+1-234-567-8900', 'string', 'Teléfono de contacto'),
('direccion_contacto', 'Calle Principal 123, Ciudad', 'string', 'Dirección física'),
('max_intentos_login', '5', 'integer', 'Máximo de intentos de login antes de bloqueo'),
('tiempo_bloqueo_minutos', '30', 'integer', 'Tiempo de bloqueo en minutos'),
('formato_fecha', 'd/m/Y', 'string', 'Formato de fecha para el sistema'),
('moneda_predeterminada', 'USD', 'string', 'Moneda predeterminada');

SET FOREIGN_KEY_CHECKS = 1;

COMMIT;

-- ============================================
-- INSTALACIÓN COMPLETADA
-- ============================================
-- Credenciales por defecto:
-- Usuario: admin@institucion.com
-- Contraseña: admin123
-- 
-- ⚠️ IMPORTANTE: Cambiar la contraseña después del primer acceso
-- ============================================
