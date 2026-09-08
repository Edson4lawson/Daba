<?php
/**
 * Configuration CORS Centralisée - Daba
 * Ce fichier doit être inclus au tout début de chaque endpoint API
 * pour garantir que les headers CORS sont envoyés correctement
 */

// Gérer les requêtes OPTIONS (preflight) AVANT tout autre traitement
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Headers CORS pour preflight
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    $allowedOrigins = [
        'https://daba-tg.vercel.app',
        'https://daba.vercel.app',
        'https://www.daba.tg',
        'https://daba.tg',
        'http://localhost:5173',
        'http://localhost:3000',
        'http://localhost:8080',
        'http://127.0.0.1:5173',
        'http://127.0.0.1:3000',
        'http://127.0.0.1:8080'
    ];
    
    if (in_array($origin, $allowedOrigins)) {
        header("Access-Control-Allow-Origin: $origin");
    } else {
        header('Access-Control-Allow-Origin: *');
    }
    
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-Token, X-2FA-Token');
    header('Access-Control-Max-Age: 86400');
    header('Access-Control-Allow-Credentials: true');
    http_response_code(204);
    exit();
}

// Envoyer les headers CORS pour toutes les requêtes
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowedOrigins = [
    'https://daba-tg.vercel.app',
    'https://daba.vercel.app',
    'https://www.daba.tg',
    'https://daba.tg',
    'http://localhost:5173',
    'http://localhost:3000',
    'http://localhost:8080',
    'http://127.0.0.1:5173',
    'http://127.0.0.1:3000',
    'http://127.0.0.1:8080'
];

if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
    header('Access-Control-Allow-Credentials: true');
} else {
    header('Access-Control-Allow-Origin: *');
}

header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-Token, X-2FA-Token');
header('Access-Control-Max-Age: 86400');
