<?php

require_once 'config/config.php';

$db = getDB();

try {
    $db->exec('ALTER TABLE eventos MODIFY COLUMN fecha_fin DATE NULL');
    echo 'fecha_fin made nullable';
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
