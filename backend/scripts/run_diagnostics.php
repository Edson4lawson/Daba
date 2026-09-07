<?php
/**
 * Script de diagnostic complet de Daba
 */

require_once __DIR__ . '/../config/db.php';

echo "=== DIAGNOSTIC DABA ===\n\n";

$errors = [];
$warnings = [];

// 1. Connexion Base de Données
try {
    $pdo->query("SELECT 1");
    echo "✅ [DB] Connexion réussie à la base de données.\n";
} catch (Exception $e) {
    $errors[] = "Connexion DB échouée : " . $e->getMessage();
    echo "❌ [DB] Connexion échouée.\n";
}

// 2. Vérification des colonnes de la table users
if (empty($errors)) {
    try {
        $stmt = $pdo->query("DESCRIBE users");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $requiredColumns = ['id', 'email', 'password', 'first_name', 'last_name', 'phone', 'address', 'role'];
        foreach ($requiredColumns as $col) {
            if (in_array($col, $columns)) {
                echo "✅ [DB] Colonne users.'$col' présente.\n";
            } else {
                $errors[] = "La colonne '$col' est manquante dans la table users.";
                echo "❌ [DB] Colonne users.'$col' manquante.\n";
            }
        }
    } catch (Exception $e) {
        $errors[] = "Erreur lors de la description de la table users : " . $e->getMessage();
    }
}

// 3. Vérification de l'existence des fichiers critiques d'API backend
$backendDir = __DIR__ . '/../';
$apiFiles = [
    'auth/login.php',
    'auth/register.php',
    'auth/update_profile.php',
    'auth/refresh.php',
    'auth/logout.php',
    'cart/get.php',
    'cart/add.php',
    'cart/update.php',
    'cart/remove.php',
    'products/get_all.php',
    'categories/get_all.php',
];

foreach ($apiFiles as $file) {
    $path = $backendDir . $file;
    if (file_exists($path)) {
        // Tester s'il compile (pas d'erreur de syntaxe)
        $output = [];
        $returnVar = 0;
        exec("php -l " . escapeshellarg($path), $output, $returnVar);
        if ($returnVar === 0) {
            echo "✅ [API] Fichier backend/$file présent et compile sans erreur.\n";
        } else {
            $errors[] = "Erreur de syntaxe dans backend/$file : " . implode("\n", $output);
            echo "❌ [API] Erreur de syntaxe dans backend/$file.\n";
        }
    } else {
        $errors[] = "Fichier API manquant : backend/$file";
        echo "❌ [API] Fichier backend/$file introuvable.\n";
    }
}

// 4. Bilan final
echo "\n=== BILAN DU DIAGNOSTIC ===\n";
if (empty($errors)) {
    echo "🎉 TOUT EST PRÊT POUR L'HÉBERGEMENT DE PRODUCTION ! Aucun problème détecté.\n";
} else {
    echo "⚠️ DES ERREURS ONT ÉTÉ DÉTECTÉES :\n";
    foreach ($errors as $err) {
        echo "   - $err\n";
    }
}
if (!empty($warnings)) {
    echo "📢 AVERTISSEMENTS :\n";
    foreach ($warnings as $warn) {
        echo "   - $warn\n";
    }
}

exit(empty($errors) ? 0 : 1);
?>
