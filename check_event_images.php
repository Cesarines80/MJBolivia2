<?php
require_once 'config/config.php';

$db = getDB();
$stmt = $db->query("SELECT id, titulo, imagen, imagen_portada, fecha_inicio, fecha_evento, estado FROM eventos ORDER BY id ASC");
$eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Eventos próximos y sus imágenes:\n\n";

foreach ($eventos as $evento) {
    echo "ID: {$evento['id']}\n";
    echo "Título: {$evento['titulo']}\n";
    echo "Fecha inicio: {$evento['fecha_inicio']}\n";
    echo "Fecha evento: {$evento['fecha_evento']}\n";
    echo "Estado: {$evento['estado']}\n";
    echo "Imagen: {$evento['imagen']}\n";
    echo "Imagen portada: {$evento['imagen_portada']}\n";

    if ($evento['imagen']) {
        $filePath = UPLOADS_DIR . $evento['imagen'];
        $url = UPLOADS_URL . $evento['imagen'];
        echo "Ruta del archivo: $filePath\n";
        echo "URL: $url\n";
        if (file_exists($filePath)) {
            echo "✓ Archivo existe\n";
        } else {
            echo "✗ Archivo NO existe\n";
        }
    } else {
        echo "Sin imagen\n";
    }
    echo "\n";
}
