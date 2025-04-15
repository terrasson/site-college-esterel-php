<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

header('Content-Type: application/json');

if (!isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $photoId = $data['id'] ?? null;

    if (!$photoId) {
        throw new Exception('ID de photo manquant');
    }

    $pdo = getPDOConnection();
    
    // Récupérer l'URL de la photo
    $stmt = $pdo->prepare("SELECT url FROM photos_direction WHERE id = ?");
    $stmt->execute([$photoId]);
    $photo = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($photo) {
        // Supprimer le fichier
        $filePath = __DIR__ . '/..' . $photo['url'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // Supprimer l'entrée de la base de données
        $stmt = $pdo->prepare("DELETE FROM photos_direction WHERE id = ?");
        $stmt->execute([$photoId]);
    }

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 