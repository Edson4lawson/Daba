<?php
/**
 * API pour créer un produit
 */

require_once __DIR__ . '/../../config/headers.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../middleware/auth.php';

// Authentifier l'administrateur
$user = authenticate();
$allowedRoles = ['admin', 'magasinier'];
if (!in_array($user['role'], $allowedRoles)) {
    sendJsonResponse(['error' => 'Accès refusé'], 403);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(['error' => 'Méthode non autorisée'], 405);
}

$data = getJsonData();

$name = $data['name'] ?? null;
$description = $data['description'] ?? '';
$price = $data['price'] ?? 0;
$category_id = $data['category_id'] ?? null;
$status = $data['status'] ?? 'draft';
$image_url = $data['image_url'] ?? '';

if (!$name || !$price || !$category_id) {
    sendJsonResponse(['error' => 'Champs obligatoires manquants'], 400);
}

// Générer un slug
$slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));

try {
    $stmt = $pdo->prepare("
        INSERT INTO products (name, slug, description, price, category_id, status, image_url, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
    ");
    
    $stmt->execute([$name, $slug, $description, $price, $category_id, $status, $image_url]);
    $id = $pdo->lastInsertId();

    sendJsonResponse([
        'success' => true,
        'product_id' => $id,
        'message' => 'Produit créé avec succès'
    ]);

} catch (Exception $e) {
    sendJsonResponse(['error' => 'Erreur serveur: ' . $e->getMessage()], 500);
}
?>
