<?php
require_once __DIR__ . '/../../config/headers.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../middleware/auth.php';
require_once __DIR__ . '/../../middleware/rbac.php';

// Vérifier la méthode
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(['error' => 'Méthode non autorisée'], 405);
}

// Authentifier l'utilisateur
$user = authenticate();

// Vérifier les rôles autorisés (admin, comptable)
if (!hasRole($user, ['admin', 'comptable'])) {
    sendJsonResponse(['error' => 'Accès non autorisé'], 403);
}

// Récupérer les données
$data = getJsonData();

// Valider les données
if (empty($data['invoice_id']) || empty($data['status'])) {
    sendJsonResponse(['error' => 'invoice_id et status sont requis'], 400);
}

$invoiceId = (int)$data['invoice_id'];
$newStatus = $data['status'];

// Valider le statut
$allowedStatuses = ['pending', 'paid', 'cancelled'];
if (!in_array($newStatus, $allowedStatuses)) {
    sendJsonResponse(['error' => 'Statut invalide. Valeurs autorisées: pending, paid, cancelled'], 400);
}

try {
    // Récupérer la facture actuelle
    $stmt = $pdo->prepare('SELECT * FROM invoices WHERE id = ?');
    $stmt->execute([$invoiceId]);
    $invoice = $stmt->fetch();

    if (!$invoice) {
        sendJsonResponse(['error' => 'Facture non trouvée'], 404);
    }

    // Mettre à jour le statut
    $stmt = $pdo->prepare('UPDATE invoices SET status = ? WHERE id = ?');
    $stmt->execute([$newStatus, $invoiceId]);

    sendJsonResponse([
        'message' => 'Statut de la facture mis à jour avec succès',
        'invoice_id' => $invoiceId,
        'status' => $newStatus
    ]);

} catch (PDOException $e) {
    error_log('Erreur update_status invoice: ' . $e->getMessage());
    sendJsonResponse(['error' => 'Erreur lors de la mise à jour du statut'], 500);
}
