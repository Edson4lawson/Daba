<?php
/**
 * Chargement centralisé des variables d'environnement - Daba
 * Évite le double parsing du fichier .env
 * 
 * @version 1.0.0
 */

if (defined('DABA_ENV_LOADED')) {
    return;
}
define('DABA_ENV_LOADED', true);

$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Ignorer les commentaires
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            // Supprimer les guillemets si présents
            $value = trim($value, '"\'');
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}
