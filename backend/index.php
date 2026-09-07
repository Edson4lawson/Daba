<?php
/**
 * Routeur principal - Daba Backend
 * Gère toutes les requêtes API et envoie les headers CORS
 */

// Gérer les requêtes OPTIONS (preflight) AVANT tout autre traitement
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Headers CORS pour preflight
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-Token, X-2FA-Token');
    header('Access-Control-Max-Age: 86400');
    header('Access-Control-Allow-Credentials: true');
    http_response_code(204);
    exit();
}

// Charger la configuration CORS et headers
require_once __DIR__ . '/config/headers.php';

// Récupérer le chemin de la requête
$requestUri = $_SERVER['REQUEST_URI'];
$scriptName = $_SERVER['SCRIPT_NAME'];

// Extraire le chemin relatif (enlever la partie du script)
$path = parse_url($requestUri, PHP_URL_PATH);
$path = str_replace(dirname($scriptName), '', $path);
$path = str_replace('/backend', '', $path);
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

// Si le fichier n'existe pas, essayer dans le dossier api
if (!file_exists($filePath)) {
    $filePath = __DIR__ . '/api/' . $path;
    if (!pathinfo($filePath, PATHINFO_EXTENSION)) {
        $filePath .= '.php';
    }
}

// Si le fichier n'existe toujours pas, essayer dans les sous-dossiers
if (!file_exists($filePath)) {
    // Essayer de trouver le fichier dans tous les sous-dossiers
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
    echo json_encode(['error' => 'Endpoint not found', 'path' => $path, 'tried' => $filePath]);
    exit();
}

// Inclure et exécuter le fichier
require_once $filePath;
