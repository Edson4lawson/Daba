<?php
/**
 * Envoi de code 2FA par email - Daba
 * Envoie un code à 6 chiffres par email comme alternative au QR code
 * 
 * @endpoint POST /api/auth/2fa/email
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

try {
    // Vérifier si l'utilisateur a déjà une configuration 2FA
    $stmt = $pdo->prepare('SELECT * FROM two_factor_auth WHERE user_id = ?');
    $stmt->execute([$user['id']]);
    $twoFactor = $stmt->fetch();
    
    // Générer un code à 6 chiffres
    $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    
    // Hasher le code pour le stockage
    $hashedCode = password_hash($code, PASSWORD_DEFAULT);
    
    // Stocker le code temporairement (expire dans 10 minutes)
    $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));
    
    if ($twoFactor) {
        // Mettre à jour la configuration existante
        $stmt = $pdo->prepare('
            UPDATE two_factor_auth 
            SET email_code = ?, email_code_expires_at = ?, enabled = 0
            WHERE user_id = ?
        ');
        $stmt->execute([$hashedCode, $expiresAt, $user['id']]);
    } else {
        // Créer une nouvelle configuration
        $stmt = $pdo->prepare('
            INSERT INTO two_factor_auth (user_id, email_code, email_code_expires_at, enabled)
            VALUES (?, ?, ?, 0)
        ');
        $stmt->execute([$user['id'], $hashedCode, $expiresAt]);
    }
    
    // Envoyer l'email avec le code
    $emailSent = send2FAEmail($user['email'], $code);
    
    if (!$emailSent) {
        sendJsonResponse(['error' => 'Erreur lors de l\'envoi de l\'email. Veuillez réessayer.'], 500);
    }
    
    sendJsonResponse([
        'message' => 'Code envoyé par email',
        'email' => maskEmail($user['email']),
        'expires_in' => 600 // 10 minutes en secondes
    ]);
    
} catch (PDOException $e) {
    error_log('Erreur 2FA email: ' . $e->getMessage());
    sendJsonResponse(['error' => 'Une erreur technique est survenue lors de l\'envoi du code par email. Veuillez réessayer dans quelques instants.'], 500);
} catch (Exception $e) {
    error_log('Erreur générale 2FA email: ' . $e->getMessage());
    sendJsonResponse(['error' => 'Une erreur inattendue est survenue lors de l\'envoi du code par email. Veuillez réessayer.'], 500);
}

/**
 * Envoie un email avec le code 2FA
 */
function send2FAEmail($toEmail, $code) {
    $apiKey = getenv('SENDGRID_API_KEY');
    
    // Si SendGrid n'est pas configuré, utiliser mail() PHP
    if (empty($apiKey)) {
        $subject = 'Code de vérification 2FA - Daba';
        $message = "
            <html>
            <head>
                <title>Code de vérification 2FA</title>
            </head>
            <body style='font-family: Arial, sans-serif;'>
                <h2>Code de vérification 2FA</h2>
                <p>Votre code de vérification pour Daba est :</p>
                <h1 style='color: #FF6B35; font-size: 48px; letter-spacing: 5px;'>{$code}</h1>
                <p>Ce code expire dans 10 minutes.</p>
                <p>Si vous n'avez pas demandé ce code, ignorez cet email.</p>
                <p style='color: #666; font-size: 12px;'>Ceci est un message automatique, ne répondez pas.</p>
            </body>
            </html>
        ";
        
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: Daba <noreply@daba.tg>\r\n";
        
        return mail($toEmail, $subject, $message, $headers);
    }
    
    // Utiliser SendGrid si configuré
    $url = 'https://api.sendgrid.com/v3/mail/send';
    
    $data = [
        'personalizations' => [
            [
                'to' => [
                    ['email' => $toEmail]
                ],
                'subject' => 'Code de vérification 2FA - Daba'
            ]
        ],
        'from' => [
            'email' => getenv('SENDGRID_FROM_EMAIL') ?: 'noreply@daba.tg',
            'name' => getenv('SENDGRID_FROM_NAME') ?: 'Daba'
        ],
        'content' => [
            [
                'type' => 'text/html',
                'value' => "
                    <html>
                    <head>
                        <title>Code de vérification 2FA</title>
                    </head>
                    <body style='font-family: Arial, sans-serif;'>
                        <h2>Code de vérification 2FA</h2>
                        <p>Votre code de vérification pour Daba est :</p>
                        <h1 style='color: #FF6B35; font-size: 48px; letter-spacing: 5px;'>{$code}</h1>
                        <p>Ce code expire dans 10 minutes.</p>
                        <p>Si vous n'avez pas demandé ce code, ignorez cet email.</p>
                        <p style='color: #666; font-size: 12px;'>Ceci est un message automatique, ne répondez pas.</p>
                    </body>
                    </html>
                "
            ]
        ]
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return $httpCode === 202;
}

/**
 * Masque l'email pour la confidentialité
 */
function maskEmail($email) {
    $parts = explode('@', $email);
    $name = $parts[0];
    $domain = $parts[1];
    
    $maskedName = substr($name, 0, 2) . str_repeat('*', strlen($name) - 2);
    
    return $maskedName . '@' . $domain;
}
