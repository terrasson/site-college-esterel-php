<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/Security.php';

// Vérifier l'authentification
if (!isAuthenticated()) {
    header('HTTP/1.1 401 Unauthorized');
    exit(json_encode(['error' => 'Non autorisé']));
}

// Vérifier les permissions
if (!hasPermission('admin')) {
    header('HTTP/1.1 403 Forbidden');
    exit(json_encode(['error' => 'Permission refusée']));
}

// Vérifier la méthode HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    exit(json_encode(['error' => 'Méthode non autorisée']));
}

// Récupérer les données
$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['message'])) {
    header('HTTP/1.1 400 Bad Request');
    exit(json_encode(['error' => 'Message manquant']));
}

try {
    $pdo = getPDOConnection();
    
    // Mettre à jour le message dans la base de données
    $stmt = $pdo->prepare('UPDATE bandeau_defilant SET message = :message, updated_at = NOW()');
    $stmt->execute(['message' => $data['message']]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Bandeau défilant mis à jour avec succès'
    ]);
} catch (PDOException $e) {
    header('HTTP/1.1 500 Internal Server Error');
    error_log($e->getMessage());
    exit(json_encode(['error' => 'Erreur lors de la mise à jour du bandeau défilant']));
} 