<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

header('Content-Type: application/json');

if (!isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

try {
    $pdo = getPDOConnection();
    $uploadDir = __DIR__ . '/../uploads/direction/';
    
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $response = ['success' => true, 'files' => []];

    if (isset($_FILES['files'])) {
        foreach ($_FILES['files']['tmp_name'] as $key => $tmp_name) {
            $fileName = uniqid() . '_' . $_FILES['files']['name'][$key];
            $filePath = $uploadDir . $fileName;
            
            if (move_uploaded_file($tmp_name, $filePath)) {
                $url = '/uploads/direction/' . $fileName;
                
                // Insérer dans la base de données
                $stmt = $pdo->prepare("INSERT INTO photos_direction (url) VALUES (?)");
                $stmt->execute([$url]);
                
                $response['files'][] = [
                    'name' => $fileName,
                    'url' => $url
                ];
            }
        }
    }

    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 