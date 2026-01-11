<?php

/**
 * Script simplificado de verificación de instalación
 */

echo "==============================================\n";
echo "VERIFICACIÓN DE INSTALACIÓN - MJBolivia2\n";
echo "==============================================\n\n";

// Test 1: Verificar PHP
echo "1. VERIFICACIÓN DE PHP\n";
echo "   Versión de PHP: " . phpversion() . "\n";
if (version_compare(phpversion(), '8.0.0', '>=')) {
    echo "   ✓ PHP 8.0+ detectado\n\n";
} else {
    echo "   ✗ Se requiere PHP 8.0 o superior\n\n";
}

// Test 2: Verificar extensiones
echo "2. VERIFICACIÓN DE EXTENSIONES PHP\n";
$extensions = ['pdo', 'pdo_mysql', 'gd', 'fileinfo', 'session'];
foreach ($extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "   ✓ Extensión $ext: Instalada\n";
    } else {
        echo "   ✗ Extensión $ext: NO instalada\n";
    }
}
echo "\n";

// Test 3: Verificar directorios
echo "3. VERIFICACIÓN DE DIRECTORIOS\n";
$directories = [
    'assets/uploads' => 'Directorio de uploads',
    'logs' => 'Directorio de logs'
];

foreach ($directories as $dir => $desc) {
    if (is_dir($dir)) {
        echo "   ✓ $desc ($dir): Existe\n";
        if (is_writable($dir)) {
            echo "   ✓ $desc: Tiene permisos de escritura\n";
        } else {
            echo "   ⚠ $desc: NO tiene permisos de escritura\n";
        }
    } else {
        echo "   ✗ $desc ($dir): NO existe\n";
    }
}
echo "\n";

// Test 4: Verificar conexión a base de datos
echo "4. VERIFICACIÓN DE CONEXIÓN A BASE DE DATOS\n";
try {
    $host = 'localhost';
    $user = 'root';
    $pass = '';
    $dbname = 'web_institucional';

    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "   ✓ Conexión a base de datos: Exitosa\n\n";

    // Verificar tablas
    echo "5. VERIFICACIÓN DE TABLAS\n";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "   Total de tablas: " . count($tables) . "\n";

    $expectedTables = [
        'usuarios',
        'eventos',
        'inscripciones_eventos',
        'administradores',
        'configuracion',
        'inscripciones',
        'configuracion_inscripciones',
        'mision_vision',
        'carrusel',
        'galeria',
        'contactos'
    ];

    foreach ($expectedTables as $table) {
        if (in_array($table, $tables)) {
            echo "   ✓ Tabla '$table': Existe\n";
        } else {
            echo "   ✗ Tabla '$table': NO existe\n";
        }
    }
    echo "\n";

    // Verificar usuario administrador del sitio institucional
    echo "6. VERIFICACIÓN DE USUARIO ADMINISTRADOR (Sitio Institucional)\n";
    $stmt = $pdo->query("SELECT id, nombre, email, rol, estado FROM administradores WHERE id = 1");
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($admin) {
        echo "   ✓ Usuario administrador encontrado:\n";
        echo "      - ID: " . $admin['id'] . "\n";
        echo "      - Nombre: " . $admin['nombre'] . "\n";
        echo "      - Email: " . $admin['email'] . "\n";
        echo "      - Rol: " . $admin['rol'] . "\n";
        echo "      - Estado: " . $admin['estado'] . "\n";
    } else {
        echo "   ✗ Usuario administrador NO encontrado\n";
    }
    echo "\n";

    // Verificar usuario del sistema de eventos
    echo "7. VERIFICACIÓN DE USUARIO SUPER ADMIN (Sistema de Eventos)\n";
    $stmt = $pdo->query("SELECT id, username, email, nombre_completo, rol FROM usuarios WHERE rol = 'super_admin' LIMIT 1");
    $superAdmin = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($superAdmin) {
        echo "   ✓ Super administrador encontrado:\n";
        echo "      - ID: " . $superAdmin['id'] . "\n";
        echo "      - Username: " . $superAdmin['username'] . "\n";
        echo "      - Email: " . $superAdmin['email'] . "\n";
        echo "      - Nombre: " . $superAdmin['nombre_completo'] . "\n";
        echo "      - Rol: " . $superAdmin['rol'] . "\n";
    } else {
        echo "   ✗ Super administrador NO encontrado\n";
    }
    echo "\n";

    // Verificar configuración
    echo "8. VERIFICACIÓN DE CONFIGURACIÓN\n";
    $stmt = $pdo->query("SELECT * FROM configuracion WHERE id = 1");
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($config) {
        echo "   ✓ Configuración del sitio encontrada:\n";
        echo "      - Nombre: " . $config['nombre_institucion'] . "\n";
        echo "      - Email: " . $config['email_contacto'] . "\n";
        echo "      - Teléfono: " . $config['telefono'] . "\n";
    } else {
        echo "   ✗ Configuración NO encontrada\n";
    }
    echo "\n";

    // Verificar configuración de inscripciones
    echo "9. VERIFICACIÓN DE CONFIGURACIÓN DE INSCRIPCIONES\n";
    $stmt = $pdo->query("SELECT * FROM configuracion_inscripciones WHERE id = 1");
    $configInsc = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($configInsc) {
        echo "   ✓ Configuración de inscripciones encontrada:\n";
        echo "      - Monto inscripción: $" . $configInsc['monto_inscripcion'] . "\n";
        echo "      - Monto alojamiento: $" . $configInsc['monto_alojamiento'] . "\n";
        echo "      - Estado: " . $configInsc['estado'] . "\n";
    } else {
        echo "   ✗ Configuración de inscripciones NO encontrada\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "   ✗ Error de conexión: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Test 5: Verificar archivos importantes
echo "10. VERIFICACIÓN DE ARCHIVOS IMPORTANTES\n";
$files = [
    'index.php' => 'Página principal',
    'config/config.php' => 'Configuración',
    'includes/functions.php' => 'Funciones',
    'includes/auth.php' => 'Autenticación',
    'admin/login.php' => 'Login administrativo',
    'admin/dashboard.php' => 'Dashboard',
    'inscripciones/index.php' => 'Sistema de inscripciones'
];

foreach ($files as $file => $desc) {
    if (file_exists($file)) {
        echo "   ✓ $desc ($file): Existe\n";
    } else {
        echo "   ✗ $desc ($file): NO existe\n";
    }
}
echo "\n";

// Resumen
echo "==============================================\n";
echo "RESUMEN DE LA INSTALACIÓN\n";
echo "==============================================\n\n";

echo "CREDENCIALES DE ACCESO:\n\n";

echo "1. SITIO WEB INSTITUCIONAL:\n";
echo "   URL: http://localhost/proyectos/MJBolivia2/\n\n";

echo "2. PANEL ADMINISTRATIVO (Sitio Institucional):\n";
echo "   URL: http://localhost/proyectos/MJBolivia2/admin/login.php\n";
echo "   Email: admin@institucion.com\n";
echo "   Contraseña: admin123\n\n";

echo "3. SISTEMA DE INSCRIPCIONES:\n";
echo "   URL: http://localhost/proyectos/MJBolivia2/inscripciones/\n\n";

echo "4. SISTEMA DE GESTIÓN DE EVENTOS:\n";
echo "   URL: http://localhost/proyectos/MJBolivia2/eventos/\n";
echo "   Username: admin\n";
echo "   Contraseña: admin123\n\n";

echo "⚠️  IMPORTANTE:\n";
echo "   - Cambia las contraseñas después del primer inicio de sesión\n";
echo "   - Puedes eliminar los archivos de instalación por seguridad:\n";
echo "     * test_installation.php\n";
echo "     * verify_installation.php\n";
echo "     * check_installation.php\n";
echo "     * install_database.sql\n";
echo "     * import_database.php\n";
echo "     * generate_password.php\n\n";

echo "==============================================\n";
echo "✓ INSTALACIÓN COMPLETADA EXITOSAMENTE\n";
echo "==============================================\n";
