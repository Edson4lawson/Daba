<?php
/**
 * Endpoint backend : Création d'un lead B2B / Demande de devis
 * Path: backend/leads/create.php
 */

// Configuration Headers & CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée. Utilisez POST.']);
    exit();
}

require_once __DIR__ . '/../config/db.php';

// Récupération des données reçues (JSON ou form-data)
$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);

if (!$input) {
    $input = $_POST;
}

$contact = isset($input['contact']) ? sanitize($input['contact']) : '';
$company = isset($input['company']) ? sanitize($input['company']) : '';
$phone = isset($input['phone']) ? sanitize($input['phone']) : '';
$email = isset($input['email']) ? sanitize($input['email']) : '';
$establishmentType = isset($input['establishment_type']) ? sanitize($input['establishment_type']) : 'Autre';
$frequency = isset($input['frequency']) ? sanitize($input['frequency']) : '';
$need = isset($input['need']) ? sanitize($input['need']) : '';

// Validation des champs obligatoires
if (empty($contact) || empty($phone) || empty($need)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Veuillez remplir les champs obligatoires : nom/contact, téléphone et votre besoin.'
    ]);
    exit();
}

try {
    // S'assurer que la table leads existe
    $tableSql = "CREATE TABLE IF NOT EXISTS leads (
        id INT AUTO_INCREMENT PRIMARY KEY,
        contact VARCHAR(191) NOT NULL,
        company VARCHAR(191) NULL,
        phone VARCHAR(50) NOT NULL,
        email VARCHAR(191) NULL,
        establishment_type VARCHAR(100) NULL,
        frequency VARCHAR(100) NULL,
        need TEXT NOT NULL,
        status VARCHAR(50) DEFAULT 'new',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    
    $pdo->exec($tableSql);

    // Insertion du lead
    $stmt = $pdo->prepare("INSERT INTO leads (contact, company, phone, email, establishment_type, frequency, need) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$contact, $company, $phone, $email, $establishmentType, $frequency, $need]);
    
    $leadId = $pdo->lastInsertId();

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Votre demande de devis B2B a bien été enregistrée. Notre équipe commerciale vous recontactera sous 48h.',
        'lead_id' => $leadId
    ]);
} catch (Exception $e) {
    // En cas d'erreur de BD, on enregistre dans un fichier log fallback et renvoie un succès gracieux
    $logMsg = date('Y-m-d H:i:s') . " - Lead B2B: Contact: $contact | Tel: $phone | Etab: $establishmentType | Devis: $need\n";
    @file_put_contents(__DIR__ . '/../logs/b2b_leads.log', $logMsg, FILE_APPEND);

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Votre demande de devis B2B a bien été transmise. Notre équipe vous recontactera sous 48h.'
    ]);
}
