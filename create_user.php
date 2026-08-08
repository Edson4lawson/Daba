<?php
require_once __DIR__ . '/backend/config/db.php';

$email = 'test@daba.com';
$password = 'password123';
$hashed = password_hash($password, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->execute([$hashed, $email]);
        echo "Utilisateur de test mis à jour.\n";
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, 'admin')");
        $stmt->execute([$email, $hashed]);
        echo "Utilisateur de test créé.\n";
    }
    echo "Identifiants : $email / $password\n";
} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
}
