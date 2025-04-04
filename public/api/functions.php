<?php
require_once __DIR__ . '/config.php';

// Fonction de vérification de l'authentification
function isAuthenticated() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Fonction de vérification des permissions
function hasPermission($requiredRole) {
    if (!isAuthenticated()) return false;
    return $_SESSION['user_role'] === $requiredRole || $_SESSION['user_role'] === 'admin';
}

// Fonction de nettoyage des entrées
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Fonction de génération de token CSRF
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Fonction de validation de fichier
function validateFile($file, $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf']) {
    if (!isset($file['error']) || is_array($file['error'])) {
        return false;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    if (!in_array($file['type'], $allowedTypes)) {
        return false;
    }

    return true;
}

// Fonction pour créer un message flash
function setFlashMessage($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

// Fonction pour récupérer et effacer un message flash
function getFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
} 