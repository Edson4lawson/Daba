<?php
/**
 * API pour modifier un produit
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
$id = $data['id'] ?? null;

if (!$id) {
    sendJsonResponse(['error' => 'ID du produit manquant'], 400);
}

$name = $data['name'] ?? null;
$description = $data['description'] ?? null;
$price = $data['price'] ?? null;
$category_id = $data['category_id'] ?? null;
$status = $data['status'] ?? null;
$image_url = $data['image_url'] ?? null;
$stock = $data['stock'] ?? null;

try {
    // Construire la requête dynamiquement pour ne mettre à jour que ce qui est envoyé
    $fields = [];
    $params = [];

    if ($name !== null) { $fields[] = "name = ?"; $params[] = $name; }
    if ($description !== null) { $fields[] = "description = ?"; $params[] = $description; }
    if ($price !== null) { $fields[] = "price = ?"; $params[] = $price; }
    if ($category_id !== null) { $fields[] = "category_id = ?"; $params[] = $category_id; }
    if ($status !== null) { $fields[] = "status = ?"; $params[] = $status; }
    if ($image_url !== null) { $fields[] = "image_url = ?"; $params[] = $image_url; }
    if ($stock !== null) { $fields[] = "stock_quantity = ?"; $params[] = $stock; }

    if (empty($fields)) {
        sendJsonResponse(['error' => 'Aucune donnée à mettre à jour'], 400);
    }

    $params[] = $id;
    $sql = "UPDATE products SET " . implode(", ", $fields) . ", updated_at = NOW() WHERE id = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    sendJsonResponse([
        'success' => true,
        'message' => 'Produit mis à jour avec succès'
    ]);

} catch (Exception $e) {
    sendJsonResponse(['error' => 'Erreur serveur: ' . $e->getMessage()], 500);
}
?>
