<?php
require_once __DIR__ . '/Security.php';

echo "Génération des clés de sécurité pour le fichier .env\n\n";

// Générer des clés plus longues pour plus de sécurité
echo "APP_KEY=" . Security::generateSecureKey(32) . "\n";
echo "ENCRYPTION_KEY=" . Security::generateSecureKey(32) . "\n";
echo "JWT_SECRET=" . Security::generateSecureKey(64) . "\n\n";  // Clé JWT plus longue

echo "Copiez ces clés dans votre fichier .env\n";
echo "IMPORTANT:\n";
echo "1. Ne partagez JAMAIS ces clés\n";
echo "2. Ne les committez pas dans Git\n";
echo "3. Utilisez des clés différentes en production\n";
echo "4. Gardez une sauvegarde sécurisée de ces clés\n"; 