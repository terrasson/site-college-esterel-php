<?php
function loadEnv() {
    $envFile = __DIR__ . '/../../.env';
    if (file_exists($envFile)) {
        // Débogage
        error_log("Fichier .env trouvé");
        error_log("Contenu des variables d'environnement :");
        error_log("DB_HOST: " . getenv('DB_HOST'));
        error_log("DB_NAME: " . getenv('DB_NAME'));
        error_log("DB_USER: " . getenv('DB_USER'));
        // Ne pas logger DB_PASS pour des raisons de sécurité
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                putenv("$key=$value");
            }
        }
    } else {
        error_log("Fichier .env non trouvé");
    }
}

function getPDOConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        loadEnv();
        
        $host = getenv('DB_HOST');
        $dbname = getenv('DB_NAME');
        $user = getenv('DB_USER');
        $pass = getenv('DB_PASS');
        
        try {
            $pdo = new PDO(
                "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
                $user,
                $pass,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                ]
            );
        } catch (PDOException $e) {
            error_log("Erreur de connexion à la base de données : " . $e->getMessage());
            throw new Exception("Erreur de connexion à la base de données");
        }
    }
    
    return $pdo;
}

function getDBConnection() {
    return getPDOConnection();
} 