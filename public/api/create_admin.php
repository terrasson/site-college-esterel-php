<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/Security.php';

// Initialiser la sécurité
Security::init();

// Paramètres des utilisateurs depuis .env
$users = [
    [
        'username' => getenv('ADMIN_USERNAME'),
        'password' => getenv('ADMIN_PASSWORD'),
        'role' => 'admin'
    ],
    [
        'username' => getenv('CUISINE_USERNAME'),
        'password' => getenv('CUISINE_PASSWORD'),
        'role' => 'cuisine'
    ],
    [
        'username' => getenv('DIRECTION_USERNAME'),
        'password' => getenv('DIRECTION_PASSWORD'),
        'role' => 'direction'
    ]
];

try {
    $pdo = getDBConnection();
    
    foreach ($users as $user) {
        // Vérifier si l'utilisateur existe déjà
        $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
        $stmt->execute([$user['username']]);
        
        if (!$stmt->fetch()) {
            // Créer l'utilisateur
            $stmt = $pdo->prepare('INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)');
            $password_hash = Security::hashPassword($user['password']);
            $stmt->execute([$user['username'], $password_hash, $user['role']]);
            
            echo "Utilisateur {$user['role']} créé avec succès!\n";
            echo "Username: " . $user['username'] . "\n";
            echo "Password: [Défini dans le fichier .env]\n";
            echo "IMPORTANT: Changez ce mot de passe après la première connexion!\n\n";
        } else {
            echo "L'utilisateur {$user['role']} existe déjà.\n\n";
        }
    }
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
} 