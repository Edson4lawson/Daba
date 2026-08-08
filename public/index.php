<?php
/**
 * Point d'entrée principal - Bloom Chloé API
 * Ce fichier sert de router pour toutes les requêtes API
 */

// Désactiver l'affichage des erreurs en production
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// Configuration du timezone
date_default_timezone_set('Africa/Lome');

// Charger les configurations
require_once __DIR__ . '/../backend/config/db.php';
require_once __DIR__ . '/../backend/config/security_headers.php';

// Logger les requêtes (uniquement en production pour debug)
$logFile = __DIR__ . '/../backend/logs/access_' . date('Y-m-d') . '.log';
$logDir = dirname($logFile);
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

$logEntry = sprintf(
    "[%s] %s %s %s | IP: %s | UA: %s\n",
    date('Y-m-d H:i:s'),
    $_SERVER['REQUEST_METHOD'],
    $_SERVER['REQUEST_URI'],
    $_SERVER['SERVER_PROTOCOL'],
    $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 100)
);
file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);

// Router les requêtes vers l'endpoint approprié
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Nettoyer l'URI
$requestUri = str_replace('/api', '', $requestUri);
$requestUri = rtrim($requestUri, '/');

// Mapping des routes
$routes = [
    // Auth
    'POST /auth/login' => '/../backend/auth/login.php',
    'POST /auth/register' => '/../backend/auth/register.php',
    'POST /auth/logout' => '/../backend/auth/logout.php',
    'POST /auth/refresh' => '/../backend/auth/refresh.php',
    'POST /auth/forgot-password' => '/../backend/auth/forgot-password.php',
    'POST /auth/reset-password' => '/../backend/auth/reset-password.php',
    'POST /auth/verify-email' => '/../backend/auth/verify-email.php',
    'POST /auth/update-profile' => '/../backend/auth/update_profile.php',
    
    // Products
    'GET /products/get_all' => '/../backend/products/get_all.php',
    'GET /products/get_one' => '/../backend/products/get_one.php',
    
    // Categories
    'GET /categories/get_all' => '/../backend/categories/get_all.php',
    
    // Cart
    'GET /cart/get' => '/../backend/cart/get.php',
    'POST /cart/add' => '/../backend/cart/add.php',
    'POST /cart/update' => '/../backend/cart/update.php',
    'POST /cart/remove' => '/../backend/cart/remove.php',
    
    // Favorites
    'GET /favorites/get' => '/../backend/favorites/get.php',
    'POST /favorites/add' => '/../backend/favorites/add.php',
    'POST /favorites/remove' => '/../backend/favorites/remove.php',
    
    // Orders
    'POST /orders/create' => '/../backend/orders/create.php',
    'GET /orders/get_user_orders' => '/../backend/orders/get_user_orders.php',
    'GET /orders/get_one' => '/../backend/orders/get_one.php',
    
    // Payment
    'POST /payment/process' => '/../backend/payment/process.php',
    'POST /payment/create' => '/../backend/payment/create.php',
    'POST /payment/stripe-webhook' => '/../backend/payment/stripe_webhook.php',
    
    // Admin
    'GET /admin/stream' => '/../backend/admin/stream.php',
];

// Route key
$routeKey = $requestMethod . ' ' . $requestUri;

// Vérifier si la route existe
if (isset($routes[$routeKey])) {
    $targetFile = __DIR__ . $routes[$routeKey];
    if (file_exists($targetFile)) {
        include $targetFile;
        exit;
    }
}

// Route non trouvée
http_response_code(404);
header('Content-Type: application/json');
echo json_encode([
    'error' => 'Endpoint non trouvé',
    'path' => $requestUri,
    'method' => $requestMethod
]);
exit;
