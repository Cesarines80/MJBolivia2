<?php
require_once 'config/config.php';

$db = getDB();

try {
    $db->exec('ALTER TABLE eventos ADD COLUMN color VARCHAR(7) DEFAULT NULL AFTER estado');
    echo 'Campo color agregado exitosamente';
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
