<?php
// Script temporal para generar hash de contraseña
$password = 'admin123';
$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
echo "Hash generado para 'admin123':\n";
echo $hash . "\n";
file_put_contents('password_hash.txt', $hash);
echo "\nHash guardado en password_hash.txt\n";
