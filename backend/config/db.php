<?php
/**
 * Configuration de la base de données - daba
 * 
 * ⚠️ SÉCURITÉ: Les credentials sont chargés depuis le fichier .env
 * Créez api/.env à partir de api/.env.example avant utilisation
 */

// =============================================================================  
// CHARGEMENT DES VARIABLES D'ENVIRONNEMENT
// =============================================================================

$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Ignorer les commentaires
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            // Supprimer les guillemets si présents
            $value = trim($value, '"\'');
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

// =============================================================================
// CONFIGURATION DE LA BASE DE DONNÉES
// =============================================================================

define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'daba');
define('APP_ENV', getenv('APP_ENV') ?: 'development');

// =============================================================================
// CONNEXION PDO SÉCURISÉE
// =============================================================================

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false, // ⚠️ SÉCURITÉ: Désactive l'émulation des requêtes préparées
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci, time_zone = '+00:00'"
    ];
    
    // Ajouter SSL en production si configuré
    if (APP_ENV === 'production' && getenv('MYSQL_SSL_CA')) {
        $options[PDO::MYSQL_ATTR_SSL_CA] = getenv('MYSQL_SSL_CA');
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = true;
    }
    
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    
} catch (PDOException $e) {
    // ⚠️ SÉCURITÉ: Ne jamais exposer les détails d'erreur en production
    if (APP_ENV !== 'production') {
        error_log('Erreur DB: ' . $e->getMessage());
    }
    
    http_response_code(500);
    echo json_encode(['error' => 'Service temporairement indisponible']);
    exit();
}

// =============================================================================
// FONCTIONS UTILITAIRES
// =============================================================================

/**
 * Nettoie et valide les entrées utilisateur
 * 
 * @param string $data Donnée à nettoyer
 * @return string Donnée nettoyée
 */
function sanitize($data) {
    if (!is_string($data)) {
        return $data;
    }
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    return $data;
}

/**
 * Génère un token sécurisé
 * 
 * @param int $length Longueur en bytes (défaut: 32 = 64 caractères hex)
 * @return string Token hexadécimal
 */
function generateSecureToken($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Vérifie si l'environnement est en production
 * 
 * @return bool
 */
function isProduction() {
    return APP_ENV === 'production';
}
