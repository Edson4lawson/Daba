<?php
/**
 * Stripe Webhook Handler - Daba
 * Traite les webhooks Stripe pour confirmer les paiements
 * 
 * @endpoint POST /payment/stripe-webhook
 */

require_once __DIR__ . '/../config/headers.php';
require_once __DIR__ . '/../config/db.php';

// Récupérer le payload du webhook
$payload = @file_get_contents('php://input');
$sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'];

// Récupérer la clé secrète du webhook depuis l'environnement
$webhookSecret = getenv('STRIPE_WEBHOOK_SECRET');

if (!$webhookSecret) {
    error_log('STRIPE_WEBHOOK_SECRET non configuré');
    http_response_code(500);
    echo json_encode(['error' => 'Configuration webhook manquante']);
    exit;
}

// Vérifier la signature Stripe
try {
    $event = null;
    
    // Note: En production, utilisez la bibliothèque Stripe PHP
    // Pour cet exemple, nous utilisons une vérification simplifiée
    // Installer: composer require stripe/stripe-php
    
    // Vérification de signature (simplifiée - utiliser Stripe SDK en production)
    if (!$sigHeader) {
        throw new Exception('Signature webhook manquante');
    }
    
    // Parser l'événement
    $event = json_decode($payload, true);
    
    if (!$event || !isset($event['type'])) {
        throw new Exception('Payload webhook invalide');
    }
    
    // Traiter l'événement selon son type
    switch ($event['type']) {
        case 'checkout.session.completed':
            handleCheckoutSessionCompleted($event['data']['object']);
            break;
            
        case 'payment_intent.succeeded':
            handlePaymentIntentSucceeded($event['data']['object']);
            break;
            
        case 'payment_intent.payment_failed':
            handlePaymentIntentFailed($event['data']['object']);
            break;
            
        case 'charge.refunded':
            handleChargeRefunded($event['data']['object']);
            break;
            
        default:
            // Événement non géré mais reçu avec succès
            error_log('Webhook non géré: ' . $event['type']);
    }
    
    http_response_code(200);
    echo json_encode(['status' => 'success']);
    
} catch (Exception $e) {
    error_log('Erreur webhook Stripe: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}

/**
 * Traite la complétion d'une session checkout
 */
function handleCheckoutSessionCompleted($session) {
    global $pdo;
    
    try {
        $orderId = $session['metadata']['order_id'] ?? null;
        
        if (!$orderId) {
            error_log('Order ID manquant dans le webhook');
            return;
        }
        
        // Mettre à jour le statut de la commande
        $stmt = $pdo->prepare('
            UPDATE orders 
            SET status = ?, payment_status = ?, updated_at = NOW()
            WHERE id = ?
        ');
        
        $stmt->execute(['processing', 'paid', $orderId]);
        
        // Mettre à jour l'enregistrement de paiement
        $stmt = $pdo->prepare('
            UPDATE payments 
            SET status = ?, transaction_id = ?, metadata = ?
            WHERE order_id = ?
        ');
        
        $paymentData = [
            'stripe_session_id' => $session['id'],
            'stripe_payment_intent' => $session['payment_intent'],
            'customer_email' => $session['customer_details']['email'] ?? null,
            'completed_at' => date('Y-m-d H:i:s')
        ];
        
        $stmt->execute([
            'succeeded',
            $session['payment_intent'] ?? null,
            json_encode($paymentData),
            $orderId
        ]);
        
        // Envoyer email de confirmation (à implémenter)
        // sendOrderConfirmationEmail($orderId);
        
        error_log("Commande $orderId payée avec succès via Stripe");
        
    } catch (PDOException $e) {
        error_log('Erreur mise à jour commande webhook: ' . $e->getMessage());
    }
}

/**
 * Traite un paiement réussi
 */
function handlePaymentIntentSucceeded($paymentIntent) {
    global $pdo;
    
    try {
        $orderId = $paymentIntent['metadata']['order_id'] ?? null;
        
        if ($orderId) {
            $stmt = $pdo->prepare('
                UPDATE orders 
                SET payment_status = ?, updated_at = NOW()
                WHERE id = ?
            ');
            $stmt->execute(['paid', $orderId]);
        }
        
    } catch (PDOException $e) {
        error_log('Erreur payment_intent.succeeded: ' . $e->getMessage());
    }
}

/**
 * Traite un échec de paiement
 */
function handlePaymentIntentFailed($paymentIntent) {
    global $pdo;
    
    try {
        $orderId = $paymentIntent['metadata']['order_id'] ?? null;
        
        if ($orderId) {
            $stmt = $pdo->prepare('
                UPDATE orders 
                SET status = ?, payment_status = ?, updated_at = NOW()
                WHERE id = ?
            ');
            $stmt->execute(['cancelled', 'failed', $orderId]);
        }
        
        // Logger la raison de l'échec
        $errorMessage = $paymentIntent['last_payment_error']['message'] ?? 'Erreur inconnue';
        error_log("Paiement échoué pour commande $orderId: $errorMessage");
        
    } catch (PDOException $e) {
        error_log('Erreur payment_intent.payment_failed: ' . $e->getMessage());
    }
}

/**
 * Traite un remboursement
 */
function handleChargeRefunded($charge) {
    global $pdo;
    
    try {
        $paymentIntentId = $charge['payment_intent'] ?? null;
        
        if ($paymentIntentId) {
            // Trouver la commande associée
            $stmt = $pdo->prepare('
                SELECT order_id FROM payments 
                WHERE transaction_id = ? OR metadata LIKE ?
                LIMIT 1
            ');
            $stmt->execute([$paymentIntentId, "%$paymentIntentId%"]);
            $payment = $stmt->fetch();
            
            if ($payment) {
                $stmt = $pdo->prepare('
                    UPDATE orders 
                    SET status = ?, updated_at = NOW()
                    WHERE id = ?
                ');
                $stmt->execute(['refunded', $payment['order_id']]);
                
                error_log("Commande {$payment['order_id']} remboursée");
            }
        }
        
    } catch (PDOException $e) {
        error_log('Erreur charge.refunded: ' . $e->getMessage());
    }
}
