<?php
require_once __DIR__ . '/../../config/headers.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../middleware/auth.php';

// Authentifier l'administrateur
$user = authenticate();
$allowedRoles = ['admin'];
if (!in_array($user['role'], $allowedRoles)) {
    sendJsonResponse(['error' => 'Accès refusé'], 403);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(['error' => 'Méthode non autorisée'], 405);
}

$data = getJsonData();
$id = $data['id'] ?? null;

if (!$id) {
    sendJsonResponse(['error' => 'ID manquant'], 400);
}

try {
    // 1. Supprimer les items de la commande d'abord (contrainte FK)
    $stmt = $pdo->prepare("DELETE FROM order_items WHERE order_id = ?");
    $stmt->execute([$id]);

    // 2. Supprimer la commande
    $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
    $stmt->execute([$id]);

    if ($stmt->rowCount() === 0) {
        sendJsonResponse(['error' => 'Commande non trouvée'], 404);
    }

    sendJsonResponse(['message' => 'Commande supprimée avec succès']);

} catch (Exception $e) {
    sendJsonResponse(['error' => 'Erreur serveur', 'message' => $e->getMessage()], 500);
}
?>
