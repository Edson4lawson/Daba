<?php
require_once __DIR__ . '/../config/headers.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../helpers/invoice_helper.php';

// Vérifier si la requête est de type POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(['error' => 'Méthode non autorisée'], 405);
}

// Récupérer les données de la requête
$data = getJsonData();

// Déterminer si c'est une commande invité ou connecté
$isGuestOrder = !empty($data['guest_checkout']) && !empty($data['phone']);

if ($isGuestOrder) {
    // Mode invité: pas de token requis, identification par téléphone
    $guestName = $data['name'] ?? '';
    $guestPhone = $data['phone'] ?? '';
    $guestAddress = $data['address'] ?? '';

    if (empty($guestName) || empty($guestPhone) || empty($guestAddress)) {
        sendJsonResponse(['error' => 'Nom, téléphone et adresse sont obligatoires pour le checkout invité'], 400);
    }

    // Valider le format du téléphone
    if (!preg_match('/^\+?[0-9]{10,15}$/', $guestPhone)) {
        sendJsonResponse(['error' => 'Format de téléphone invalide'], 400);
    }
} else {
    // Mode connecté: authentification requise
    $user = authenticate();
}

// Valider les données d'entrée selon le mode
if ($isGuestOrder) {
    // Mode invité: items sont fournis directement
    if (empty($data['items']) || !is_array($data['items'])) {
        sendJsonResponse(['error' => 'Les articles sont obligatoires pour le checkout invité'], 400);
    }
    $shippingAddress = $guestAddress;
} else {
    // Mode connecté: validation standard
    $requiredFields = ['shipping_address', 'payment_method'];
    foreach ($requiredFields as $field) {
        if (empty($data[$field])) {
            sendJsonResponse(['error' => 'Tous les champs sont obligatoires'], 400);
        }
    }
    $shippingAddress = $data['shipping_address'];
}

// Valider la méthode de paiement
$allowedPaymentMethods = ['credit_card', 'paypal', 'mobile_money', 'cash_on_delivery'];
if (!empty($data['payment_method']) && !in_array($data['payment_method'], $allowedPaymentMethods)) {
    sendJsonResponse(['error' => 'Méthode de paiement non valide'], 400);
}

try {
    $pdo->beginTransaction();

    // 1. Récupérer ou créer l'utilisateur (mode invité) ou utiliser l'utilisateur connecté
    if ($isGuestOrder) {
        // Mode invité: rechercher ou créer le client par téléphone
        $stmt = $pdo->prepare('SELECT id, first_name, last_name, address FROM users WHERE phone = ?');
        $stmt->execute([$guestPhone]);
        $user = $stmt->fetch();

        if ($user) {
            // Utilisateur existant: mettre à jour l'adresse si fournie
            $userId = $user['id'];
            $stmt = $pdo->prepare('UPDATE users SET address = ? WHERE id = ?');
            $stmt->execute([$guestAddress, $userId]);
        } else {
            // Nouveau client: créer le compte avec email fictif basé sur le téléphone
            $guestEmail = 'guest_' . preg_replace('/[^0-9]/', '', $guestPhone) . '@daba.local';
            $stmt = $pdo->prepare('
                INSERT INTO users (email, password, phone, first_name, last_name, address, role_id, created_at)
                VALUES (?, ?, ?, ?, ?, ?, (SELECT id FROM roles WHERE name = "customer"), NOW())
            ');
            $stmt->execute([$guestEmail, '', $guestPhone, $guestName, '', $guestAddress]);
            $userId = $pdo->lastInsertId();
        }
    } else {
        // Mode connecté: utiliser l'utilisateur authentifié
        $userId = $user['id'];
    }

    // 2. Récupérer les items (panier ou items directs)
    if ($isGuestOrder) {
        // Mode invité: items fournis directement
        $orderItems = [];
        foreach ($data['items'] as $item) {
            if (empty($item['product_id']) || !isset($item['quantity'])) {
                $pdo->rollBack();
                sendJsonResponse(['error' => 'Chaque article doit avoir un product_id et une quantity'], 400);
            }
            if ($item['quantity'] < 1) {
                $pdo->rollBack();
                sendJsonResponse(['error' => 'La quantité doit être supérieure à 0'], 400);
            }

            // Récupérer les détails du produit avec lock FOR UPDATE
            $stmt = $pdo->prepare('
                SELECT id, name, price, stock_quantity
                FROM products
                WHERE id = ? AND status = "published"
                FOR UPDATE
            ');
            $stmt->execute([$item['product_id']]);
            $product = $stmt->fetch();

            if (!$product) {
                $pdo->rollBack();
                sendJsonResponse(['error' => 'Produit non trouvé ou non disponible'], 400);
            }

            if ($product['stock_quantity'] < $item['quantity']) {
                $pdo->rollBack();
                sendJsonResponse([
                    'error' => 'Stock insuffisant pour le produit: ' . $product['name'],
                    'product_id' => $item['product_id'],
                    'available_quantity' => $product['stock_quantity'],
                    'requested_quantity' => $item['quantity']
                ], 400);
            }

            $orderItems[] = [
                'product_id' => $product['id'],
                'product_name' => $product['name'],
                'price' => $product['price'],
                'quantity' => $item['quantity'],
                'total' => $product['price'] * $item['quantity']
            ];
        }
    } else {
        // Mode connecté: récupérer le panier
        $cartQuery = "
            SELECT
                c.product_id,
                p.name as product_name,
                p.price,
                p.stock_quantity as available_quantity,
                c.quantity as requested_quantity
            FROM cart c
            JOIN products p ON c.product_id = p.id
            WHERE c.user_id = ? AND p.status = 'published'
            FOR UPDATE
        ";

        $stmt = $pdo->prepare($cartQuery);
        $stmt->execute([$userId]);
        $cartItems = $stmt->fetchAll();

        if (empty($cartItems)) {
            $pdo->rollBack();
            sendJsonResponse(['error' => 'Votre panier est vide'], 400);
        }

        // Convertir les items du panier en orderItems
        $orderItems = [];
        foreach ($cartItems as $item) {
            if ($item['available_quantity'] < $item['requested_quantity']) {
                $pdo->rollBack();
                sendJsonResponse([
                    'error' => 'Stock insuffisant pour le produit: ' . $item['product_name'],
                    'product_id' => $item['product_id'],
                    'available_quantity' => $item['available_quantity'],
                    'requested_quantity' => $item['requested_quantity']
                ], 400);
            }

            $itemTotal = $item['price'] * $item['requested_quantity'];
            $orderItems[] = [
                'product_id' => $item['product_id'],
                'product_name' => $item['product_name'],
                'price' => $item['price'],
                'quantity' => $item['requested_quantity'],
                'total' => $itemTotal
            ];
        }
    }

    if (empty($orderItems)) {
        $pdo->rollBack();
        sendJsonResponse(['error' => 'Aucun article à commander'], 400);
    }

    // 3. Calculer le total
    $subtotal = 0;
    foreach ($orderItems as $item) {
        $subtotal += $item['total'];
    }

    // Calculer les frais de livraison
    $shippingFee = calculateShippingFee($subtotal, $shippingAddress);
    $totalAmount = $subtotal + $shippingFee;

    // 4. Créer la commande
    $orderNumber = 'ORD-' . strtoupper(substr(uniqid(), -8));

    // Déterminer le canal
    $canal = $isGuestOrder ? 'site' : 'site';

    $stmt = $pdo->prepare('INSERT INTO orders (
        user_id, total_amount, status, canal, shipping_address, shipping_fee, tax_amount, notes
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');    
    
    $stmt->execute([
        $userId,
        $totalAmount,
        'pending',
        $canal,
        $shippingAddress,
        $shippingFee,
        0, // Pas de taxe par défaut ou déjà incluse
        $data['customer_note'] ?? null
    ]);
    
    $orderId = $pdo->lastInsertId();
    
    // 4. Ajouter les articles de la commande
    foreach ($orderItems as $item) {
        $stmt = $pdo->prepare('INSERT INTO order_items (order_id, product_id, product_name, quantity, price_at_purchase) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([
            $orderId,
            $item['product_id'],
            $item['product_name'],
            $item['quantity'],
            $item['price']
        ]);
        
        // Mettre à jour le stock
        $updateStockStmt = $pdo->prepare('UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?');
        $updateStockStmt->execute([$item['quantity'], $item['product_id']]);
    }
    
    // 5. Vider le panier (seulement en mode connecté)
    if (!$isGuestOrder) {
        $stmt = $pdo->prepare('DELETE FROM cart WHERE user_id = ?');
        $stmt->execute([$userId]);
    }
    
    // 6. Créer un enregistrement de paiement (si méthode de paiement fournie)
    if (!empty($data['payment_method'])) {
        $paymentStmt = $pdo->prepare('INSERT INTO payments (
            order_id, transaction_id, provider, amount, currency, status, metadata
        ) VALUES (?, ?, ?, ?, ?, ?, ?)');

        $transactionId = 'ORD-' . time() . '-' . mt_rand(1000, 9999);
        $paymentData = [
            'provider' => $data['payment_method'],
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
            'guest_checkout' => $isGuestOrder
        ];

        $paymentStmt->execute([
            $orderId,
            $transactionId,
            $data['payment_method'],
            $totalAmount,
            'XOF',
            'pending',
            json_encode($paymentData)
        ]);
    }
    
    $pdo->commit();
    
    // Générer automatiquement la facture
    $invoiceId = generateInvoice($pdo, $orderId, $totalAmount);
    
    sendJsonResponse([
        'message' => 'Commande créée avec succès',
        'order_id' => $orderId,
        'order_number' => $orderNumber,
        'status' => 'pending',
        'amount' => $totalAmount,
        'invoice_id' => $invoiceId
    ], 201);
    
} catch (Exception $e) {
    if ($pdo && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Erreur lors de la création de la commande: ' . $e->getMessage());
    sendJsonResponse(['error' => 'Erreur lors de la création de la commande. Veuillez vérifier vos informations.'], 500);
}

/**
 * Calcule les frais de livraison en fonction du montant et de l'adresse
 * TODO: Implémenter la logique de calcul des frais de livraison selon les règles métier
 */
function calculateShippingFee(float $subtotal, string $shippingAddress): int {
    // Pour l'instant, retourne 0 par défaut
    // La logique métier sera définie ultérieurement
    return 0;
}
?>
