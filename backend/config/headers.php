<?php
/**
 * Configuration CORS et Headers de Sécurité Unifiés - daba
 * Ce fichier centralise CORS, sécurité et fonctions utilitaires
 * 
 * @version 2.0.0 - Production Ready
 */

// =============================================================================
// CHARGEMENT VARIABLES D'ENVIRONNEMENT
// =============================================================================

require_once __DIR__ . '/env.php';


// =============================================================================
// CONFIGURATION CORS SÉCURISÉE
// =============================================================================

$isProduction = ($_ENV['APP_ENV'] ?? 'development') === 'production';

// Origines autorisées depuis l'environnement ou hardcoded
$allowedOrigins = [];
if (!empty($_ENV['ALLOWED_ORIGINS'])) {
    $allowedOrigins = explode(',', $_ENV['ALLOWED_ORIGINS']);
    $allowedOrigins = array_map('trim', $allowedOrigins);
} else {
    // Fallback pour développement - autoriser tous les ports localhost
    $allowedOrigins = [
        'http://localhost:5173',
        'http://localhost:3000',
        'http://localhost:8080',
        'http://127.0.0.1:5173',
        'http://127.0.0.1:3000',
        'http://127.0.0.1:8080'
    ];
}

// Récupérer l'origine de la requête
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

// Vérifier si l'origine est autorisée
$originAllowed = false;
if (in_array($origin, $allowedOrigins)) {
    $originAllowed = true;
} elseif (!$isProduction) {
    // En développement, autoriser localhost avec n'importe quel port
    if (preg_match('/^https?:\/\/(localhost|127\.0\.0\.1)(:\d+)?$/', $origin)) {
        $originAllowed = true;
    }
}

// Définir CORS uniquement si l'origine est autorisée
if ($originAllowed) {
    if ($origin !== '*') {
        header("Access-Control-Allow-Origin: $origin");
    }
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-Token, X-2FA-Token');
    header('Access-Control-Max-Age: 86400');
    header('Access-Control-Expose-Headers: X-RateLimit-Limit, X-RateLimit-Remaining, X-RateLimit-Reset');
}

// =============================================================================
// HEADERS DE SÉCURITÉ
// =============================================================================

// Empêcher le MIME sniffing
header('X-Content-Type-Options: nosniff');

// Protection contre le clickjacking
header('X-Frame-Options: DENY');

// Protection XSS (legacy, mais toujours utile)
header('X-XSS-Protection: 1; mode=block');

// Politique de référent
header('Referrer-Policy: strict-origin-when-cross-origin');

// Politique de permissions (désactiver les fonctionnalités non utilisées)
header('Permissions-Policy: geolocation=(), microphone=(), camera=(), payment=(), usb=()');

// Content Security Policy pour les réponses JSON
header("Content-Security-Policy: default-src 'none'; frame-ancestors 'none'");

// Type de contenu JSON avec charset
header('Content-Type: application/json; charset=utf-8');

// =============================================================================
// HSTS (HTTP Strict Transport Security)
// =============================================================================

if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
}

// =============================================================================
// CACHE CONTROL
// =============================================================================

// Désactiver le cache pour les réponses API (données sensibles)
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

// =============================================================================
// GESTION DES REQUÊTES PREFLIGHT (OPTIONS)
// =============================================================================

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

// =============================================================================
// FONCTIONS UTILITAIRES
// =============================================================================

/**
 * Envoie une réponse JSON sécurisée
 */
function sendJsonResponse(mixed $data, int $statusCode = 200): void {
    http_response_code($statusCode);
    echo json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
    exit();
}

/**
 * Récupère et valide les données JSON de la requête
 */
function getJsonData(): array {
    $json = file_get_contents('php://input');
    
    if (empty($json)) {
        return $_POST ?? [];
    }

    $data = json_decode($json, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        return $_POST ?? [];
    }
    
    return is_array($data) ? $data : [];
}
