<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

try {
    $pdo = getDBConnection();
    
    echo "Connexion à la base de données réussie!\n\n";
    
    // Lecture et exécution du fichier SQL
    echo "Création des tables...\n";
    $sql = file_get_contents(__DIR__ . '/database.sql');
    $pdo->exec($sql);
    echo "Tables créées avec succès!\n\n";
    
    // Création de l'utilisateur admin
    echo "Configuration de l'administrateur...\n";
    require_once __DIR__ . '/create_admin.php';
    
    // Création de la table documents_direction
    $pdo->exec("CREATE TABLE IF NOT EXISTS documents_direction (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        type VARCHAR(50) NOT NULL,
        url VARCHAR(255) NOT NULL,
        created_at DATETIME NOT NULL,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    echo "Table documents_direction créée avec succès.\n";

    echo "\nConfiguration terminée avec succès!\n";
    echo "Vous pouvez maintenant vous connecter à l'adresse: http://localhost/\n";
    echo "Utilisez les identifiants fournis ci-dessus.\n";
    
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
} 