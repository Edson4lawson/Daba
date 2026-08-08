<?php
require_once __DIR__ . '/../../config/headers.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../middleware/auth.php';
require_once __DIR__ . '/../../middleware/rbac.php';

header('Content-Type: application/json');

// Script de debug pour tester l'authentification avec un token spécifique
$token = $_GET['token'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? '';

echo json_encode([
    'step1_token_received' => !empty($token),
    'step1_token_length' => strlen($token),
    'step1_token_prefix' => substr($token, 0, 20) . '...'
], JSON_PRETTY_PRINT);

try {
    $user = authenticate();
    
    echo json_encode([
        'step2_authenticated' => true,
        'step2_user_id' => $user['id'],
        'step2_user_email' => $user['email'],
        'step2_user_role' => $user['role'],
        'step2_user_role_name' => $user['role_name'] ?? null,
    ], JSON_PRETTY_PRINT);
    
    $hasAdminRole = hasRole($user, ['admin']);
    $hasComptableRole = hasRole($user, ['comptable']);
    $hasAnyAllowedRole = hasRole($user, ['admin', 'comptable']);
    
    echo json_encode([
        'step3_has_admin_role' => $hasAdminRole,
        'step3_has_comptable_role' => $hasComptableRole,
        'step3_has_any_allowed_role' => $hasAnyAllowedRole,
        'step3_can_access_invoices' => $hasAnyAllowedRole
    ], JSON_PRETTY_PRINT);
    
    if ($hasAnyAllowedRole) {
        // Tester la requête réelle
        $query = "
            SELECT 
                i.id,
                i.invoice_number,
                i.order_id,
                i.amount,
                i.status,
                i.created_at,
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
            'step4_query_executed' => true,
            'step4_invoice_count' => count($invoices),
            'step4_sample_invoices' => $invoices
        ], JSON_PRETTY_PRINT);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ], JSON_PRETTY_PRINT);
}
