<?php
require_once __DIR__ . '/../../config/headers.php';
require_once __DIR__ . '/../../config/db.php';

// Script de test sans authentification pour diagnostiquer le problème
header('Content-Type: application/json');

try {
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
        ORDER BY i.created_at DESC
        LIMIT 5
    ";
    
    $stmt = $pdo->query($query);
    $invoices = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'invoices' => $invoices,
        'total' => count($invoices)
    ], JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
