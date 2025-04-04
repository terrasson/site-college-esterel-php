<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

header('Content-Type: application/json');

try {
    $pdo = getPDOConnection();
    
    // Récupérer le message du bandeau défilant
    $stmt = $pdo->query('SELECT message FROM bandeau_defilant ORDER BY updated_at DESC LIMIT 1');
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'message' => $result ? $result['message'] : ''
    ]);
} catch (PDOException $e) {
    header('HTTP/1.1 500 Internal Server Error');
    error_log($e->getMessage());
    exit(json_encode(['error' => 'Erreur lors de la récupération du bandeau défilant']));
} 