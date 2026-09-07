<?php
/**
 * Désactivation 2FA (Two-Factor Authentication) - Daba
 * Désactive le 2FA pour un utilisateur (nécessite confirmation)
 * 
 * @endpoint POST /api/auth/2fa/disable
 */

require_once __DIR__ . '/../config/headers.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../middleware/auth.php';

// Vérifier la méthode
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(['error' => 'Méthode de requête non autorisée. Veuillez utiliser la méthode POST.'], 405);
}

// Authentifier l'utilisateur
$user = authenticate();

// Récupérer les données
$data = getJsonData();

if (empty($data['password'])) {
    sendJsonResponse(['error' => 'Veuillez entrer votre mot de passe pour confirmer la désactivation de la double authentification.'], 400);
}

try {
    // Vérifier le mot de passe
    $stmt = $pdo->prepare('SELECT password FROM users WHERE id = ?');
    $stmt->execute([$user['id']]);
    $userData = $stmt->fetch();
    
    if (!$userData || !password_verify($data['password'], $userData['password'])) {
        sendJsonResponse(['error' => 'Mot de passe incorrect'], 401);
    }
    
    // Vérifier si l'utilisateur est admin (les admins ne peuvent pas désactiver le 2FA)
    if ($user['role'] === 'admin' || $user['role'] === 'super_admin') {
        sendJsonResponse(['error' => 'Les administrateurs ne peuvent pas désactiver le 2FA'], 403);
    }
    
    // Désactiver le 2FA
    $stmt = $pdo->prepare('UPDATE two_factor_auth SET enabled = 0 WHERE user_id = ?');
    $stmt->execute([$user['id']]);
    
    // Supprimer les appareils de confiance
    $stmt = $pdo->prepare('DELETE FROM trusted_devices WHERE user_id = ?');
    $stmt->execute([$user['id']]);
    
    sendJsonResponse([
        'message' => '2FA désactivé avec succès',
        'enabled' => false
    ]);
    
} catch (PDOException $e) {
    error_log('Erreur 2FA disable: ' . $e->getMessage());
    sendJsonResponse(['error' => 'Une erreur technique est survenue lors de la désactivation de la double authentification. Veuillez réessayer dans quelques instants.'], 500);
} catch (Exception $e) {
    error_log('Erreur générale 2FA disable: ' . $e->getMessage());
    sendJsonResponse(['error' => 'Une erreur inattendue est survenue lors de la désactivation de la double authentification. Veuillez réessayer.'], 500);
}
