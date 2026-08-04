<?php
/**
 * Générateur de Sitemap XML Dynamique - Bloom Chloé
 * Génère un sitemap.xml optimisé pour Google et les autres moteurs de recherche
 * 
 * @endpoint GET /sitemap.xml
 */

require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/xml; charset=utf-8');

// Configuration
$baseUrl = 'https://daba.com';
$lastmod = date('Y-m-d');

// Début du XML
$xml = '<?xml version="1.0" encoding="UTF-8"?>';
$xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"';
$xml .= ' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"';
$xml .= ' xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9';
$xml .= ' http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">';

// =============================================================================
# PAGES STATIQUES PRIORITAIRES
# =============================================================================

$staticPages = [
    ['url' => '', 'priority' => '1.0', 'changefreq' => 'daily'],
    ['url' => '/boutique', 'priority' => '0.9', 'changefreq' => 'daily'],
    ['url' => '/faq', 'priority' => '0.8', 'changefreq' => 'weekly'],
    ['url' => '/shipping', 'priority' => '0.7', 'changefreq' => 'monthly'],
    ['url' => '/returns', 'priority' => '0.7', 'changefreq' => 'monthly'],
    ['url' => '/privacy', 'priority' => '0.5', 'changefreq' => 'monthly'],
    ['url' => '/terms', 'priority' => '0.5', 'changefreq' => 'monthly'],
    ['url' => '/cookie-policy', 'priority' => '0.3', 'changefreq' => 'yearly'],
];

foreach ($staticPages as $page) {
    $xml .= '<url>';
    $xml .= '<loc>' . $baseUrl . $page['url'] . '</loc>';
    $xml .= '<lastmod>' . $lastmod . '</lastmod>';
    $xml .= '<changefreq>' . $page['changefreq'] . '</changefreq>';
    $xml .= '<priority>' . $page['priority'] . '</priority>';
    $xml .= '</url>';
}

// =============================================================================
# CATÉGORIES
# =============================================================================

try {
    $stmt = $pdo->query("SELECT slug, updated_at FROM categories WHERE slug IS NOT NULL ORDER BY name");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($categories as $cat) {
        $catLastmod = $cat['updated_at'] ? date('Y-m-d', strtotime($cat['updated_at'])) : $lastmod;
        $xml .= '<url>';
        $xml .= '<loc>' . $baseUrl . '/categorie/' . htmlspecialchars($cat['slug']) . '</loc>';
        $xml .= '<lastmod>' . $catLastmod . '</lastmod>';
        $xml .= '<changefreq>weekly</changefreq>';
        $xml .= '<priority>0.8</priority>';
        $xml .= '</url>';
    }
} catch (Exception $e) {
    // Silencer l'erreur si la table n'existe pas encore
}

// =============================================================================
# PRODUITS (Indexation optimisée pour les recherches de produits)
# =============================================================================

try {
    // Récupérer uniquement les produits publiés avec stock
    $stmt = $pdo->query("
        SELECT slug, updated_at, created_at 
        FROM products 
        WHERE status = 'published' 
        AND slug IS NOT NULL 
        AND stock_quantity > 0
        ORDER BY created_at DESC
        LIMIT 50000
    ");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($products as $product) {
        $prodLastmod = $product['updated_at'] ? date('Y-m-d', strtotime($product['updated_at'])) : $lastmod;
        $xml .= '<url>';
        $xml .= '<loc>' . $baseUrl . '/produit/' . htmlspecialchars($product['slug']) . '</loc>';
        $xml .= '<lastmod>' . $prodLastmod . '</lastmod>';
        $xml .= '<changefreq>daily</changefreq>';
        $xml .= '<priority>0.9</priority>';
        $xml .= '</url>';
    }
} catch (Exception $e) {
    // Silencer l'erreur
}

// =============================================================================
# FERMETURE XML
# =============================================================================

$xml .= '</urlset>';

echo $xml;
