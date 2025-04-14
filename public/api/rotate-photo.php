<?php
// Log dans un fichier spécifique
$logFile = __DIR__ . '/rotation.log';
error_log("=== DÉBUT ROTATION ===\n", 3, $logFile);

// Activer l'affichage complet des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/database.php';

// Log les includes
error_log("Includes OK", 3, $logFile);

try {
    // Log toutes les données reçues
    error_log("POST data: " . print_r($_POST, true) . "\n", 3, $logFile);
    error_log("GET data: " . print_r($_GET, true) . "\n", 3, $logFile);
    error_log("Raw input: " . file_get_contents('php://input') . "\n", 3, $logFile);

    // Vérifier si GD est installé
    if (!extension_loaded('gd')) {
        error_log("GD n'est pas installé\n", 3, $logFile);
        throw new Exception('Extension GD non installée');
    }

    // Récupérer les données
    $id = $_POST['id'] ?? null;
    $angle = (int)($_POST['angle'] ?? 90);

    // Inverser l'angle pour corriger le sens de rotation
    $angle = -$angle;

    error_log("ID: $id, Angle après inversion: $angle\n", 3, $logFile);

    if (!$id) {
        throw new Exception('ID manquant');
    }

    // Récupérer le chemin du fichier
    $pdo = getPDOConnection();
    $stmt = $pdo->prepare("SELECT url FROM photos_cuisine WHERE id = ?");
    $stmt->execute([$id]);
    $photo = $stmt->fetch();

    if (!$photo) {
        throw new Exception('Photo non trouvée');
    }

    // Chemin complet du fichier
    $filePath = __DIR__ . '/..' . $photo['url'];
    error_log("Fichier: $filePath\n", 3, $logFile);
    
    // Vérifier les permissions
    error_log("Permissions: " . substr(sprintf('%o', fileperms($filePath)), -4) . "\n", 3, $logFile);
    error_log("Propriétaire: " . fileowner($filePath) . "\n", 3, $logFile);
    
    // Charger l'image
    $source = imagecreatefromjpeg($filePath);
    if (!$source) {
        throw new Exception('Impossible de charger l\'image');
    }
    error_log("Image chargée\n", 3, $logFile);

    // Faire la rotation
    $rotated = imagerotate($source, $angle, 0);
    if (!$rotated) {
        throw new Exception('Erreur lors de la rotation');
    }
    error_log("Rotation effectuée\n", 3, $logFile);

    // Sauvegarder l'image
    if (!imagejpeg($rotated, $filePath, 90)) {
        throw new Exception('Impossible de sauvegarder l\'image');
    }
    error_log("Image sauvegardée\n", 3, $logFile);

    // Libérer la mémoire
    imagedestroy($source);
    imagedestroy($rotated);

    // Forcer le cache-control
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache');

    error_log("=== FIN ROTATION ===\n", 3, $logFile);
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    error_log("ERREUR: " . $e->getMessage() . "\n", 3, $logFile);
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

error_log("=== FIN ROTATION ===", 3, __DIR__ . '/rotation.log');

// Log simple pour voir ce qu'on reçoit
var_dump($_POST);
var_dump($_FILES);
die("Test de réception des données"); 