<?php

/**
 * Script de prueba completo del sistema
 */

echo "==============================================\n";
echo "PRUEBA COMPLETA DEL SISTEMA - MJBolivia2\n";
echo "==============================================\n\n";

// Configuración
$baseUrl = 'http://localhost/proyectos/MJBolivia2';
$errors = [];
$warnings = [];
$success = [];

// Test 1: Verificar que el servidor web está funcionando
echo "1. VERIFICACIÓN DEL SERVIDOR WEB\n";
try {
    $context = stream_context_create([
        'http' => [
            'timeout' => 5,
            'ignore_errors' => true
        ]
    ]);

    $response = @file_get_contents($baseUrl . '/index.php', false, $context);

    if ($response !== false) {
        echo "   ✓ Servidor web respondiendo correctamente\n";
        echo "   ✓ Página principal accesible\n";
        $success[] = "Página principal accesible";

        // Verificar que no hay errores PHP en la página
        if (strpos($response, 'Fatal error') !== false || strpos($response, 'Parse error') !== false) {
            echo "   ✗ Se detectaron errores PHP en la página\n";
            $errors[] = "Errores PHP en página principal";
        } else {
            echo "   ✓ No se detectaron errores PHP\n";
            $success[] = "Sin errores PHP en página principal";
        }
    } else {
        echo "   ✗ No se pudo acceder al servidor web\n";
        echo "   ⚠ Asegúrate de que Apache esté ejecutándose en XAMPP\n";
        $errors[] = "Servidor web no responde";
    }
} catch (Exception $e) {
    echo "   ✗ Error al conectar: " . $e->getMessage() . "\n";
    $errors[] = "Error de conexión al servidor web";
}
echo "\n";

// Test 2: Verificar archivos críticos
echo "2. VERIFICACIÓN DE ARCHIVOS CRÍTICOS\n";
$criticalFiles = [
    'index.php' => 'Página principal',
    'admin/login.php' => 'Login administrativo',
    'admin/dashboard.php' => 'Dashboard',
    'inscripciones/index.php' => 'Sistema de inscripciones',
    'config/config.php' => 'Configuración',
    'includes/functions.php' => 'Funciones',
    'includes/auth.php' => 'Autenticación'
];

foreach ($criticalFiles as $file => $desc) {
    if (file_exists($file)) {
        echo "   ✓ $desc: Existe\n";

        // Verificar sintaxis PHP
        $output = [];
        $return = 0;
        exec("php -l \"$file\" 2>&1", $output, $return);

        if ($return === 0) {
            echo "   ✓ $desc: Sintaxis correcta\n";
            $success[] = "$desc - sintaxis correcta";
        } else {
            echo "   ✗ $desc: Error de sintaxis\n";
            $errors[] = "$desc - error de sintaxis";
        }
    } else {
        echo "   ✗ $desc: NO existe\n";
        $errors[] = "$desc no existe";
    }
}
echo "\n";

// Test 3: Verificar conexión a base de datos desde diferentes módulos
echo "3. VERIFICACIÓN DE CONEXIÓN A BASE DE DATOS\n";
try {
    require_once 'config/config.php';
    $db = getDB();
    echo "   ✓ Conexión desde config.php: Exitosa\n";
    $success[] = "Conexión a BD exitosa";

    // Verificar tablas críticas
    $criticalTables = [
        'usuarios' => 'Sistema de usuarios',
        'administradores' => 'Administradores del sitio',
        'eventos' => 'Eventos',
        'inscripciones' => 'Inscripciones',
        'configuracion' => 'Configuración del sitio'
    ];

    foreach ($criticalTables as $table => $desc) {
        $stmt = $db->query("SELECT COUNT(*) FROM `$table`");
        $count = $stmt->fetchColumn();
        echo "   ✓ Tabla '$table' ($desc): $count registros\n";
        $success[] = "Tabla $table accesible";
    }
} catch (Exception $e) {
    echo "   ✗ Error de conexión: " . $e->getMessage() . "\n";
    $errors[] = "Error de conexión a BD: " . $e->getMessage();
}
echo "\n";

// Test 4: Verificar usuarios administradores
echo "4. VERIFICACIÓN DE USUARIOS ADMINISTRADORES\n";
try {
    // Admin del sitio institucional
    $stmt = $db->query("SELECT email, rol, estado FROM administradores WHERE id = 1");
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin) {
        echo "   ✓ Admin del sitio institucional:\n";
        echo "      - Email: " . $admin['email'] . "\n";
        echo "      - Rol: " . $admin['rol'] . "\n";
        echo "      - Estado: " . $admin['estado'] . "\n";
        $success[] = "Admin del sitio configurado";
    } else {
        echo "   ✗ Admin del sitio NO encontrado\n";
        $errors[] = "Admin del sitio no encontrado";
    }

    // Super admin del sistema de eventos
    $stmt = $db->query("SELECT username, email, rol FROM usuarios WHERE rol = 'super_admin' LIMIT 1");
    $superAdmin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($superAdmin) {
        echo "   ✓ Super admin del sistema de eventos:\n";
        echo "      - Username: " . $superAdmin['username'] . "\n";
        echo "      - Email: " . $superAdmin['email'] . "\n";
        echo "      - Rol: " . $superAdmin['rol'] . "\n";
        $success[] = "Super admin configurado";
    } else {
        echo "   ✗ Super admin NO encontrado\n";
        $errors[] = "Super admin no encontrado";
    }
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
    $errors[] = "Error al verificar usuarios: " . $e->getMessage();
}
echo "\n";

// Test 5: Verificar configuración del sistema
echo "5. VERIFICACIÓN DE CONFIGURACIÓN\n";
try {
    $stmt = $db->query("SELECT * FROM configuracion WHERE id = 1");
    $config = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($config) {
        echo "   ✓ Configuración del sitio:\n";
        echo "      - Nombre: " . $config['nombre_institucion'] . "\n";
        echo "      - Email: " . $config['email_contacto'] . "\n";
        echo "      - Teléfono: " . $config['telefono'] . "\n";
        $success[] = "Configuración del sitio OK";
    }

    $stmt = $db->query("SELECT * FROM configuracion_inscripciones WHERE id = 1");
    $configInsc = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($configInsc) {
        echo "   ✓ Configuración de inscripciones:\n";
        echo "      - Monto inscripción: $" . $configInsc['monto_inscripcion'] . "\n";
        echo "      - Monto alojamiento: $" . $configInsc['monto_alojamiento'] . "\n";
        $success[] = "Configuración de inscripciones OK";
    }
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
    $errors[] = "Error al verificar configuración";
}
echo "\n";

// Test 6: Verificar permisos y estructura de directorios
echo "6. VERIFICACIÓN DE PERMISOS\n";
$directories = [
    'assets/uploads' => 'Uploads',
    'logs' => 'Logs'
];

foreach ($directories as $dir => $desc) {
    if (is_dir($dir)) {
        if (is_writable($dir)) {
            echo "   ✓ $desc: Permisos de escritura OK\n";
            $success[] = "$desc - permisos OK";

            // Intentar crear un archivo de prueba
            $testFile = $dir . '/test_' . time() . '.txt';
            if (@file_put_contents($testFile, 'test')) {
                echo "   ✓ $desc: Prueba de escritura exitosa\n";
                @unlink($testFile);
                $success[] = "$desc - escritura funcional";
            } else {
                echo "   ⚠ $desc: No se pudo escribir archivo de prueba\n";
                $warnings[] = "$desc - problema de escritura";
            }
        } else {
            echo "   ✗ $desc: Sin permisos de escritura\n";
            $errors[] = "$desc sin permisos de escritura";
        }
    } else {
        echo "   ✗ $desc: Directorio no existe\n";
        $errors[] = "$desc no existe";
    }
}
echo "\n";

// Test 7: Verificar funciones críticas
echo "7. VERIFICACIÓN DE FUNCIONES CRÍTICAS\n";
try {
    // Probar función de hash de contraseña
    $testPassword = 'test123';
    $hash = password_hash($testPassword, PASSWORD_BCRYPT);
    if (password_verify($testPassword, $hash)) {
        echo "   ✓ Sistema de hash de contraseñas: Funcional\n";
        $success[] = "Hash de contraseñas funcional";
    } else {
        echo "   ✗ Sistema de hash de contraseñas: Error\n";
        $errors[] = "Hash de contraseñas no funciona";
    }

    // Probar función de limpieza de datos
    if (function_exists('cleanInput')) {
        $testInput = '<script>alert("test")</script>';
        $cleaned = cleanInput($testInput);
        if (strpos($cleaned, '<script>') === false) {
            echo "   ✓ Función cleanInput: Funcional\n";
            $success[] = "cleanInput funcional";
        } else {
            echo "   ✗ Función cleanInput: No sanitiza correctamente\n";
            $errors[] = "cleanInput no funciona correctamente";
        }
    }

    // Probar conexión a BD
    if (function_exists('getDB')) {
        $testDb = getDB();
        if ($testDb instanceof PDO) {
            echo "   ✓ Función getDB: Funcional\n";
            $success[] = "getDB funcional";
        }
    }
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
    $errors[] = "Error en funciones críticas";
}
echo "\n";

// Test 8: Verificar sesiones
echo "8. VERIFICACIÓN DE SISTEMA DE SESIONES\n";
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "   ✓ Sesiones PHP: Activas\n";
    $success[] = "Sesiones activas";
} else {
    echo "   ⚠ Sesiones PHP: No iniciadas (normal en CLI)\n";
    $warnings[] = "Sesiones no iniciadas en CLI";
}

// Verificar tabla de sesiones
try {
    $stmt = $db->query("SHOW TABLES LIKE 'sesiones'");
    if ($stmt->fetch()) {
        echo "   ✓ Tabla de sesiones: Existe\n";
        $success[] = "Tabla sesiones existe";
    }
} catch (Exception $e) {
    echo "   ⚠ Tabla de sesiones: " . $e->getMessage() . "\n";
}
echo "\n";

// Resumen final
echo "==============================================\n";
echo "RESUMEN DE PRUEBAS\n";
echo "==============================================\n\n";

echo "✅ EXITOSAS: " . count($success) . "\n";
foreach ($success as $item) {
    echo "   • $item\n";
}
echo "\n";

if (count($warnings) > 0) {
    echo "⚠️  ADVERTENCIAS: " . count($warnings) . "\n";
    foreach ($warnings as $item) {
        echo "   • $item\n";
    }
    echo "\n";
}

if (count($errors) > 0) {
    echo "❌ ERRORES: " . count($errors) . "\n";
    foreach ($errors as $item) {
        echo "   • $item\n";
    }
    echo "\n";
    echo "⚠️  Se encontraron errores que deben ser corregidos.\n\n";
} else {
    echo "✅ NO SE ENCONTRARON ERRORES\n\n";
}

// Conclusión
echo "==============================================\n";
if (count($errors) === 0) {
    echo "✅ SISTEMA LISTO PARA USAR\n";
    echo "==============================================\n\n";

    echo "Próximos pasos:\n";
    echo "1. Abre tu navegador y ve a: $baseUrl\n";
    echo "2. Accede al panel admin: $baseUrl/admin/login.php\n";
    echo "3. Credenciales: admin@institucion.com / admin123\n";
    echo "4. Cambia la contraseña por defecto\n";
    echo "5. Personaliza la configuración del sitio\n\n";
} else {
    echo "⚠️  REQUIERE ATENCIÓN\n";
    echo "==============================================\n\n";
    echo "Por favor, revisa y corrige los errores antes de usar el sistema.\n\n";
}

echo "Para pruebas manuales en el navegador:\n";
echo "• Sitio web: $baseUrl/\n";
echo "• Panel admin: $baseUrl/admin/login.php\n";
echo "• Inscripciones: $baseUrl/inscripciones/\n";
echo "• Eventos: $baseUrl/eventos/\n\n";
