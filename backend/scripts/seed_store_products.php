<?php
/**
 * daba — Seed des produits de la section STORE
 * Ajoute les produits spécifiques avec la source 'store'
 */

require_once __DIR__ . '/../config/db.php';

echo "=== daba — Seed de la Section Store ===\n\n";

if (!isset($pdo)) {
    die("[ERREUR] Connexion BDD impossible\n");
}

// Récupérer les catégories
$catStmt = $pdo->query("SELECT id, name FROM categories");
$catMap = [];
while($row = $catStmt->fetch(PDO::FETCH_ASSOC)) {
    $catMap[$row['name']] = $row['id'];
}

$storeProducts = [
    [
        'id' => 101, 
        'name' => 'Couvre-matelas + taies imperméables', 
        'category' => 'Maison & Confort', 
        'price' => 7500, 
        'image' => 'store1.jpg', 
        'desc' => "Protège le matelas et les oreillers contre l'eau et les taches. Doux, respirant et très confortable pour un sommeil agréable."
    ],
    [
        'id' => 102, 
        'name' => "Table d'appoint (salon / salle à manger)", 
        'category' => 'Maison & Confort', 
        'price' => 14000, 
        'image' => 'store2.jpg', 
        'desc' => "Table pratique et élégante pour salon ou salle à manger. Idéale pour poser objets, boissons ou décoration."
    ],
    [
        'id' => 103, 
        'name' => 'Carafe + 4 verres', 
        'category' => 'Cuisine & Art de la Table', 
        'price' => 6500, 
        'image' => 'store3.jpg', 
        'desc' => "Ensemble pratique et élégant pour servir vos boissons. Parfait pour la maison ou les invités."
    ],
    [
        'id' => 104, 
        'name' => 'Chic gourde Thermos (maintien de température)', 
        'category' => 'Maison & Confort', 
        'price' => 4000, 
        'image' => 'store4.jpg', 
        'desc' => "Garde les boissons chaudes ou froides pendant plusieurs heures. Design chic, idéale pour le travail ou les déplacements."
    ],
    [
        'id' => 105, 
        'name' => 'Raquette anti-moustique', 
        'category' => 'Entretien & Bricolage', 
        'price' => 3000, 
        'image' => 'store5.jpg', 
        'desc' => "Ustensile efficace pour se débarrasser des insectes. Simple d'utilisation et efficace."
    ],
    [
        'id' => 106, 
        'name' => 'Kit perceuse', 
        'category' => 'Entretien & Bricolage', 
        'price' => 10000, 
        'image' => 'store6.jpg', 
        'desc' => "Kit complet pour bricolage et réparations à domicile. Pratique, robuste et polyvalent."
    ],
    [
        'id' => 107, 
        'name' => 'Serviette de bain compressée', 
        'category' => 'Maison & Confort', 
        'price' => 1000, 
        'image' => 'store7.jpg', 
        'desc' => "Compacte, légère et très absorbante. Idéale pour voyage, sport ou sorties."
    ],
    [
        'id' => 108, 
        'name' => 'Chic gourde', 
        'category' => 'Maison & Confort', 
        'price' => 4000, 
        'image' => 'store8.jpg', 
        'desc' => "Gourde moderne et pratique pour un usage quotidien. Facile à transporter et résistante."
    ],
    [
        'id' => 109, 
        'name' => 'Moulinex à sec', 
        'category' => 'Cuisine & Art de la Table', 
        'price' => 5500, 
        'image' => 'store9.jpg', 
        'desc' => "Permet de moudre rapidement épices et aliments secs. Pratique et indispensable en cuisine."
    ],
    [
        'id' => 110, 
        'name' => "Agrandisseur d'écran", 
        'category' => 'High-Tech & Gadgets', 
        'price' => 2600, 
        'image' => 'store10.jpg', 
        'desc' => "Agrandit l'écran du téléphone pour plus de confort visuel. Idéal pour vidéos et films."
    ],
    [
        'id' => 111, 
        'name' => 'Étagère de douche', 
        'category' => 'Maison & Confort', 
        'price' => 1500, 
        'image' => 'store11.jpg', 
        'desc' => "Rangement pratique pour accessoires de bain. Facile à installer et résistante à l'humidité."
    ],
    [
        'id' => 112, 
        'name' => 'Carafe + verres', 
        'category' => 'Cuisine & Art de la Table', 
        'price' => 7000, 
        'image' => 'store12.jpg', 
        'desc' => "Ensemble élégant pour servir toutes vos boissons. Idéal pour la maison ou le bureau."
    ]
];

$stmt = $pdo->prepare("
    INSERT INTO products (id, category_id, name, slug, description, price, stock, stock_quantity, image_url, rating, source, status) 
    VALUES (:id, :cat_id, :name, :slug, :desc, :price, 50, 50, :image, 4.8, 'store', 'published')
    ON DUPLICATE KEY UPDATE 
        category_id=VALUES(category_id),
        name=VALUES(name),
        slug=VALUES(slug),
        description=VALUES(description),
        price=VALUES(price),
        image_url=VALUES(image_url)
");

$count = 0;
foreach ($storeProducts as $p) {
    $catId = $catMap[$p['category']] ?? 35; // Default to Maison & Confort
    
    // Générer le slug (ex: couvre-matelas-taies-impermeables-101)
    $cleanName = strtolower(trim($p['name']));
    $cleanName = preg_replace('/[^a-z0-9\s-]/', '', $cleanName);
    $cleanName = preg_replace('/[\s-]+/', '-', $cleanName);
    $slug = trim($cleanName, '-') . '-' . $p['id'];
    
    $stmt->execute([
        ':id' => $p['id'],
        ':cat_id' => $catId,
        ':name' => $p['name'],
        ':slug' => $slug,
        ':desc' => $p['desc'],
        ':price' => $p['price'],
        ':image' => $p['image']
    ]);
    $count++;
    echo "[+] Store: {$p['name']}\n";
}

echo "\n✅ Successfully seeded $count store products!\n";
