-- =============================================================================
-- DABA - DONNÉES DE TEST
-- =============================================================================
-- Exécuter ce script après avoir créé les tables et l'utilisateur admin

USE daba;

-- Insérer des catégories de test
INSERT INTO categories (name, description, created_at) VALUES
('Robes', 'Magnifiques robes pour toutes occasions', NOW()),
('Chaussures', 'Chaussures élégantes et confortables', NOW()),
('Accessoires', 'Accessoires de mode tendance', NOW()),
('Sacs', 'Sacs à main et sacs à dos fashion', NOW()),
('Bijoux', 'Bijoux fins et bijoux de fantaisie', NOW());

-- Insérer des produits de test
INSERT INTO products (
    name, 
    description, 
    price, 
    category_id, 
    stock_quantity, 
    image_url, 
    thumbnail, 
    slug, 
    source,
    created_at
) VALUES
('Robe Soirée Élégante', 'Robe longue en soie parfaite pour les occasions spéciales', 45000, 1, 10, '/images/robe1.jpg', '/images/robe1_thumb.jpg', 'robe-soiree-elegante', 'store', NOW()),
('Robe d\'Été Florale', 'Robe légère avec motif floral pour l\'été', 25000, 1, 15, '/images/robe2.jpg', '/images/robe2_thumb.jpg', 'robe-ete-florale', 'store', NOW()),
('Talons Hauts Noirs', 'Talons aiguilles élégants de 12cm', 35000, 2, 8, '/images/talons1.jpg', '/images/talons1_thumb.jpg', 'talons-hauts-noirs', 'store', NOW()),
('Bottines en Cuir', 'Bottines confortables en cuir véritable', 55000, 2, 12, '/images/bottines1.jpg', '/images/bottines1_thumb.jpg', 'bottines-cuir', 'store', NOW()),
('Sac à Main Luxe', 'Sac à main en cuir avec fermoir doré', 75000, 4, 6, '/images/sac1.jpg', '/images/sac1_thumb.jpg', 'sac-main-luxe', 'store', NOW()),
('Sacoche Tendance', 'Sacoche moderne pour un look casual', 28000, 4, 20, '/images/sacoche1.jpg', '/images/sacoche1_thumb.jpg', 'sacoche-tendance', 'store', NOW()),
('Collier Perles', 'Collier élégant en perles de culture', 18000, 5, 25, '/images/collier1.jpg', '/images/collier1_thumb.jpg', 'collier-perles', 'store', NOW()),
('Bracelet Or', 'Bracelet fin en plaqué or', 22000, 5, 18, '/images/bracelet1.jpg', '/images/bracelet1_thumb.jpg', 'bracelet-or', 'store', NOW());

-- Insérer des commandes de test
INSERT INTO orders (
    user_id, 
    total_amount, 
    status, 
    shipping_address, 
    created_at
) VALUES
(1, 95000, 'completed', '123 Rue de la Mode, Paris, France', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(1, 68000, 'pending', '45 Avenue des Champs-Élysées, Paris, France', DATE_SUB(NOW(), INTERVAL 3 DAY)),
(1, 125000, 'completed', '78 Boulevard Saint-Germain, Paris, France', DATE_SUB(NOW(), INTERVAL 1 DAY));

-- Insérer des détails de commandes
INSERT INTO order_items (order_id, product_id, quantity, price) VALUES
(1, 1, 1, 45000),
(1, 5, 1, 75000),
(2, 2, 1, 25000),
(2, 6, 1, 28000),
(2, 7, 1, 18000),
(3, 3, 1, 35000),
(3, 4, 1, 55000),
(3, 8, 1, 22000);

-- Afficher confirmation
SELECT 'Données de test insérées avec succès!' AS message;
SELECT COUNT(*) as total_categories FROM categories;
SELECT COUNT(*) as total_products FROM products;
SELECT COUNT(*) as total_orders FROM orders;
