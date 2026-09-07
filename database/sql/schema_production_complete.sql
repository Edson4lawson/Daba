-- =============================================================================
-- DABA - SCHÉMA DE PRODUCTION COMPLET & CONSOLIDÉ (MySQL 8.0 / MariaDB 10.4+)
-- =============================================================================
-- Ce fichier regroupe TOUTES les migrations du projet en un seul script propre :
-- - Utilisateurs, 2FA, Sécurité (Tokens, Logs, Réinitialisations)
-- - Rôles RBAC complets (Customer, Commercial, Magasinier, Comptable, Support, Manager, Admin, Super Admin)
-- - Permissions associées à chaque rôle
-- - Catalogue : Catégories officielles et Produits avec unités et stocks
-- - Gestion des Paniers et Favoris (Wishlist)
-- - Commandes (avec canal de commande : site, whatsapp, external) & Lignes de commandes
-- - Factures (Invoices) avec statuts et numéros uniques
-- - Paiements & Transactions
-- - Rate Limiting applicatif
-- =============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------------------------------
-- 1. TABLE DES RÔLES & PERMISSIONS (RBAC)
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    level INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_name (name),
    INDEX idx_level (level)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    module VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_name (name),
    INDEX idx_module (module)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS role_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    permission_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    UNIQUE KEY unique_role_permission (role_id, permission_id),
    INDEX idx_role_id (role_id),
    INDEX idx_permission_id (permission_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertion des Rôles
INSERT INTO roles (id, name, description, level) VALUES
(1, 'customer', 'Client standard du site et WhatsApp', 1),
(2, 'support', 'Support client', 2),
(3, 'commercial', 'Gestion commerciale et ventes', 2),
(4, 'magasinier', 'Gestion du stock et entrepôt', 2),
(5, 'comptable', 'Gestion comptable et facturation', 3),
(6, 'manager', 'Gestionnaire de boutique', 3),
(7, 'admin', 'Administrateur', 4),
(8, 'super_admin', 'Super Administrateur plateforme', 5)
ON DUPLICATE KEY UPDATE name=VALUES(name), description=VALUES(description), level=VALUES(level);

-- Insertion des Permissions
INSERT INTO permissions (name, description, module) VALUES
('products.view', 'Voir les produits', 'products'),
('products.create', 'Créer des produits', 'products'),
('products.update', 'Modifier les produits', 'products'),
('products.delete', 'Supprimer les produits', 'products'),
('orders.view', 'Voir les commandes', 'orders'),
('orders.view_all', 'Voir toutes les commandes', 'orders'),
('orders.update_status', 'Modifier le statut des commandes', 'orders'),
('orders.refund', 'Rembourser les commandes', 'orders'),
('invoices.view', 'Voir les factures', 'invoices'),
('invoices.export', 'Exporter les factures', 'invoices'),
('users.view', 'Voir les utilisateurs', 'users'),
('users.create', 'Créer des utilisateurs', 'users'),
('users.update', 'Modifier les utilisateurs', 'users'),
('users.delete', 'Supprimer les utilisateurs', 'users'),
('users.manage_roles', 'Gérer les rôles utilisateurs', 'users'),
('categories.view', 'Voir les catégories', 'categories'),
('categories.create', 'Créer des catégories', 'categories'),
('categories.update', 'Modifier les catégories', 'categories'),
('categories.delete', 'Supprimer les catégories', 'categories'),
('reports.view', 'Voir les rapports', 'reports'),
('reports.export', 'Exporter les rapports', 'reports'),
('system.settings', 'Modifier les paramètres système', 'system'),
('system.logs', 'Voir les logs système', 'system'),
('system.backup', 'Gérer les sauvegardes', 'system')
ON DUPLICATE KEY UPDATE name=VALUES(name), description=VALUES(description);

-- Liaison Rôles - Permissions
-- Customer
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p
WHERE r.name = 'customer' AND p.name IN ('products.view', 'orders.view')
ON DUPLICATE KEY UPDATE role_id=VALUES(role_id);

-- Commercial
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p
WHERE r.name = 'commercial' AND p.name IN ('products.view', 'categories.view', 'orders.view', 'orders.view_all', 'orders.update_status', 'reports.view')
ON DUPLICATE KEY UPDATE role_id=VALUES(role_id);

-- Magasinier
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p
WHERE r.name = 'magasinier' AND p.name IN ('products.view', 'products.update', 'categories.view', 'orders.view')
ON DUPLICATE KEY UPDATE role_id=VALUES(role_id);

-- Comptable
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p
WHERE r.name = 'comptable' AND p.name IN ('orders.view', 'orders.view_all', 'invoices.view', 'invoices.export', 'reports.view', 'reports.export')
ON DUPLICATE KEY UPDATE role_id=VALUES(role_id);

-- Support
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p
WHERE r.name = 'support' AND p.name IN ('products.view', 'orders.view', 'orders.view_all', 'orders.update_status')
ON DUPLICATE KEY UPDATE role_id=VALUES(role_id);

-- Manager
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p
WHERE r.name = 'manager' AND p.name IN (
    'products.view', 'products.create', 'products.update',
    'orders.view', 'orders.view_all', 'orders.update_status', 'orders.refund',
    'categories.view', 'categories.create', 'categories.update',
    'invoices.view', 'invoices.export',
    'reports.view', 'reports.export'
)
ON DUPLICATE KEY UPDATE role_id=VALUES(role_id);

-- Admin
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p
WHERE r.name = 'admin' AND p.name NOT IN ('system.backup')
ON DUPLICATE KEY UPDATE role_id=VALUES(role_id);

-- Super Admin
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p
WHERE r.name = 'super_admin'
ON DUPLICATE KEY UPDATE role_id=VALUES(role_id);


-- -----------------------------------------------------------------------------
-- 2. TABLE DES UTILISATEURS & SÉCURITÉ
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) DEFAULT NULL,
    last_name VARCHAR(100) DEFAULT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    role ENUM('customer', 'commercial', 'magasinier', 'comptable', 'support', 'manager', 'admin', 'super_admin') DEFAULT 'customer',
    role_id INT NULL,
    two_factor_required TINYINT(1) DEFAULT 0,
    token VARCHAR(255) DEFAULT NULL,
    token_expires_at DATETIME DEFAULT NULL,
    failed_login_attempts INT DEFAULT 0,
    locked_until DATETIME DEFAULT NULL,
    last_login_at DATETIME DEFAULT NULL,
    last_login_ip VARCHAR(45) DEFAULT NULL,
    email_verified_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE SET NULL,
    INDEX idx_email (email),
    INDEX idx_phone (phone),
    INDEX idx_token (token),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS refresh_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    revoked TINYINT(1) DEFAULT 0,
    revoked_at DATETIME NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_user_id (user_id),
    INDEX idx_revoked (revoked)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS email_verifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    verified_at DATETIME NULL,
    used TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS login_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    email VARCHAR(255) DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent VARCHAR(255) DEFAULT NULL,
    status ENUM('success', 'failed', 'blocked') NOT NULL,
    failure_reason VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_date (user_id, created_at),
    INDEX idx_ip_date (ip_address, created_at),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tables 2FA & Sécurité des appareils
CREATE TABLE IF NOT EXISTS two_factor_auth (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS two_factor_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_token VARCHAR(255) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    verified TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_session_token (session_token),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS trusted_devices (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- -----------------------------------------------------------------------------
-- 3. CATALOGUE : CATÉGORIES & PRODUITS
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    image_url VARCHAR(255),
    icon VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT DEFAULT NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) DEFAULT NULL UNIQUE,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    compare_price DECIMAL(10, 2) DEFAULT NULL,
    stock INT DEFAULT 0,
    stock_quantity INT DEFAULT 0,
    unit VARCHAR(50) DEFAULT 'pièce' COMMENT 'Unité : kg, 500g, paquet, carton, pièce, etc.',
    image_url VARCHAR(255),
    thumbnail VARCHAR(255) DEFAULT NULL,
    gallery_urls TEXT DEFAULT NULL,
    rating DECIMAL(2,1) DEFAULT 0.0,
    source ENUM('produit', 'store') DEFAULT 'produit',
    status ENUM('published', 'draft', 'archived') DEFAULT 'published',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_category (category_id),
    INDEX idx_source (source)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- -----------------------------------------------------------------------------
-- 4. PANIER & FAVORIS
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_cart_item (user_id, product_id),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_favorite (user_id, product_id),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- -----------------------------------------------------------------------------
-- 5. COMMANDES, LIGNES DE COMMANDES, FACTURES, PAIEMENTS
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    guest_info JSON DEFAULT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'completed', 'cancelled') DEFAULT 'pending',
    canal VARCHAR(50) DEFAULT 'site' COMMENT 'Canal : site, whatsapp, external, direct',
    shipping_address TEXT,
    shipping_fee DECIMAL(10,2) DEFAULT 0.00,
    tax_amount DECIMAL(10,2) DEFAULT 0.00,
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_status (status),
    INDEX idx_canal (canal)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT DEFAULT NULL,
    product_name VARCHAR(255) NOT NULL,
    quantity INT NOT NULL,
    price_at_purchase DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
    INDEX idx_order (order_id),
    INDEX idx_product (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    invoice_number VARCHAR(50) NOT NULL UNIQUE,
    amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'paid', 'cancelled') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    pdf_url VARCHAR(255) NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_invoice_number (invoice_number),
    INDEX idx_order_id (order_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    transaction_id VARCHAR(255),
    provider VARCHAR(50) NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'XOF',
    status VARCHAR(50) NOT NULL,
    metadata JSON DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_order_id (order_id),
    INDEX idx_transaction (transaction_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- -----------------------------------------------------------------------------
-- 6. RATE LIMITING APPLICATIF
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS rate_limits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    action VARCHAR(50) NOT NULL,
    attempts INT DEFAULT 1,
    last_attempt_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    blocked_until DATETIME DEFAULT NULL,
    INDEX idx_ip_action (ip_address, action)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- -----------------------------------------------------------------------------
-- 7. DONNÉES DE BASE OFFICIELLES (Catégories Daba)
-- -----------------------------------------------------------------------------

INSERT INTO categories (name, slug, description, image_url, icon) VALUES
('Bien-être et relaxation', 'bien-etre-relaxation', 'Produits de bien-être et relaxation', NULL, 'solar:heart-pulse-bold'),
('Accessoire de coiffure', 'accessoire-coiffure', 'Accessoires pour cheveux et coiffure', NULL, 'solar:scissors-bold'),
('Soin personnel', 'soin-personnel', 'Produits de soin personnel', NULL, 'solar:hand-stars-bold'),
('Beauté et soin personnel', 'beaute-soin-personnel', 'Beauté et soin personnel', NULL, 'solar:palette-bold'),
('Accessoire tech', 'accessoire-tech', 'Accessoires technologiques', NULL, 'solar:smartphone-bold'),
('Santé féminine', 'sante-feminine', 'Produits de santé féminine', NULL, 'solar:heart-bold'),
('Accessoire de cuisine', 'accessoire-cuisine', 'Accessoires de cuisine', NULL, 'solar:chef-hat-bold'),
('Accessoire de douche', 'accessoire-douche', 'Accessoires de douche', NULL, 'solar:bath-bold'),
('Accessoire', 'accessoire', 'Accessoires divers', NULL, 'solar:bag-bold'),
('Accessoire de tournage', 'accessoire-tournage', 'Accessoires de tournage et photographie', NULL, 'solar:camera-bold'),
('Accessoire de beauté', 'accessoire-beaute', 'Accessoires de beauté', NULL, 'solar:magic-stick-bold'),
('Bien-être', 'bien-etre', 'Produits bien-être', NULL, 'solar:sun-bold'),
('Soin corporel', 'soin-corporel', 'Produits de soin corporel', NULL, 'solar:hand-heart-bold'),
('Esthétique et soin personnel', 'esthetique-soin', 'Esthétique et soin personnel', NULL, 'solar:star-bold'),
('Bien-être et plaisir personnel', 'bien-etre-plaisir', 'Bien-être et plaisir personnel', NULL, 'solar:heart-shine-bold'),
('Bien-être et soin de la peau', 'bien-etre-soin-peau', 'Bien-être et soin de la peau', NULL, 'solar:water-bold'),
('Soin personnel et beauté', 'soin-personnel-beaute', 'Soin personnel et beauté', NULL, 'solar:palette-round-bold'),
('Jardinage et lavage', 'jardinage-lavage', 'Jardinage et lavage', NULL, 'solar:leaf-bold'),
('Sport et bien-être', 'sport-bien-etre', 'Sport et bien-être', NULL, 'solar:running-bold'),
('Accessoire de bureau', 'accessoire-bureau', 'Accessoires de bureau', NULL, 'solar:monitor-bold'),
('Ménage', 'menage', 'Articles de ménage', NULL, 'solar:broom-bold'),
('Accessoire de sortie', 'accessoire-sortie', 'Accessoires de sortie', NULL, 'solar:bag-check-bold'),
('Accessoire de chambre', 'accessoire-chambre', 'Accessoires de chambre', NULL, 'solar:bed-bold'),
('Bien-être et santé', 'bien-etre-sante', 'Bien-être et santé', NULL, 'solar:shield-bold'),
('Accessoire personnel', 'accessoire-personnel', 'Accessoires personnels', NULL, 'solar:user-bold'),
('Mobilier de maison', 'mobilier-maison', 'Mobilier de maison', NULL, 'solar:sofa-bold'),
('Art de la table', 'art-table', 'Art de la table', NULL, 'solar:cup-bold'),
('Accessoire anti-insecte', 'accessoire-anti-insecte', 'Accessoires anti-insecte', NULL, 'solar:bug-bold'),
('Bricolage', 'bricolage', 'Outils de bricolage', NULL, 'solar:wrench-bold'),
('Accessoire High-Tech', 'accessoire-high-tech', 'Accessoires High-Tech', NULL, 'solar:laptop-bold'),
('Collection Daba', 'collection-daba', 'Collection exclusive Daba', NULL, 'solar:crown-bold'),
('Range vaisselle', 'range-vaisselle', 'Rangement pour vaisselle', NULL, 'solar:sort-bold')
ON DUPLICATE KEY UPDATE name=VALUES(name);


-- -----------------------------------------------------------------------------
-- 8. COMPTE SUPER ADMIN PAR DÉFAUT
-- Mot de passe par défaut : Admin123!
-- À modifier impérativement dès la première connexion en production !
-- -----------------------------------------------------------------------------

INSERT INTO users (email, password, first_name, last_name, role, role_id)
SELECT 'admin@daba.tg', '$2y$12$LJ3m4ys.NUOvGQZ5UYueNe/FgKR5F0VHuVkYW3N.JQG/5.hxljMaO', 'Admin', 'Daba', 'super_admin', id
FROM roles WHERE name = 'super_admin'
ON DUPLICATE KEY UPDATE role='super_admin', role_id=(SELECT id FROM roles WHERE name='super_admin' LIMIT 1);

SET FOREIGN_KEY_CHECKS = 1;
