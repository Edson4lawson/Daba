<?php
require_once __DIR__ . '/../../config/headers.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../middleware/auth.php';

// Authentifier
$user = authenticate();
$allowedRoles = ['admin', 'commercial', 'comptable'];
if (!in_array($user['role'], $allowedRoles)) {
    sendJsonResponse(['error' => 'Accès refusé'], 403);
}

echo json_encode([]);
?>