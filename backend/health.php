<?php
/**
 * Health Check Endpoint - Daba API
 * Utilisé par Render et les load balancers pour vérifier la santé du service
 */

header('Content-Type: application/json; charset=utf-8');

try {
    // Vérifier la connexion à la base de données
    require_once __DIR__ . '/config/db.php';
    
    $health = [
        'status' => 'healthy',
        'timestamp' => date('c'),
        'version' => '1.0.0',
        'environment' => getenv('APP_ENV') ?: 'unknown',
        'checks' => []
    ];
    
    // Check Database
    try {
        $pdo->query("SELECT 1");
        $health['checks']['database'] = [
            'status' => 'up',
            'message' => 'Connexion base de données OK'
        ];
    } catch (Exception $e) {
        $health['checks']['database'] = [
            'status' => 'down',
            'message' => 'Connexion base de données échouée'
        ];
        $health['status'] = 'unhealthy';
    }
    
    // Check Disk Space (si disponible)
    if (function_exists('disk_free_space')) {
        $freeSpace = disk_free_space('/');
        $totalSpace = disk_total_space('/');
        if ($freeSpace !== false && $totalSpace !== false) {
            $freePercent = ($freeSpace / $totalSpace) * 100;
            $health['checks']['disk'] = [
                'status' => $freePercent > 10 ? 'up' : 'warning',
                'free_space_gb' => round($freeSpace / 1073741824, 2),
                'free_percent' => round($freePercent, 2)
            ];
            
            if ($freePercent < 10) {
                $health['status'] = 'degraded';
            }
        }
    }
    
    // Check Memory
    if (function_exists('memory_get_usage')) {
        $memoryUsage = memory_get_usage(true);
        $memoryLimit = ini_get('memory_limit');
        $health['checks']['memory'] = [
            'status' => 'up',
            'usage_mb' => round($memoryUsage / 1048576, 2),
            'limit' => $memoryLimit
        ];
    }
    
    // Déterminer le code HTTP
    $statusCode = $health['status'] === 'healthy' ? 200 : 503;
    http_response_code($statusCode);
    
    echo json_encode($health, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(503);
    echo json_encode([
        'status' => 'unhealthy',
        'timestamp' => date('c'),
        'error' => $e->getMessage()
    ]);
}
