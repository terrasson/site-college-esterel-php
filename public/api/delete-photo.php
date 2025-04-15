<?php
// Activer l'affichage complet des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Log toutes les données reçues
error_log("POST data: " . print_r($_POST, true));
error_log("Raw input: " . file_get_contents('php://input'));

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/database.php';

header('Content-Type: application/json');

if (!isAuthenticated()) {
    http_response_code(401);
    exit(json_encode(['error' => 'Non autorisé']));
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? null;
    $type = $data['type'] ?? 'cuisine';

    if (!$id) {
        throw new Exception('ID manquant');
    }

    $pdo = getPDOConnection();
    
    // Sélectionner la bonne table selon le type
    $table = $type === 'direction' ? 'photos_direction' : 'photos_cuisine';
    
    // Récupérer l'URL de l'image avant la suppression
    $stmt = $pdo->prepare("SELECT url FROM $table WHERE id = ?");
    $stmt->execute([$id]);
    $photo = $stmt->fetch();

    if ($photo) {
        // Supprimer le fichier physique
        $filePath = __DIR__ . '/..' . $photo['url'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // Supprimer l'entrée de la base de données
        $stmt = $pdo->prepare("DELETE FROM $table WHERE id = ?");
        $stmt->execute([$id]);
    }

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 