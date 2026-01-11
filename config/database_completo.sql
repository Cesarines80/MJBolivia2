-- Sistema de Gestion de Eventos con Roles
-- Base de datos completa con aislamiento de eventos

-- Tabla de usuarios con roles
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    nombre_completo VARCHAR(100) NOT NULL,
    rol ENUM('super_admin', 'admin', 'usuario') DEFAULT 'usuario',
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultimo_acceso TIMESTAMP NULL,
    intentos_fallidos INT DEFAULT 0,
    bloqueado_hasta TIMESTAMP NULL,
    INDEX idx_username (username),
    INDEX idx_rol (rol)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de eventos (cada evento es independiente)
CREATE TABLE IF NOT EXISTS eventos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(200) NOT NULL,
    descripcion TEXT,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    fecha_inicio_inscripcion DATE NOT NULL,
    fecha_fin_inscripcion DATE NOT NULL,
    lugar VARCHAR(200),
    imagen_portada VARCHAR(255),
    estado ENUM('activo', 'inactivo', 'finalizado') DEFAULT 'activo',
    creado_por INT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (creado_por) REFERENCES usuarios(id) ON DELETE RESTRICT,
    INDEX idx_fechas (fecha_inicio_inscripcion, fecha_fin_inscripcion),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de configuracion por evento (cada evento tiene su propia config)
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

-- Tabla de inscripciones por evento (aisladas entre eventos)
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

-- Tabla de grupos por evento
CREATE TABLE IF NOT EXISTS grupos_eventos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    evento_id INT NOT NULL,
    numero_grupo INT NOT NULL,
    nombre_grupo VARCHAR(100),
    descripcion TEXT,
    capacidad_maxima INT DEFAULT 0,
    lider_grupo VARCHAR(100),
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (evento_id) REFERENCES eventos(id) ON DELETE CASCADE,
    UNIQUE KEY unique_grupo_evento (evento_id, numero_grupo),
    INDEX idx_evento_grupo (evento_id, numero_grupo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de pagos para seguimiento detallado
CREATE TABLE IF NOT EXISTS pagos_inscripciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    inscripcion_id INT NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    tipo_pago ENUM('Efectivo', 'QR', 'Deposito', 'Transferencia') NOT NULL,
    referencia_pago VARCHAR(100),
    fecha_pago DATE NOT NULL,
    confirmado_por INT,
    notas TEXT,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (inscripcion_id) REFERENCES inscripciones_eventos(id) ON DELETE CASCADE,
    FOREIGN KEY (confirmado_por) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_inscripcion (inscripcion_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de log de actividades
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

-- Tabla de permisos por rol
CREATE TABLE IF NOT EXISTS permisos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rol VARCHAR(20) NOT NULL,
    recurso VARCHAR(100) NOT NULL,
    accion VARCHAR(50) NOT NULL,
    descripcion TEXT,
    UNIQUE KEY unique_permiso (rol, recurso, accion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar permisos por defecto
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
('usuario', 'inscripciones', 'ver_propia', 'Ver inscripcion propia');

-- Tabla de asignacion de administradores a eventos
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

-- Tabla de configuracion global del sistema (solo para super admin)
CREATE TABLE IF NOT EXISTS configuracion_global (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(100) NOT NULL UNIQUE,
    valor TEXT,
    tipo VARCHAR(20) DEFAULT 'string',
    descripcion TEXT,
    editable BOOLEAN DEFAULT TRUE,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar configuracion global por defecto
INSERT INTO configuracion_global (clave, valor, tipo, descripcion) VALUES
('nombre_sistema', 'Sistema de Gestion de Eventos', 'string', 'Nombre del sistema'),
('correo_contacto', 'contacto@example.com', 'string', 'Correo de contacto'),
('telefono_contacto', '+1-234-567-8900', 'string', 'Telefono de contacto'),
('direccion_contacto', 'Calle Principal 123, Ciudad', 'string', 'Direccion fisica'),
('max_intentos_login', '5', 'integer', 'Maximo de intentos de login antes de bloqueo'),
('tiempo_bloqueo_minutos', '30', 'integer', 'Tiempo de bloqueo en minutos'),
('formato_fecha', 'd/m/Y', 'string', 'Formato de fecha para el sistema'),
('moneda_predeterminada', 'USD', 'string', 'Moneda predeterminada');

-- Tablas del sistema original (mantenidas para el sitio institucional)
CREATE TABLE IF NOT EXISTS carousel (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    descripcion TEXT,
    imagen VARCHAR(255) NOT NULL,
    orden INT DEFAULT 0,
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS eventos_institucion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    descripcion TEXT,
    fecha DATE NOT NULL,
    hora TIME,
    lugar VARCHAR(200),
    imagen VARCHAR(255),
    estado ENUM('activo', 'inactivo') DEFAULT 'activo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS galeria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    descripcion TEXT,
    imagen VARCHAR(255) NOT NULL,
    categoria VARCHAR(100),
    fecha DATE,
    orden INT DEFAULT 0,
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mision_vision (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo ENUM('mision', 'vision') NOT NULL,
    titulo VARCHAR(200) NOT NULL,
    contenido TEXT NOT NULL,
    imagen VARCHAR(255),
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS contactos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    telefono VARCHAR(20),
    asunto VARCHAR(200),
    mensaje TEXT NOT NULL,
    leido BOOLEAN DEFAULT FALSE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS paginas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    contenido TEXT,
    meta_descripcion TEXT,
    meta_keywords TEXT,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

-- Insertar usuario super administrador por defecto
INSERT INTO usuarios (username, password, email, nombre_completo, rol, activo) VALUES
('admin', '$2y$10$LxW0jxuQfPXZR0Yf4x3R.uLJd5K8sHSaJ4nK8A4n8A4n8A4n8A4n8', 'admin@example.com', 'Administrador Principal', 'super_admin', TRUE);

-- Nota: La contraseña por defecto es 'admin123'
-- Debe ser cambiada inmediatamente después del primer login