<?php
/**
 * Configuration 2FA (Two-Factor Authentication) - Daba
 * Génère un secret TOTP et un QR code pour l'activation
 * 
 * @endpoint POST /api/auth/2fa/setup
 */

require_once __DIR__ . '/../config/headers.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../middleware/auth.php';

// Vérifier la méthode
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(['error' => 'Méthode non autorisée'], 405);
}

// Authentifier l'utilisateur
$user = authenticate();

try {
    // Générer un secret TOTP sécurisé (Base32)
    // En production, utiliser: composer require robthree/twofactorauth
    $secret = generateTOTPSecret();
    
    // Nom de l'application pour le QR code
    $appName = 'Daba';
    $accountName = $user['email'];
    
    // Générer l'URL OTPAuth pour le QR code
    // Format: otpauth://totp/Label:Account?secret=Secret&issuer=Issuer
    $otpauthUrl = sprintf(
        'otpauth://totp/%s:%s?secret=%s&issuer=%s&algorithm=SHA1&digits=6&period=30',
        rawurlencode($appName),
        rawurlencode($accountName),
        $secret,
        rawurlencode($appName)
    );
    
    // Générer les codes de secours (10 codes)
    $backupCodes = generateBackupCodes();
    
    // Stocker temporairement (non activé encore)
    $stmt = $pdo->prepare('
        INSERT INTO two_factor_auth (user_id, secret, enabled, backup_codes)
        VALUES (?, ?, 0, ?)
        ON DUPLICATE KEY UPDATE secret = ?, backup_codes = ?, enabled = 0
    ');
    $stmt->execute([$user['id'], $secret, json_encode($backupCodes), $secret, json_encode($backupCodes)]);
    
    sendJsonResponse([
        'secret' => $secret,
        'otpauth_url' => $otpauthUrl,
        'backup_codes' => $backupCodes,
        'message' => 'Scannez le QR code avec votre application d\'authentification (Google Authenticator, Authy, etc.)'
    ]);
    
} catch (PDOException $e) {
    error_log('Erreur 2FA setup: ' . $e->getMessage());
    sendJsonResponse(['error' => 'Une erreur technique est survenue lors de la configuration de la double authentification. Veuillez réessayer dans quelques instants.'], 500);
} catch (Exception $e) {
    error_log('Erreur générale 2FA setup: ' . $e->getMessage());
    sendJsonResponse(['error' => 'Une erreur inattendue est survenue lors de la configuration de la double authentification. Veuillez réessayer.'], 500);
}

/**
 * Génère un secret TOTP sécurisé (Base32)
 */
function generateTOTPSecret() {
    // Générer 32 octets aléatoires
    $random = random_bytes(32);
    
    // Encoder en Base32
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $secret = '';
    for ($i = 0; $i < strlen($random); $i += 5) {
        $chunk = substr($random, $i, 5);
        $chunk .= str_repeat("\0", 5 - strlen($chunk));
        
        $value = ord($chunk[0]) << 24 |
                  ord($chunk[1]) << 16 |
                  ord($chunk[2]) << 8 |
                  ord($chunk[3]) << 0;
        
        for ($j = 0; $j < 8; $j++) {
            $index = ($value >> (32 - ($j + 1) * 5)) & 31;
            $secret .= $chars[$index];
        }
    }
    
    return substr($secret, 0, 32);
}

/**
 * Génère 10 codes de secours
 */
function generateBackupCodes() {
    $codes = [];
    for ($i = 0; $i < 10; $i++) {
        $code = bin2hex(random_bytes(4));
        $codes[] = strtoupper(substr($code, 0, 4) . '-' . substr($code, 4, 8));
    }
    return $codes;
}
