<?php
session_start();
require_once 'config.php';
require_once 'database.php';
require_once 'functions.php';

// Vérifier l'authentification
if (!isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['error' => 'Non autorisé']);
    exit;
}

header('Content-Type: application/json');

try {
    $pdo = getPDOConnection();
    // Forcer UTF8mb4
    $pdo->exec("SET NAMES utf8mb4");
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['message'])) {
        throw new Exception('Message manquant');
    }

    // Debug log
    error_log('Données reçues : ' . print_r($data, true));
    
    // Sauvegarder le nouveau message
    $stmt = $pdo->prepare("INSERT INTO ticker_message (message, speed) VALUES (?, ?)");
    $result = $stmt->execute([
        $data['message'],
        $data['speed'] ?? 30
    ]);

    // Debug log
    error_log('Résultat de l\'insertion : ' . ($result ? 'succès' : 'échec'));
    
    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Erreur lors de la sauvegarde');
    }
    
} catch (Exception $e) {
    error_log("Erreur save-ticker : " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur : ' . $e->getMessage()]);
} 