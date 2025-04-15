<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/auth.php';

header('Content-Type: application/json');

try {
    // Vérifier l'authentification
    if (!isAuthenticated()) {
        http_response_code(401);
        throw new Exception('Non autorisé');
    }

    // Récupérer les données
    $data = json_decode(file_get_contents('php://input'), true);
    $photoId = $data['id'] ?? null;

    if (!$photoId) {
        throw new Exception('ID de photo manquant');
    }

    $pdo = getPDOConnection();

    // Vérifier si la photo existe
    $stmt = $pdo->prepare("SELECT id FROM photos_cuisine WHERE id = ?");
    $stmt->execute([$photoId]);
    if (!$stmt->fetch()) {
        throw new Exception('Photo non trouvée');
    }

    // Ajouter la photo au diaporama
    $stmt = $pdo->prepare("INSERT INTO diaporama (photo_id, type) VALUES (?, 'cuisine')");
    $stmt->execute([$photoId]);

    echo json_encode(['success' => true, 'message' => 'Photo ajoutée avec succès']);

} catch (Exception $e) {
    error_log("Erreur add-to-slider: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} 