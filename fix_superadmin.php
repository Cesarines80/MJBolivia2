<?php
require_once 'config/config.php';
$db = getDB();
$db->exec("UPDATE usuarios SET bloqueado_hasta = NULL, intentos_fallidos = 0 WHERE username = 'superadmin'");
$db->exec("DELETE FROM intentos_login");
echo "Superadmin unblocked and all login attempts cleared";
