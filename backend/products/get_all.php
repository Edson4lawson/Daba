<?php
/**
 * API pour récupérer tous les produits
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/headers.php';
require_once __DIR__ . '/../config/db.php';

// Vérifier la méthode
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendJsonResponse(['error' => 'Méthode non autorisée'], 405);
}

try {
    // La connexion $pdo est déjà initialisée par config/db.php

    
    // Paramètres de pagination
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 100; // Augmenté pour éviter les coupures
    $source = isset($_GET['source']) ? $_GET['source'] : null;
    $categoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : null;
    $offset = ($page - 1) * $per_page;

    // Requête de base
    $sql = "
        SELECT p.id, p.name, p.slug, p.description, p.price, p.stock_quantity as stock,
               p.unit, p.image_url, p.source, p.status, p.created_at, p.updated_at,
               p.is_featured, p.is_newest, p.is_bestseller, p.is_special_offer,
               c.name as category_name, p.category_id 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
    ";
    
    // Filtres WHERE
    $whereConditions = [];
    if ($source) {
        $whereConditions[] = "p.source = " . $pdo->quote($source);
    }
    if ($categoryId) {
        $whereConditions[] = "p.category_id = " . $pdo->quote($categoryId);
    }
    
    if (!empty($whereConditions)) {
        $sql .= " WHERE " . implode(' AND ', $whereConditions);
    }
    
    $sql .= " ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(1, $per_page, PDO::PARAM_INT);
    $stmt->bindValue(2, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Total
    $totalStmt = $pdo->query("SELECT COUNT(*) as total FROM products");
    $total = (int)$totalStmt->fetch()["total"];
    
    sendJsonResponse([
        "data" => $products,
        "pagination" => [
            "total" => $total,
            "per_page" => $per_page,
            "current_page" => $page,
            "last_page" => ceil($total / $per_page),
            "from" => $total > 0 ? $offset + 1 : 0,
            "to" => min($offset + $per_page, $total)
        ]
    ]);
    
} catch (Exception $e) {
    sendJsonResponse([
        "error" => "Erreur serveur",
        "message" => $e->getMessage()
    ], 500);
}

?>