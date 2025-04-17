<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/database.php';

header('Content-Type: application/json');

if (!isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Non authentifié']);
    exit;
}

try {
    // Vérification du fichier
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Erreur lors de l\'upload du fichier');
    }

    $file = $_FILES['file'];
    $fileName = $file['name'];
    $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    // Vérification du type de fichier
    $allowedTypes = ['pdf', 'doc', 'docx'];
    if (!in_array($fileType, $allowedTypes)) {
        throw new Exception('Type de fichier non autorisé');
    }

    // Chemin absolu pour le stockage des fichiers
    $uploadDir = __DIR__ . '/../../public/assets/data/direction-documents/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Génération d'un nom unique
    $uniqueName = uniqid() . '_' . basename($fileName);
    $uploadPath = $uploadDir . $uniqueName;

    // Upload du fichier
    if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
        throw new Exception('Erreur lors de l\'enregistrement du fichier');
    }

    // URL relative pour la base de données
    $url = '/assets/data/direction-documents/' . $uniqueName;

    // Enregistrement dans la base de données
    $pdo = getPDOConnection();
    $stmt = $pdo->prepare("INSERT INTO direction_documents (title, type, url, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$fileName, $fileType, $url]);

    echo json_encode([
        'success' => true,
        'document' => [
            'id' => $pdo->lastInsertId(),
            'title' => $fileName,
            'type' => $fileType,
            'url' => $url
        ]
    ]);

} catch (Exception $e) {
    error_log("Erreur : " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?> 