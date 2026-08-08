<?php
require_once __DIR__ . '/../config/headers.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../middleware/auth.php';

header('Content-Type: application/json');

// Endpoint debug pour vérifier le rôle de l'utilisateur connecté
try {
    $user = authenticate();
    
    echo json_encode([
        'success' => true,
        'user_id' => $user['id'],
        'user_email' => $user['email'],
        'user_role' => $user['role'],
        'user_role_id' => $user['role_id'],
        'user_role_name' => $user['role_name'] ?? null,
        'is_admin' => $user['role'] === 'admin',
        'is_comptable' => $user['role'] === 'comptable',
        'can_access_invoices' => in_array($user['role'], ['admin', 'comptable'])
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
