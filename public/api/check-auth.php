<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/functions.php';

header('Content-Type: application/json');

if (isAuthenticated()) {
    http_response_code(200);
    echo json_encode([
        'status' => 'authenticated',
        'user' => [
            'username' => $_SESSION['username'],
            'role' => $_SESSION['user_role']
        ]
    ]);
} else {
    http_response_code(401);
    echo json_encode(['status' => 'not_authenticated']);
} 