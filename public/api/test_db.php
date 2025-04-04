<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config.php';

try {
    echo "Tentative de connexion à la base de données...\n";
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";port=3306;dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    echo "Connexion réussie !\n";
    
    // Tester une requête simple
    $stmt = $pdo->query("SELECT VERSION() as version");
    $version = $stmt->fetch();
    echo "Version MySQL : " . $version['version'] . "\n";
    
} catch (PDOException $e) {
    echo "Erreur de connexion : " . $e->getMessage() . "\n";
    echo "Code d'erreur : " . $e->getCode() . "\n";
    
    // Afficher plus de détails pour le débogage
    echo "\nDétails de la configuration :\n";
    echo "Host: " . DB_HOST . "\n";
    echo "Database: " . DB_NAME . "\n";
    echo "User: " . DB_USER . "\n";
    echo "DSN: mysql:host=" . DB_HOST . ";port=3306;dbname=" . DB_NAME . ";charset=utf8mb4\n";
} 