<?php
$host = 'localhost';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Drop and Create DB
    $pdo->exec("DROP DATABASE IF EXISTS `daba`");
    $pdo->exec("CREATE DATABASE `daba` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Database created.\n";
    
    // Select DB
    $pdo->exec("USE `daba`");
    
    // Execution de schema.sql
    $schema = file_get_contents(__DIR__ . '/database/sql/schema.sql');
    $pdo->exec($schema);
    echo "Schema executed.\n";
    
    // Execution de create_admin.sql
    $admin = file_get_contents(__DIR__ . '/database/sql/create_admin.sql');
    $pdo->exec($admin);
    echo "Admin created.\n";
    
} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage() . "\n");
}
