<?php
require_once 'config/config.php';

$eventos = Eventos::getUpcoming(6);

echo "Eventos próximos:\n\n";

foreach ($eventos as $evento) {
    echo "ID: {$evento['id']}\n";
    echo "Título: {$evento['titulo']}\n";
    echo "Imagen: {$evento['imagen']}\n";
    echo "\n";
}
