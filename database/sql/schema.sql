-- =============================================================================
-- DABA - SCHÉMA DE BASE DE DONNÉES COMPLET
-- =============================================================================
-- Exécuter ce fichier pour créer/mettre à jour la base de données
-- =============================================================================

-- Création de la base de données si elle n'existe pas
CREATE DATABASE IF NOT EXISTS daba CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE daba;

-- =============================================================================
-- TABLE DES UTILISATEURS
-- =============================================================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) DEFAULT NULL,
    last_name VARCHAR(100) DEFAULT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    role ENUM('customer', 'admin') DEFAULT 'customer',
    token VARCHAR(255) DEFAULT NULL,
    token_expires_at DATETIME DEFAULT NULL,
    failed_login_attempts INT DEFAULT 0,
    locked_until DATETIME DEFAULT NULL,
    last_login_at DATETIME DEFAULT NULL,
    last_login_ip VARCHAR(45) DEFAULT NULL,
    email_verified_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_token (token)
) ENGINE=InnoDB;

-- =============================================================================
-- TABLE DES REFRESH TOKENS
-- =============================================================================
CREATE TABLE IF NOT EXISTS refresh_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent VARCHAR(255) DEFAULT NULL,
    revoked TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB;

-- =============================================================================
-- TABLE DES VÉRIFICATIONS EMAIL
-- =============================================================================
CREATE TABLE IF NOT EXISTS email_verifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    used TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (token)
) ENGINE=InnoDB;

-- =============================================================================
-- TABLE DES LOGS DE CONNEXION
-- =============================================================================
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
    INDEX idx_ip (ip_address),
    INDEX idx_created (created_at)
) ENGINE=InnoDB;

-- =============================================================================
-- TABLE DES CATÉGORIES
-- =============================================================================
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    image_url VARCHAR(255),
    icon VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_slug (slug)
) ENGINE=InnoDB;

-- =============================================================================
-- TABLE DES PRODUITS
-- =============================================================================
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
    image_url VARCHAR(255),
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
) ENGINE=InnoDB;

-- =============================================================================
-- TABLE DU PANIER
-- =============================================================================
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
) ENGINE=InnoDB;

-- =============================================================================
-- TABLE DES FAVORIS (WISHLIST)
-- =============================================================================
CREATE TABLE IF NOT EXISTS favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_favorite (user_id, product_id),
    INDEX idx_user (user_id)
) ENGINE=InnoDB;

-- =============================================================================
-- TABLE DES COMMANDES
-- =============================================================================
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    guest_info JSON DEFAULT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'completed', 'cancelled') DEFAULT 'pending',
    shipping_address TEXT,
    shipping_fee DECIMAL(10,2) DEFAULT 0.00,
    tax_amount DECIMAL(10,2) DEFAULT 0.00,
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- =============================================================================
-- TABLE DES LIGNES DE COMMANDE
-- =============================================================================
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT DEFAULT NULL,
    product_name VARCHAR(255) NOT NULL,
    quantity INT NOT NULL,
    price_at_purchase DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- =============================================================================
-- TABLE DES PAIEMENTS
-- =============================================================================
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
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =============================================================================
-- TABLE DE RATE LIMITING
-- =============================================================================
CREATE TABLE IF NOT EXISTS rate_limits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    action VARCHAR(50) NOT NULL,
    attempts INT DEFAULT 1,
    last_attempt_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    blocked_until DATETIME DEFAULT NULL,
    INDEX idx_ip_action (ip_address, action)
) ENGINE=InnoDB;

-- =============================================================================
-- INSERTION DES CATÉGORIES
-- =============================================================================
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

-- =============================================================================
-- CRÉATION D'UN UTILISATEUR ADMIN PAR DÉFAUT (mot de passe: Admin123!)
-- =============================================================================
INSERT INTO users (email, password, first_name, last_name, role) VALUES
('admin@daba.tg', '$2y$12$LJ3m4ys.NUOvGQZ5UYueNe/FgKR5F0VHuVkYW3N.JQG/5.hxljMaO', 'Admin', 'Daba', 'admin')
ON DUPLICATE KEY UPDATE email=email;
