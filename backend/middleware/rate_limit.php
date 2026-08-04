<?php
/**
 * Middleware de Rate Limiting Avancé pour Daba
 * Protège contre les attaques brute-force et DDoS applicatif
 *
 * @author Security Audit
 * @version 2.0.0 - Production Ready
 */

/**
 * Applique une limite de requêtes par IP et endpoint avec protection avancée
 *
 * @param string $endpoint Identifiant de l'endpoint
 * @param int $maxAttempts Nombre maximum de tentatives
 * @param int $windowSeconds Fenêtre de temps en secondes
 */
function rateLimit($endpoint, $maxAttempts = 60, $windowSeconds = 60) {
    // Désactiver en développement
    if (getenv('APP_ENV') !== 'production') {
        return;
    }

    $ip = getClientIP();
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    $key = "rate_limit:{$endpoint}:{$ip}";
    
    // Répertoire pour stocker les données de rate limiting
    $rateLimitDir = sys_get_temp_dir() . '/daba_rate_limit';
    if (!is_dir($rateLimitDir)) {
        mkdir($rateLimitDir, 0755, true);
    }
    
    $file = $rateLimitDir . '/' . md5($key) . '.json';
    
    // Charger les données existantes
    $data = [
        'attempts' => 0,
        'reset' => time() + $windowSeconds,
        'blocked_until' => 0,
        'first_attempt' => time(),
        'user_agent_hash' => md5($userAgent)
    ];
    
    if (file_exists($file)) {
        $existingData = json_decode(file_get_contents($file), true);
        if ($existingData) {
            $data = array_merge($data, $existingData);
        }
    }
    
    // Détection de changement d'user-agent (possible attaque)
    if (isset($data['user_agent_hash']) && $data['user_agent_hash'] !== md5($userAgent)) {
        logSuspiciousActivity($ip, $endpoint, 'user_agent_changed');
        // Bloquer immédiatement si l'user-agent change
        $data['blocked_until'] = time() + 3600; // 1 heure
        file_put_contents($file, json_encode($data), LOCK_EX);
        
        http_response_code(403);
        echo json_encode(['error' => 'Accès refusé pour raisons de sécurité']);
        exit();
    }
    
    // Vérifier si bloqué
    if ($data['blocked_until'] > time()) {
        $retryAfter = $data['blocked_until'] - time();
        header("Retry-After: $retryAfter");
        header('X-RateLimit-Limit: ' . $maxAttempts);
        header('X-RateLimit-Remaining: 0');
        header('X-RateLimit-Reset: ' . $data['blocked_until']);
        
        http_response_code(429);
        echo json_encode([
            'error' => 'Trop de requêtes. Veuillez réessayer dans ' . formatDuration($retryAfter) . '.',
            'retry_after' => $retryAfter
        ]);
        exit();
    }
    
    // Réinitialiser si la fenêtre est expirée
    if (time() > $data['reset']) {
        $data = [
            'attempts' => 0,
            'reset' => time() + $windowSeconds,
            'blocked_until' => 0,
            'first_attempt' => time(),
            'user_agent_hash' => md5($userAgent)
        ];
    }
    
    // Vérifier la limite
    if ($data['attempts'] >= $maxAttempts) {
        // Bloquer pour une durée progressive avec backoff exponentiel
        $exponent = floor(log($data['attempts'] / $maxAttempts + 1, 2));
        $blockDuration = min($windowSeconds * pow(2, $exponent), 7200); // Max 2 heures
        $data['blocked_until'] = time() + $blockDuration;
        file_put_contents($file, json_encode($data), LOCK_EX);
        
        $retryAfter = $blockDuration;
        header("Retry-After: $retryAfter");
        header('X-RateLimit-Limit: ' . $maxAttempts);
        header('X-RateLimit-Remaining: 0');
        header('X-RateLimit-Reset: ' . $data['blocked_until']);
        
        // Logger la tentative suspecte
        logSuspiciousActivity($ip, $endpoint, 'rate_limit_exceeded');
        
        http_response_code(429);
        echo json_encode([
            'error' => 'Trop de requêtes. Veuillez réessayer dans ' . formatDuration($retryAfter) . '.',
            'retry_after' => $retryAfter
        ]);
        exit();
    }
    
    // Incrémenter le compteur
    $data['attempts']++;
    file_put_contents($file, json_encode($data), LOCK_EX);
    
    // Ajouter les headers de rate limiting
    header('X-RateLimit-Limit: ' . $maxAttempts);
    header('X-RateLimit-Remaining: ' . max(0, $maxAttempts - $data['attempts']));
    header('X-RateLimit-Reset: ' . $data['reset']);
}

/**
 * Rate limiting strict pour le login
 * 5 tentatives par 5 minutes, puis blocage progressif
 */
function loginRateLimit() {
    rateLimit('login', 5, 300);
}

/**
 * Rate limiting pour l'inscription
 * 3 inscriptions par heure par IP
 */
function registerRateLimit() {
    rateLimit('register', 3, 3600);
}

/**
 * Rate limiting pour les requêtes API générales
 * 100 requêtes par minute
 */
function apiRateLimit() {
    rateLimit('api', 100, 60);
}

/**
 * Rate limiting pour les requêtes de paiement
 * 10 tentatives par heure
 */
function paymentRateLimit() {
    rateLimit('payment', 10, 3600);
}

/**
 * Obtient l'IP réelle du client (gère les proxies)
 */
function getClientIP() {
    $headers = [
        'HTTP_CF_CONNECTING_IP',     // Cloudflare
        'HTTP_X_FORWARDED_FOR',      // Proxy standard
        'HTTP_X_REAL_IP',            // Nginx
        'HTTP_CLIENT_IP',            // Autres proxies
        'REMOTE_ADDR'                // Direct
    ];
    
    foreach ($headers as $header) {
        if (!empty($_SERVER[$header])) {
            $ips = explode(',', $_SERVER[$header]);
            $ip = trim($ips[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/**
 * Formate une durée en secondes en texte lisible
 */
function formatDuration($seconds) {
    if ($seconds < 60) {
        return $seconds . ' seconde' . ($seconds > 1 ? 's' : '');
    } elseif ($seconds < 3600) {
        $minutes = ceil($seconds / 60);
        return $minutes . ' minute' . ($minutes > 1 ? 's' : '');
    } else {
        $hours = ceil($seconds / 3600);
        return $hours . ' heure' . ($hours > 1 ? 's' : '');
    }
}

/**
 * Log les activités suspectes
 */
function logSuspiciousActivity($ip, $endpoint, $type) {
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $logFile = $logDir . '/security_' . date('Y-m-d') . '.log';
    $logEntry = sprintf(
        "[%s] IP: %s | Endpoint: %s | Type: %s | User-Agent: %s\n",
        date('Y-m-d H:i:s'),
        $ip,
        $endpoint,
        $type,
        $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
    );
    
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

/**
 * Nettoie les anciens fichiers de rate limiting
 * À appeler via cron toutes les heures
 */
function cleanupRateLimitFiles() {
    $rateLimitDir = sys_get_temp_dir() . '/bloom_rate_limit';
    if (!is_dir($rateLimitDir)) {
        return;
    }
    
    $files = glob($rateLimitDir . '/*.json');
    $now = time();
    
    foreach ($files as $file) {
        $data = json_decode(file_get_contents($file), true);
        if ($data && isset($data['reset']) && $data['reset'] < $now - 3600) {
            unlink($file);
        }
    }
}
?>
