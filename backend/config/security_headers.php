<?php
/**
 * Configuration des Headers de Sécurité pour daba
 * 
 * Ce fichier centralise tous les headers de sécurité HTTP
 * À inclure dans tous les fichiers API
 * 
 * @author Security Audit
 * @version 1.0.0
 */

// =============================================================================
// CONFIGURATION CORS SÉCURISÉE
// =============================================================================

// Liste blanche des origines autorisées
$allowedOrigins = [
    'http://localhost:5173',       // Vite dev server
    'http://localhost:3000',       // Alternative dev
    'http://127.0.0.1:5173',      // Localhost alternatif
    'https://daba.com',    // Production
    'https://www.daba.com' // Production WWW
];

// Récupérer l'origine de la requête
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

// Vérifier si l'origine est autorisée
if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
    header('Access-Control-Allow-Credentials: true');
} elseif (getenv('APP_ENV') === 'development') {
    // En développement, autoriser localhost avec n'importe quel port
    if (preg_match('/^https?:\/\/(localhost|127\.0\.0\.1)(:\d+)?$/', $origin)) {
        header("Access-Control-Allow-Origin: $origin");
        header('Access-Control-Allow-Credentials: true');
    }
}
// Si l'origine n'est pas autorisée, ne pas définir de header CORS
// Le navigateur bloquera automatiquement la requête cross-origin

// Headers CORS additionnels
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-Token');
header('Access-Control-Max-Age: 86400'); // Cache preflight pendant 24h
header('Access-Control-Expose-Headers: X-RateLimit-Limit, X-RateLimit-Remaining, X-RateLimit-Reset');

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
// Empêche l'exécution si la réponse est mal interprétée
header("Content-Security-Policy: default-src 'none'; frame-ancestors 'none'");

// Type de contenu JSON avec charset
header('Content-Type: application/json; charset=utf-8');

// =============================================================================
// HSTS (HTTP Strict Transport Security)
// =============================================================================

// Activer uniquement en HTTPS
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
    // Répondre immédiatement aux requêtes preflight
    http_response_code(204); // No Content
    exit();
}

// =============================================================================
// FONCTIONS UTILITAIRES
// =============================================================================

/**
 * Envoie une réponse JSON sécurisée
 * 
 * @param mixed $data Données à envoyer
 * @param int $statusCode Code HTTP
 * @param array $additionalHeaders Headers additionnels
 */
function sendSecureJsonResponse($data, $statusCode = 200, $additionalHeaders = []) {
    http_response_code($statusCode);
    
    foreach ($additionalHeaders as $name => $value) {
        header("$name: $value");
    }
    
    // S'assurer que Content-Type est défini
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
    }
    
    // Encoder en JSON avec options de sécurité
    echo json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
    exit();
}

/**
 * Récupère et valide les données JSON de la requête
 * 
 * @param int $maxSize Taille maximale en bytes (défaut: 1MB)
 * @return array Données décodées
 */
function getSecureJsonData($maxSize = 1048576) {
    // Vérifier la taille du contenu
    $contentLength = $_SERVER['CONTENT_LENGTH'] ?? 0;
    if ($contentLength > $maxSize) {
        sendSecureJsonResponse(['error' => 'Requête trop volumineuse'], 413);
    }
    
    // Lire le corps de la requête
    $json = file_get_contents('php://input');
    
    // Vérifier que le JSON n'est pas vide
    if (empty($json)) {
        sendSecureJsonResponse(['error' => 'Corps de requête vide'], 400);
    }
    
    // Décoder le JSON
    $data = json_decode($json, true);
    
    // Vérifier les erreurs de parsing
    if (json_last_error() !== JSON_ERROR_NONE) {
        sendSecureJsonResponse([
            'error' => 'JSON invalide',
            'details' => json_last_error_msg()
        ], 400);
    }
    
    return $data;
}

/**
 * Valide un token CSRF
 * 
 * @param string $token Token à valider
 * @return bool
 */
function validateCsrfToken($token) {
    if (empty($token)) {
        return false;
    }
    
    // Le token devrait être stocké en session ou validé côté serveur
    // Ici, on vérifie juste le format
    return preg_match('/^[a-f0-9]{64}$/i', $token);
}

/**
 * Génère un token CSRF
 * 
 * @return string
 */
function generateCsrfToken() {
    return bin2hex(random_bytes(32));
}
?>
