<?php
require_once __DIR__ . '/../config/headers.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../middleware/auth.php';

// Vérifier si la requête est de type POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(['error' => 'Méthode non autorisée'], 405);
}

// Authentifier l'utilisateur
$user = authenticate();

// Récupérer les données de la requête
$data = getJsonData();

// Valider les données d'entrée
$requiredFields = ['order_id', 'payment_method'];
foreach ($requiredFields as $field) {
    if (empty($data[$field])) {
        sendJsonResponse(['error' => 'Tous les champs sont obligatoires'], 400);
    }
}

$orderId = (int)$data['order_id'];
$paymentMethod = $data['payment_method'];

// Valider la méthode de paiement
$allowedPaymentMethods = ['credit_card', 'paypal', 'mobile_money', 'cash_on_delivery'];
if (!in_array($paymentMethod, $allowedPaymentMethods)) {
    sendJsonResponse(['error' => 'Méthode de paiement non valide'], 400);
}

try {
    $pdo->beginTransaction();
    
    // 1. Vérifier que la commande appartient bien à l'utilisateur
    $stmt = $pdo->prepare('
        SELECT o.*, p.status as payment_status 
        FROM orders o
        LEFT JOIN payments p ON o.id = p.order_id
        WHERE o.id = ? AND o.user_id = ?
        FOR UPDATE
    ');
    $stmt->execute([$orderId, $user['id']]);
    $order = $stmt->fetch();
    
    if (!$order) {
        $pdo->rollBack();
        sendJsonResponse(['error' => 'Commande non trouvée'], 404);
    }
    
    // 2. Vérifier que la commande n'a pas déjà été payée
    if ($order['payment_status'] === 'paid') {
        $pdo->rollBack();
        sendJsonResponse(['error' => 'Cette commande a déjà été payée'], 400);
    }
    
    // 3. Traiter le paiement en fonction de la méthode choisie
    $paymentResult = processPayment($order, $data, $paymentMethod);
    
    if (!$paymentResult['success']) {
        $pdo->rollBack();
        sendJsonResponse(['error' => $paymentResult['message']], 400);
    }
    
    // 4. Mettre à jour le statut de la commande et du paiement
    $updateOrderStmt = $pdo->prepare('
        UPDATE orders 
        SET status = ?, payment_status = ?, updated_at = NOW()
        WHERE id = ?
    ');
    
    $updateOrderStmt->execute([
        $paymentResult['order_status'],
        $paymentResult['payment_status'],
        $orderId
    ]);
    
    // 5. Mettre à jour ou créer l'enregistrement de paiement
    $paymentDetails = json_encode([
        'payment_method' => $paymentMethod,
        'transaction_id' => $paymentResult['transaction_id'] ?? null,
        'status' => $paymentResult['payment_status'],
        'amount' => $order['total_amount'],
        'processed_at' => date('Y-m-d H:i:s'),
        'details' => $paymentResult['details'] ?? []
    ]);
    
    // Vérifier si un paiement existe déjà pour cette commande
    $paymentStmt = $pdo->prepare('SELECT id FROM payments WHERE order_id = ?');
    $paymentStmt->execute([$orderId]);
    $existingPayment = $paymentStmt->fetch();
    
    if ($existingPayment) {
        // Mettre à jour le paiement existant
        $updatePaymentStmt = $pdo->prepare('
            UPDATE payments 
            SET 
                status = ?,
                transaction_id = ?,
                payment_details = ?,
                updated_at = NOW()
            WHERE order_id = ?
        ');
        
        $updatePaymentStmt->execute([
            $paymentResult['payment_status'],
            $paymentResult['transaction_id'] ?? null,
            $paymentDetails,
            $orderId
        ]);
    } else {
        // Créer un nouvel enregistrement de paiement
        $insertPaymentStmt = $pdo->prepare('
            INSERT INTO payments (
                order_id, amount, payment_method, 
                transaction_id, status, payment_details
            ) VALUES (?, ?, ?, ?, ?, ?)
        ');
        
        $insertPaymentStmt->execute([
            $orderId,
            $order['total_amount'],
            $paymentMethod,
            $paymentResult['transaction_id'] ?? null,
            $paymentResult['payment_status'],
            $paymentDetails
        ]);
    }
    
    $pdo->commit();
    
    // 6. Envoyer un email de confirmation (à implémenter)
    // $this->sendOrderConfirmationEmail($user, $order, $paymentResult);
    
    // 7. Retourner la réponse
    sendJsonResponse([
        'success' => true,
        'message' => $paymentResult['message'],
        'order_id' => $orderId,
        'order_status' => $paymentResult['order_status'],
        'payment_status' => $paymentResult['payment_status'],
        'transaction_id' => $paymentResult['transaction_id'] ?? null,
        'confirmation_url' => $paymentResult['confirmation_url'] ?? null
    ]);
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Erreur lors du traitement du paiement: ' . $e->getMessage());
    sendJsonResponse(['error' => 'Erreur lors du traitement du paiement: ' . $e->getMessage()], 500);
}

/**
 * Traite le paiement en fonction de la méthode choisie
 */
function processPayment($order, $data, $paymentMethod) {
    switch ($paymentMethod) {
        case 'credit_card':
            return processCreditCardPayment($order, $data);
        case 'paypal':
            return processPayPalPayment($order, $data);
        case 'mobile_money':
            return processMobileMoneyPayment($order, $data);
        case 'cash_on_delivery':
            return processCashOnDeliveryPayment($order, $data);
        default:
            return [
                'success' => false,
                'message' => 'Méthode de paiement non prise en charge'
            ];
    }
}

/**
 * Traite un paiement par carte bancaire
 */
function processCreditCardPayment($order, $data) {
    // Valider les données de la carte (simplifié pour l'exemple)
    $requiredFields = ['card_number', 'card_expiry', 'card_cvc', 'card_name'];
    foreach ($requiredFields as $field) {
        if (empty($data[$field])) {
            return [
                'success' => false,
                'message' => 'Tous les champs de la carte sont obligatoires'
            ];
        }
    }
    
    // Ici, vous intégreriez normalement avec une passerelle de paiement comme Stripe
    // Pour cet exemple, on simule un paiement réussi dans 90% des cas
    $success = (mt_rand(1, 100) <= 90);
    
    if ($success) {
        return [
            'success' => true,
            'message' => 'Paiement par carte accepté',
            'order_status' => 'processing',
            'payment_status' => 'paid',
            'transaction_id' => 'CARD' . time() . mt_rand(1000, 9999),
            'details' => [
                'last4' => substr(str_replace(' ', '', $data['card_number']), -4),
                'brand' => 'VISA', // Détecter la marque de la carte
                'expiry' => $data['card_expiry']
            ]
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Paiement refusé par la banque. Veuillez vérifier vos informations ou utiliser un autre moyen de paiement.'
        ];
    }
}

/**
 * Traite un paiement PayPal
 */
function processPayPalPayment($order, $data) {
    // Ici, vous intégreriez avec l'API PayPal
    // Pour cet exemple, on simule une redirection vers PayPal
    
    $paypalUrl = 'https://www.sandbox.paypal.com/checkoutnow?token=' . uniqid('PAYPAL-', true);
    
    return [
        'success' => true,
        'message' => 'Redirection vers PayPal requise',
        'order_status' => 'pending',
        'payment_status' => 'pending',
        'confirmation_url' => $paypalUrl,
        'details' => [
            'redirect_url' => $paypalUrl,
            'payment_id' => 'PAYPAL-' . time()
        ]
    ];
}

/**
 * Traite un paiement par Mobile Money
 */
function processMobileMoneyPayment($order, $data) {
    // Valider le numéro de téléphone
    if (empty($data['phone_number'])) {
        return [
            'success' => false,
            'message' => 'Le numéro de téléphone est requis pour le paiement Mobile Money'
        ];
    }
    
    // Ici, vous intégreriez avec une passerelle Mobile Money comme MTN Mobile Money, Orange Money, etc.
    // Pour cet exemple, on simule une demande de paiement
    
    $transactionId = 'MM' . time() . mt_rand(1000, 9999);
    
    return [
        'success' => true,
        'message' => 'Demande de paiement Mobile Money envoyée. Veuillez valider le paiement depuis votre téléphone.',
        'order_status' => 'pending',
        'payment_status' => 'pending',
        'transaction_id' => $transactionId,
        'details' => [
            'phone_number' => $data['phone_number'],
            'amount' => $order['total_amount'],
            'transaction_id' => $transactionId
        ]
    ];
}

/**
 * Traite un paiement à la livraison (Cash on Delivery)
 */
function processCashOnDeliveryPayment($order, $data) {
    // Le paiement à la livraison ne nécessite pas de traitement immédiat
    // La commande est validée et le paiement sera collecté à la livraison
    
    $transactionId = 'COD' . time() . mt_rand(1000, 9999);
    
    return [
        'success' => true,
        'message' => 'Commande validée. Le paiement sera effectué à la livraison.',
        'order_status' => 'processing',
        'payment_status' => 'pending',
        'transaction_id' => $transactionId,
        'details' => [
            'payment_method' => 'cash_on_delivery',
            'amount' => $order['total_amount'],
            'transaction_id' => $transactionId,
            'note' => 'Paiement à la livraison'
        ]
    ];
}
?>
