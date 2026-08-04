<?php
require_once __DIR__ . '/../../config/headers.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../middleware/auth.php';
require_once __DIR__ . '/../../middleware/rbac.php';

// Vérifier la méthode
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendJsonResponse(['error' => 'Méthode non autorisée'], 405);
}

// Authentifier l'utilisateur
$user = authenticate();

// Vérifier les rôles autorisés (admin, comptable)
if (!hasRole($user, ['admin', 'comptable'])) {
    sendJsonResponse(['error' => 'Accès non autorisé'], 403);
}

// Récupérer les filtres
$status = $_GET['status'] ?? '';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';
$search = $_GET['search'] ?? '';

try {
    // Construire la requête de base avec jointure sur orders et users
    $query = "
        SELECT 
            i.id,
            i.invoice_number,
            i.order_id,
            i.amount,
            i.status,
            i.created_at,
            o.total_amount as order_total,
            o.status as order_status,
            u.first_name,
            u.last_name,
            u.phone
        FROM invoices i
        LEFT JOIN orders o ON i.order_id = o.id
        LEFT JOIN users u ON o.user_id = u.id
        WHERE 1=1
    ";
    
    $params = [];
    
    // Filtre par statut
    if (!empty($status)) {
        $query .= " AND i.status = ?";
        $params[] = $status;
    }
    
    // Filtre par plage de dates
    if (!empty($dateFrom)) {
        $query .= " AND i.created_at >= ?";
        $params[] = $dateFrom . ' 00:00:00';
    }
    
    if (!empty($dateTo)) {
        $query .= " AND i.created_at <= ?";
        $params[] = $dateTo . ' 23:59:59';
    }
    
    // Filtre par recherche (numéro de facture ou nom client)
    if (!empty($search)) {
        $query .= " AND (i.invoice_number LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ? OR u.phone LIKE ?)";
        $searchParam = "%$search%";
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
    }
    
    // Trier par date de création décroissante
    $query .= " ORDER BY i.created_at DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $invoices = $stmt->fetchAll();
    
    sendJsonResponse([
        'invoices' => $invoices,
        'total' => count($invoices)
    ]);

} catch (PDOException $e) {
    error_log('Erreur get_all invoices: ' . $e->getMessage());
    sendJsonResponse(['error' => 'Erreur lors de la récupération des factures'], 500);
}
