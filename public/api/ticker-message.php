<?php
require_once 'config.php';
require_once 'database.php';

header('Content-Type: application/json');

try {
    $pdo = getPDOConnection();
    
    // Récupérer le message du ticker depuis la base de données
    $stmt = $pdo->query("SELECT message, speed FROM ticker_message ORDER BY id DESC LIMIT 1");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        echo json_encode([
            'message' => $result['message'],
            'speed' => $result['speed'] ?? 30
        ]);
    } else {
        // Message par défaut si aucun n'est trouvé
        echo json_encode([
            'message' => 'Bienvenue au Collège de l\'Estérel',
            'speed' => 30
        ]);
    }
} catch (Exception $e) {
    error_log("Erreur ticker : " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur']);
} 