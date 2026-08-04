<?php
/**
 * Script de migration simple - Utilise PDO avec exécution instruction par instruction
 * 
 * Usage: php backend/scripts/migrate_simple.php
 */

echo "=== MIGRATIONS SIMPLIFIÉES ===\n\n";

// Configuration directe (pas de .env pour éviter les problèmes)
$dbHost = '127.0.0.1';
$dbPort = '3306';
$dbName = 'daba';
$dbUser = 'root';
$dbPass = '';

echo "Connexion à MySQL...\n";

try {
    $dsn = "mysql:host=$dbHost;port=$dbPort;charset=utf8mb4";
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    
    echo "✓ Connexion réussie\n\n";
    
    // Créer la base si elle n'existe pas
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✓ Base vérifiée\n\n";
    
    $pdo->exec("USE `$dbName`");
    
    // Migration 002 - 2FA
    echo "=== MIGRATION 002: 2FA ===\n";
    
    $tables2FA = [
        "CREATE TABLE IF NOT EXISTS two_factor_auth (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL UNIQUE,
            secret VARCHAR(255) NOT NULL,
            enabled TINYINT(1) DEFAULT 0,
            backup_codes JSON DEFAULT NULL,
            last_used_at DATETIME DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_user_id (user_id),
            INDEX idx_enabled (enabled)
        ) ENGINE=InnoDB",
        
        "CREATE TABLE IF NOT EXISTS two_factor_sessions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            session_token VARCHAR(255) NOT NULL UNIQUE,
            expires_at DATETIME NOT NULL,
            verified TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_session_token (session_token),
            INDEX idx_expires (expires_at)
        ) ENGINE=InnoDB",
        
        "CREATE TABLE IF NOT EXISTS trusted_devices (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            device_identifier VARCHAR(255) NOT NULL,
            user_agent VARCHAR(255),
            ip_address VARCHAR(45),
            last_used_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            expires_at DATETIME NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_user_device (user_id, device_identifier),
            INDEX idx_expires (expires_at)
        ) ENGINE=InnoDB"
    ];
    
    foreach ($tables2FA as $sql) {
        try {
            $pdo->exec($sql);
            echo "✓ Table créée\n";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'already exists') !== false) {
                echo "○ Table existe déjà\n";
            } else {
                echo "✗ Erreur: " . $e->getMessage() . "\n";
            }
        }
    }
    
    // Ajouter la colonne two_factor_required si elle n'existe pas
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN two_factor_required TINYINT(1) DEFAULT 0 AFTER role");
        echo "✓ Colonne two_factor_required ajoutée\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'already exists') !== false) {
            echo "○ Colonne two_factor_required existe déjà\n";
        }
    }
    
    echo "\n=== MIGRATION 003: RBAC ===\n";
    
    // Tables RBAC
    $tablesRBAC = [
        "CREATE TABLE IF NOT EXISTS roles (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50) NOT NULL UNIQUE,
            description TEXT,
            level INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_name (name),
            INDEX idx_level (level)
        ) ENGINE=InnoDB",
        
        "CREATE TABLE IF NOT EXISTS permissions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL UNIQUE,
            description TEXT,
            module VARCHAR(50) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_name (name),
            INDEX idx_module (module)
        ) ENGINE=InnoDB",
        
        "CREATE TABLE IF NOT EXISTS role_permissions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            role_id INT NOT NULL,
            permission_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
            FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
            UNIQUE KEY unique_role_permission (role_id, permission_id),
            INDEX idx_role_id (role_id),
            INDEX idx_permission_id (permission_id)
        ) ENGINE=InnoDB"
    ];
    
    foreach ($tablesRBAC as $sql) {
        try {
            $pdo->exec($sql);
            echo "✓ Table créée\n";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'already exists') !== false) {
                echo "○ Table existe déjà\n";
            } else {
                echo "✗ Erreur: " . $e->getMessage() . "\n";
            }
        }
    }
    
    // Insérer les rôles
    $roles = [
        "('customer', 'Client standard', 1)",
        "('support', 'Support client', 2)",
        "('manager', 'Gestionnaire', 3)",
        "('admin', 'Administrateur', 4)",
        "('super_admin', 'Super Administrateur', 5)"
    ];
    
    foreach ($roles as $role) {
        try {
            $pdo->exec("INSERT INTO roles (name, description, level) VALUES $role ON DUPLICATE KEY UPDATE name=name");
            echo "✓ Rôle inséré\n";
        } catch (PDOException $e) {
            echo "○ Rôle existe déjà\n";
        }
    }
    
    // Ajouter la colonne role_id si elle n'existe pas
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN role_id INT NULL AFTER role");
        echo "✓ Colonne role_id ajoutée\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'already exists') !== false) {
            echo "○ Colonne role_id existe déjà\n";
        }
    }
    
    // Ajouter la foreign key
    try {
        $pdo->exec("ALTER TABLE users ADD FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE SET NULL");
        echo "✓ Foreign key ajoutée\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'already exists') !== false || strpos($e->getMessage(), 'Duplicate') !== false) {
            echo "○ Foreign key existe déjà\n";
        }
    }
    
    // Migrer les rôles existants
    try {
        $pdo->exec("UPDATE users SET role_id = (SELECT id FROM roles WHERE name = role) WHERE role_id IS NULL");
        echo "✓ Rôles migrés\n";
    } catch (PDOException $e) {
        echo "○ Migration déjà effectuée\n";
    }
    
    echo "\n=== VÉRIFICATION ===\n";
    
    $result = $pdo->query("SHOW TABLES");
    echo "Tables dans la base:\n";
    while ($row = $result->fetch(PDO::FETCH_NUM)) {
        echo "  - " . $row[0] . "\n";
    }
    
    echo "\n=== MIGRATIONS TERMINÉES ===\n";
    
} catch (PDOException $e) {
    echo "✗ Erreur: " . $e->getMessage() . "\n";
    exit(1);
}
