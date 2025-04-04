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

// Récupération des données
$data = json_decode(file_get_contents('php://input'), true);
$filename = $data['filename'] ?? '';
$type = $data['type'] ?? '';
$degrees = $data['degrees'] ?? 0;

if (!$filename || !$type || !$degrees) {
    http_response_code(400);
    echo json_encode(['error' => 'Paramètres manquants']);
    exit;
}

// Vérification du type
if (!in_array($type, ['cuisine', 'direction'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Type invalide']);
    exit;
}

// Chemin de l'image
$imagePath = __DIR__ . '/../../assets/img/' . $type . '/' . $filename;

if (!file_exists($imagePath)) {
    http_response_code(404);
    echo json_encode(['error' => 'Image non trouvée']);
    exit;
}

try {
    // Chargement de l'image
    $imageInfo = getimagesize($imagePath);
    $mimeType = $imageInfo['mime'];
    
    switch ($mimeType) {
        case 'image/jpeg':
            $source = imagecreatefromjpeg($imagePath);
            break;
        case 'image/png':
            $source = imagecreatefrompng($imagePath);
            break;
        case 'image/gif':
            $source = imagecreatefromgif($imagePath);
            break;
        default:
            throw new Exception('Format d\'image non supporté');
    }
    
    // Rotation de l'image
    $rotated = imagerotate($source, $degrees, 0);
    
    // Sauvegarde de l'image
    switch ($mimeType) {
        case 'image/jpeg':
            imagejpeg($rotated, $imagePath, 90);
            break;
        case 'image/png':
            imagepng($rotated, $imagePath, 9);
            break;
        case 'image/gif':
            imagegif($rotated, $imagePath);
            break;
    }
    
    // Libération de la mémoire
    imagedestroy($source);
    imagedestroy($rotated);
    
    // Ajout d'un timestamp pour forcer le rechargement de l'image
    $timestamp = time();
    echo json_encode([
        'success' => true,
        'url' => '/assets/img/' . $type . '/' . $filename . '?t=' . $timestamp
    ]);
    
} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erreur lors de la rotation de l\'image']);
    exit;
} 