<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

header('Content-Type: application/json');

// Vérification de l'authentification
if (!isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['error' => 'Non authentifié']);
    exit;
}

try {
    $photoDir = __DIR__ . '/../../assets/img/direction/';
    $photos = [];
    
    if (is_dir($photoDir)) {
        $files = scandir($photoDir);
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..' && is_file($photoDir . $file)) {
                $photos[] = [
                    'filename' => $file,
                    'url' => '/assets/img/direction/' . $file
                ];
            }
        }
    }
    
    echo json_encode($photos);
    
} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erreur lors de la récupération des photos']);
    exit;
} 