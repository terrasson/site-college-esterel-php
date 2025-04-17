<?php
require_once 'config.php';
require_once 'database.php';

try {
    $pdo = getPDOConnection();
    
    // Admin
    $admin_password = getenv('ADMIN_PASSWORD');
    $admin_hash = password_hash($admin_password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE username = 'admin'");
    $stmt->execute([$admin_hash]);
    
    // Test de vérification
    if (password_verify($admin_password, $admin_hash)) {
        echo "Admin OK - Hash mis à jour\n";
    }
    
    // Direction
    $direction_password = getenv('DIRECTION_PASSWORD');
    $direction_hash = password_hash($direction_password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE username = 'direction'");
    $stmt->execute([$direction_hash]);
    
    // Cuisine
    $cuisine_password = getenv('CUISINE_PASSWORD');
    $cuisine_hash = password_hash($cuisine_password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE username = 'cuisine'");
    $stmt->execute([$cuisine_hash]);
    
    echo "Mots de passe mis à jour avec succès\n";
    
} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
} 