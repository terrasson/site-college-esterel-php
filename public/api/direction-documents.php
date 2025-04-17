<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/database.php';

header('Content-Type: application/json');

if (!isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Non authentifié']);
    exit;
}

try {
    $pdo = getPDOConnection();
    
    // Suppression d'un document
    if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            throw new Exception('ID manquant');
        }

        // Récupérer l'URL du fichier avant la suppression
        $stmt = $pdo->prepare("SELECT url FROM direction_documents WHERE id = ?");
        $stmt->execute([$id]);
        $document = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($document) {
            // Supprimer le fichier physique
            $filePath = __DIR__ . '/../../public' . $document['url'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            
            // Supprimer l'entrée de la base de données
            $stmt = $pdo->prepare("DELETE FROM direction_documents WHERE id = ?");
            $stmt->execute([$id]);
        }
        
        echo json_encode(['success' => true]);
        exit;
    }
    
    // Liste des documents
    $stmt = $pdo->query("SELECT * FROM direction_documents ORDER BY created_at DESC");
    $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'documents' => $documents
    ]);
    
} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?> 