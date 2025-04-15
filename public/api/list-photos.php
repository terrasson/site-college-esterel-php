<?php
require_once 'config.php';
require_once 'functions.php';

header('Content-Type: application/json');

try {
    $section = $_GET['section'] ?? 'cuisine';
    $table = $section === 'direction' ? 'photos_direction' : 'photos_cuisine';
    
    $pdo = getPDOConnection();
    $stmt = $pdo->query("SELECT id, url FROM $table ORDER BY id DESC");
    $photos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($photos);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} 