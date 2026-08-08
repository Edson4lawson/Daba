<?php
/**
 * Inscription utilisateur (ADMIN SEULEMENT)
 * Permet uniquement aux admins de créer des comptes staff
 * 
 * @endpoint POST /api/auth/register.php
 * @header Authorization: Bearer {admin_token}
 * @body { "email": "string", "password": "string", "first_name": "string", "last_name": "string", "role_id": "int" }
 */

require_once __DIR__ . '/../config/headers.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../middleware/rate_limit.php';
require_once __DIR__ . '/../utils/password.php';

// ⚠️ PROTECTION: Authentification admin requise
$user = authenticate();

// Vérifier que l'utilisateur est un admin (admin ou super_admin)
requireAdmin($user);

// ⚠️ PROTECTION: Limite à 10 créations par heure par admin
rateLimit('admin_create_user', 10, 3600);

// Vérifier si la requête est de type POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(['error' => 'Méthode non autorisée'], 405);
}

// Récupérer les données de la requête
$data = getJsonData();

// Valider les données d'entrée
$requiredFields = ['email', 'password', 'first_name', 'last_name'];
foreach ($requiredFields as $field) {
    if (empty($data[$field])) {
        sendJsonResponse(['error' => 'Tous les champs sont obligatoires'], 400);
    }
}

// Valider role_id si fourni (pour créer des comptes staff)
$roleId = isset($data['role_id']) ? (int)$data['role_id'] : null;
if ($roleId !== null) {
    // Vérifier que le rôle existe
    $stmt = $pdo->prepare('SELECT id, name FROM roles WHERE id = ?');
    $stmt->execute([$roleId]);
    $role = $stmt->fetch();
    if (!$role) {
        sendJsonResponse(['error' => 'Rôle invalide'], 400);
    }
} else {
    // Par défaut: rôle customer
    $stmt = $pdo->prepare('SELECT id FROM roles WHERE name = "customer"');
    $stmt->execute();
    $role = $stmt->fetch();
    $roleId = $role['id'];
}

// Nettoyer l'email
$email = strtolower(trim($data['email']));

// Valider l'email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendJsonResponse(['error' => 'Format d\'email invalide'], 400);
}

// ⚠️ SÉCURITÉ: Valider la force du mot de passe (Politique stricte OWASP)

$passwordError = validatePasswordStrength($data['password']);
if ($passwordError) {
    sendJsonResponse(['error' => $passwordError], 400);
}

// Vérifier si l'utilisateur existe déjà
$stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
$stmt->execute([$email]);
if ($stmt->fetch()) {
    sendJsonResponse(['error' => 'Cette adresse email est déjà associée à un compte. Veuillez vous connecter ou utiliser une autre adresse email.'], 409);
}

// Hacher le mot de passe avec Argon2id (algorithme le plus sécurisé)
// Argon2id est recommandé par OWASP et NIST pour le hash de mots de passe
$hashedPassword = hashPassword($data['password']);

$ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);

try {
    $pdo->beginTransaction();

    // Insérer le nouvel utilisateur avec le rôle spécifié
    $stmt = $pdo->prepare('INSERT INTO users (email, password, first_name, last_name, address, phone, role_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())');
    $stmt->execute([
        $email,
        $hashedPassword,
        trim($data['first_name']),
        trim($data['last_name']),
        $data['address'] ?? null,
        $data['phone'] ?? null,
        $roleId
    ]);
    
    $userId = $pdo->lastInsertId();
    
    // Générer l'Access Token (15 minutes)
    $accessToken = bin2hex(random_bytes(32));
    $accessExpiresAt = date('Y-m-d H:i:s', strtotime('+15 minutes'));
    
    $stmt = $pdo->prepare('UPDATE users SET token = ?, token_expires_at = ? WHERE id = ?');
    $stmt->execute([$accessToken, $accessExpiresAt, $userId]);
    
    // Générer le Refresh Token (30 jours)
    $refreshToken = bin2hex(random_bytes(64));
    $refreshExpiresAt = date('Y-m-d H:i:s', strtotime('+30 days'));
    
    $stmt = $pdo->prepare('
        INSERT INTO refresh_tokens (user_id, token, expires_at, ip_address, user_agent, created_at) 
        VALUES (?, ?, ?, ?, ?, NOW())
    ');
    $stmt->execute([$userId, $refreshToken, $refreshExpiresAt, $ipAddress, $userAgent]);
    
    // Générer un token de vérification email
    $emailVerifyToken = bin2hex(random_bytes(32));
    $emailVerifyExpires = date('Y-m-d H:i:s', strtotime('+24 hours'));
    
    $stmt = $pdo->prepare('
        INSERT INTO email_verifications (user_id, token, expires_at, created_at) 
        VALUES (?, ?, ?, NOW())
    ');
    $stmt->execute([$userId, $emailVerifyToken, $emailVerifyExpires]);
    
    $pdo->commit();
    
    // En production, envoyer l'email de vérification
    // sendVerificationEmail($email, $data['first_name'], $emailVerifyToken);
    
    // Log pour développement
    error_log("Email verification token for $email: $emailVerifyToken");
    
    // Préparer les données utilisateur
    $userData = [
        'id' => $userId,
        'email' => $email,
        'first_name' => trim($data['first_name']),
        'last_name' => trim($data['last_name']),
        'phone' => trim($data['phone'] ?? ''),
        'address' => trim($data['address'] ?? ''),
        'role' => 'customer',
        'email_verified' => false
    ];
    
    // Retourner les tokens
    sendJsonResponse([
        'message' => 'Inscription réussie. Un email de vérification a été envoyé.',
        'access_token' => $accessToken,
        'refresh_token' => $refreshToken,
        'token_type' => 'Bearer',
        'expires_in' => 900,
        'user' => $userData,
        // En développement uniquement
        'dev_email_token' => (getenv('APP_ENV') !== 'production') ? $emailVerifyToken : null
    ], 201);
    
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Erreur lors de l\'inscription: ' . $e->getMessage());
    sendJsonResponse(['error' => 'Une erreur technique est survenue lors de la création de votre compte. Veuillez réessayer dans quelques instants. Si le problème persiste, contactez notre support.'], 500);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Erreur générale lors de l\'inscription: ' . $e->getMessage());
    sendJsonResponse(['error' => 'Une erreur inattendue est survenue lors de l\'inscription. Veuillez réessayer.'], 500);
}

/**
 * Envoi de l'email de vérification (à implémenter)
 */
function sendVerificationEmail($email, $firstName, $token) {
    $verifyUrl = getenv('FRONTEND_URL') . "/verify-email?token=$token";
    
    // Implémenter avec PHPMailer, SendGrid, etc.
    // $subject = "Vérifiez votre email - Bloom Chloé";
    // ...
}
?>

