<?php
/**
 * Analyse des fichiers en double dans le projet
 */

echo "🔍 ANALYSE DES FICHIERS EN DOUBLE\n";
echo "===================================\n\n";

$projectRoot = __DIR__ . '/..';

// 1. Scanner les dossiers suspects
echo "1️⃣ DOSSIERS SUSPECTS (anciens/abandonnés)\n";
echo "========================================\n";

$suspiciousDirs = [
    '_abandoned_api',
    '_abandoned_src', 
    '_old_admin_attempt',
    '_old_frontend_attempt',
    'daba-backend',
    'api'
];

foreach ($suspiciousDirs as $dir) {
    $path = $projectRoot . '/' . $dir;
    if (is_dir($path)) {
        $files = array_slice(scandir($path), 2);
        $count = count($files);
        echo "📁 $dir ($count fichiers)\n";
        
        // Afficher quelques fichiers
        foreach (array_slice($files, 0, 5) as $file) {
            echo "   - $file\n";
        }
        if ($count > 5) {
            echo "   ... et " . ($count - 5) . " autres\n";
        }
    } else {
        echo "❌ $dir (inexistant)\n";
    }
}

echo "\n";

// 2. Comparer les configurations
echo "2️⃣ FICHIERS DE CONFIGURATION EN DOUBLE\n";
echo "======================================\n";

$configFiles = [
    'vite.config.js' => 'Configuration Vite',
    'package.json' => 'Dépendances NPM',
    '.env' => 'Variables environnement',
    'index.html' => 'Page principale'
];

foreach ($configFiles as $file => $description) {
    $locations = [];
    
    // Chercher dans la racine
    if (file_exists($projectRoot . '/' . $file)) {
        $locations[] = 'racine';
    }
    
    // Chercher dans frontend
    if (file_exists($projectRoot . '/frontend/' . $file)) {
        $locations[] = 'frontend';
    }
    
    if (count($locations) > 1) {
        echo "⚠️  $file trouvé dans: " . implode(', ', $locations) . "\n";
    } elseif (count($locations) === 1) {
        echo "✅ $file uniquement dans: " . $locations[0] . "\n";
    } else {
        echo "❌ $file introuvable\n";
    }
}

echo "\n";

// 3. Comparer les fichiers backend
echo "3️⃣ FICHIERS BACKEND EN DOUBLE\n";
echo "=============================\n";

$backendDirs = [
    'backend',
    'daba-backend',
    'api'
];

$backendFiles = [];
foreach ($backendDirs as $dir) {
    $path = $projectRoot . '/' . $dir;
    if (is_dir($path)) {
        $files = glob($path . '/*.php');
        foreach ($files as $file) {
            $filename = basename($file);
            $backendFiles[$filename][] = $dir;
        }
    }
}

// Afficher les doublons
foreach ($backendFiles as $filename => $locations) {
    if (count($locations) > 1) {
        echo "⚠️  $filename dans: " . implode(', ', $locations) . "\n";
    }
}

echo "\n";

// 4. Comparer les fichiers frontend
echo "4️⃣ FICHIERS FRONTEND EN DOUBLE\n";
echo "==============================\n";

$frontendDirs = [
    'frontend/src',
    '_abandoned_src',
    '_old_frontend_attempt'
];

$frontendFiles = [];
foreach ($frontendDirs as $dir) {
    $path = $projectRoot . '/' . $dir;
    if (is_dir($path)) {
        $files = glob($path . '/*.{vue,js}', GLOB_BRACE);
        foreach ($files as $file) {
            $filename = basename($file);
            $frontendFiles[$filename][] = $dir;
        }
    }
}

// Afficher les doublons
foreach ($frontendFiles as $filename => $locations) {
    if (count($locations) > 1) {
        echo "⚠️  $filename dans: " . implode(', ', $locations) . "\n";
    }
}

echo "\n";

// 5. Scripts batch en double
echo "5️⃣ SCRIPTS BATCH EN DOUBLE\n";
echo "==========================\n";

$batchFiles = glob($projectRoot . '/*.bat');
$batchNames = [];
foreach ($batchFiles as $file) {
    $filename = basename($file);
    $batchNames[$filename][] = $file;
}

foreach ($batchNames as $filename => $locations) {
    if (count($locations) > 1) {
        echo "⚠️  $filename trouvé " . count($locations) . " fois\n";
        foreach ($locations as $loc) {
            echo "   - $loc\n";
        }
    }
}

echo "\n";

// 6. Résumé et recommandations
echo "6️⃣ RÉSUMÉ ET RECOMMANDATIONS\n";
echo "=============================\n";

$totalSuspicious = 0;
foreach ($suspiciousDirs as $dir) {
    if (is_dir($projectRoot . '/' . $dir)) {
        $totalSuspicious++;
    }
}

echo "📊 Dossiers suspects: $totalSuspicious\n";
echo "💡 Ces dossiers contiennent probablement des anciennes versions ou tentatives abandonnées\n";

echo "\n🗑️  RECOMMANDATIONS DE NETTOYAGE:\n";
echo "   1. Supprimer les dossiers _old_ et _abandoned_ si inutiles\n";
echo "   2. Garder uniquement les fichiers de configuration dans frontend/\n";
echo "   3. Supprimer les scripts batch en double\n";
echo "   4. Conserver uniquement le backend/ actif\n";

echo "\n✅ ANALYSE TERMINÉE\n";

?>
