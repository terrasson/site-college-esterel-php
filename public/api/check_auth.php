<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

// Vérifier si l'utilisateur est authentifié
if (!isAuthenticated()) {
    // Rediriger vers la page de login
    header('Location: /index.php');
    exit;
}

// Si l'utilisateur est authentifié, on le laisse accéder à la page demandée
$requestedPage = $_SERVER['REQUEST_URI'];

// Vérifier les permissions spécifiques selon la page
if (strpos($requestedPage, 'admin-') === 0) {
    $pageType = explode('-', substr($requestedPage, 6))[0];
    
    switch ($pageType) {
        case 'photos':
            if (!hasPermission('admin') && !hasPermission($pageType)) {
                header('Location: /navigation.php');
                exit;
            }
            break;
        default:
            if (!hasPermission('admin')) {
                header('Location: /navigation.php');
                exit;
            }
    }
}

// Si tout est OK, on continue vers la page demandée
return; 