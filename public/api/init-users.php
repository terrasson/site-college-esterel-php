<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/Security.php';

try {
    // Initialiser la sécurité
    Security::init();
    echo "Sécurité initialisée\n";
    
    // Connexion à la base de données
    $pdo = getDBConnection();
    echo "Connexion à la base de données réussie\n";
    
    // Créer la table users si elle n'existe pas
    $pdo->exec('CREATE TABLE IF NOT EXISTS users (
        username TEXT UNIQUE,
        password_hash TEXT,
        role TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )');
    echo "Table users créée ou existante\n";
    
    // Récupérer les variables d'environnement
    echo "Variables d'environnement :\n";
    echo "ADMIN_USERNAME: " . getenv('ADMIN_USERNAME') . "\n";
    echo "CUISINE_USERNAME: " . getenv('CUISINE_USERNAME') . "\n";
    echo "DIRECTION_USERNAME: " . getenv('DIRECTION_USERNAME') . "\n";
    
    // Fonction pour nettoyer le mot de passe des commentaires
    function cleanPassword($password) {
        // Supprimer tout ce qui suit un # dans la chaîne
        return trim(preg_replace('/#.*$/', '', $password));
    }
    
    // Tableau des utilisateurs à créer
    $users = [
        [
            'username' => getenv('ADMIN_USERNAME'),
            'password' => cleanPassword(getenv('ADMIN_PASSWORD')),
            'role' => 'admin'
        ],
        [
            'username' => getenv('CUISINE_USERNAME'),
            'password' => cleanPassword(getenv('CUISINE_PASSWORD')),
            'role' => 'cuisine'
        ],
        [
            'username' => getenv('DIRECTION_USERNAME'),
            'password' => cleanPassword(getenv('DIRECTION_PASSWORD')),
            'role' => 'direction'
        ]
    ];
    
    // Insérer ou mettre à jour chaque utilisateur
    foreach ($users as $user) {
        echo "\nTraitement de l'utilisateur : " . $user['username'] . "\n";
        
        try {
            $stmt = $pdo->prepare('INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)');
            $password_hash = Security::hashPassword($user['password']);
            $stmt->execute([$user['username'], $password_hash, $user['role']]);
            echo "Utilisateur " . $user['username'] . " créé\n";
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Code d'erreur pour duplicate
                $stmt = $pdo->prepare('UPDATE users SET password_hash = ?, role = ? WHERE username = ?');
                $password_hash = Security::hashPassword($user['password']);
                $stmt->execute([$password_hash, $user['role'], $user['username']]);
                echo "Utilisateur " . $user['username'] . " mis à jour\n";
            } else {
                throw $e;
            }
        }
    }
    
    // Afficher tous les utilisateurs
    echo "\nUtilisateurs dans la base de données :\n";
    $stmt = $pdo->query('SELECT username, role FROM users');
    while ($row = $stmt->fetch()) {
        echo $row['username'] . " (" . $row['role'] . ")\n";
    }
    
    echo "\nUtilisateurs initialisés avec succès !\n";
    
} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
    exit(1);
} 