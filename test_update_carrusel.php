<?php

/**
 * Script de prueba para UPDATE de Carrusel
 */
require_once 'config/config.php';

// Simular sesión de administrador
$_SESSION['admin_id'] = 1;
$_SESSION['admin_nombre'] = 'Administrador Principal';
$_SESSION['admin_email'] = 'admin@institucion.com';
$_SESSION['admin_rol'] = 'superadmin';
$_SESSION['is_admin'] = true;

echo "<h1>Prueba de UPDATE - Carrusel</h1><hr>";

try {
    // 1. Crear un elemento de prueba
    echo "<h2>1. Crear elemento de prueba</h2>";
    $testData = [
        'titulo' => 'Test Update ' . time(),
        'descripcion' => 'Descripción original',
        'imagen' => '',
        'tipo' => 'imagen',
        'url' => '',
        'orden' => 0,
        'estado' => 'activo'
    ];

    if (Carrusel::create($testData)) {
        $db = getDB();
        $lastId = $db->lastInsertId();
        echo "✅ Elemento creado con ID: $lastId<br>";

        // 2. Leer el elemento
        echo "<h2>2. Leer elemento creado</h2>";
        $item = Carrusel::getById($lastId);
        echo "<pre>";
        print_r($item);
        echo "</pre>";

        // 3. Actualizar SIN cambiar imagen
        echo "<h2>3. Actualizar SIN cambiar imagen</h2>";
        $updateData = [
            'titulo' => 'Test Actualizado ' . time(),
            'descripcion' => 'Descripción actualizada',
            'imagen' => $item['imagen'], // Mantener imagen existente
            'tipo' => 'imagen',
            'url' => 'https://example.com',
            'orden' => 1,
            'estado' => 'inactivo'
        ];

        echo "Datos a actualizar:<br><pre>";
        print_r($updateData);
        echo "</pre>";

        if (Carrusel::update($lastId, $updateData)) {
            echo "✅ Elemento actualizado correctamente<br>";

            // 4. Verificar actualización
            echo "<h2>4. Verificar actualización</h2>";
            $updatedItem = Carrusel::getById($lastId);
            echo "<pre>";
            print_r($updatedItem);
            echo "</pre>";

            if ($updatedItem['titulo'] === $updateData['titulo']) {
                echo "✅ Título actualizado correctamente<br>";
            } else {
                echo "❌ Error: Título no actualizado<br>";
            }

            if ($updatedItem['estado'] === 'inactivo') {
                echo "✅ Estado actualizado correctamente<br>";
            } else {
                echo "❌ Error: Estado no actualizado<br>";
            }
        } else {
            echo "❌ Error al actualizar elemento<br>";
        }

        // 5. Limpiar - eliminar elemento de prueba
        echo "<h2>5. Limpiar</h2>";
        if (Carrusel::delete($lastId)) {
            echo "✅ Elemento de prueba eliminado<br>";
        }
    } else {
        echo "❌ Error al crear elemento de prueba<br>";
    }
} catch (Exception $e) {
    echo "<div style='background:#ffebee; padding:15px; border-left:4px solid #f44336;'>";
    echo "<h2>❌ Error en las Pruebas</h2>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Archivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Línea:</strong> " . $e->getLine() . "</p>";
    echo "</div>";
}

echo "<hr>";
echo "<h2>Resumen</h2>";
echo "<p>Si todas las pruebas pasaron, el UPDATE funciona correctamente.</p>";
echo "<p>Ahora puedes probar desde el navegador en: <a href='admin/carrusel.php'>admin/carrusel.php</a></p>";
