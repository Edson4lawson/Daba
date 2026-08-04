<?php
/**
 * Seeder Daba Products
 * Insère les catégories et produits Daba dans la base de données
 * Réutilisable: peut être exécuté plusieurs fois sans doublons
 */

require_once __DIR__ . '/../../backend/config/db.php';

echo "=== SEEDER DABA PRODUCTS ===\n\n";

try {
    $pdo->beginTransaction();

    // 1. Créer les catégories Daba
    echo "Création des catégories...\n";
    
    $categories = [
        ['name' => 'Poulet', 'slug' => 'poulet', 'description' => 'Produits à base de poulet'],
        ['name' => 'Jambon', 'slug' => 'jambon', 'description' => 'Produits à base de jambon'],
        ['name' => 'Charcuterie', 'slug' => 'charcuterie', 'description' => 'Charcuterie variée']
    ];

    $categoryIds = [];
    
    foreach ($categories as $cat) {
        // Vérifier si la catégorie existe déjà
        $stmt = $pdo->prepare('SELECT id FROM categories WHERE slug = ?');
        $stmt->execute([$cat['slug']]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            $categoryIds[$cat['name']] = $existing['id'];
            echo "  Catégorie '{$cat['name']}' existe déjà (ID: {$existing['id']})\n";
        } else {
            $stmt = $pdo->prepare('INSERT INTO categories (name, slug, description, created_at) VALUES (?, ?, ?, NOW())');
            $stmt->execute([$cat['name'], $cat['slug'], $cat['description']]);
            $categoryIds[$cat['name']] = $pdo->lastInsertId();
            echo "  Catégorie '{$cat['name']}' créée (ID: {$categoryIds[$cat['name']]})\n";
        }
    }

    // 2. Créer les produits Daba
    echo "\nCréation des produits...\n";
    
    $products = [
        // Poulet
        ['name' => 'Poulet', 'price' => 2600, 'unit' => 'kg', 'category' => 'Poulet'],
        ['name' => 'Poulet fumé', 'price' => 3400, 'unit' => 'kg', 'category' => 'Poulet'],
        ['name' => 'Blanc de poulet', 'price' => 6000, 'unit' => 'kg', 'category' => 'Poulet'],
        ['name' => 'Cuisses de poulet', 'price' => 2200, 'unit' => 'kg', 'category' => 'Poulet'],
        ['name' => 'Cuisses de poulet fumées', 'price' => 3000, 'unit' => 'kg', 'category' => 'Poulet'],
        ['name' => 'Ailes de poulet', 'price' => 2000, 'unit' => 'kg', 'category' => 'Poulet'],
        ['name' => 'Gésiers', 'price' => 1900, 'unit' => 'kg', 'category' => 'Poulet'],
        ['name' => 'Crispy poulet pané', 'price' => 2000, 'unit' => '500g', 'category' => 'Poulet'],
        
        // Jambon
        ['name' => 'Jambon de volailles', 'price' => 6000, 'unit' => 'kg', 'category' => 'Jambon'],
        ['name' => 'Jambon aux fines herbes', 'price' => 6500, 'unit' => 'kg', 'category' => 'Jambon'],
        ['name' => 'Jambon tranché 5 tranches', 'price' => 1000, 'unit' => 'kg', 'category' => 'Jambon'],
        
        // Charcuterie
        ['name' => 'Chipsy', 'price' => 2000, 'unit' => 'paquet 500g', 'category' => 'Charcuterie'],
        ['name' => 'Merguez', 'price' => 6000, 'unit' => 'kg', 'category' => 'Charcuterie'],
        ['name' => 'Chipo', 'price' => 6000, 'unit' => 'kg', 'category' => 'Charcuterie'],
        ['name' => 'Saucisses cuites', 'price' => 1000, 'unit' => '410g', 'category' => 'Charcuterie'],
        ['name' => 'Pâté de foie', 'price' => 1000, 'unit' => '200g', 'category' => 'Charcuterie'],
        ['name' => 'Saucissons', 'price' => 2000, 'unit' => '300g', 'category' => 'Charcuterie']
    ];

    $insertedCount = 0;
    $skippedCount = 0;

    foreach ($products as $prod) {
        $categoryId = $categoryIds[$prod['category']];
        $slug = strtolower(str_replace([' ', 'é', 'è', 'ê'], ['-', 'e', 'e', 'e'], $prod['name']));
        
        // Vérifier si le produit existe déjà
        $stmt = $pdo->prepare('SELECT id FROM products WHERE slug = ?');
        $stmt->execute([$slug]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            echo "  Produit '{$prod['name']}' existe déjà (ID: {$existing['id']})\n";
            $skippedCount++;
        } else {
            $stmt = $pdo->prepare('
                INSERT INTO products (name, slug, price, unit, category_id, source, status, stock_quantity, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, "produit", "published", 100, NOW(), NOW())
            ');
            $stmt->execute([
                $prod['name'],
                $slug,
                $prod['price'],
                $prod['unit'],
                $categoryId
            ]);
            echo "  Produit '{$prod['name']}' créé (ID: {$pdo->lastInsertId()}, {$prod['price']} FCFA/{$prod['unit']})\n";
            $insertedCount++;
        }
    }

    $pdo->commit();

    echo "\n=== SEEDER TERMINÉ ===\n";
    echo "Catégories: " . count($categories) . "\n";
    echo "Produits insérés: $insertedCount\n";
    echo "Produits ignorés (existant): $skippedCount\n";

} catch (PDOException $e) {
    $pdo->rollBack();
    echo "\n=== ERREUR ===\n";
    echo "Erreur: " . $e->getMessage() . "\n";
    exit(1);
}
