<?php

require_once 'config/config.php';

$db = getDB();

echo "<h1>Creando tablas faltantes</h1>";

// Crear tabla configuracion_eventos
$db->exec("
CREATE TABLE IF NOT EXISTS configuracion_eventos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    evento_id INT NOT NULL,
    precio_base DECIMAL(10,2) DEFAULT 0.00,
    precio_alojamiento DECIMAL(10,2) DEFAULT 0.00,
    max_participantes INT DEFAULT 200,
    requiere_aprobacion BOOLEAN DEFAULT FALSE,
    instrucciones_pago TEXT,
    campos_extra JSON,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (evento_id) REFERENCES eventos(id) ON DELETE CASCADE,
    UNIQUE KEY unique_evento (evento_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

echo "Tabla configuracion_eventos creada<br>";

// Crear tabla log_actividades
$db->exec("
CREATE TABLE IF NOT EXISTS log_actividades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    evento_id INT,
    accion VARCHAR(100) NOT NULL,
    descripcion TEXT,
    ip_address VARCHAR(45),
    fecha_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    FOREIGN KEY (evento_id) REFERENCES eventos(id) ON DELETE SET NULL,
    INDEX idx_usuario (usuario_id),
    INDEX idx_evento_log (evento_id),
    INDEX idx_fecha (fecha_hora)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

echo "Tabla log_actividades creada<br>";

// Crear tabla eventos_administradores
$db->exec("
CREATE TABLE IF NOT EXISTS eventos_administradores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    evento_id INT NOT NULL,
    usuario_id INT NOT NULL,
    asignado_por INT NOT NULL,
    fecha_asignacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (evento_id) REFERENCES eventos(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (asignado_por) REFERENCES usuarios(id) ON DELETE RESTRICT,
    UNIQUE KEY unique_asignacion (evento_id, usuario_id),
    INDEX idx_usuario_evento (usuario_id, evento_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

echo "Tabla eventos_administradores creada<br>";

// Crear tabla sesiones
$db->exec("
CREATE TABLE IF NOT EXISTS sesiones (
    id VARCHAR(128) PRIMARY KEY,
    usuario_id INT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    ultima_actividad TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    datos TEXT,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_usuario_sesion (usuario_id),
    INDEX idx_ultima_actividad (ultima_actividad)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

echo "Tabla sesiones creada<br>";

// Crear tabla intentos_login
$db->exec("
CREATE TABLE IF NOT EXISTS intentos_login (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    username VARCHAR(50),
    intentos INT DEFAULT 1,
    ultimo_intento TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    bloqueado BOOLEAN DEFAULT FALSE,
    INDEX idx_ip (ip_address),
    INDEX idx_username (username),
    INDEX idx_ultimo_intento (ultimo_intento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

echo "Tabla intentos_login creada<br>";

// Crear tabla permisos
$db->exec("
CREATE TABLE IF NOT EXISTS permisos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rol VARCHAR(20) NOT NULL,
    recurso VARCHAR(100) NOT NULL,
    accion VARCHAR(50) NOT NULL,
    descripcion TEXT,
    UNIQUE KEY unique_permiso (rol, recurso, accion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

echo "Tabla permisos creada<br>";

// Insertar permisos por defecto
$db->exec("
INSERT INTO permisos (rol, recurso, accion, descripcion) VALUES
('super_admin', 'eventos', 'crear', 'Crear nuevos eventos'),
('super_admin', 'eventos', 'editar', 'Editar cualquier evento'),
('super_admin', 'eventos', 'eliminar', 'Eliminar eventos'),
('super_admin', 'eventos', 'configurar', 'Configurar eventos'),
('super_admin', 'usuarios', 'crear', 'Crear usuarios'),
('super_admin', 'usuarios', 'editar', 'Editar usuarios'),
('super_admin', 'usuarios', 'eliminar', 'Eliminar usuarios'),
('super_admin', 'configuracion', 'acceder', 'Acceder a configuracion global'),
('super_admin', 'todos_eventos', 'acceder', 'Acceder a todos los eventos'),

('admin', 'eventos_asignados', 'acceder', 'Acceder solo a eventos asignados'),
('admin', 'inscripciones', 'crear', 'Crear inscripciones'),
('admin', 'inscripciones', 'editar', 'Editar inscripciones'),
('admin', 'inscripciones', 'eliminar', 'Eliminar inscripciones'),
('admin', 'inscripciones', 'aprobar', 'Aprobar inscripciones'),
('admin', 'grupos', 'formar', 'Formar grupos'),
('admin', 'reportes', 'ver', 'Ver reportes'),
('admin', 'pagos', 'registrar', 'Registrar pagos'),

('usuario', 'inscripciones', 'crear_propia', 'Crear inscripcion propia'),
('usuario', 'inscripciones', 'ver_propia', 'Ver inscripcion propia')
ON DUPLICATE KEY UPDATE descripcion = VALUES(descripcion);
");

echo "Permisos insertados<br>";

// Crear tabla configuracion_global
$db->exec("
CREATE TABLE IF NOT EXISTS configuracion_global (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(100) NOT NULL UNIQUE,
    valor TEXT,
    tipo VARCHAR(20) DEFAULT 'string',
    descripcion TEXT,
    editable BOOLEAN DEFAULT TRUE,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

echo "Tabla configuracion_global creada<br>";

// Insertar configuracion global por defecto
$db->exec("
INSERT INTO configuracion_global (clave, valor, tipo, descripcion) VALUES
('nombre_sistema', 'Sistema de Gestion de Eventos', 'string', 'Nombre del sistema'),
('correo_contacto', 'contacto@example.com', 'string', 'Correo de contacto'),
('telefono_contacto', '+1-234-567-8900', 'string', 'Telefono de contacto'),
('direccion_contacto', 'Calle Principal 123, Ciudad', 'string', 'Direccion fisica'),
('max_intentos_login', '5', 'integer', 'Maximo de intentos de login antes de bloqueo'),
('tiempo_bloqueo_minutos', '30', 'integer', 'Tiempo de bloqueo en minutos'),
('formato_fecha', 'd/m/Y', 'string', 'Formato de fecha para el sistema'),
('moneda_predeterminada', 'USD', 'string', 'Moneda predeterminada')
ON DUPLICATE KEY UPDATE valor = VALUES(valor);
");

echo "Configuracion global insertada<br>";

// Crear tabla inscripciones_eventos
$db->exec("
CREATE TABLE IF NOT EXISTS inscripciones_eventos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    evento_id INT NOT NULL,
    codigo_inscripcion VARCHAR(20) NOT NULL UNIQUE,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    telefono VARCHAR(20),
    fecha_nacimiento DATE NOT NULL,
    iglesia VARCHAR(150),
    departamento VARCHAR(100),
    sexo ENUM('Masculino', 'Femenino') NOT NULL,
    tipo_inscripcion ENUM('Efectivo', 'QR', 'Deposito', 'Beca') NOT NULL,
    monto_pagado DECIMAL(10,2) DEFAULT 0.00,
    monto_total DECIMAL(10,2) NOT NULL,
    alojamiento ENUM('Si', 'No') DEFAULT 'No',
    grupo INT DEFAULT NULL,
    estado_pago ENUM('pendiente', 'parcial', 'completo', 'beca') DEFAULT 'pendiente',
    aprobado BOOLEAN DEFAULT FALSE,
    fecha_inscripcion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (evento_id) REFERENCES eventos(id) ON DELETE CASCADE,
    INDEX idx_evento (evento_id),
    INDEX idx_sexo (sexo),
    INDEX idx_grupo (grupo),
    INDEX idx_estado_pago (estado_pago),
    INDEX idx_codigo (codigo_inscripcion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

echo "Tabla inscripciones_eventos creada<br>";

echo "<h2>Tablas creadas exitosamente</h2>";
