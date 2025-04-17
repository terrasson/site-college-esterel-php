<?php
require_once 'config.php';

$users = [
    'admin' => getenv('ADMIN_PASSWORD'),
    'direction' => getenv('DIRECTION_PASSWORD'),
    'cuisine' => getenv('CUISINE_PASSWORD')
];

foreach ($users as $username => $password) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    echo "=== $username ===\n";
    echo "Hash : $hash\n";
    
    if (password_verify($password, $hash)) {
        echo "✓ Vérification OK\n";
    } else {
        echo "✗ Vérification échouée\n";
    }
    echo "\n";
} 