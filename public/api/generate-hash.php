<?php
require_once 'config.php';

// Utiliser les mots de passe depuis les variables d'environnement
$passwords = [
    'admin' => getenv('ADMIN_PASSWORD'),
    'direction' => getenv('DIRECTION_PASSWORD'),
    'cuisine' => getenv('CUISINE_PASSWORD')
];

echo "Hashs des mots de passe :\n\n";

foreach ($passwords as $user => $password) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    echo "Utilisateur : $user\n";
    echo "Hash : $hash\n\n";
} 