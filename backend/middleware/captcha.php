<?php
/**
 * Middleware CAPTCHA - Daba
 * Implémente un CAPTCHA après plusieurs tentatives échouées
 * Utilise reCAPTCHA v3 pour une expérience utilisateur fluide
 *
 * @author Security Audit
 * @version 1.0.0
 */

/**
 * Vérifie si un CAPTCHA est requis pour cette IP/endpoint
 * 
 * @param string $endpoint Identifiant de l'endpoint
 * @return bool True si CAPTCHA requis
 */
function requiresCaptcha($endpoint) {
    $ip = getClientIP();
    $key = "captcha_require:{$endpoint}:{$ip}";

    $rateLimitDir = sys_get_temp_dir() . '/daba_rate_limit';
    $file = $rateLimitDir . '/' . md5($key) . '.json';
    
    if (!file_exists($file)) {
        return false;
    }
    
    $data = json_decode(file_get_contents($file), true);
    
    // CAPTCHA requis après 3 échecs
    return isset($data['failed_attempts']) && $data['failed_attempts'] >= 3;
}

/**
 * Incrémente le compteur d'échecs pour CAPTCHA
 * 
 * @param string $endpoint Identifiant de l'endpoint
 */
function incrementCaptchaFailure($endpoint) {
    $ip = getClientIP();
    $key = "captcha_require:{$endpoint}:{$ip}";

    $rateLimitDir = sys_get_temp_dir() . '/daba_rate_limit';
    if (!is_dir($rateLimitDir)) {
        mkdir($rateLimitDir, 0755, true);
    }

    $file = $rateLimitDir . '/' . md5($key) . '.json';
    
    $data = ['failed_attempts' => 0, 'last_attempt' => time()];
    if (file_exists($file)) {
        $existing = json_decode(file_get_contents($file), true);
        if ($existing) {
            $data = array_merge($data, $existing);
        }
    }
    
    // Réinitialiser après 1 heure
    if (time() - $data['last_attempt'] > 3600) {
        $data['failed_attempts'] = 0;
    }
    
    $data['failed_attempts']++;
    $data['last_attempt'] = time();
    
    file_put_contents($file, json_encode($data), LOCK_EX);
}

/**
 * Réinitialise le compteur CAPTCHA après succès
 * 
 * @param string $endpoint Identifiant de l'endpoint
 */
function resetCaptchaFailure($endpoint) {
    $ip = getClientIP();
    $key = "captcha_require:{$endpoint}:{$ip}";

    $rateLimitDir = sys_get_temp_dir() . '/daba_rate_limit';
    $file = $rateLimitDir . '/' . md5($key) . '.json';
    
    if (file_exists($file)) {
        unlink($file);
    }
}

/**
 * Vérifie le token reCAPTCHA
 * 
 * @param string $token Token reCAPTCHA
 * @param float $score Score minimum (0.0 à 1.0)
 * @return bool True si valide
 */
function verifyRecaptcha($token, $score = 0.5) {
    $secretKey = getenv('RECAPTCHA_SECRET_KEY');
    
    if (!$secretKey) {
        // En développement, accepter si pas configuré
        return getenv('APP_ENV') !== 'production';
    }
    
    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $data = [
        'secret' => $secretKey,
        'response' => $token,
        'remoteip' => getClientIP()
    ];
    
    $options = [
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/x-www-form-urlencoded',
            'content' => http_build_query($data),
            'timeout' => 10
        ]
    ];
    
    $context = stream_context_create($options);
    $response = file_get_contents($url, false, $context);
    
    if ($response === false) {
        error_log('reCAPTCHA verification failed: could not reach Google');
        return false;
    }
    
    $result = json_decode($response, true);
    
    if (!$result['success']) {
        error_log('reCAPTCHA verification failed: ' . json_encode($result['error-codes'] ?? []));
        return false;
    }
    
    // Vérifier le score pour reCAPTCHA v3
    if (isset($result['score']) && $result['score'] < $score) {
        error_log('reCAPTCHA score too low: ' . $result['score']);
        return false;
    }
    
    return true;
}

/**
 * Force la vérification CAPTCHA
 * 
 * @param string $endpoint Identifiant de l'endpoint
 * @param float $minScore Score minimum
 */
function requireCaptcha($endpoint, $minScore = 0.5) {
    if (!requiresCaptcha($endpoint)) {
        return;
    }
    
    $data = getJsonData();
    $token = $data['recaptcha_token'] ?? null;
    
    if (!$token) {
        http_response_code(400);
        echo json_encode([
            'error' => 'CAPTCHA requis',
            'require_captcha' => true
        ]);
        exit();
    }
    
    if (!verifyRecaptcha($token, $minScore)) {
        http_response_code(400);
        echo json_encode([
            'error' => 'CAPTCHA invalide',
            'require_captcha' => true
        ]);
        exit();
    }
}
