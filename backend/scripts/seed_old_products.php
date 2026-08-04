<?php
require_once __DIR__ . '/backend/config/db.php';

echo "🌱 Seed des ANCIENS produits daba...\n\n";

$catStmt = $pdo->query('SELECT id, name FROM categories');
$categoriesDb = $catStmt->fetchAll();
$categoryMap = [];
foreach ($categoriesDb as $cat) {
    $categoryMap[strtolower(trim($cat['name']))] = $cat['id'];
}

function findCategoryId($categoryName, $categoryMap) {
    $key = strtolower(trim($categoryName));
    if (isset($categoryMap[$key])) {
        return $categoryMap[$key];
    }
    foreach ($categoryMap as $name => $id) {
        if (strpos($key, $name) !== false || strpos($name, $key) !== false) {
            return $id;
        }
    }
    return null;
}

function generateSlug($title, $id) {
    $slug = strtolower(trim($title));
    $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
    $slug = preg_replace('/[\s-]+/', '-', $slug);
    $slug = trim($slug, '-');
    if (empty($slug)) {
        $slug = 'produit';
    }
    return $slug . '-' . $id;
}

$oldProducts = [
    [
        'id' => 201, // Use new IDs above 112 to avoid conflict
        'title' => 'Fleur de Cerisier',
        'category' => 'Bien-être et soin de la peau',
        'price' => 89990, // multiplied by 1000 since it is CFA maybe? Wait, original price was 89.99, let's keep it similar
        'rating' => 4.8,
        'stock' => 15,
        'image' => 'image1.jpg',
        'source' => 'produit',
        'description' => 'Un parfum délicat aux notes florales de cerisier en fleur.'
    ],
    [
        'id' => 202,
        'title' => 'Fraîcheur Absolue',
        'category' => 'Bien-être et soin de la peau',
        'price' => 65500,
        'rating' => 4.5,
        'stock' => 22,
        'image' => 'image2.jpg',
        'source' => 'produit',
        'description' => 'Une fraîcheur incomparable avec des notes d\'agrumes.'
    ],
    [
        'id' => 203,
        'title' => 'Santal Royal',
        'category' => 'Bien-être et soin de la peau',
        'price' => 120000,
        'rating' => 4.9,
        'stock' => 8,
        'image' => 'image3.jpg',
        'source' => 'produit',
        'description' => 'Un sillage chaleureux et enveloppant.'
    ],
    [
        'id' => 204,
        'title' => 'Brume d\'Été',
        'category' => 'Bien-être et soin de la peau',
        'price' => 75000,
        'rating' => 4.3,
        'stock' => 30,
        'image' => 'image4.jpg',
        'source' => 'produit',
        'description' => 'Une eau légère comme une caresse.'
    ],
    [
        'id' => 205,
        'title' => 'Orient Mystique',
        'category' => 'Bien-être et soin de la peau',
        'price' => 95000,
        'rating' => 4.7,
        'stock' => 12,
        'image' => 'image5.jpg',
        'source' => 'produit',
        'description' => 'Un parfum envoûtant et profond.'
    ],
    [
        'id' => 206,
        'title' => 'Zeste Citronné',
        'category' => 'Bien-être et soin de la peau',
        'price' => 55000,
        'rating' => 4.2,
        'stock' => 25,
        'image' => 'image6.jpg',
        'source' => 'produit',
        'description' => 'Une explosion vitaminée de citron.'
    ],
    [
        'id' => 207,
        'title' => 'Rose Éternelle',
        'category' => 'Bien-être et soin de la peau',
        'price' => 85000,
        'rating' => 4.6,
        'stock' => 18,
        'image' => 'image7.jpg',
        'source' => 'produit',
        'description' => 'L\'élégance intemporelle d\'un bouquet de roses.'
    ],
    [
        'id' => 208,
        'title' => 'Élixir Nocturne',
        'category' => 'Accessoire de sortie',
        'price' => 145000,
        'rating' => 5.0,
        'stock' => 5,
        'image' => 'parfum-luxe-01.jpg',
        'source' => 'produit',
        'description' => 'Un accessoire indispensable pour vos soirées.'
    ],
    [
        'id' => 209,
        'title' => 'Or Blanc',
        'category' => 'Accessoire de sortie',
        'price' => 115000,
        'rating' => 4.8,
        'stock' => 10,
        'image' => 'parfum-luxe-02.jpg',
        'source' => 'produit',
        'description' => 'L\'incarnation du luxe minimaliste.'
    ],
    [
        'id' => 210,
        'title' => 'Prestige Gold',
        'category' => 'Accessoire de sortie',
        'price' => 180000,
        'rating' => 4.9,
        'stock' => 3,
        'image' => 'parfum-luxe-03.jpg',
        'source' => 'produit',
        'description' => 'L\'ultime raffinement.'
    ],
    [
        'id' => 211,
        'title' => 'Jardin Secret',
        'category' => 'Accessoire de beauté',
        'price' => 78000,
        'rating' => 4.4,
        'stock' => 20,
        'image' => 'parfum-luxe-04.jpg',
        'source' => 'produit',
        'description' => 'Un bijou délicat inspiré par la nature.'
    ],
    [
        'id' => 212,
        'title' => 'Velours Noir',
        'category' => 'Accessoire de sortie',
        'price' => 130000,
        'rating' => 4.7,
        'stock' => 7,
        'image' => 'parfum-luxe-05.jpg',
        'source' => 'produit',
        'description' => 'Doux au toucher et structuré.'
    ],
    [
        'id' => 213,
        'title' => 'Essence Divine',
        'category' => 'Accessoire de beauté',
        'price' => 105000,
        'rating' => 4.6,
        'stock' => 14,
        'image' => 'parfum-luxe-06.jpg',
        'source' => 'produit',
        'description' => 'Une pièce rayonnante qui illumine le visage.'
    ],
    [
        'id' => 214,
        'title' => 'Charisme',
        'category' => 'Accessoire de beauté',
        'price' => 98000,
        'rating' => 4.5,
        'stock' => 16,
        'image' => 'parfum-luxe-07.jpg',
        'source' => 'produit',
        'description' => 'Pour celles qui osent.'
    ],
    [
        'id' => 215,
        'title' => 'Douce Rêverie',
        'category' => 'Accessoire de beauté',
        'price' => 60000,
        'rating' => 4.3,
        'stock' => 28,
        'image' => 'parfum-luxe-08.jpg',
        'source' => 'produit',
        'description' => 'Légèreté et douceur.'
    ]
];

$insertStmt = $pdo->prepare('
    INSERT INTO products (id, name, slug, description, price, stock, stock_quantity, image_url, rating, source, category_id, status) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, "published")
    ON DUPLICATE KEY UPDATE 
        name=VALUES(name), description=VALUES(description), price=VALUES(price), 
        stock=VALUES(stock), stock_quantity=VALUES(stock_quantity), image_url=VALUES(image_url),
        rating=VALUES(rating), source=VALUES(source), category_id=VALUES(category_id)
');

$inserted = 0;
foreach ($oldProducts as $p) {
    $slug = generateSlug($p['title'], $p['id']);
    $categoryId = findCategoryId($p['category'], $categoryMap) ?: 1; // Default to 1 if not found
    
    try {
        $insertStmt->execute([
            $p['id'],
            $p['title'],
            $slug,
            $p['description'],
            $p['price'],
            $p['stock'],
            $p['stock'],
            $p['image'],
            $p['rating'],
            $p['source'],
            $categoryId
        ]);
        $inserted++;
        echo "  ✅ #{$p['id']} - {$p['title']}\n";
    } catch (PDOException $e) {
        echo "  ❌ #{$p['id']} - {$p['title']}: {$e->getMessage()}\n";
    }
}
echo "Total inserted: $inserted\n";
