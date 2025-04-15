<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function loadEnv() {
    $envFile = __DIR__ . '/../../.env';
    
    if (!file_exists($envFile)) {
        error_log("Fichier .env non trouvé : $envFile");
        return false;
    }

    error_log("Fichier .env trouvé : $envFile");
    
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        list($name, $value) = explode('=', $line, 2);
        
        $name = trim($name);
        $value = trim($value);
        
        // Enlever les commentaires à la fin de la ligne
        if (strpos($value, '#') !== false) {
            $value = trim(explode('#', $value)[0]);
        }
        
        // Enlever les guillemets
        $value = trim($value, '"');
        
        putenv("$name=$value");
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
        
        error_log("Variable définie : $name = $value");
    }
    
    return true;
}

// Charger les variables d'environnement
loadEnv();

// Vérifier que les variables importantes sont définies
$requiredVars = ['DB_HOST', 'DB_NAME', 'DB_USER', 'ADMIN_PASSWORD'];
foreach ($requiredVars as $var) {
    if (!getenv($var)) {
        error_log("Variable d'environnement manquante : $var");
    }
}

error_log("ADMIN_PASSWORD from getenv: " . getenv('ADMIN_PASSWORD'));

// Configuration des chemins
define('BASE_PATH', dirname(__DIR__));
define('UPLOAD_PATH', BASE_PATH . getenv('UPLOAD_PATH'));

// Configuration de l'application
define('SITE_NAME', getenv('APP_NAME'));
define('DEBUG_MODE', getenv('APP_DEBUG') === 'true');

// Fonction de gestion des erreurs
function handleError($errno, $errstr, $errfile, $errline) {
    if (DEBUG_MODE) {
        echo "Erreur [$errno] $errstr<br />\n";
        echo "Ligne $errline dans $errfile<br />\n";
    }
}

set_error_handler('handleError');

// Supprimer ou commenter les error_log
// error_log("Configuration chargée");
// error_log("Variables d'environnement chargées"); 