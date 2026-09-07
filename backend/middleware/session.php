<?php
/**
 * Gestion de Sessions Sécurisées - Daba
 * Implémente HttpOnly, Secure, SameSite, rotation, expiration
 * 
 * @author Security Audit
 * @version 1.0.0
 */

/**
 * Configure les paramètres de session sécurisés
 */
function configureSecureSession() {
    // Configuration de base
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 1);
    ini_set('session.cookie_samesite', 'Strict');
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_lifetime', 0); // Session cookie (expire à la fermeture du navigateur)
    ini_set('session.gc_maxlifetime', 3600); // 1 heure côté serveur
    
    // Empêcher l'accès aux cookies via JavaScript
    ini_set('session.cookie_httponly', true);
    
    // N'envoyer les cookies que sur HTTPS
    ini_set('session.cookie_secure', true);
    
    // Protection contre le session fixation
    ini_set('session.use_strict_mode', true);
    
    // Utiliser un ID de session fort
    ini_set('session.sid_length', 32);
    ini_set('session.sid_bits_per_character', 6);
    
    // Désactiver la mise en cache des pages de session
    ini_set('session.cache_limiter', 'nocache');
    
    // Démarrer la session si pas déjà démarrée
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Régénère l'ID de session (protection contre session fixation)
 * 
 * @param bool $deleteOld Supprimer l'ancienne session
 */
function rotateSessionId($deleteOld = true) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    session_regenerate_id($deleteOld);
    
    // Marquer la rotation pour audit
    $_SESSION['last_rotation'] = time();
    $_SESSION['rotation_count'] = ($_SESSION['rotation_count'] ?? 0) + 1;
}

/**
 * Vérifie si la session doit être régénérée
 * 
 * @param int $maxAge Âge maximum en secondes (défaut: 15 minutes)
 * @return bool True si rotation requise
 */
function shouldRotateSession($maxAge = 900) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['last_rotation'])) {
        return true;
    }
    
    return (time() - $_SESSION['last_rotation']) > $maxAge;
}

/**
 * Vérifie si la session a expiré
 * 
 * @param int $maxLifetime Durée maximale en secondes (défaut: 1 heure)
 * @return bool True si expirée
 */
function isSessionExpired($maxLifetime = 3600) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['last_activity'])) {
        $_SESSION['last_activity'] = time();
        return false;
    }
    
    return (time() - $_SESSION['last_activity']) > $maxLifetime;
}

/**
 * Met à jour l'activité de la session
 */
function updateSessionActivity() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $_SESSION['last_activity'] = time();
}

/**
 * Détruit la session et les cookies
 */
function destroySession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $_SESSION = [];
    
    // Supprimer le cookie de session
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }
    
    session_destroy();
}

/**
 * Déconnecte toutes les sessions d'un utilisateur
 * 
 * @param int $userId ID utilisateur
 */
function revokeAllUserSessions($userId) {
    global $pdo;
    
    // Marquer tous les refresh tokens comme révoqués
    $stmt = $pdo->prepare('UPDATE refresh_tokens SET revoked = 1 WHERE user_id = ?');
    $stmt->execute([$userId]);
    
    // Supprimer les appareils de confiance
    $stmt = $pdo->prepare('DELETE FROM trusted_devices WHERE user_id = ?');
    $stmt->execute([$userId]);
    
    // Supprimer les sessions 2FA
    $stmt = $pdo->prepare('DELETE FROM two_factor_sessions WHERE user_id = ?');
    $stmt->execute([$userId]);
    
    // Logger la révocation
    error_log("Toutes les sessions révoquées pour l'utilisateur $userId");
}

/**
 * Déconnecte les sessions sur d'autres appareils
 * 
 * @param int $userId ID utilisateur
 * @param string $currentSessionId ID de la session actuelle à conserver
 */
function revokeOtherSessions($userId, $currentSessionId = null) {
    global $pdo;
    
    // Révoquer tous les refresh tokens sauf celui actuel
    if ($currentSessionId) {
        $stmt = $pdo->prepare('
            UPDATE refresh_tokens 
            SET revoked = 1 
            WHERE user_id = ? AND id != ?
        ');
        $stmt->execute([$userId, $currentSessionId]);
    } else {
        $stmt = $pdo->prepare('UPDATE refresh_tokens SET revoked = 1 WHERE user_id = ?');
        $stmt->execute([$userId]);
    }
    
    // Supprimer les appareils de confiance
    $stmt = $pdo->prepare('DELETE FROM trusted_devices WHERE user_id = ?');
    $stmt->execute([$userId]);
}

/**
 * Définit les cookies de session sécurisés
 * 
 * @param string $name Nom du cookie
 * @param string $value Valeur
 * @param int $expires Expiration en secondes
 */
function setSecureCookie($name, $value, $expires = 0) {
    $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
    
    setcookie(
        $name,
        $value,
        $expires,
        '/', // Path
        '', // Domain (vide = domaine actuel)
        $secure, // Secure
        true, // HttpOnly
        true // SameSite Strict
    );
}

/**
 * Vérifie si l'IP a changé (détection de session hijacking)
 * 
 * @return bool True si IP a changé
 */
function hasIPChanged() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['client_ip'])) {
        $_SESSION['client_ip'] = getClientIP();
        return false;
    }
    
    return $_SESSION['client_ip'] !== getClientIP();
}

/**
 * Vérifie si le User-Agent a changé
 * 
 * @return bool True si User-Agent a changé
 */
function hasUserAgentChanged() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['client_ua'])) {
        $_SESSION['client_ua'] = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        return false;
    }
    
    return $_SESSION['client_ua'] !== ($_SERVER['HTTP_USER_AGENT'] ?? 'unknown');
}

/**
 * Vérifie l'intégrité de la session
 * 
 * @return bool True si session valide
 */
function validateSessionIntegrity() {
    if (hasIPChanged() || hasUserAgentChanged()) {
        destroySession();
        return false;
    }
    
    if (isSessionExpired()) {
        destroySession();
        return false;
    }
    
    return true;
}

/**
 * Initialise une session sécurisée
 */
function initializeSecureSession() {
    configureSecureSession();
    
    if (!isset($_SESSION['created'])) {
        $_SESSION['created'] = time();
        $_SESSION['last_activity'] = time();
        $_SESSION['last_rotation'] = time();
        $_SESSION['client_ip'] = getClientIP();
        $_SESSION['client_ua'] = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $_SESSION['rotation_count'] = 0;
    }
    
    // Vérifier l'intégrité
    if (!validateSessionIntegrity()) {
        http_response_code(401);
        echo json_encode(['error' => 'Session invalide ou expirée']);
        exit();
    }
    
    // Rotation automatique si nécessaire
    if (shouldRotateSession()) {
        rotateSessionId();
    }
    
    // Mettre à jour l'activité
    updateSessionActivity();
}
