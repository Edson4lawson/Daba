<?php
/**
 * API pour basculer l'état "En vedette" d'un produit
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
$feature = $data['feature'] ?? 'is_featured'; // Par défaut is_featured
$value = isset($data['value']) ? (int)$data['value'] : null;

if (!$id) {
    sendJsonResponse(['error' => 'ID du produit manquant'], 400);
}

// Liste des champs autorisés pour éviter l'injection SQL sur le nom de colonne
$allowedFeatures = ['is_featured', 'is_newest', 'is_bestseller', 'is_special_offer'];
if (!in_array($feature, $allowedFeatures)) {
    sendJsonResponse(['error' => 'Caractéristique non autorisée'], 400);
}

try {
    if ($value === null) {
        // Si aucune valeur n'est fournie, on bascule (toggle)
        $stmt = $pdo->prepare("SELECT $feature FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch();
        if (!$product) {
            sendJsonResponse(['error' => 'Produit non trouvé'], 404);
        }
        $value = $product[$feature] ? 0 : 1;
    }

    // Mettre à jour la colonne dynamique
    $stmt = $pdo->prepare("UPDATE products SET $feature = ? WHERE id = ?");
    $stmt->execute([$value, $id]);

    sendJsonResponse([
        'success' => true,
        'feature' => $feature,
        'value' => (bool)$value,
        'message' => 'État mis à jour avec succès'
    ]);

} catch (Exception $e) {
    sendJsonResponse(['error' => 'Erreur serveur: ' . $e->getMessage()], 500);
}
?>
