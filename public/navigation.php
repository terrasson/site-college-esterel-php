<?php
session_start();
require_once __DIR__ . '/api/config.php';
require_once __DIR__ . '/api/functions.php';

// Vérification de l'authentification
if (!isAuthenticated()) {
    header('Location: /index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Navigation - Cuisine de l'Esterel</title>
    <link rel="stylesheet" href="/css/multi-login-style.css" />
    <link rel="stylesheet" href="/styles/common.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            height: 100vh;
            /* Hauteur totale de la fenêtre */
            display: flex;
            justify-content: center;
            /* Centre horizontalement */
            align-items: center;
            /* Centre verticalement */
        }

        .navigation-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            width: 90%;
            /* Largeur responsive */
            max-width: 400px;
            /* Largeur maximale */
            margin: 20px;
            /* Marge autour du conteneur */
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }

        .nav-button {
            width: 100%;
            padding: 15px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-bottom: 15px;
            transition: background-color 0.3s ease;
        }

        .nav-button:hover {
            background: #2980b9;
        }

        .nav-button:last-child {
            margin-bottom: 0;
        }

        /* Media queries pour la responsivité */
        @media screen and (max-width: 480px) {
            .navigation-container {
                padding: 20px;
                margin: 10px;
            }

            .nav-button {
                padding: 12px;
                font-size: 14px;
            }
        }
    </style>
</head>

<body>
    <script src="/js/auth-check.js"></script>
    <button id="backButton" class="back-button">← Retour</button>
    <div class="navigation-container">
        <h2>Navigation</h2>
        <button class="nav-button" onclick="window.location.href='cuisine.php'">Département</button>
        <button class="nav-button" onclick="window.location.href='direction.php'">La Direction</button>
        <button class="nav-button" onclick="window.location.href='affichage-dynamique.php'">Affichage
            Dynamique</button>
        <button class="nav-button" onclick="window.location.href='contact.php'">Contact et Crédits</button>
        <button class="nav-button" style="background-color: #dc3545;" onclick="window.location.href='/api/logout.php'">Déconnexion</button>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.getElementById('backButton').addEventListener('click', () => {
                window.history.back();
            });
        });
    </script>
</body>

</html> 