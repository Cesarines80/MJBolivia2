<?php
require_once 'config/config.php';
$db = getDB();
$newHash = password_hash('superadmin123', PASSWORD_BCRYPT, ['cost' => 10]);
$stmt = $db->prepare("UPDATE usuarios SET password = ? WHERE username = ?");
$stmt->execute([$newHash, 'superadmin']);
echo "Password reset to superadmin123";
