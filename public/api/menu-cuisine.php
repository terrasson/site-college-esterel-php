<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/database.php';

header('Content-Type: application/json');

// Log pour debug
error_log("Début du traitement menu-cuisine.php");

// Vérification de l'authentification
if (!isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Non authentifié']);
    exit;
}

try {
    $pdo = getPDOConnection();
    error_log("Connexion PDO établie");
    
    // Suppression d'un menu
    if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            throw new Exception('ID manquant');
        }

        // Récupérer l'URL du fichier avant la suppression
        $stmt = $pdo->prepare("SELECT url FROM menus WHERE id = ?");
        $stmt->execute([$id]);
        $menu = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($menu) {
            // Supprimer le fichier physique
            $filePath = __DIR__ . '/../../public' . $menu['url'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            
            // Supprimer l'entrée de la base de données
            $stmt = $pdo->prepare("DELETE FROM menus WHERE id = ?");
            $stmt->execute([$id]);
        }
        
        echo json_encode(['success' => true]);
        exit;
    }
    
    // Récupération de tous les menus
    $stmt = $pdo->query("SELECT * FROM menus ORDER BY created_at DESC");
    $menus = $stmt->fetchAll(PDO::FETCH_ASSOC);
    error_log("Nombre de menus trouvés : " . count($menus));
    
    echo json_encode([
        'success' => true,
        'menus' => $menus
    ]);
    
} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?> 