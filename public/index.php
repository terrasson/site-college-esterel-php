<?php
session_start();
require_once __DIR__ . '/api/config.php';
require_once __DIR__ . '/api/functions.php';

// Débogage temporaire
error_reporting(E_ALL);
ini_set('display_errors', 1);
var_dump($_SESSION);

// Si l'utilisateur est déjà connecté, rediriger vers navigation.php
if (isAuthenticated()) {
    header('Location: /navigation.php');
    exit;
}

// Générer un nouveau token CSRF
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Collège de l'Esterel</title>
    <link rel="stylesheet" href="/css/multi-login-style.css" />
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .login-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            color: #444;
            font-weight: 500;
        }

        input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background: #2980b9;
        }

        .error-message {
            color: red;
            text-align: center;
            margin-top: 10px;
            background-color: #ffebee;
            padding: 10px;
            border-radius: 4px;
            margin-top: 15px;
        }

        .success-message {
            color: green;
            text-align: center;
            margin-top: 10px;
            background-color: #e8f5e9;
            padding: 10px;
            border-radius: 4px;
            margin-top: 15px;
        }

        input:focus {
            outline: 2px solid #2980b9;
            border-color: #2980b9;
        }

        input:required {
            border-left: 3px solid #3498db;
        }
    </style>
</head>

<body>
    <main class="login-container" role="main">
        <h1>Connexion Administration</h1>
        <form id="loginForm" action="/api/auth.php" method="POST" aria-label="Formulaire de connexion">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <div class="form-group">
                <label for="username">Identifiant</label>
                <input type="text" id="username" name="username" required 
                       aria-required="true" autocomplete="username">
            </div>
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required 
                       aria-required="true" autocomplete="current-password">
            </div>
            <button type="submit" aria-label="Se connecter">Se connecter</button>
            
            <?php if (isset($_SESSION['error'])): ?>
                <p class="error-message" role="alert">
                    <?php echo htmlspecialchars($_SESSION['error']); ?>
                    <?php unset($_SESSION['error']); ?>
                </p>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['success'])): ?>
                <p class="success-message" role="alert">
                    <?php echo htmlspecialchars($_SESSION['success']); ?>
                    <?php unset($_SESSION['success']); ?>
                </p>
            <?php endif; ?>
        </form>
    </main>
    <script src="/js/login.js"></script>
</body>

</html> 