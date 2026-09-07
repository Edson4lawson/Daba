<?php
/**
 * Endpoint de rafraîchissement des tokens
 * Permet de renouveler l'access token expiré avec un refresh token valide
 * 
 * @endpoint POST /api/auth/refresh.php
 * @body { "refresh_token": "string" }
 * @return { "access_token": "string", "refresh_token": "string", "expires_in": int }
 */

require_once __DIR__ . '/../config/headers.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../middleware/rate_limit.php';

// Rate limiting pour le refresh (plus permissif que le login)
rateLimit('token_refresh', 20, 300); // 20 requêtes par 5 minutes

// Vérifier la méthode HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(['error' => 'Méthode non autorisée'], 405);
}

// Récupérer les données
$data = getJsonData();
$refreshToken = $data['refresh_token'] ?? '';

if (empty($refreshToken)) {
    sendJsonResponse(['error' => 'Refresh token requis'], 400);
}

try {
    // Vérifier le refresh token dans la base
    $stmt = $pdo->prepare('
        SELECT rt.*, u.id as user_id, u.email, u.first_name, u.last_name, u.phone, u.address, u.role 
        FROM refresh_tokens rt
        INNER JOIN users u ON rt.user_id = u.id
        WHERE rt.token = ? 
        AND rt.expires_at > NOW() 
        AND rt.revoked = 0
    ');
    $stmt->execute([$refreshToken]);
    $tokenData = $stmt->fetch();
    
    if (!$tokenData) {
        // Log tentative suspecte
        error_log("Invalid refresh token attempt from IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
        sendJsonResponse(['error' => 'Refresh token invalide ou expiré'], 401);
    }
    
    $pdo->beginTransaction();
    
    // Révoquer l'ancien refresh token (rotation obligatoire)
    $stmt = $pdo->prepare('UPDATE refresh_tokens SET revoked = 1 WHERE id = ?');
    $stmt->execute([$tokenData['id']]);
    
    // Générer un nouvel access token (15 minutes)
    $newAccessToken = bin2hex(random_bytes(32));
    $accessExpiresAt = date('Y-m-d H:i:s', strtotime('+15 minutes'));
    
    // Mettre à jour l'access token de l'utilisateur
    $stmt = $pdo->prepare('UPDATE users SET token = ?, token_expires_at = ? WHERE id = ?');
    $stmt->execute([$newAccessToken, $accessExpiresAt, $tokenData['user_id']]);
    
    // Générer un nouveau refresh token (30 jours)
    $newRefreshToken = bin2hex(random_bytes(64));
    $refreshExpiresAt = date('Y-m-d H:i:s', strtotime('+30 days'));
    
    $stmt = $pdo->prepare('
        INSERT INTO refresh_tokens (user_id, token, expires_at, ip_address, user_agent, created_at) 
        VALUES (?, ?, ?, ?, ?, NOW())
    ');
    $stmt->execute([
        $tokenData['user_id'],
        $newRefreshToken,
        $refreshExpiresAt,
        $_SERVER['REMOTE_ADDR'] ?? null,
        substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255)
    ]);
    
    $pdo->commit();
    
    // Retourner les nouveaux tokens
    sendJsonResponse([
        'access_token' => $newAccessToken,
        'refresh_token' => $newRefreshToken,
        'expires_in' => 900, // 15 minutes en secondes
        'token_type' => 'Bearer',
        'user' => [
            'id' => $tokenData['user_id'],
            'email' => $tokenData['email'],
            'first_name' => $tokenData['first_name'],
            'last_name' => $tokenData['last_name'],
            'phone' => $tokenData['phone'],
            'address' => $tokenData['address'],
            'role' => $tokenData['role']
        ]
    ]);
    
} catch (Exception $e) {
    if ($pdo && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('DABA ERROR [Refresh]: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    error_log('DABA ERROR [Refresh]: ' . $e->getMessage());
    sendJsonResponse(['error' => 'Erreur lors du rafraîchissement de la session.'], 500);
}
?>
