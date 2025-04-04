<?php
require_once __DIR__ . '/database.php';

class Security {
    private static $encryptionKey;
    private static $appKey;
    private static $jwtSecret;
    private static $jwtExpiration;

    public static function init() {
        loadEnv();
        
        error_log('Lecture des clés de sécurité...');
        error_log('ENCRYPTION_KEY: ' . getenv('ENCRYPTION_KEY'));
        error_log('APP_KEY: ' . getenv('APP_KEY'));
        error_log('JWT_SECRET: ' . getenv('JWT_SECRET'));
        error_log('JWT_EXPIRATION: ' . getenv('JWT_EXPIRATION'));
        
        self::$encryptionKey = getenv('ENCRYPTION_KEY');
        self::$appKey = getenv('APP_KEY');
        self::$jwtSecret = getenv('JWT_SECRET');
        self::$jwtExpiration = getenv('JWT_EXPIRATION');

        if (empty(self::$encryptionKey) || empty(self::$appKey) || empty(self::$jwtSecret)) {
            throw new Exception('Les clés de sécurité ne sont pas configurées dans le fichier .env');
        }
    }

    public static function hashPassword($password) {
        error_log('Hachage du mot de passe : ' . $password);
        $hash = password_hash($password, PASSWORD_DEFAULT);
        error_log('Hash généré : ' . $hash);
        return $hash;
    }

    public static function verifyPassword($password, $hash) {
        error_log('Vérification du mot de passe');
        error_log('Mot de passe fourni : ' . $password);
        error_log('Hash stocké : ' . $hash);
        $result = password_verify($password, $hash);
        error_log('Résultat de la vérification : ' . ($result ? 'succès' : 'échec'));
        return $result;
    }

    public static function encrypt($data) {
        $iv = random_bytes(16); // Vecteur d'initialisation
        $encrypted = openssl_encrypt(
            $data,
            'AES-256-CBC',
            self::$encryptionKey,
            0,
            $iv
        );
        return base64_encode($iv . $encrypted);
    }

    public static function decrypt($data) {
        $data = base64_decode($data);
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);
        return openssl_decrypt(
            $encrypted,
            'AES-256-CBC',
            self::$encryptionKey,
            0,
            $iv
        );
    }

    public static function generateSecureKey($length = 32) {
        return bin2hex(random_bytes($length));
    }

    public static function generateJWT($userId, $role) {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode([
            'sub' => $userId,
            'role' => $role,
            'iat' => time(),
            'exp' => time() + intval(self::$jwtExpiration / 1000)
        ]);

        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

        $signature = hash_hmac('sha256', 
            $base64UrlHeader . "." . $base64UrlPayload, 
            self::$jwtSecret, 
            true
        );
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    public static function verifyJWT($token) {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }

        $header = base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[0]));
        $payload = base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[1]));
        $signature = base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[2]));

        $verificationSignature = hash_hmac('sha256', 
            $parts[0] . "." . $parts[1], 
            self::$jwtSecret, 
            true
        );

        if ($signature !== $verificationSignature) {
            return false;
        }

        $payload = json_decode($payload, true);
        if (!$payload || !isset($payload['exp']) || $payload['exp'] < time()) {
            return false;
        }

        return $payload;
    }
} 