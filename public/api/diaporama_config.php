<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

header('Content-Type: application/json');

// Chemin vers le fichier de configuration
$configFile = __DIR__ . '/data/diaporama_config.json';

// Vérifier si le dossier data existe, sinon le créer
if (!file_exists(__DIR__ . '/data')) {
    mkdir(__DIR__ . '/data', 0777, true);
}

// Méthode GET - Lire la configuration
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (file_exists($configFile)) {
        echo file_get_contents($configFile);
    } else {
        // Retourner une configuration vide par défaut
        echo json_encode([
            'cuisine' => ['medias' => [], 'schedules' => []],
            'direction' => ['medias' => [], 'schedules' => []]
        ]);
    }
}

// Méthode POST - Sauvegarder la configuration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON data');
        }
        
        // Sauvegarder dans le fichier
        if (file_put_contents($configFile, json_encode($data, JSON_PRETTY_PRINT)) === false) {
            throw new Exception('Failed to save configuration');
        }
        
        http_response_code(200);
        echo json_encode(['success' => true]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
} 