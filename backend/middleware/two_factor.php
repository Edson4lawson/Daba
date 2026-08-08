<?php
/**
 * Middleware de vérification 2FA - Bloom Chloé
 * Vérifie que le 2FA est activé et validé pour les administrateurs
 * 
 * @author Security Audit
 * @version 1.0.0
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../utils/totp.php';

/**
 * Vérifie si l'utilisateur a complété le 2FA
 * 
 * @param array $user Données utilisateur
 * @return bool True si 2FA validé ou non requis
 */
function requireTwoFactorVerification($user) {
    global $pdo;
    
    // Les admins doivent toujours avoir le 2FA activé
    if (in_array($user['role'], ['admin', 'super_admin'])) {
        // Vérifier si le 2FA est activé
        $stmt = $pdo->prepare('SELECT enabled FROM two_factor_auth WHERE user_id = ?');
        $stmt->execute([$user['id']]);
        $twoFactor = $stmt->fetch();
        
        if (!$twoFactor || !$twoFactor['enabled']) {
            http_response_code(403);
            echo json_encode([
                'error' => '2FA obligatoire pour les administrateurs',
                'require_2fa_setup' => true
            ]);
            exit();
        }
    }
    
    // Vérifier si le 2FA a été validé pour cette session
    $sessionToken = $_SERVER['HTTP_X_2FA_TOKEN'] ?? null;
    
    if ($sessionToken) {
        $stmt = $pdo->prepare('
            SELECT verified, expires_at 
            FROM two_factor_sessions 
            WHERE user_id = ? AND session_token = ? AND verified = 1 AND expires_at > NOW()
        ');
        $stmt->execute([$user['id'], $sessionToken]);
        $session = $stmt->fetch();
        
        if ($session) {
            return true; // 2FA validé
        }
    }
    
    // Vérifier si c'est un appareil de confiance
    $deviceIdentifier = generateDeviceIdentifier();
    $ipAddress = getClientIP();
    
    $stmt = $pdo->prepare('
        SELECT id 
        FROM trusted_devices 
        WHERE user_id = ? AND device_identifier = ? AND ip_address = ? AND expires_at > NOW()
    ');
    $stmt->execute([$user['id'], $deviceIdentifier, $ipAddress]);
    
    if ($stmt->fetch()) {
        // Mettre à jour le dernier usage
        $stmt = $pdo->prepare('UPDATE trusted_devices SET last_used_at = NOW() WHERE user_id = ? AND device_identifier = ?');
        $stmt->execute([$user['id'], $deviceIdentifier]);
        return true;
    }
    
    // 2FA requis mais non validé
    http_response_code(403);
    echo json_encode([
        'error' => 'Vérification 2FA requise',
        'require_2fa_verification' => true
    ]);
    exit();
}

/**
 * Génère un identifiant unique pour l'appareil
 */
function generateDeviceIdentifier() {
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    $acceptLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'unknown';
    return hash('sha256', $userAgent . $acceptLanguage);
}

/**
 * Valide un code 2FA lors de la connexion
 * 
 * @param int $userId ID utilisateur
 * @param string $code Code TOTP
 * @return bool True si valide
 */
function validateTwoFactorCode($userId, $code) {
    global $pdo;
    
    $stmt = $pdo->prepare('SELECT secret, backup_codes FROM two_factor_auth WHERE user_id = ? AND enabled = 1');
    $stmt->execute([$userId]);
    $twoFactor = $stmt->fetch();
    
    if (!$twoFactor) {
        return false;
    }
    
    // Nettoyer le code
    $code = preg_replace('/[^0-9]/', '', $code);
    
    // Vérifier code de secours
    if (strlen($code) === 8) {
        $backupCodes = json_decode($twoFactor['backup_codes'], true) ?: [];
        
        if (in_array(strtoupper($code), $backupCodes)) {
            // Retirer le code utilisé
            $backupCodes = array_diff($backupCodes, [strtoupper($code)]);
            
            $stmt = $pdo->prepare('UPDATE two_factor_auth SET backup_codes = ?, last_used_at = NOW() WHERE user_id = ?');
            $stmt->execute([json_encode(array_values($backupCodes)), $userId]);
            
            return true;
        }
    }
    
    // Vérifier code TOTP
    return verifyTOTP($twoFactor['secret'], $code);
}

/**
 * Crée une session 2FA temporaire
 * 
 * @param int $userId ID utilisateur
 * @return string Token de session
 */
function createTwoFactorSession($userId) {
    global $pdo;
    
    $token = bin2hex(random_bytes(32));
    $expiresAt = date('Y-m-d H:i:s', strtotime('+5 minutes'));
    
    $stmt = $pdo->prepare('
        INSERT INTO two_factor_sessions (user_id, session_token, expires_at, verified)
        VALUES (?, ?, ?, 0)
    ');
    $stmt->execute([$userId, $token, $expiresAt]);
    
    return $token;
}

/**
 * Marque une session 2FA comme vérifiée
 * 
 * @param int $userId ID utilisateur
 * @param string $token Token de session
 */
function verifyTwoFactorSession($userId, $token) {
    global $pdo;
    
    $stmt = $pdo->prepare('
        UPDATE two_factor_sessions 
        SET verified = 1 
        WHERE user_id = ? AND session_token = ? AND expires_at > NOW()
    ');
    $stmt->execute([$userId, $token]);
}

/**
 * Ajoute un appareil de confiance
 * 
 * @param int $userId ID utilisateur
 * @param bool $remember Se souvenir de l'appareil
 */
function addTrustedDevice($userId, $remember = false) {
    global $pdo;
    
    if (!$remember) {
        return;
    }
    
    $deviceIdentifier = generateDeviceIdentifier();
    $userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);
    $ipAddress = getClientIP();
    $expiresAt = date('Y-m-d H:i:s', strtotime('+30 days'));
    
    $stmt = $pdo->prepare('
        INSERT INTO trusted_devices (user_id, device_identifier, user_agent, ip_address, expires_at)
        VALUES (?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE expires_at = ?, last_used_at = NOW()
    ');
    $stmt->execute([$userId, $deviceIdentifier, $userAgent, $ipAddress, $expiresAt, $expiresAt]);
}


