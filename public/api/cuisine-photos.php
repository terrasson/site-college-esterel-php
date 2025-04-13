<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/database.php';

// Activer l'affichage des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// Log de début
error_log("Début de cuisine-photos.php");

// Vérification de l'authentification
if (!isAuthenticated()) {
    error_log("Non authentifié");
    http_response_code(401);
    echo json_encode(['error' => 'Non authentifié']);
    exit;
}

try {
    error_log("Tentative de connexion à la base de données");
    $pdo = getPDOConnection();
    error_log("Connexion réussie");
    
    // Vérification de l'existence de la table
    $tables = $pdo->query("SHOW TABLES LIKE 'photos_cuisine'")->fetchAll();
    error_log("Tables trouvées : " . print_r($tables, true));
    
    // Récupération des photos de cuisine
    $stmt = $pdo->query("SELECT * FROM photos_cuisine ORDER BY created_at DESC");
    error_log("Requête exécutée");
    
    $photos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    error_log("Photos récupérées : " . print_r($photos, true));
    
    echo json_encode($photos);
    
} catch (Exception $e) {
    error_log("Erreur : " . $e->getMessage());
    error_log("Trace : " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode(['error' => 'Erreur lors de la récupération des photos : ' . $e->getMessage()]);
    exit;
} 