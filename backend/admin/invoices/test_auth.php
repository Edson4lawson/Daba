<?php
require_once __DIR__ . '/../../config/headers.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../middleware/auth.php';
require_once __DIR__ . '/../../middleware/rbac.php';

header('Content-Type: application/json');

// Script de test avec authentification pour diagnostiquer le problème RBAC
try {
    // Authentifier l'utilisateur
    $user = authenticate();
    
    echo json_encode([
        'success' => true,
        'user_authenticated' => true,
        'user_id' => $user['id'],
        'user_email' => $user['email'],
        'user_role' => $user['role'],
        'has_admin_role' => hasRole($user, ['admin']),
        'has_comptable_role' => hasRole($user, ['comptable']),
        'has_any_allowed_role' => hasRole($user, ['admin', 'comptable'])
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ], JSON_PRETTY_PRINT);
}
