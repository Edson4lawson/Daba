<?php
/**
 * Validation de mot de passe partagée - Daba
 * Politique stricte OWASP unifiée pour l'inscription et le reset
 * 
 * @version 1.0.0
 */

/**
 * Valide la force du mot de passe selon la politique OWASP
 * 
 * @param string $password Le mot de passe à valider
 * @return string|null Message d'erreur ou null si valide
 */
function validatePasswordStrength($password) {
    // Minimum 12 caractères
    if (strlen($password) < 12) {
        return 'Le mot de passe doit contenir au moins 12 caractères';
    }
    // Maximum 128 caractères
    if (strlen($password) > 128) {
        return 'Le mot de passe ne peut pas dépasser 128 caractères';
    }
    // Au moins une majuscule
    if (!preg_match('/[A-Z]/', $password)) {
        return 'Le mot de passe doit contenir au moins une majuscule';
    }
    // Au moins une minuscule
    if (!preg_match('/[a-z]/', $password)) {
        return 'Le mot de passe doit contenir au moins une minuscule';
    }
    // Au moins un chiffre
    if (!preg_match('/[0-9]/', $password)) {
        return 'Le mot de passe doit contenir au moins un chiffre';
    }
    // Au moins un caractère spécial
    if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
        return 'Le mot de passe doit contenir au moins un caractère spécial (!@#$%^&*(),.?":{}|<>)';
    }
    // Interdire les mots de passe communs (liste simplifiée)
    $commonPasswords = ['password', '123456', 'qwerty', 'admin', 'welcome', 'letmein'];
    if (in_array(strtolower($password), $commonPasswords)) {
        return 'Ce mot de passe est trop commun. Choisissez un mot de passe plus complexe.';
    }
    // Interdire les séquences
    if (preg_match('/(012|123|234|345|456|567|678|789|890|abc|bcd|cde|def|efg|fgh|ghi|hij|ijk|jkl|klm|lmn|mno|nop|opq|pqr|qrs|rst|stu|tuv|uvw|vwx|wxy|xyz)/i', $password)) {
        return 'Le mot de passe ne doit pas contenir de séquences consécutives.';
    }
    // Interdire les répétitions
    if (preg_match('/(.)\\1{2,}/', $password)) {
        return 'Le mot de passe ne doit pas contenir de caractères répétés plus de 2 fois.';
    }
    return null;
}

/**
 * Hash un mot de passe avec Argon2id (algorithme recommandé OWASP/NIST)
 * 
 * @param string $password Le mot de passe en clair
 * @return string Le hash Argon2id
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_ARGON2ID, [
        'memory_cost' => 65536,      // 64 MB
        'time_cost' => 4,            // 4 itérations
        'threads' => 3               // 3 threads
    ]);
}
