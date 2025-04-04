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

// Vérification du type de document
$type = $_GET['type'] ?? '';
if (!in_array($type, ['direction', 'cuisine'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Type de document invalide']);
    exit;
}

// Vérification du fichier uploadé
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Erreur lors de l\'upload du fichier']);
    exit;
}

$file = $_FILES['file'];
$fileName = $file['name'];
$fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

// Vérification du type de fichier
$allowedTypes = ['pdf', 'doc', 'docx', 'ppt', 'pptx'];
if (!in_array($fileType, $allowedTypes)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Type de fichier non autorisé']);
    exit;
}

// Création du dossier d'upload si nécessaire
$uploadDir = __DIR__ . '/../../assets/data/' . ($type === 'cuisine' ? 'menu-cuisine' : 'document-direction');
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Génération d'un nom de fichier unique
$uniqueName = uniqid() . '_' . $fileName;
$uploadPath = $uploadDir . '/' . $uniqueName;

// Déplacement du fichier
if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erreur lors de l\'enregistrement du fichier']);
    exit;
}

try {
    $pdo = getPDOConnection();
    
    // Insertion dans la base de données
    $table = 'documents_' . $type;
    $stmt = $pdo->prepare("INSERT INTO $table (title, type, url, created_at) VALUES (?, ?, ?, NOW())");
    $url = '/assets/data/' . ($type === 'cuisine' ? 'menu-cuisine' : 'document-direction') . '/' . $uniqueName;
    $stmt->execute([$fileName, $fileType, $url]);
    
    $documentId = $pdo->lastInsertId();
    
    echo json_encode([
        'success' => true,
        'document' => [
            'id' => $documentId,
            'title' => $fileName,
            'type' => $fileType,
            'url' => $url
        ]
    ]);
    
} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erreur lors de l\'enregistrement en base de données']);
    exit;
}
?> 