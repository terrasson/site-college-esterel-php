<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

header('Content-Type: application/json');

// Vérification de l'authentification
if (!isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Non authentifié']);
    exit;
}

// Récupération des données
$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['id']) || !isset($data['type'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Données manquantes']);
    exit;
}

$id = $data['id'];
$type = $data['type'];

// Vérification du type
if (!in_array($type, ['direction', 'cuisine'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Type de document invalide']);
    exit;
}

try {
    $pdo = getPDOConnection();
    
    // Récupération de l'URL du fichier
    $table = 'documents_' . $type;
    $stmt = $pdo->prepare("SELECT url FROM $table WHERE id = ?");
    $stmt->execute([$id]);
    $document = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$document) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Document non trouvé']);
        exit;
    }
    
    // Suppression du fichier physique
    $filePath = __DIR__ . '/../../' . ltrim($document['url'], '/');
    if (file_exists($filePath)) {
        unlink($filePath);
    }
    
    // Suppression de l'entrée en base de données
    $stmt = $pdo->prepare("DELETE FROM $table WHERE id = ?");
    $stmt->execute([$id]);
    
    echo json_encode(['success' => true]);
    
} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erreur lors de la suppression']);
    exit;
}
?> 