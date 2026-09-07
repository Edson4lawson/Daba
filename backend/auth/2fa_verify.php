<?php
/**
 * Vérification 2FA (Two-Factor Authentication) - Daba
 * Vérifie le code TOTP et active le 2FA
 * 
 * @endpoint POST /api/auth/2fa/verify
 */

require_once __DIR__ . '/../config/headers.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../utils/totp.php';

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
    
    if (!$twoFactor) {
        sendJsonResponse(['error' => 'Configuration 2FA non trouvée'], 404);
    }
    
    $code = preg_replace('/[^0-9]/', '', $data['code']);
    
    // Vérifier si c'est un code de secours
    if (strlen($code) === 8) {
        $backupCodes = json_decode($twoFactor['backup_codes'], true) ?: [];
        
        if (in_array(strtoupper($code), $backupCodes)) {
            // Retirer le code utilisé
            $backupCodes = array_diff($backupCodes, [strtoupper($code)]);
            
            $stmt = $pdo->prepare('UPDATE two_factor_auth SET backup_codes = ? WHERE user_id = ?');
            $stmt->execute([json_encode(array_values($backupCodes)), $user['id']]);
            
            // Activer le 2FA
            $stmt = $pdo->prepare('UPDATE two_factor_auth SET enabled = 1, last_used_at = NOW() WHERE user_id = ?');
            $stmt->execute([$user['id']]);
            
            sendJsonResponse([
                'message' => '2FA activé avec succès (code de secours utilisé)',
                'remaining_codes' => count($backupCodes)
            ]);
        }
        
        sendJsonResponse(['error' => 'Code de secours invalide'], 400);
    }
    
    // Vérifier le code TOTP
    if (!verifyTOTP($twoFactor['secret'], $code)) {
        sendJsonResponse(['error' => 'Le code de double authentification est incorrect. Veuillez vérifier votre application d\'authentification et réessayer.'], 400);
    }
    
    // Activer le 2FA
    $stmt = $pdo->prepare('UPDATE two_factor_auth SET enabled = 1, last_used_at = NOW() WHERE user_id = ?');
    $stmt->execute([$user['id']]);
    
    sendJsonResponse([
        'message' => '2FA activé avec succès',
        'enabled' => true
    ]);
    
} catch (PDOException $e) {
    error_log('Erreur 2FA verify: ' . $e->getMessage());
    sendJsonResponse(['error' => 'Une erreur technique est survenue lors de la vérification du code 2FA. Veuillez réessayer dans quelques instants.'], 500);
} catch (Exception $e) {
    error_log('Erreur générale 2FA verify: ' . $e->getMessage());
    sendJsonResponse(['error' => 'Une erreur inattendue est survenue lors de la vérification du code 2FA. Veuillez réessayer.'], 500);
}


