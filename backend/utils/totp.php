<?php
/**
 * Fonctions TOTP partagées - Daba
 * Utilisées par 2fa_verify.php et two_factor.php
 * 
 * @version 1.0.0
 */

/**
 * Vérifie un code TOTP
 * Implémentation simplifiée - utiliser robthree/twofactorauth en production
 */
function verifyTOTP($secret, $code) {
    // Décoder le secret Base32
    $secret = base32Decode($secret);
    
    // Obtenir le compteur de temps actuel (période de 30 secondes)
    $time = floor(time() / 30);
    
    // Vérifier le code actuel et les codes adjacents (±1 pour tolérance d'horloge)
    for ($i = -1; $i <= 1; $i++) {
        $counter = $time + $i;
        $expectedCode = generateTOTPCode($secret, $counter);
        
        if (hash_equals($expectedCode, $code)) {
            return true;
        }
    }
    
    return false;
}

/**
 * Génère un code TOTP pour un compteur donné
 */
function generateTOTPCode($secret, $counter) {
    // Convertir le compteur en bytes (big-endian)
    $counterBytes = pack('N*', 0) . pack('N*', $counter);
    
    // HMAC-SHA1
    $hash = hash_hmac('sha1', $counterBytes, $secret, true);
    
    // Dynamic truncation
    $offset = ord($hash[19]) & 0x0F;
    $code = (
        ((ord($hash[$offset]) & 0x7F) << 24) |
        ((ord($hash[$offset + 1]) & 0xFF) << 16) |
        ((ord($hash[$offset + 2]) & 0xFF) << 8) |
        (ord($hash[$offset + 3]) & 0xFF)
    ) % 1000000;
    
    return str_pad($code, 6, '0', STR_PAD_LEFT);
}

/**
 * Décode un secret Base32
 */
function base32Decode($secret) {
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $secret = strtoupper($secret);
    
    $bits = '';
    for ($i = 0; $i < strlen($secret); $i++) {
        if ($secret[$i] === '=') continue;
        $val = strpos($chars, $secret[$i]);
        if ($val === false) continue;
        $bits .= str_pad(decbin($val), 5, '0', STR_PAD_LEFT);
    }
    
    $bytes = '';
    for ($i = 0; $i + 8 <= strlen($bits); $i += 8) {
        $bytes .= chr(bindec(substr($bits, $i, 8)));
    }
    
    return $bytes;
}
