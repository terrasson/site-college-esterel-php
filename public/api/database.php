<?php
require_once __DIR__ . '/config.php';

function getPDOConnection() {
    try {
        error_log("Tentative de connexion à la base de données...");
        error_log("Host: " . getenv('DB_HOST'));
        error_log("Database: " . getenv('DB_NAME'));
        
        $dsn = "mysql:host=" . getenv('DB_HOST') . ";dbname=" . getenv('DB_NAME') . ";charset=utf8mb4";
        $pdo = new PDO($dsn, getenv('DB_USER'), getenv('DB_PASS'));
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        error_log("Connexion à la base de données réussie");
        return $pdo;
    } catch (PDOException $e) {
        error_log("Erreur de connexion à la base de données : " . $e->getMessage());
        throw $e;
    }
}

function getDBConnection() {
    return getPDOConnection();
} 