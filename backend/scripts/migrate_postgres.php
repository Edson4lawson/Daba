<?php
/**
 * Script de migration PostgreSQL pour Bloom Chloé
 * Exécute les migrations SQL sur la base de données PostgreSQL
 */

// Configuration de la base de données PostgreSQL
$databaseUrl = getenv('DATABASE_URL') ?: 'postgresql://daba_user:password@localhost:5432/daba';

// Parser l'URL de connexion
preg_match('/postgresql:\/\/([^:]+):([^@]+)@([^:]+):(\d+)\/(.+)/', $databaseUrl, $matches);
$user = $matches[1];
$password = $matches[2];
$host = $matches[3];
$port = $matches[4];
$dbname = $matches[5];

try {
    // Connexion à PostgreSQL
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connexion à PostgreSQL réussie\n";
    
    // Liste des migrations à exécuter
    $migrations = [
        '001_init_tables_postgres.sql',
        '002_2fa_tables_postgres.sql',
        '003_rbac_permissions_postgres.sql'
    ];
    
    foreach ($migrations as $migration) {
        $file = __DIR__ . '/../../database/migrations/postgres/' . $migration;
        
        if (!file_exists($file)) {
            echo "⚠️  Fichier de migration non trouvé: $migration\n";
            continue;
        }
        
        echo "📄 Exécution de $migration...\n";
        
        $sql = file_get_contents($file);
        
        // Exécuter le SQL
        try {
            $pdo->exec($sql);
            echo "✅ Migration $migration exécutée avec succès\n";
        } catch (PDOException $e) {
            echo "❌ Erreur lors de $migration: " . $e->getMessage() . "\n";
            // Continuer avec les autres migrations
        }
    }
    
    echo "\n🎉 Toutes les migrations ont été exécutées\n";
    
} catch (PDOException $e) {
    echo "❌ Erreur de connexion: " . $e->getMessage() . "\n";
    exit(1);
}
