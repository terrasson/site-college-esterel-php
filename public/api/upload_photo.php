<?php
session_start();
require_once 'config.php';
require_once 'functions.php';
require_once 'Security.php';
require_once 'database.php';

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

// Vérifier l'authentification
if (!isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

// Vérifier le type de photo
$type = $_GET['type'] ?? '';
if ($type !== 'cuisine' && $type !== 'direction') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Type invalide']);
    exit;
}

// Vérifier les permissions
if (!hasPermission('admin') && !hasPermission($type)) {
    header('HTTP/1.1 403 Forbidden');
    exit(json_encode(['error' => 'Permission refusée']));
}

header('Content-Type: application/json');

try {
    $pdo = getPDOConnection();
    $uploadDir = __DIR__ . '/../uploads/' . $type . '/';
    
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    if (isset($_FILES['file'])) {
        $file = $_FILES['file'];
        $fileName = uniqid() . '_' . basename($file['name']);
        $targetPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $url = '/uploads/' . $type . '/' . $fileName;
            
            // Insérer dans la bonne table selon le type
            $table = $type === 'cuisine' ? 'photos_cuisine' : 'photos_direction';
            $stmt = $pdo->prepare("INSERT INTO $table (url) VALUES (?)");
            $stmt->execute([$url]);
            
            echo json_encode([
                'success' => true,
                'url' => $url,
                'id' => $pdo->lastInsertId()
            ]);
        } else {
            throw new Exception('Erreur lors du déplacement du fichier');
        }
    } else {
        throw new Exception('Aucun fichier reçu');
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 