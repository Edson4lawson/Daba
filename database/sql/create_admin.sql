-- =============================================================================
-- DABA - CRÉATION UTILISATEUR ADMIN PAR DÉFAUT
-- =============================================================================
-- Exécuter ce script après avoir créé la base de données

USE daba;

-- Créer utilisateur admin par défaut avec mot de passe hashé
-- Mot de passe: admin123
INSERT INTO users (
    email, 
    password, 
    first_name, 
    last_name, 
    role,
    email_verified_at,
    created_at
) VALUES (
    'admin@daba.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- hash de "admin123"
    'Admin',
    'Daba',
    'admin',
    NOW(),
    NOW()
);

-- Afficher confirmation
SELECT 'Utilisateur admin créé avec succès!' AS message;
SELECT * FROM users WHERE email = 'admin@daba.com';
