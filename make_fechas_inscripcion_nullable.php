<?php

require_once 'config/config.php';

$db = getDB();

try {
    $db->exec('ALTER TABLE eventos MODIFY COLUMN fecha_inicio_inscripcion DATE NULL');
    $db->exec('ALTER TABLE eventos MODIFY COLUMN fecha_fin_inscripcion DATE NULL');
    echo 'fechas inscripcion made nullable';
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
