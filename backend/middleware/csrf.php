<?php
/**
 * Protection CSRF (Cross-Site Request Forgery) - Daba
 * Génère et valide les tokens CSRF
 * 
 * @author Security Audit
 * @version 1.0.0
 */

/**
 * Génère un token CSRF
 * 
 * @return string Token CSRF
 */
function generateCSRFToken() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_expires'] = time() + 3600; // 1 heure
    }
    
    return $_SESSION['csrf_token'];
}

/**
 * Valide un token CSRF
 * 
 * @param string $token Token à valider
 * @return bool True si valide
 */
function validateCSRFToken($token) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_expires'])) {
        return false;
    }
    
    // Vérifier l'expiration
    if (time() > $_SESSION['csrf_token_expires']) {
        unset($_SESSION['csrf_token']);
        unset($_SESSION['csrf_token_expires']);
        return false;
    }
    
    // Vérifier le token avec timing-safe comparison
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Régénère le token CSRF (après login, etc.)
 * 
 * @return string Nouveau token
 */
function regenerateCSRFToken() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    $_SESSION['csrf_token_expires'] = time() + 3600;
    
    return $_SESSION['csrf_token'];
}

/**
 * Exige un token CSRF valide
 * 
 * @param array $data Données de la requête
 */
function requireCSRFToken($data) {
    $token = $data['csrf_token'] ?? $data['_token'] ?? null;
    
    if (!$token) {
        http_response_code(400);
        echo json_encode(['error' => 'Token CSRF manquant']);
        exit();
    }
    
    if (!validateCSRFToken($token)) {
        http_response_code(403);
        echo json_encode(['error' => 'Token CSRF invalide ou expiré']);
        exit();
    }
}

/**
 * Ajoute le token CSRF aux données de réponse
 * 
 * @param array $response Données de réponse
 * @return array Données avec token CSRF
 */
function addCSRFTokenToResponse($response) {
    $response['csrf_token'] = generateCSRFToken();
    return $response;
}
