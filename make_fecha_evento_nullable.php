<?php

require_once 'config/config.php';

$db = getDB();

try {
    $db->exec('ALTER TABLE eventos MODIFY COLUMN fecha_evento DATE NULL');
    echo 'fecha_evento made nullable';
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
