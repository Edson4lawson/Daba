<?php
require_once __DIR__ . '/backend/config/db.php';

header('Content-Type: text/plain');
echo "--- DIAGNOSTIC DABA ---\n\n";

echo "1. Connexion DB :\n";
try {
    if (isset($pdo)) {
        echo "   [OK] Connexion établie.\n";
        
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        echo "   Tables trouvées : " . implode(', ', $tables) . "\n";
        
        if (in_array('users', $tables)) {
            $count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
            echo "   Compte utilisateurs : $count\n";
            
            if ($count > 0) {
                $users = $pdo->query("SELECT id, email, role FROM users LIMIT 5")->fetchAll();
                foreach ($users as $u) {
                    echo "   - User : {$u['email']} (ID: {$u['id']}, Role: {$u['role']})\n";
                }
            } else {
                echo "   [!] Aucun utilisateur trouvé dans la table 'users'.\n";
            }
        } else {
            echo "   [ERREUR] Table 'users' absente !\n";
        }
    } else {
        echo "   [ERREUR] \$pdo non défini.\n";
    }
} catch (Exception $e) {
    echo "   [ERREUR] " . $e->getMessage() . "\n";
}

echo "\n2. Configuration Système :\n";
echo "   PHP Version : " . PHP_VERSION . "\n";
echo "   OS : " . PHP_OS . "\n";
echo "   IP Serveur : " . ($_SERVER['SERVER_ADDR'] ?? '127.0.0.1') . "\n";
