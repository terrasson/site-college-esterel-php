<?php
require_once __DIR__ . '/api/config.php';
require_once __DIR__ . '/api/functions.php';

// Vérification de l'authentification
if (!isAuthenticated()) {
    header('Location: /login.php');
    exit;
}

// Récupérer les documents
try {
    $pdo = getPDOConnection();
    $stmt = $pdo->query('SELECT * FROM documents_direction ORDER BY created_at DESC');
    $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log($e->getMessage());
    $documents = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documents Direction - Collège de l'Estérel</title>
    <link rel="stylesheet" href="/styles/common.css">
    <style>
        .documents-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .documents-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .document-card {
            background: #fff;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }

        .document-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .document-icon {
            width: 48px;
            height: 48px;
            margin-bottom: 1rem;
        }

        .document-name {
            font-weight: 600;
            margin-bottom: 0.5rem;
            word-break: break-word;
        }

        .document-type {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .document-link {
            display: inline-block;
            background: #007bff;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            text-decoration: none;
            transition: background-color 0.2s;
        }

        .document-link:hover {
            background: #0056b3;
        }

        .no-documents {
            text-align: center;
            padding: 2rem;
            color: #666;
        }

        @media (max-width: 768px) {
            .documents-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="documents-container">
        <h1>Documents de la Direction</h1>
        
        <?php if (empty($documents)): ?>
        <div class="no-documents">
            <p>Aucun document n'est disponible pour le moment.</p>
        </div>
        <?php else: ?>
        <div class="documents-grid">
            <?php foreach ($documents as $doc): ?>
            <div class="document-card">
                <img src="/images/icons/<?php echo getDocumentIcon($doc['type']); ?>" 
                     alt="<?php echo htmlspecialchars($doc['type']); ?>" 
                     class="document-icon">
                <div class="document-name"><?php echo htmlspecialchars($doc['title']); ?></div>
                <div class="document-type"><?php echo getDocumentTypeLabel($doc['type']); ?></div>
                <a href="<?php echo htmlspecialchars($doc['url']); ?>" 
                   class="document-link" 
                   target="_blank">
                    Télécharger
                </a>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <script>
        // Ajout d'un gestionnaire d'événements pour les liens de téléchargement
        document.querySelectorAll('.document-link').forEach(link => {
            link.addEventListener('click', (e) => {
                // Vous pouvez ajouter ici un suivi des téléchargements si nécessaire
                console.log('Document téléchargé:', link.href);
            });
        });
    </script>
</body>
</html>

<?php
function getDocumentIcon($type) {
    switch (strtolower($type)) {
        case 'pdf':
            return 'pdf-icon.png';
        case 'doc':
        case 'docx':
            return 'word-icon.png';
        case 'ppt':
        case 'pptx':
            return 'powerpoint-icon.png';
        default:
            return 'document-icon.png';
    }
}

function getDocumentTypeLabel($type) {
    switch (strtolower($type)) {
        case 'pdf':
            return 'Document PDF';
        case 'doc':
        case 'docx':
            return 'Document Word';
        case 'ppt':
        case 'pptx':
            return 'Présentation PowerPoint';
        default:
            return 'Document';
    }
}
?> 