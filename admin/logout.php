<?php
require_once __DIR__ . '/../config/config.php';

// Cerrar sesion
if (isset($auth)) {
    $auth->logout();
} else {
    session_start();
    session_destroy();
}

// Redirigir al login
header('Location: login.php');
exit;
?>