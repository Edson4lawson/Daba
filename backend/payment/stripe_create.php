<?php
/**
 * Création de session de paiement Stripe - Daba
 * Crée une session Checkout Stripe pour le paiement
 * 
 * @endpoint POST /payment/stripe-create
 */

require_once __DIR__ . '/../config/headers.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../middleware/auth.php';

// Vérifier la méthode
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(['error' => 'Méthode non autorisée'], 405);
}

// Authentifier l'utilisateur
$user = authenticate();

// Récupérer les données
$data = getJsonData();

// Valider les données
if (empty($data['order_id'])) {
    sendJsonResponse(['error' => 'ID de commande requis'], 400);
}

$orderId = (int)$data['order_id'];

try {
    $pdo->beginTransaction();
    
    // Récupérer la commande
    $stmt = $pdo->prepare('
        SELECT o.*, u.email 
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        WHERE o.id = ? AND o.user_id = ?
    ');
    $stmt->execute([$orderId, $user['id']]);
    $order = $stmt->fetch();
    
    if (!$order) {
        $pdo->rollBack();
        sendJsonResponse(['error' => 'Commande non trouvée'], 404);
    }
    
    if ($order['payment_status'] === 'paid') {
        $pdo->rollBack();
        sendJsonResponse(['error' => 'Cette commande a déjà été payée'], 400);
    }
    
    // Récupérer les articles de la commande
    $stmt = $pdo->prepare('
        SELECT product_name, quantity, price_at_purchase
        FROM order_items
        WHERE order_id = ?
    ');
    $stmt->execute([$orderId]);
    $items = $stmt->fetchAll();
    
    // Construire les line items pour Stripe
    $lineItems = [];
    foreach ($items as $item) {
        $lineItems[] = [
            'price_data' => [
                'currency' => 'xof',
                'product_data' => [
                    'name' => $item['product_name'],
                ],
                'unit_amount' => (int)($item['price_at_purchase'] * 100), // Stripe utilise les centimes
            ],
            'quantity' => $item['quantity'],
        ];
    }
    
    // Ajouter les frais de livraison si applicable
    if ($order['shipping_fee'] > 0) {
        $lineItems[] = [
            'price_data' => [
                'currency' => 'xof',
                'product_data' => [
                    'name' => 'Frais de livraison',
                ],
                'unit_amount' => (int)($order['shipping_fee'] * 100),
            ],
            'quantity' => 1,
        ];
    }
    
    // Configuration Stripe
    $stripeSecretKey = getenv('STRIPE_SECRET_KEY');
    
    if (!$stripeSecretKey) {
        $pdo->rollBack();
        sendJsonResponse(['error' => 'Configuration Stripe manquante'], 500);
    }
    
    // En production, utiliser la bibliothèque Stripe PHP
    // Pour cet exemple, nous retournons les données pour que le frontend crée la session
    // En production, utilisez:
    // \Stripe\Stripe::setApiKey($stripeSecretKey);
    // $session = \Stripe\Checkout\Session::create([...]);
    
    // Pour l'instant, nous simulons la création
    $checkoutSession = [
        'id' => 'cs_test_' . uniqid(),
        'url' => 'https://checkout.stripe.com/pay/' . uniqid(),
        'amount_total' => (int)($order['total_amount'] * 100),
        'currency' => 'xof'
    ];
    
    $pdo->commit();
    
    sendJsonResponse([
        'checkout_session_id' => $checkoutSession['id'],
        'checkout_url' => $checkoutSession['url'],
        'amount' => $order['total_amount'],
        'currency' => 'xof'
    ]);
    
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Erreur création session Stripe: ' . $e->getMessage());
    sendJsonResponse(['error' => 'Erreur lors de la création du paiement'], 500);
}
