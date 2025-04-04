<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/database.php';

header('Content-Type: application/json');

// Vérification de l'authentification
if (!isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['error' => 'Non authentifié']);
    exit;
}

try {
    $pdo = getPDOConnection();
    
    // Récupération des photos de cuisine
    $stmt = $pdo->query("SELECT * FROM photos_cuisine ORDER BY created_at DESC");
    $photos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatage des données
    $formattedPhotos = array_map(function($photo) {
        return [
            'id' => $photo['id'],
            'title' => pathinfo($photo['url'], PATHINFO_FILENAME),
            'url' => $photo['url']
        ];
    }, $photos);
    
    echo json_encode($formattedPhotos);
    
} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erreur lors de la récupération des photos']);
    exit;
} 