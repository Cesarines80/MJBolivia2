<?php
require_once 'config/config.php';
require_once 'includes/functions.php';

echo "<h1>Debug de Imágenes</h1>";

// Obtener datos
$carrusel = Carrusel::getAll();
$eventos = Eventos::getUpcoming(6);
$galeria = Galeria::getAll();

echo "<h2>Carrusel:</h2>";
foreach ($carrusel as $item) {
    echo "ID: {$item['id']}, Imagen: {$item['imagen']}, URL: " . UPLOADS_URL . $item['imagen'] . "<br>";
}

echo "<h2>Eventos:</h2>";
foreach ($eventos as $evento) {
    echo "ID: {$evento['id']}, Imagen: {$evento['imagen']}, URL: " . UPLOADS_URL . $evento['imagen'] . "<br>";
}

echo "<h2>Galería:</h2>";
foreach ($galeria as $item) {
    echo "ID: {$item['id']}, Imagen: {$item['imagen']}, URL: " . UPLOADS_URL . $item['imagen'] . "<br>";
}
