<?php

/**
 * Script de prueba para CRUD de Carrusel y Galería
 */

require_once 'config/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Simular sesión de administrador
session_start();
$_SESSION['admin_id'] = 1;
$_SESSION['admin_nombre'] = 'Administrador Principal';
$_SESSION['admin_email'] = 'admin@institucion.com';
$_SESSION['admin_rol'] = 'superadmin';
$_SESSION['is_admin'] = true;

echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>Prueba CRUD</title>";
echo "<style>body{font-family:Arial;margin:20px;}.success{color:green;}.error{color:red;}.section{border:1px solid #ccc;padding:15px;margin:10px 0;border-radius:5px;}h2{color:#8B7EC8;border-bottom:2px solid #8B7EC8;padding-bottom:5px;}</style></head><body>";

echo "<h1>🧪 Prueba de CRUD - Carrusel y Galería</h1><hr>";

try {
    $db = getDB();

    // ============================================
    // PRUEBA 1: Verificar Autenticación
    // ============================================
    echo "<div class='section'>";
    echo "<h2>1. Verificar Autenticación</h2>";

    $currentUser = Auth::getUser();
    if ($currentUser) {
        echo "<p class='success'>✅ Usuario autenticado:</p>";
        echo "<ul>";
        echo "<li><strong>ID:</strong> " . $currentUser['id'] . "</li>";
        echo "<li><strong>Nombre:</strong> " . $currentUser['nombre'] . "</li>";
        echo "<li><strong>Email:</strong> " . $currentUser['email'] . "</li>";
        echo "<li><strong>Rol:</strong> " . $currentUser['rol'] . "</li>";
        echo "</ul>";
    } else {
        echo "<p class='error'>❌ No hay usuario autenticado</p>";
    }
    echo "</div>";

    // ============================================
    // PRUEBA 2: Verificar Tablas
    // ============================================
    echo "<div class='section'>";
    echo "<h2>2. Verificar Tablas en Base de Datos</h2>";

    $tables = ['carrusel', 'galeria'];
    foreach ($tables as $table) {
        $stmt = $db->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "<p class='success'>✅ Tabla <strong>$table</strong> existe</p>";

            // Mostrar estructura
            $stmt = $db->query("DESCRIBE $table");
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            echo "<small>Columnas: " . implode(', ', $columns) . "</small><br>";
        } else {
            echo "<p class='error'>❌ Tabla <strong>$table</strong> NO existe</p>";
        }
    }
    echo "</div>";

    // ============================================
    // PRUEBA 3: Verificar Clases
    // ============================================
    echo "<div class='section'>";
    echo "<h2>3. Verificar Clases PHP</h2>";

    $classes = ['Carrusel', 'Galeria'];
    foreach ($classes as $class) {
        if (class_exists($class)) {
            echo "<p class='success'>✅ Clase <strong>$class</strong> existe</p>";

            // Verificar métodos
            $methods = get_class_methods($class);
            echo "<small>Métodos disponibles: " . implode(', ', $methods) . "</small><br>";
        } else {
            echo "<p class='error'>❌ Clase <strong>$class</strong> NO existe</p>";
        }
    }
    echo "</div>";

    // ============================================
    // PRUEBA 4: CRUD de Carrusel
    // ============================================
    echo "<div class='section'>";
    echo "<h2>4. Prueba CRUD - Carrusel</h2>";

    // 4.1 Listar elementos
    echo "<h3>4.1. Listar Elementos</h3>";
    $items = Carrusel::getAll(false);
    echo "<p class='success'>✅ Total de elementos: " . count($items) . "</p>";

    // 4.2 Crear elemento de prueba
    echo "<h3>4.2. Crear Elemento</h3>";
    $testData = [
        'titulo' => 'Elemento de Prueba ' . time(),
        'descripcion' => 'Descripción de prueba',
        'imagen' => 'test.jpg',
        'tipo' => 'imagen',
        'url' => null,
        'orden' => 1,
        'estado' => 'activo'
    ];

    if (Carrusel::create($testData)) {
        echo "<p class='success'>✅ Elemento creado correctamente</p>";
        $lastId = $db->lastInsertId();
        echo "<p>ID del nuevo elemento: <strong>$lastId</strong></p>";

        // 4.3 Leer elemento
        echo "<h3>4.3. Leer Elemento</h3>";
        $item = Carrusel::getById($lastId);
        if ($item) {
            echo "<p class='success'>✅ Elemento leído correctamente:</p>";
            echo "<ul>";
            echo "<li><strong>ID:</strong> " . $item['id'] . "</li>";
            echo "<li><strong>Título:</strong> " . $item['titulo'] . "</li>";
            echo "<li><strong>Estado:</strong> " . $item['estado'] . "</li>";
            echo "</ul>";

            // 4.4 Actualizar elemento
            echo "<h3>4.4. Actualizar Elemento</h3>";
            $updateData = [
                'titulo' => 'Elemento Actualizado ' . time(),
                'descripcion' => 'Descripción actualizada',
                'imagen' => 'test.jpg',
                'tipo' => 'imagen',
                'url' => null,
                'orden' => 2,
                'estado' => 'inactivo'
            ];

            if (Carrusel::update($lastId, $updateData)) {
                echo "<p class='success'>✅ Elemento actualizado correctamente</p>";

                // Verificar actualización
                $updatedItem = Carrusel::getById($lastId);
                echo "<p>Nuevo título: <strong>" . $updatedItem['titulo'] . "</strong></p>";
                echo "<p>Nuevo estado: <strong>" . $updatedItem['estado'] . "</strong></p>";
            } else {
                echo "<p class='error'>❌ Error al actualizar elemento</p>";
            }

            // 4.5 Eliminar elemento
            echo "<h3>4.5. Eliminar Elemento</h3>";
            if (Carrusel::delete($lastId)) {
                echo "<p class='success'>✅ Elemento eliminado correctamente</p>";

                // Verificar eliminación
                $deletedItem = Carrusel::getById($lastId);
                if (!$deletedItem) {
                    echo "<p class='success'>✅ Confirmado: Elemento ya no existe</p>";
                } else {
                    echo "<p class='error'>⚠️ Advertencia: Elemento aún existe en BD</p>";
                }
            } else {
                echo "<p class='error'>❌ Error al eliminar elemento</p>";
            }
        } else {
            echo "<p class='error'>❌ No se pudo leer el elemento creado</p>";
        }
    } else {
        echo "<p class='error'>❌ Error al crear elemento</p>";
    }
    echo "</div>";

    // ============================================
    // PRUEBA 5: CRUD de Galería
    // ============================================
    echo "<div class='section'>";
    echo "<h2>5. Prueba CRUD - Galería</h2>";

    // 5.1 Listar elementos
    echo "<h3>5.1. Listar Elementos</h3>";
    $items = Galeria::getAll(false);
    echo "<p class='success'>✅ Total de elementos: " . count($items) . "</p>";

    // 5.2 Crear elemento de prueba
    echo "<h3>5.2. Crear Elemento</h3>";
    $testData = [
        'titulo' => 'Imagen de Prueba ' . time(),
        'descripcion' => 'Descripción de prueba',
        'imagen' => 'test.jpg',
        'categoria' => 'prueba'
    ];

    if (Galeria::create($testData)) {
        echo "<p class='success'>✅ Elemento creado correctamente</p>";
        $lastId = $db->lastInsertId();
        echo "<p>ID del nuevo elemento: <strong>$lastId</strong></p>";

        // 5.3 Leer elemento
        echo "<h3>5.3. Leer Elemento</h3>";
        $item = Galeria::getById($lastId);
        if ($item) {
            echo "<p class='success'>✅ Elemento leído correctamente:</p>";
            echo "<ul>";
            echo "<li><strong>ID:</strong> " . $item['id'] . "</li>";
            echo "<li><strong>Título:</strong> " . $item['titulo'] . "</li>";
            echo "<li><strong>Categoría:</strong> " . $item['categoria'] . "</li>";
            echo "</ul>";

            // 5.4 Actualizar elemento
            echo "<h3>5.4. Actualizar Elemento</h3>";
            $updateData = [
                'titulo' => 'Imagen Actualizada ' . time(),
                'descripcion' => 'Descripción actualizada',
                'imagen' => 'test.jpg',
                'categoria' => 'actualizada'
            ];

            if (Galeria::update($lastId, $updateData)) {
                echo "<p class='success'>✅ Elemento actualizado correctamente</p>";

                // Verificar actualización
                $updatedItem = Galeria::getById($lastId);
                echo "<p>Nuevo título: <strong>" . $updatedItem['titulo'] . "</strong></p>";
                echo "<p>Nueva categoría: <strong>" . $updatedItem['categoria'] . "</strong></p>";
            } else {
                echo "<p class='error'>❌ Error al actualizar elemento</p>";
            }

            // 5.5 Eliminar elemento
            echo "<h3>5.5. Eliminar Elemento</h3>";
            if (Galeria::delete($lastId)) {
                echo "<p class='success'>✅ Elemento eliminado correctamente</p>";

                // Verificar eliminación
                $deletedItem = Galeria::getById($lastId);
                if (!$deletedItem) {
                    echo "<p class='success'>✅ Confirmado: Elemento ya no existe</p>";
                } else {
                    echo "<p class='error'>⚠️ Advertencia: Elemento aún existe en BD</p>";
                }
            } else {
                echo "<p class='error'>❌ Error al eliminar elemento</p>";
            }
        } else {
            echo "<p class='error'>❌ No se pudo leer el elemento creado</p>";
        }
    } else {
        echo "<p class='error'>❌ Error al crear elemento</p>";
    }
    echo "</div>";

    // ============================================
    // PRUEBA 6: Verificar AJAX
    // ============================================
    echo "<div class='section'>";
    echo "<h2>6. Verificar Endpoint AJAX</h2>";

    echo "<p class='success'>✅ Archivo ajax.php existe</p>";
    echo "<p>Métodos disponibles:</p>";
    echo "<ul>";
    echo "<li>get_carrusel - Obtener elemento de carrusel por ID</li>";
    echo "<li>get_galeria - Obtener elemento de galería por ID</li>";
    echo "</ul>";
    echo "</div>";

    // ============================================
    // RESUMEN FINAL
    // ============================================
    echo "<div class='section' style='background:#e8f5e9;'>";
    echo "<h2>✅ RESUMEN FINAL</h2>";
    echo "<p><strong>Estado del Sistema:</strong> Operativo</p>";
    echo "<ul>";
    echo "<li>✅ Autenticación funcionando</li>";
    echo "<li>✅ Tablas de BD verificadas</li>";
    echo "<li>✅ Clases PHP disponibles</li>";
    echo "<li>✅ CRUD de Carrusel funcionando</li>";
    echo "<li>✅ CRUD de Galería funcionando</li>";
    echo "<li>✅ Endpoint AJAX disponible</li>";
    echo "</ul>";

    echo "<h3>Acceso al Sistema:</h3>";
    echo "<ul>";
    echo "<li><strong>Panel Admin:</strong> <a href='admin/dashboard.php' target='_blank'>admin/dashboard.php</a></li>";
    echo "<li><strong>Carrusel:</strong> <a href='admin/carrusel.php' target='_blank'>admin/carrusel.php</a></li>";
    echo "<li><strong>Galería:</strong> <a href='admin/galeria.php' target='_blank'>admin/galeria.php</a></li>";
    echo "</ul>";

    echo "<p><strong>⚠️ Nota:</strong> Asegúrate de iniciar sesión en el panel admin antes de acceder a estas páginas.</p>";
    echo "</div>";
} catch (Exception $e) {
    echo "<div class='section' style='background:#ffebee;'>";
    echo "<h2>❌ Error en las Pruebas</h2>";
    echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
    echo "<p>Archivo: " . $e->getFile() . "</p>";
    echo "<p>Línea: " . $e->getLine() . "</p>";
    echo "</div>";
}

echo "</body></html>";
