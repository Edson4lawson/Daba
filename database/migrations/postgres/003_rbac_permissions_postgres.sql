-- Table roles
CREATE TABLE IF NOT EXISTS roles (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table permissions
CREATE TABLE IF NOT EXISTS permissions (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table role_permissions
CREATE TABLE IF NOT EXISTS role_permissions (
    id SERIAL PRIMARY KEY,
    role_id INTEGER REFERENCES roles(id) ON DELETE CASCADE,
    permission_id INTEGER REFERENCES permissions(id) ON DELETE CASCADE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(role_id, permission_id)
);

-- Insérer les rôles par défaut
INSERT INTO roles (name, description) VALUES
('customer', 'Client standard'),
('admin', 'Administrateur'),
('super_admin', 'Super administrateur')
ON CONFLICT (name) DO NOTHING;

-- Insérer les permissions par défaut
INSERT INTO permissions (name, description) VALUES
('products.read', 'Lire les produits'),
('products.write', 'Créer/modifier les produits'),
('products.delete', 'Supprimer les produits'),
('orders.read', 'Lire les commandes'),
('orders.write', 'Créer/modifier les commandes'),
('orders.delete', 'Supprimer les commandes'),
('users.read', 'Lire les utilisateurs'),
('users.write', 'Créer/modifier les utilisateurs'),
('users.delete', 'Supprimer les utilisateurs'),
('categories.read', 'Lire les catégories'),
('categories.write', 'Créer/modifier les catégories'),
('categories.delete', 'Supprimer les catégories'),
('analytics.read', 'Lire les statistiques'),
('settings.read', 'Lire les paramètres'),
('settings.write', 'Modifier les paramètres')
ON CONFLICT (name) DO NOTHING;

-- Assigner les permissions aux rôles
-- Customer: lecture seule
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p
WHERE r.name = 'customer' AND p.name IN ('products.read', 'orders.read')
ON CONFLICT DO NOTHING;

-- Admin: lecture et écriture
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p
WHERE r.name = 'admin' AND p.name NOT LIKE 'users.delete'
ON CONFLICT DO NOTHING;

-- Super Admin: tout
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p
WHERE r.name = 'super_admin'
ON CONFLICT DO NOTHING;

-- Ajouter colonne role_id si elle n'existe pas
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns
        WHERE table_name = 'users' AND column_name = 'role_id'
    ) THEN
        ALTER TABLE users ADD COLUMN role_id INTEGER REFERENCES roles(id);
        
        -- Migrer les rôles existants
        UPDATE users SET role_id = (SELECT id FROM roles WHERE name = role) WHERE role_id IS NULL;
    END IF;
END $$;
