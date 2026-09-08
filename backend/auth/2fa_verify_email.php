<?php
/**
 * Vérification du code 2FA par email - Daba
 * Vérifie le code envoyé par email et active le 2FA
 * 
 * @endpoint POST /api/auth/2fa/verify-email
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

// Récupérer les données
$data = getJsonData();

if (empty($data['code'])) {
    sendJsonResponse(['error' => 'Code 2FA requis'], 400);
}

try {
    // Récupérer la configuration 2FA de l'utilisateur
    $stmt = $pdo->prepare('SELECT * FROM two_factor_auth WHERE user_id = ?');
    $stmt->execute([$user['id']]);
    $twoFactor = $stmt->fetch();
    
    if (!$twoFactor || !$twoFactor['email_code']) {
        sendJsonResponse(['error' => 'Aucun code email en attente. Veuillez d\'abord demander un code.'], 404);
    }
    
    // Vérifier si le code a expiré
    if (strtotime($twoFactor['email_code_expires_at']) < time()) {
        sendJsonResponse(['error' => 'Le code a expiré. Veuillez demander un nouveau code.'], 400);
    }
    
    // Vérifier le code
    if (!password_verify($data['code'], $twoFactor['email_code'])) {
        sendJsonResponse(['error' => 'Le code est incorrect. Veuillez vérifier votre email et réessayer.'], 400);
    }
    
    // Activer le 2FA
    $stmt = $pdo->prepare('UPDATE two_factor_auth SET enabled = 1, last_used_at = NOW(), email_code = NULL, email_code_expires_at = NULL WHERE user_id = ?');
    $stmt->execute([$user['id']]);
    
    sendJsonResponse([
        'message' => '2FA activé avec succès par email',
        'enabled' => true
    ]);
    
} catch (PDOException $e) {
    error_log('Erreur 2FA verify email: ' . $e->getMessage());
    sendJsonResponse(['error' => 'Une erreur technique est survenue lors de la vérification du code. Veuillez réessayer dans quelques instants.'], 500);
} catch (Exception $e) {
    error_log('Erreur générale 2FA verify email: ' . $e->getMessage());
    sendJsonResponse(['error' => 'Une erreur inattendue est survenue lors de la vérification du code. Veuillez réessayer.'], 500);
}
