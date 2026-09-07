-- =============================================================================
-- DABA - SCHÉMA DE PRODUCTION COMPLET POUR SUPABASE (PostgreSQL)
-- =============================================================================
-- Ce script unique crée l'intégralité de la base de données DABA sur Supabase :
-- 1. Rôles et Permissions (RBAC complet : 8 rôles métier)
-- 2. Utilisateurs, Sécurité, Tokens et 2FA
-- 3. Catalogue complet (Catégories officielles et Produits avec unités)
-- 4. Panier et Favoris
-- 5. Commandes (avec canal de commande : site, whatsapp, external), Lignes et Factures
-- 6. Paiements et Rate Limiting
-- 7. Sessions WhatsApp pour n8n (whatsapp_sessions)
-- 8. Compte Super Administrateur d'initialisation
-- =============================================================================

-- Activer les extensions utiles
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pgcrypto";

-- -----------------------------------------------------------------------------
-- 1. RÔLES ET PERMISSIONS (RBAC)
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS public.roles (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    level INTEGER DEFAULT 0,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS public.permissions (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    module VARCHAR(50) NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS public.role_permissions (
    id SERIAL PRIMARY KEY,
    role_id INTEGER NOT NULL REFERENCES public.roles(id) ON DELETE CASCADE,
    permission_id INTEGER NOT NULL REFERENCES public.permissions(id) ON DELETE CASCADE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(role_id, permission_id)
);

-- Insertion des Rôles
INSERT INTO public.roles (id, name, description, level) VALUES
(1, 'customer', 'Client standard du site et WhatsApp', 1),
(2, 'support', 'Support client', 2),
(3, 'commercial', 'Gestion commerciale et ventes', 2),
(4, 'magasinier', 'Gestion du stock et entrepôt', 2),
(5, 'comptable', 'Gestion comptable et facturation', 3),
(6, 'manager', 'Gestionnaire de boutique', 3),
(7, 'admin', 'Administrateur', 4),
(8, 'super_admin', 'Super Administrateur plateforme', 5)
ON CONFLICT (id) DO UPDATE SET 
    name = EXCLUDED.name, 
    description = EXCLUDED.description, 
    level = EXCLUDED.level;

-- Réajuster la séquence des rôles
SELECT setval('roles_id_seq', (SELECT MAX(id) FROM public.roles));

-- Insertion des Permissions
INSERT INTO public.permissions (name, description, module) VALUES
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
ON CONFLICT (name) DO NOTHING;

-- Liaison Rôles - Permissions
-- Customer
INSERT INTO public.role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM public.roles r, public.permissions p
WHERE r.name = 'customer' AND p.name IN ('products.view', 'orders.view')
ON CONFLICT DO NOTHING;

-- Commercial
INSERT INTO public.role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM public.roles r, public.permissions p
WHERE r.name = 'commercial' AND p.name IN ('products.view', 'categories.view', 'orders.view', 'orders.view_all', 'orders.update_status', 'reports.view')
ON CONFLICT DO NOTHING;

-- Magasinier
INSERT INTO public.role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM public.roles r, public.permissions p
WHERE r.name = 'magasinier' AND p.name IN ('products.view', 'products.update', 'categories.view', 'orders.view')
ON CONFLICT DO NOTHING;

-- Comptable
INSERT INTO public.role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM public.roles r, public.permissions p
WHERE r.name = 'comptable' AND p.name IN ('orders.view', 'orders.view_all', 'invoices.view', 'invoices.export', 'reports.view', 'reports.export')
ON CONFLICT DO NOTHING;

-- Support
INSERT INTO public.role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM public.roles r, public.permissions p
WHERE r.name = 'support' AND p.name IN ('products.view', 'orders.view', 'orders.view_all', 'orders.update_status')
ON CONFLICT DO NOTHING;

-- Manager
INSERT INTO public.role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM public.roles r, public.permissions p
WHERE r.name = 'manager' AND p.name IN (
    'products.view', 'products.create', 'products.update',
    'orders.view', 'orders.view_all', 'orders.update_status', 'orders.refund',
    'categories.view', 'categories.create', 'categories.update',
    'invoices.view', 'invoices.export',
    'reports.view', 'reports.export'
)
ON CONFLICT DO NOTHING;

-- Admin
INSERT INTO public.role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM public.roles r, public.permissions p
WHERE r.name = 'admin' AND p.name NOT IN ('system.backup')
ON CONFLICT DO NOTHING;

-- Super Admin
INSERT INTO public.role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM public.roles r, public.permissions p
WHERE r.name = 'super_admin'
ON CONFLICT DO NOTHING;


-- -----------------------------------------------------------------------------
-- 2. UTILISATEURS & SÉCURITÉ
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS public.users (
    id SERIAL PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    phone VARCHAR(30),
    address TEXT,
    role VARCHAR(50) DEFAULT 'customer',
    role_id INTEGER REFERENCES public.roles(id) ON DELETE SET NULL,
    two_factor_required BOOLEAN DEFAULT FALSE,
    token VARCHAR(255),
    token_expires_at TIMESTAMP WITH TIME ZONE,
    failed_login_attempts INTEGER DEFAULT 0,
    locked_until TIMESTAMP WITH TIME ZONE,
    last_login_at TIMESTAMP WITH TIME ZONE,
    last_login_ip VARCHAR(45),
    email_verified_at TIMESTAMP WITH TIME ZONE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS public.refresh_tokens (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES public.users(id) ON DELETE CASCADE,
    token VARCHAR(255) UNIQUE NOT NULL,
    expires_at TIMESTAMP WITH TIME ZONE NOT NULL,
    revoked BOOLEAN DEFAULT FALSE,
    revoked_at TIMESTAMP WITH TIME ZONE,
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS public.email_verifications (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES public.users(id) ON DELETE CASCADE,
    token VARCHAR(255) UNIQUE NOT NULL,
    expires_at TIMESTAMP WITH TIME ZONE NOT NULL,
    verified_at TIMESTAMP WITH TIME ZONE,
    used BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS public.password_resets (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES public.users(id) ON DELETE CASCADE,
    token VARCHAR(255) UNIQUE NOT NULL,
    expires_at TIMESTAMP WITH TIME ZONE NOT NULL,
    used_at TIMESTAMP WITH TIME ZONE,
    ip_address VARCHAR(45),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS public.login_logs (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES public.users(id) ON DELETE SET NULL,
    email VARCHAR(255),
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    status VARCHAR(50) NOT NULL,
    failure_reason VARCHAR(255),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Tables 2FA & Appareils
CREATE TABLE IF NOT EXISTS public.two_factor_auth (
    id SERIAL PRIMARY KEY,
    user_id INTEGER UNIQUE NOT NULL REFERENCES public.users(id) ON DELETE CASCADE,
    secret VARCHAR(255) NOT NULL,
    enabled BOOLEAN DEFAULT FALSE,
    backup_codes JSONB DEFAULT '[]'::jsonb,
    last_used_at TIMESTAMP WITH TIME ZONE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS public.two_factor_sessions (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES public.users(id) ON DELETE CASCADE,
    session_token VARCHAR(255) UNIQUE NOT NULL,
    expires_at TIMESTAMP WITH TIME ZONE NOT NULL,
    verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS public.trusted_devices (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES public.users(id) ON DELETE CASCADE,
    device_identifier VARCHAR(255) NOT NULL,
    user_agent VARCHAR(255),
    ip_address VARCHAR(45),
    last_used_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP WITH TIME ZONE NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);


-- -----------------------------------------------------------------------------
-- 3. CATALOGUE : CATÉGORIES & PRODUITS
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS public.categories (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description TEXT,
    image_url VARCHAR(255),
    icon VARCHAR(255),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS public.products (
    id SERIAL PRIMARY KEY,
    category_id INTEGER REFERENCES public.categories(id) ON DELETE SET NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    compare_price DECIMAL(10, 2),
    stock INTEGER DEFAULT 0,
    stock_quantity INTEGER DEFAULT 0,
    unit VARCHAR(50) DEFAULT 'pièce',
    image_url VARCHAR(255),
    thumbnail VARCHAR(255),
    gallery_urls TEXT,
    rating DECIMAL(2,1) DEFAULT 0.0,
    source VARCHAR(50) DEFAULT 'produit',
    status VARCHAR(50) DEFAULT 'published',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);


-- -----------------------------------------------------------------------------
-- 4. PANIER & FAVORIS
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS public.cart (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES public.users(id) ON DELETE CASCADE,
    product_id INTEGER NOT NULL REFERENCES public.products(id) ON DELETE CASCADE,
    quantity INTEGER NOT NULL DEFAULT 1,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, product_id)
);

CREATE TABLE IF NOT EXISTS public.favorites (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES public.users(id) ON DELETE CASCADE,
    product_id INTEGER NOT NULL REFERENCES public.products(id) ON DELETE CASCADE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, product_id)
);


-- -----------------------------------------------------------------------------
-- 5. COMMANDES, LIGNES, FACTURES, PAIEMENTS
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS public.orders (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES public.users(id) ON DELETE SET NULL,
    guest_info JSONB,
    total_amount DECIMAL(10, 2) NOT NULL,
    status VARCHAR(50) DEFAULT 'pending',
    canal VARCHAR(50) DEFAULT 'site',
    shipping_address TEXT,
    shipping_fee DECIMAL(10,2) DEFAULT 0.00,
    tax_amount DECIMAL(10,2) DEFAULT 0.00,
    notes TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS public.order_items (
    id SERIAL PRIMARY KEY,
    order_id INTEGER NOT NULL REFERENCES public.orders(id) ON DELETE CASCADE,
    product_id INTEGER REFERENCES public.products(id) ON DELETE SET NULL,
    product_name VARCHAR(255) NOT NULL,
    quantity INTEGER NOT NULL,
    price_at_purchase DECIMAL(10, 2) NOT NULL
);

CREATE TABLE IF NOT EXISTS public.invoices (
    id SERIAL PRIMARY KEY,
    order_id INTEGER NOT NULL REFERENCES public.orders(id) ON DELETE CASCADE,
    invoice_number VARCHAR(50) UNIQUE NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    status VARCHAR(50) DEFAULT 'pending',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    pdf_url VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS public.payments (
    id SERIAL PRIMARY KEY,
    order_id INTEGER NOT NULL REFERENCES public.orders(id) ON DELETE CASCADE,
    transaction_id VARCHAR(255),
    provider VARCHAR(50) NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'XOF',
    status VARCHAR(50) NOT NULL,
    metadata JSONB,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);


-- -----------------------------------------------------------------------------
-- 6. RATE LIMITING APPLICATIF
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS public.rate_limits (
    id SERIAL PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    action VARCHAR(50) NOT NULL,
    attempts INTEGER DEFAULT 1,
    last_attempt_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    blocked_until TIMESTAMP WITH TIME ZONE
);


-- -----------------------------------------------------------------------------
-- 7. SESSIONS WHATSAPP (Mémoire n8n / Agent WhatsApp)
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS public.whatsapp_sessions (
    id BIGSERIAL PRIMARY KEY,
    session_id VARCHAR(100) UNIQUE NOT NULL,
    phone_number VARCHAR(30) NOT NULL,
    user_name VARCHAR(150),
    current_step VARCHAR(50) DEFAULT 'idle',
    cart_data JSONB DEFAULT '[]'::jsonb,
    session_data JSONB DEFAULT '{}'::jsonb,
    last_interaction TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_wa_session_id ON public.whatsapp_sessions(session_id);
CREATE INDEX IF NOT EXISTS idx_wa_phone_number ON public.whatsapp_sessions(phone_number);
CREATE INDEX IF NOT EXISTS idx_wa_last_interaction ON public.whatsapp_sessions(last_interaction);


-- -----------------------------------------------------------------------------
-- 8. INDEX POUR PERFORMANCES OPTIMALES
-- -----------------------------------------------------------------------------

CREATE INDEX IF NOT EXISTS idx_users_email ON public.users(email);
CREATE INDEX IF NOT EXISTS idx_users_phone ON public.users(phone);
CREATE INDEX IF NOT EXISTS idx_products_category ON public.products(category_id);
CREATE INDEX IF NOT EXISTS idx_products_status ON public.products(status);
CREATE INDEX IF NOT EXISTS idx_orders_user ON public.orders(user_id);
CREATE INDEX IF NOT EXISTS idx_orders_status ON public.orders(status);
CREATE INDEX IF NOT EXISTS idx_orders_canal ON public.orders(canal);
CREATE INDEX IF NOT EXISTS idx_invoices_order ON public.invoices(order_id);
CREATE INDEX IF NOT EXISTS idx_rate_limits_ip ON public.rate_limits(ip_address, action);


-- -----------------------------------------------------------------------------
-- 9. INSERTION DES CATÉGORIES OFFICIELLES DABA
-- -----------------------------------------------------------------------------

INSERT INTO public.categories (name, slug, description, image_url, icon) VALUES
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
ON CONFLICT (slug) DO UPDATE SET name = EXCLUDED.name, icon = EXCLUDED.icon;


-- -----------------------------------------------------------------------------
-- 10. CRÉATION DU COMPTE SUPER ADMIN PAR DÉFAUT
-- Mot de passe par défaut : Admin123!
-- -----------------------------------------------------------------------------

INSERT INTO public.users (email, password, first_name, last_name, role, role_id)
SELECT 'admin@daba.tg', '$2y$12$LJ3m4ys.NUOvGQZ5UYueNe/FgKR5F0VHuVkYW3N.JQG/5.hxljMaO', 'Admin', 'Daba', 'super_admin', id
FROM public.roles WHERE name = 'super_admin'
ON CONFLICT (email) DO UPDATE SET role = 'super_admin', role_id = (SELECT id FROM public.roles WHERE name = 'super_admin' LIMIT 1);
