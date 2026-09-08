<?php
/**
 * Routeur principal - Daba Backend
 * Gère toutes les requêtes API et envoie les headers CORS
 */

// Gérer les requêtes OPTIONS (preflight) AVANT tout autre traitement
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Headers CORS pour preflight - autoriser explicitement l'origine Vercel
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    if ($origin === 'https://daba-tg.vercel.app' || $origin === 'https://daba.vercel.app') {
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
$allowedOrigins = ['https://daba-tg.vercel.app', 'https://daba.vercel.app', 'https://www.daba.tg', 'https://daba.tg'];
if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
    header('Access-Control-Allow-Credentials: true');
} else {
    header('Access-Control-Allow-Origin: *');
}
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-Token, X-2FA-Token');
header('Access-Control-Max-Age: 86400');

// Charger la configuration CORS et headers
require_once __DIR__ . '/config/headers.php';

// Récupérer le chemin de la requête
$requestUri = $_SERVER['REQUEST_URI'];

// Extraire le chemin relatif
$path = parse_url($requestUri, PHP_URL_PATH);
$path = trim($path, '/');

// Si le chemin est vide, rediriger vers la racine
if (empty($path) || $path === 'index.php') {
    header('Content-Type: application/json');
    echo json_encode(['message' => 'Daba API', 'version' => '1.0.0']);
    exit();
}

// Supprimer le préfixe /api/ si présent
if (strpos($path, 'api/') === 0) {
    $path = substr($path, 4);
}

// Construire le chemin du fichier
$filePath = __DIR__ . '/' . $path;

// Si le fichier n'a pas l'extension .php, l'ajouter
if (!pathinfo($filePath, PATHINFO_EXTENSION)) {
    $filePath .= '.php';
}

// Si le fichier n'existe pas, essayer dans les sous-dossiers
if (!file_exists($filePath)) {
    // Extraire le premier segment du chemin (ex: "products" de "products/get_all.php")
    $segments = explode('/', $path);
    if (count($segments) >= 2) {
        $firstSegment = $segments[0];
        $restOfPath = implode('/', array_slice($segments, 1));
        
        // Essayer dans le sous-dossier correspondant
        $filePath = __DIR__ . '/' . $firstSegment . '/' . $restOfPath;
        if (!pathinfo($filePath, PATHINFO_EXTENSION)) {
            $filePath .= '.php';
        }
    }
}

// Si le fichier n'existe toujours pas, essayer dans le dossier api
if (!file_exists($filePath)) {
    $filePath = __DIR__ . '/api/' . $path;
    if (!pathinfo($filePath, PATHINFO_EXTENSION)) {
        $filePath .= '.php';
    }
}

// Si le fichier n'existe toujours pas, essayer une recherche récursive
if (!file_exists($filePath)) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(__DIR__, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getFilename() === basename($path) . '.php') {
            $filePath = $file->getPathname();
            break;
        }
    }
}

// Si le fichier n'existe pas, retourner 404
if (!file_exists($filePath)) {
    header('Content-Type: application/json');
    http_response_code(404);
    echo json_encode(['error' => 'Endpoint not found']);
    exit();
}

// Inclure et exécuter le fichier
require_once $filePath;
