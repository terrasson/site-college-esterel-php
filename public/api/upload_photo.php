<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/Security.php';

// Désactiver l'affichage des erreurs pour éviter de casser le JSON
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// Fonction pour logger les erreurs
function logError($message) {
    error_log("[Upload Error] " . $message);
}

// Vérifier l'authentification
if (!isAuthenticated()) {
    header('HTTP/1.1 401 Unauthorized');
    exit(json_encode(['error' => 'Non autorisé']));
}

// Vérifier le type de photo
$type = $_GET['type'] ?? '';
if (!in_array($type, ['cuisine', 'direction'])) {
    header('HTTP/1.1 400 Bad Request');
    exit(json_encode(['error' => 'Type de photo invalide']));
}

// Vérifier les permissions
if (!hasPermission('admin') && !hasPermission($type)) {
    header('HTTP/1.1 403 Forbidden');
    exit(json_encode(['error' => 'Permission refusée']));
}

header('Content-Type: application/json');

// Vérifier si un fichier a été uploadé
if (!isset($_FILES['file'])) {
    logError("Aucun fichier envoyé");
    header('HTTP/1.1 400 Bad Request');
    exit(json_encode(['error' => 'Aucun fichier envoyé']));
}

$file = $_FILES['file'];

// Log des informations du fichier
logError("Upload tentative - Type: " . $file['type'] . ", Taille: " . $file['size'] . ", Nom: " . $file['name']);

// Vérifier s'il y a eu une erreur lors de l'upload
if ($file['error'] !== UPLOAD_ERR_OK) {
    $errorMessage = match($file['error']) {
        UPLOAD_ERR_INI_SIZE => 'Le fichier dépasse la taille maximale autorisée par PHP',
        UPLOAD_ERR_FORM_SIZE => 'Le fichier dépasse la taille maximale autorisée par le formulaire',
        UPLOAD_ERR_PARTIAL => 'Le fichier n\'a été que partiellement uploadé',
        UPLOAD_ERR_NO_FILE => 'Aucun fichier n\'a été uploadé',
        UPLOAD_ERR_NO_TMP_DIR => 'Dossier temporaire manquant',
        UPLOAD_ERR_CANT_WRITE => 'Échec de l\'écriture du fichier sur le disque',
        UPLOAD_ERR_EXTENSION => 'Une extension PHP a arrêté l\'upload du fichier',
        default => 'Erreur inconnue'
    };
    logError("Erreur d'upload: " . $errorMessage);
    header('HTTP/1.1 400 Bad Request');
    exit(json_encode(['error' => $errorMessage]));
}

// Vérifier le type de fichier
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($file['type'], $allowedTypes)) {
    logError("Type de fichier non autorisé: " . $file['type']);
    header('HTTP/1.1 400 Bad Request');
    exit(json_encode(['error' => 'Type de fichier non autorisé: ' . $file['type']]));
}

// Générer un nom de fichier unique
$newFilename = $type . '_' . time() . '.webp';
$uploadDir = __DIR__ . '/../../assets/img/' . $type . '/';
$targetPath = $uploadDir . $newFilename;

// Log du chemin de destination
logError("Chemin de destination: " . $targetPath);

// Créer le dossier s'il n'existe pas
if (!file_exists($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        logError("Impossible de créer le dossier: " . $uploadDir);
        header('HTTP/1.1 500 Internal Server Error');
        exit(json_encode(['error' => 'Impossible de créer le dossier de destination']));
    }
}

try {
    // D'abord, déplacer le fichier temporaire
    $tempPath = $uploadDir . 'temp_' . uniqid();
    if (!move_uploaded_file($file['tmp_name'], $tempPath)) {
        throw new Exception('Impossible de déplacer le fichier temporaire');
    }

    // Créer une image à partir du fichier uploadé
    $sourceImage = null;
    switch($file['type']) {
        case 'image/jpeg':
            $sourceImage = @imagecreatefromjpeg($tempPath);
            break;
        case 'image/png':
            $sourceImage = @imagecreatefrompng($tempPath);
            break;
        case 'image/gif':
            $sourceImage = @imagecreatefromgif($tempPath);
            break;
        case 'image/webp':
            $sourceImage = @imagecreatefromwebp($tempPath);
            break;
    }

    if (!$sourceImage) {
        throw new Exception('Impossible de créer l\'image source');
    }

    // Pour les PNG, préserver la transparence
    if ($file['type'] === 'image/png') {
        imagepalettetotruecolor($sourceImage);
        imagealphablending($sourceImage, true);
        imagesavealpha($sourceImage, true);
    }

    // Convertir et sauvegarder en WebP
    if (!imagewebp($sourceImage, $targetPath, 80)) {
        throw new Exception('Erreur lors de la conversion en WebP');
    }

    // Libérer la mémoire et supprimer le fichier temporaire
    imagedestroy($sourceImage);
    unlink($tempPath);
    
    echo json_encode([
        'success' => true,
        'message' => 'Photo uploadée avec succès',
        'photo' => [
            'filename' => $newFilename,
            'url' => '/assets/img/' . $type . '/' . $newFilename
        ]
    ]);
} catch (Exception $e) {
    logError("Exception: " . $e->getMessage());
    header('HTTP/1.1 500 Internal Server Error');
    // Nettoyer les fichiers temporaires
    if (file_exists($tempPath)) {
        unlink($tempPath);
    }
    if (file_exists($targetPath)) {
        unlink($targetPath);
    }
    exit(json_encode(['error' => 'Erreur lors de l\'upload de la photo: ' . $e->getMessage()]));
} 