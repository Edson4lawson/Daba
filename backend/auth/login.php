<?php
/**
 * Authentification utilisateur avec Access Token + Refresh Token
 * 
 * @endpoint POST /api/auth/login.php
 * @body { "email": "string", "password": "string" }
 * @return { "access_token": "string", "refresh_token": "string", "expires_in": int, "user": object }
 */

require_once __DIR__ . '/../config/headers.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../middleware/rate_limit.php';
require_once __DIR__ . '/../middleware/two_factor.php';
require_once __DIR__ . '/../middleware/captcha.php';

// ⚠️ PROTECTION BRUTE-FORCE: Limite à 5 tentatives par 5 minutes
loginRateLimit();

// ⚠️ PROTECTION CAPTCHA: Vérifier si CAPTCHA requis après échecs
requireCaptcha('login', 0.5);

// Vérifier si la requête est de type POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(['error' => 'Méthode non autorisée'], 405);
}

// Récupérer les données de la requête
$data = getJsonData();

// Valider les données d'entrée
if (empty($data['email']) || empty($data['password'])) {
    sendJsonResponse(['error' => 'Email et mot de passe requis'], 400);
}

$email = strtolower(trim($data['email']));
$ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);

try {
    // Récupérer l'utilisateur par email avec jointure sur roles pour le nom du rôle
    $stmt = $pdo->prepare('
        SELECT u.*, r.name as role_name
        FROM users u
        LEFT JOIN roles r ON u.role_id = r.id
        WHERE u.email = ?
    ');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    // Vérifier si le compte est verrouillé
    if ($user && isset($user['locked_until']) && $user['locked_until'] > date('Y-m-d H:i:s')) {
        logLoginAttempt($pdo, $user['id'] ?? null, $email, $ipAddress, $userAgent, 'blocked', 'Account locked');
        sendJsonResponse(['error' => 'Compte temporairement verrouillé. Réessayez plus tard.'], 423);
    }
    
    // Vérifier si l'utilisateur existe et si le mot de passe est correct
    if (!$user || !password_verify($data['password'], $user['password'])) {
        // Incrémenter les tentatives échouées
        if ($user) {
            $attempts = ($user['failed_login_attempts'] ?? 0) + 1;
            $lockUntil = $attempts >= 5 ? date('Y-m-d H:i:s', strtotime('+15 minutes')) : null;
            
            $stmt = $pdo->prepare('UPDATE users SET failed_login_attempts = ?, locked_until = ? WHERE id = ?');
            $stmt->execute([$attempts, $lockUntil, $user['id']]);
        }
        
        logLoginAttempt($pdo, $user['id'] ?? null, $email, $ipAddress, $userAgent, 'failed', 'Invalid credentials');
        
        // Incrémenter le compteur CAPTCHA
        incrementCaptchaFailure('login');
        
        // Message d'erreur clair
        $errorMessage = 'Email ou mot de passe incorrect';
        if ($user && $attempts >= 4) {
            $errorMessage = 'Email ou mot de passe incorrect. Attention: après une tentative échouée, votre compte sera temporairement verrouillé.';
        }
        
        sendJsonResponse([
            'error' => $errorMessage,
            'require_captcha' => requiresCaptcha('login')
        ], 401);
    }
    
    // ⚠️ SÉCURITÉ: Vérifier si le 2FA est requis pour cet utilisateur
    $stmt = $pdo->prepare('SELECT enabled FROM two_factor_auth WHERE user_id = ?');
    $stmt->execute([$user['id']]);
    $twoFactor = $stmt->fetch();

    $requiresTwoFactor = false;

    // Les admins doivent toujours avoir le 2FA (désactivé en développement)
    if (in_array($user['role'], ['admin', 'super_admin']) && getenv('APP_ENV') === 'production') {
        if (!$twoFactor || !$twoFactor['enabled']) {
            logLoginAttempt($pdo, $user['id'], $email, $ipAddress, $userAgent, 'blocked', '2FA not enabled for admin');
            sendJsonResponse([
                'error' => '2FA obligatoire pour les administrateurs. Configurez-le d\'abord.',
                'require_2fa_setup' => true
            ], 403);
        }
        $requiresTwoFactor = true;
    } elseif ($twoFactor && $twoFactor['enabled']) {
        // 2FA optionnel pour staff si configuré
        $requiresTwoFactor = true;
    }
    // Pour les rôles staff (commercial, magasinier, comptable), 2FA non obligatoire
    
    // Si 2FA requis et code non fourni
    if ($requiresTwoFactor && empty($data['two_factor_code'])) {
        // Créer une session temporaire pour la vérification 2FA
        $tempSessionToken = createTwoFactorSession($user['id']);
        
        sendJsonResponse([
            'error' => 'Veuillez entrer le code de double authentification (2FA) généré par votre application d\'authentification.',
            'require_2fa_verification' => true,
            'temp_session_token' => $tempSessionToken
        ], 403);
    }
    
    // Si 2FA requis et code fourni, le vérifier
    if ($requiresTwoFactor && !empty($data['two_factor_code'])) {
        if (!validateTwoFactorCode($user['id'], $data['two_factor_code'])) {
            logLoginAttempt($pdo, $user['id'], $email, $ipAddress, $userAgent, 'failed', 'Invalid 2FA code');
            sendJsonResponse(['error' => 'Le code de double authentification est incorrect. Veuillez vérifier et réessayer.'], 401);
        }
        
        // Marquer la session comme vérifiée si token fourni
        if (!empty($data['temp_session_token'])) {
            verifyTwoFactorSession($user['id'], $data['temp_session_token']);
        }
        
        // Ajouter l'appareil comme de confiance si demandé
        if (!empty($data['remember_device'])) {
            addTrustedDevice($user['id'], true);
        }
    }
    
    $pdo->beginTransaction();
    
    // Réinitialiser les tentatives échouées
    $stmt = $pdo->prepare('UPDATE users SET failed_login_attempts = 0, locked_until = NULL, last_login_at = NOW(), last_login_ip = ? WHERE id = ?');
    $stmt->execute([$ipAddress, $user['id']]);
    
    // Générer l'Access Token (courte durée: 15 minutes)
    $accessToken = bin2hex(random_bytes(32));
    $accessExpiresAt = date('Y-m-d H:i:s', strtotime('+15 minutes'));
    
    $stmt = $pdo->prepare('UPDATE users SET token = ?, token_expires_at = ? WHERE id = ?');
    $stmt->execute([$accessToken, $accessExpiresAt, $user['id']]);
    
    // Générer le Refresh Token (longue durée: 30 jours)
    $refreshToken = bin2hex(random_bytes(64));
    $refreshExpiresAt = date('Y-m-d H:i:s', strtotime('+30 days'));
    
    $stmt = $pdo->prepare('
        INSERT INTO refresh_tokens (user_id, token, expires_at, ip_address, user_agent, created_at) 
        VALUES (?, ?, ?, ?, ?, NOW())
    ');
    $stmt->execute([$user['id'], $refreshToken, $refreshExpiresAt, $ipAddress, $userAgent]);
    
    $pdo->commit();
    
    // Logger la connexion réussie
    logLoginAttempt($pdo, $user['id'], $email, $ipAddress, $userAgent, 'success', null);
    
    // Réinitialiser le compteur CAPTCHA
    resetCaptchaFailure('login');
    
    // Préparer les données utilisateur (sans informations sensibles)
    $userData = [
        'id' => $user['id'],
        'email' => $user['email'],
        'first_name' => $user['first_name'] ?? null,
        'last_name' => $user['last_name'] ?? null,
        'phone' => $user['phone'] ?? null,
        'address' => $user['address'] ?? null,
        'role' => $user['role_name'] ?? $user['role'] ?? 'customer'
    ];
    
    // Retourner les tokens
    sendJsonResponse([
        'message' => 'Connexion réussie',
        'access_token' => $accessToken,
        'refresh_token' => $refreshToken,
        'token_type' => 'Bearer',
        'expires_in' => 900, // 15 minutes en secondes
        'user' => $userData
    ]);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Erreur lors de la connexion: ' . $e->getMessage());
    sendJsonResponse(['error' => 'Une erreur est survenue lors de l\'authentification. Veuillez réessayer.'], 500);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Erreur générale lors de la connexion: ' . $e->getMessage());
    sendJsonResponse(['error' => 'Une erreur technique est survenue lors de la connexion. Veuillez réessayer dans quelques instants. Si le problème persiste, contactez notre support.'], 500);
}

/**
 * Log une tentative de connexion
 */
function logLoginAttempt(PDO $pdo, ?int $userId, string $email, string $ipAddress, string $userAgent, string $status, ?string $reason): void {
    try {
        $stmt = $pdo->prepare('
            INSERT INTO login_logs (user_id, email, ip_address, user_agent, status, failure_reason, created_at) 
          VALUES (?, ?, ?, ?, ?, ?, NOW())
        ');
        $stmt->execute([$userId, $email, $ipAddress, $userAgent, $status, $reason]);
   } catch (PDOException $e) {
          error_log('Failed to log login attempt: ' . $e->getMessage());
    }
}
?>
