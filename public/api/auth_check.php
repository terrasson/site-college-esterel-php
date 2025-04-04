<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

// Vérification de l'authentification
if (!isAuthenticated()) {
    // Si c'est une requête AJAX, renvoyer une erreur 401
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('HTTP/1.0 401 Unauthorized');
        echo json_encode(['success' => false, 'error' => 'Non authentifié']);
        exit;
    }
    
    // Sinon, rediriger vers la page de connexion
    header('Location: /login.php');
    exit;
}
?> 