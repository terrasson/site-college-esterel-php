<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/Security.php';

error_log('Démarrage de l\'authentification');

try {
    // Log pour déboguer
    error_log('Tentative de connexion - Méthode: ' . $_SERVER['REQUEST_METHOD']);
    error_log('POST data: ' . print_r($_POST, true));
    error_log('Session data: ' . print_r($_SESSION, true));

    // Initialiser la sécurité
    Security::init();

    // Vérification de la méthode HTTP
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        error_log('Erreur: Méthode non autorisée');
        header('HTTP/1.1 405 Method Not Allowed');
        exit('Méthode non autorisée');
    }

    // Vérification du token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        error_log('Erreur: Token CSRF invalide');
        error_log('Token reçu: ' . ($_POST['csrf_token'] ?? 'non défini'));
        error_log('Token session: ' . ($_SESSION['csrf_token'] ?? 'non défini'));
        setFlashMessage('error', 'Session invalide, veuillez réessayer.');
        header('Location: /index.php');
        exit;
    }

    // Récupération et nettoyage des données
    $username = cleanInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    error_log('Tentative de connexion pour l\'utilisateur: ' . $username);

    // Vérification des champs requis
    if (empty($username) || empty($password)) {
        error_log('Erreur: Champs manquants');
        setFlashMessage('error', 'Tous les champs sont requis.');
        header('Location: /index.php');
        exit;
    }

    try {
        error_log('Variables d\'environnement : ' . print_r($_ENV, true));
        
        // Connexion à la base de données
        $pdo = getPDOConnection();
        error_log('Connexion à la base de données réussie');

        // Recherche de l'utilisateur
        $stmt = $pdo->prepare('SELECT rowid as id, username, password_hash, role FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        error_log('Recherche utilisateur - Trouvé: ' . ($user ? 'oui' : 'non'));
        if ($user) {
            error_log('ID utilisateur trouvé: ' . $user['id']);
        }

        // Vérification du mot de passe
        if ($user && Security::verifyPassword($password, $user['password_hash'])) {
            error_log('Mot de passe vérifié avec succès');
            session_regenerate_id(true);
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['username'] = $user['username'];
            
            error_log('Session créée : ' . print_r($_SESSION, true));
            
            setFlashMessage('success', 'Connexion réussie !');
            header('Location: /navigation.php');
            exit();
        } else {
            error_log('Échec de l\'authentification : mot de passe incorrect ou utilisateur non trouvé');
            error_log('Hash en base : ' . ($user ? $user['password_hash'] : 'utilisateur non trouvé'));
            error_log('Tentative avec mot de passe : ' . $password);
            setFlashMessage('error', 'Identifiant ou mot de passe incorrect.');
            header('Location: /index.php');
            exit();
        }
    } catch (PDOException $e) {
        error_log('Erreur PDO détaillée : ' . $e->getMessage());
        error_log('Trace : ' . $e->getTraceAsString());
        setFlashMessage('error', 'Une erreur est survenue lors de l\'authentification.');
        header('Location: /index.php');
        exit();
    }
} catch (Exception $e) {
    error_log('Erreur générale : ' . $e->getMessage());
    error_log('Trace : ' . $e->getTraceAsString());
    setFlashMessage('error', 'Une erreur système est survenue.');
    header('Location: /index.php');
    exit();
} 