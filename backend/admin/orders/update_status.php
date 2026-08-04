<?php
require_once __DIR__ . '/../../config/headers.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../middleware/auth.php';

// Authentifier l'administrateur
$user = authenticate();
$allowedRoles = ['admin', 'commercial', 'magasinier'];
if (!in_array($user['role'], $allowedRoles)) {
    sendJsonResponse(['error' => 'Accès refusé'], 403);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(['error' => 'Méthode non autorisée'], 405);
}

$data = getJsonData();
$order_id = $data['order_id'] ?? null;
$status = $data['status'] ?? null;

if (!$order_id || !$status) {
    sendJsonResponse(['error' => 'Données manquantes'], 400);
}

// Vérifier si le statut est valide
$valid_statuses = ['pending', 'processing', 'shipped', 'completed', 'cancelled'];
if (!in_array($status, $valid_statuses)) {
    sendJsonResponse(['error' => 'Statut invalide'], 400);
}

try {
    $stmt = $pdo->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$status, $order_id]);

    if ($stmt->rowCount() === 0) {
        sendJsonResponse(['error' => 'Commande non trouvée ou aucun changement'], 404);
    }

    sendJsonResponse(['message' => 'Statut de la commande mis à jour avec succès']);

} catch (Exception $e) {
    sendJsonResponse(['error' => 'Erreur serveur', 'message' => $e->getMessage()], 500);
}
?>
