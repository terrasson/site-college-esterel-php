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

header('Content-Type: application/json');

// Vérifier si un fichier a été uploadé
if (!isset($_FILES['file'])) {
    header('HTTP/1.1 400 Bad Request');
    exit(json_encode(['error' => 'Aucun fichier envoyé']));
}

$file = $_FILES['file'];
$title = $_POST['title'] ?? '';

// Vérifier le type de fichier
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
if (!in_array($file['type'], $allowedTypes)) {
    header('HTTP/1.1 400 Bad Request');
    exit(json_encode(['error' => 'Type de fichier non autorisé']));
}

// Générer un nom de fichier unique
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$newFilename = uniqid() . '.' . $extension;
$uploadDir = __DIR__ . '/../../uploads/diaporama/';
$targetPath = $uploadDir . $newFilename;

// Créer le dossier s'il n'existe pas
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

try {
    // Déplacer le fichier uploadé
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        throw new Exception('Erreur lors du déplacement du fichier');
    }

    $pdo = getPDOConnection();
    
    // Obtenir la position maximale actuelle
    $stmt = $pdo->query('SELECT MAX(position) as max_pos FROM diaporama');
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $position = ($result['max_pos'] ?? 0) + 1;
    
    // Insérer le nouveau média dans la base de données
    $stmt = $pdo->prepare('INSERT INTO diaporama (title, url, position, active, created_at) VALUES (:title, :url, :position, 0, NOW())');
    $stmt->execute([
        'title' => $title ?: pathinfo($file['name'], PATHINFO_FILENAME),
        'url' => '/uploads/diaporama/' . $newFilename,
        'position' => $position
    ]);
    
    $id = $pdo->lastInsertId();
    
    echo json_encode([
        'success' => true,
        'message' => 'Média uploadé avec succès',
        'item' => [
            'id' => $id,
            'title' => $title,
            'url' => '/uploads/diaporama/' . $newFilename,
            'position' => $position
        ]
    ]);
} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    error_log($e->getMessage());
    // Supprimer le fichier si l'insertion en base a échoué
    if (file_exists($targetPath)) {
        unlink($targetPath);
    }
    exit(json_encode(['error' => 'Erreur lors de l\'upload du média']));
} 