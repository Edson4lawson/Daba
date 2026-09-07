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

require_once __DIR__ . '/env.php';


// =============================================================================
// CONFIGURATION DE LA BASE DE DONNÉES (HYBRIDE MYSQL / POSTGRESQL SUPABASE)
// =============================================================================

define('APP_ENV', getenv('APP_ENV') ?: ($_ENV['APP_ENV'] ?? 'development'));
define('DB_CONNECTION', getenv('DB_CONNECTION') ?: ($_ENV['DB_CONNECTION'] ?? 'mysql'));
define('DATABASE_URL', getenv('DATABASE_URL') ?: ($_ENV['DATABASE_URL'] ?? ''));

define('DB_HOST', getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? '127.0.0.1'));
define('DB_PORT', getenv('DB_PORT') ?: ($_ENV['DB_PORT'] ?? (DB_CONNECTION === 'pgsql' ? '5432' : '3306')));
define('DB_NAME', getenv('DB_NAME') ?: ($_ENV['DB_NAME'] ?? 'daba'));
define('DB_USER', getenv('DB_USER') ?: ($_ENV['DB_USER'] ?? 'root'));
define('DB_PASS', getenv('DB_PASS') ?: ($_ENV['DB_PASS'] ?? ''));

// =============================================================================
// CONNEXION PDO SÉCURISÉE
// =============================================================================

$pdo = null;
$dbError = null;

try {
    if (!empty(DATABASE_URL) || DB_CONNECTION === 'pgsql') {
        // --- PostgreSQL / Supabase ---
        if (!empty(DATABASE_URL)) {
            $rawUrl = DATABASE_URL;
            // Normaliser l'URL de connexion
            $parsed = parse_url($rawUrl);
            
            $pgHost = $parsed['host'] ?? DB_HOST;
            $pgPort = $parsed['port'] ?? 5432;
            $pgUser = isset($parsed['user']) ? urldecode($parsed['user']) : DB_USER;
            $pgPass = isset($parsed['pass']) ? urldecode($parsed['pass']) : DB_PASS;
            $pgName = isset($parsed['path']) ? ltrim(explode('?', $parsed['path'])[0], '/') : DB_NAME;
        } else {
            $pgHost = DB_HOST;
            $pgPort = DB_PORT;
            $pgUser = DB_USER;
            $pgPass = DB_PASS;
            $pgName = DB_NAME;
        }

        // Supabase requiert sslmode=require (ou sslmode=prefer en fallback)
        $dsn = "pgsql:host={$pgHost};port={$pgPort};dbname={$pgName};sslmode=require";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_TIMEOUT => 5
        ];

        try {
            $pdo = new PDO($dsn, $pgUser, $pgPass, $options);
        } catch (PDOException $pgEx) {
            // Deuxième tentative avec sslmode=prefer si require échoue en local
            $fallbackDsn = "pgsql:host={$pgHost};port={$pgPort};dbname={$pgName};sslmode=prefer";
            $pdo = new PDO($fallbackDsn, $pgUser, $pgPass, $options);
        }
    } else {
        // --- MySQL / Laragon Local ---
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci, time_zone = '+00:00'",
            PDO::ATTR_TIMEOUT => 5
        ];

        if (APP_ENV === 'production' && getenv('MYSQL_SSL_CA')) {
            $options[PDO::MYSQL_ATTR_SSL_CA] = getenv('MYSQL_SSL_CA');
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = true;
        }

        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    }
} catch (PDOException $e) {
    $dbError = $e->getMessage();
    error_log('Erreur DB: ' . $dbError);
    
    // Si appelé depuis health.php, ne pas faire de exit() pour laisser le health check répondre
    if (defined('NO_DB_AUTO_EXIT') && NO_DB_AUTO_EXIT) {
        $pdo = null;
        return;
    }
    
    $isDebug = (getenv('APP_DEBUG') === 'true' || ($_ENV['APP_DEBUG'] ?? '') === 'true' || APP_ENV !== 'production');
    
    http_response_code(500);
    echo json_encode([
        'error' => 'Service temporairement indisponible',
        'details' => $isDebug ? $dbError : null
    ]);
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
