<?php
/**
 * Demande de réinitialisation de mot de passe
 * Génère un token et envoie un email (simulation)
 * 
 * @endpoint POST /api/auth/forgot-password.php
 * @body { "email": "string" }
 */

require_once __DIR__ . '/../config/headers.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../middleware/rate_limit.php';

// Rate limiting strict (3 demandes par heure)
rateLimit('forgot_password', 3, 3600);

// Vérifier la méthode HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(['error' => 'Méthode non autorisée'], 405);
}

$data = getJsonData();
$email = strtolower(trim($data['email'] ?? ''));

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendJsonResponse(['error' => 'Email invalide'], 400);
}

try {
    // Vérifier si l'utilisateur existe
    $stmt = $pdo->prepare('SELECT id, email, first_name FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    // Toujours retourner un succès pour éviter l'énumération d'emails
    if (!$user) {
        // Simuler un délai pour éviter les timing attacks
        usleep(random_int(100000, 500000));
        sendJsonResponse([
            'message' => 'Si cet email existe, vous recevrez un lien de réinitialisation.'
        ]);
    }
    
    // Générer un token sécurisé
    $token = bin2hex(random_bytes(32));
    $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));
    
    // Révoquer les anciens tokens
    $stmt = $pdo->prepare('DELETE FROM password_resets WHERE user_id = ?');
    $stmt->execute([$user['id']]);
    
    // Enregistrer le nouveau token
    $stmt = $pdo->prepare('
        INSERT INTO password_resets (user_id, token, expires_at, ip_address, created_at)
        VALUES (?, ?, ?, ?, NOW())
    ');
    $stmt->execute([
        $user['id'],
        $token,
        $expiresAt,
        $_SERVER['REMOTE_ADDR'] ?? null
    ]);
    
    // En production, envoyer l'email ici
    // sendPasswordResetEmail($user['email'], $user['first_name'], $token);
    
    // Log pour le développement
    error_log("Password reset token for {$user['email']}: $token");
    
    sendJsonResponse([
        'message' => 'Si cet email existe, vous recevrez un lien de réinitialisation.',
        // En développement uniquement, retirer en production
        'dev_token' => (getenv('APP_ENV') !== 'production') ? $token : null
    ]);
    
} catch (PDOException $e) {
    error_log('Erreur forgot password: ' . $e->getMessage());
    sendJsonResponse(['error' => 'Erreur lors de la demande'], 500);
}

/**
 * Fonction d'envoi d'email (à implémenter avec PHPMailer ou autre)
 */
function sendPasswordResetEmail($email, $firstName, $token) {
    $resetUrl = getenv('FRONTEND_URL') . "/reset-password?token=$token";
    
    // Exemple avec mail() natif (non recommandé en production)
    // $subject = "Réinitialisation de votre mot de passe - Daba";
    // $message = "Bonjour $firstName,\n\nCliquez ici pour réinitialiser votre mot de passe:\n$resetUrl\n\nCe lien expire dans 1 heure.";
    // $headers = "From: noreply@daba.com";
    // mail($email, $subject, $message, $headers);
}
?>
