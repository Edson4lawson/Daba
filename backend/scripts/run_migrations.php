<?php
/**
 * Script d'exécution automatique des migrations
 * Exécute les fichiers SQL de migration via mysql.exe
 * 
 * Usage: php backend/scripts/run_migrations.php
 */

echo "=== EXÉCUTION AUTOMATIQUE DES MIGRATIONS ===\n\n";

// Charger la configuration de la base de données
$envFile = __DIR__ . '/../../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            $value = trim($value, '"\'');
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

// Configuration
$dbHost = getenv('DB_HOST') ?: '127.0.0.1';
$dbPort = getenv('DB_PORT') ?: '3306';
$dbName = getenv('DB_NAME') ?: 'daba';
$dbUser = getenv('DB_USER') ?: 'root';
$dbPass = getenv('DB_PASS') ?: '';

echo "Configuration de la base de données:\n";
echo "  Host: $dbHost:$dbPort\n";
echo "  Database: $dbName\n";
echo "  User: $dbUser\n\n";

// Liste des migrations à exécuter
$migrations = [
    __DIR__ . '/../../database/migrations/002_2fa_tables.txt',
    __DIR__ . '/../../database/migrations/003_rbac_permissions.txt',
    __DIR__ . '/../../database/migrations/008_add_canal_to_orders.txt'
];

foreach ($migrations as $migrationFile) {
    if (!file_exists($migrationFile)) {
        echo "⚠ Fichier de migration non trouvé: $migrationFile\n";
        continue;
    }
    
    echo "Exécution de: " . basename($migrationFile) . "\n";
    
    // Construire la commande mysql
    $mysqlPath = 'C:\\laragon\\bin\\mysql\\mysql-8.0.30-winx64\\bin\\mysql.exe'; // Chemin Laragon par défaut
    
    // Vérifier si mysql existe à ce chemin
    if (!file_exists($mysqlPath)) {
        // Essayer d'autres chemins possibles
        $possiblePaths = [
            'C:\\laragon\\bin\\mysql\\bin\\mysql.exe',
            'C:\\laragon\\bin\\mysql\\mysql-8.0.30-winx64\\bin\\mysql.exe',
            'C:\\laragon\\bin\\mariadb\\bin\\mysql.exe',
            'C:\\xampp\\mysql\\bin\\mysql.exe',
            'C:\\wamp64\\bin\\mysql\\mysql8.0.xx\\bin\\mysql.exe',
        ];
        
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                $mysqlPath = $path;
                break;
            }
        }
        
        // Si toujours pas trouvé, utiliser 'mysql' (doit être dans PATH)
        if (!file_exists($mysqlPath)) {
            $mysqlPath = 'mysql';
        }
    }
    
    $command = sprintf(
        'cmd /c "%s -u %s -p%s -P %s -h %s %s < %s"',
        $mysqlPath,
        $dbUser,
        $dbPass,
        $dbPort,
        $dbHost,
        $dbName,
        $migrationFile
    );
    
    $output = [];
    $returnCode = 0;
    
    exec($command, $output, $returnCode);
    
    if ($returnCode === 0) {
        echo "✓ Migration réussie\n\n";
    } else {
        echo "⚠ Erreur lors de la migration (code: $returnCode)\n";
        echo "  (Les tables peuvent déjà exister)\n\n";
    }
}

echo "=== MIGRATIONS TERMINÉES ===\n";
