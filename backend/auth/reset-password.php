<?php
/**
 * Réinitialisation du mot de passe
 * 
 * @endpoint POST /api/auth/reset-password.php
 * @body { "token": "string", "password": "string", "password_confirmation": "string" }
 */

require_once __DIR__ . '/../config/headers.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../middleware/rate_limit.php';
require_once __DIR__ . '/../utils/password.php';

// Rate limiting
rateLimit('reset_password', 5, 300);

// Vérifier la méthode HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(['error' => 'Méthode non autorisée'], 405);
}

$data = getJsonData();

$token = $data['token'] ?? '';
$password = $data['password'] ?? '';
$passwordConfirmation = $data['password_confirmation'] ?? '';

// Validations
if (empty($token) || strlen($token) !== 64) {
    sendJsonResponse(['error' => 'Token invalide'], 400);
}

if (empty($password)) {
    sendJsonResponse(['error' => 'Mot de passe requis'], 400);
}

if ($password !== $passwordConfirmation) {
    sendJsonResponse(['error' => 'Les mots de passe ne correspondent pas'], 400);
}

// Valider la force du mot de passe (politique stricte OWASP unifiée)
$passwordError = validatePasswordStrength($password);
if ($passwordError) {
    sendJsonResponse(['error' => $passwordError], 400);
}

try {
    // Vérifier le token
    $stmt = $pdo->prepare('
        SELECT pr.*, u.email 
        FROM password_resets pr
        INNER JOIN users u ON pr.user_id = u.id
        WHERE pr.token = ? 
        AND pr.expires_at > NOW()
        AND pr.used_at IS NULL
    ');
    $stmt->execute([$token]);
    $reset = $stmt->fetch();
    
    if (!$reset) {
        sendJsonResponse(['error' => 'Token invalide ou expiré'], 400);
    }
    
    $pdo->beginTransaction();
    
    // Mettre à jour le mot de passe avec Argon2id (algorithme unifié)
    $hashedPassword = hashPassword($password);
    $stmt = $pdo->prepare('UPDATE users SET password = ?, failed_login_attempts = 0, locked_until = NULL WHERE id = ?');
    $stmt->execute([$hashedPassword, $reset['user_id']]);
    
    // Marquer le token comme utilisé
    $stmt = $pdo->prepare('UPDATE password_resets SET used_at = NOW() WHERE id = ?');
    $stmt->execute([$reset['id']]);
    
    // Révoquer tous les refresh tokens (sécurité)
    $stmt = $pdo->prepare('UPDATE refresh_tokens SET revoked = 1, revoked_at = NOW() WHERE user_id = ? AND revoked = 0');
    $stmt->execute([$reset['user_id']]);
    
    // Invalider l'access token actuel
    $stmt = $pdo->prepare('UPDATE users SET token = NULL, token_expires_at = NULL WHERE id = ?');
    $stmt->execute([$reset['user_id']]);
    
    $pdo->commit();
    
    // Logger l'événement
    error_log("Password reset successful for user {$reset['user_id']} ({$reset['email']}) from IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
    
    sendJsonResponse([
        'message' => 'Mot de passe réinitialisé avec succès. Veuillez vous reconnecter.'
    ]);
    
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Erreur reset password: ' . $e->getMessage());
    sendJsonResponse(['error' => 'Erreur lors de la réinitialisation'], 500);
}
?>
