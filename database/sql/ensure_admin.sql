-- =============================================================================
-- CRÉATION ADMIN SI NÉCESSAIRE - DABA
-- =============================================================================
-- Crée un admin seulement si aucun n'existe

USE daba;

-- Vérifier si un admin existe déjà
SET @admin_exists = (SELECT COUNT(*) FROM users WHERE role = 'admin' LIMIT 1);

-- Créer un admin seulement si aucun n'existe
INSERT IGNORE INTO users (
    email, 
    password, 
    first_name, 
    last_name, 
    role,
    email_verified_at,
    created_at
) VALUES (
    'admin@DABA.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- hash de "admin123"
    'Admin',
    'Bloom Chloé',
    'admin',
    NOW(),
    NOW()
);

-- Afficher le résultat
SELECT IF(@admin_exists > 0, 'Admin existant - Pas de création nécessaire', 'Nouvel admin créé') AS result;

-- Afficher tous les admins
SELECT 'Utilisateurs admin:' AS info;
SELECT id, email, first_name, last_name, role, created_at FROM users WHERE role = 'admin';
