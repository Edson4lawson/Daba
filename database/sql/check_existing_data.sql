-- =============================================================================
-- SCRIPT DE RECUPERATION DES DONNEES EXISTANTES DABA
-- =============================================================================
-- Ce script vérifie et récupère les données existantes

USE daba;

-- Vérifier les tables existantes
SELECT 'Tables existantes:' AS info;
SHOW TABLES;

-- Compter les enregistrements dans chaque table
SELECT 'Nombre d\'utilisateurs:' AS info, COUNT(*) as count FROM users;
SELECT 'Nombre de produits:' AS info, COUNT(*) as count FROM products;
SELECT 'Nombre de catégories:' AS info, COUNT(*) as count FROM categories;
SELECT 'Nombre de commandes:' AS info, COUNT(*) as count FROM orders;

-- Vérifier si un admin existe
SELECT 'Admin users:' AS info;
SELECT email, first_name, last_name, role, created_at FROM users WHERE role = 'admin';

-- Afficher quelques produits récents
SELECT 'Produits récents:' AS info;
SELECT id, name, price, category_id, created_at FROM products ORDER BY created_at DESC LIMIT 5;

-- Afficher quelques commandes récentes
SELECT 'Commandes récentes:' AS info;
SELECT id, user_id, total_amount, status, created_at FROM orders ORDER BY created_at DESC LIMIT 5;
