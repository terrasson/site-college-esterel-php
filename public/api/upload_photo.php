<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/Security.php';
require_once __DIR__ . '/database.php';

// Charger explicitement les variables d'environnement
loadEnv();

// Activer l'affichage des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Fonction pour logger les erreurs
function logError($message) {
    error_log("[Upload Debug] " . $message);
}

// Log au début du script
logError("Début du script d'upload");

// Log des informations du fichier
if (isset($_FILES['file'])) {
    logError("Fichier reçu : " . print_r($_FILES['file'], true));
} else {
    logError("Aucun fichier reçu");
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

// Générer un nom de fichier unique
$newFilename = $type . '_' . time() . '.jpg';
$uploadDir = __DIR__ . '/../assets/img/' . $type . '/';
$targetPath = $uploadDir . $newFilename;

// Log des informations du fichier
error_log("[Upload Debug] Début de l'upload");
error_log("[Upload Debug] Type: " . $file['type']);
error_log("[Upload Debug] Taille: " . $file['size']);
error_log("[Upload Debug] Nom: " . $file['name']);
error_log("[Upload Debug] Chemin temporaire: " . $file['tmp_name']);
error_log("[Upload Debug] Chemin de destination: " . $targetPath);

// Vérifier s'il y a eu une erreur lors de l'upload
if ($file['error'] !== UPLOAD_ERR_OK) {
    $errorMessage = '';
    switch($file['error']) {
        case UPLOAD_ERR_INI_SIZE:
            $errorMessage = 'Le fichier dépasse la taille maximale autorisée par PHP';
            break;
        case UPLOAD_ERR_FORM_SIZE:
            $errorMessage = 'Le fichier dépasse la taille maximale autorisée par le formulaire';
            break;
        case UPLOAD_ERR_PARTIAL:
            $errorMessage = 'Le fichier n\'a été que partiellement uploadé';
            break;
        case UPLOAD_ERR_NO_FILE:
            $errorMessage = 'Aucun fichier n\'a été uploadé';
            break;
        case UPLOAD_ERR_NO_TMP_DIR:
            $errorMessage = 'Dossier temporaire manquant';
            break;
        case UPLOAD_ERR_CANT_WRITE:
            $errorMessage = 'Échec de l\'écriture du fichier sur le disque';
            break;
        case UPLOAD_ERR_EXTENSION:
            $errorMessage = 'Une extension PHP a arrêté l\'upload du fichier';
            break;
        default:
            $errorMessage = 'Erreur inconnue';
    }
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
    error_log("[Upload Debug] Déplacement vers: " . $tempPath);
    
    if (!move_uploaded_file($file['tmp_name'], $tempPath)) {
        error_log("[Upload Error] Échec du déplacement du fichier");
        throw new Exception('Impossible de déplacer le fichier temporaire');
    }
    error_log("[Upload Debug] Fichier déplacé avec succès");

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

    // Convertir et sauvegarder en JPEG
    if (!imagejpeg($sourceImage, $targetPath, 80)) {
        throw new Exception('Erreur lors de la conversion en JPEG');
    }

    // Libérer la mémoire et supprimer le fichier temporaire
    imagedestroy($sourceImage);
    unlink($tempPath);
    
    // Une fois que le fichier est sauvegardé, enregistrer dans la base de données
    $pdo = getPDOConnection();
    $stmt = $pdo->prepare("INSERT INTO photos_cuisine (url) VALUES (?)");
    $photoUrl = '/assets/img/' . $type . '/' . $newFilename;
    $stmt->execute([$photoUrl]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Photo uploadée avec succès',
        'photo' => [
            'id' => $pdo->lastInsertId(),
            'url' => $photoUrl
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