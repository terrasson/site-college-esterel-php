<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Charger les variables d'environnement
require_once __DIR__ . '/database.php';

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
    } else {
        error_log("Erreur [$errno] $errstr dans $errfile à la ligne $errline");
    }
}

set_error_handler('handleError'); 