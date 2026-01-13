<?php
require_once 'config/config.php';
$db = getDB();
$newHash = password_hash('superadmin123', PASSWORD_BCRYPT, ['cost' => 10]);
$db->exec("UPDATE usuarios SET password = '$newHash' WHERE username = 'superadmin'");
echo "Password reset to superadmin123";
