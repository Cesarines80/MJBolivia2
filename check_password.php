<?php
require_once 'config/config.php';
$db = getDB();
$stmt = $db->prepare("SELECT password FROM usuarios WHERE username = 'superadmin'");
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Hash: " . $user['password'] . "\n";
echo "Verify superadmin123: " . (password_verify('superadmin123', $user['password']) ? 'YES' : 'NO') . "\n";
echo "Verify superadminpass: " . (password_verify('superadminpass', $user['password']) ? 'YES' : 'NO') . "\n";
