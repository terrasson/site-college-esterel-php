<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

header('Content-Type: application/json');

try {
    $pdo = getPDOConnection();
    
    // Récupérer tous les diaporamas actifs, triés par position
    $stmt = $pdo->query('SELECT * FROM diaporama WHERE active = 1 ORDER BY position ASC');
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'items' => $items
    ]);
} catch (PDOException $e) {
    header('HTTP/1.1 500 Internal Server Error');
    error_log($e->getMessage());
    exit(json_encode([
        'success' => false,
        'error' => 'Erreur lors de la récupération des diaporamas'
    ]));
} 