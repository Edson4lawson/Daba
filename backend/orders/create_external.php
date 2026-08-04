<?php
/**
 * Endpoint de création de commande externe (n8n/WhatsApp)
 * Permet de créer une commande sans utilisateur connecté via API Key
 * 
 * @endpoint POST /api/orders/create_external.php
 * @header X-API-Key: string
 * @body { "phone": "string", "name": "string", "address": "string", "items": [...], "notes": "...", "delivery_date": "...", "delivery_time": "..." }
 */

require_once __DIR__ . '/../config/headers.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/logging.php';
require_once __DIR__ . '/../middleware/rate_limit.php';

// Rate limiting: 20 requêtes par minute par IP
rateLimit('external_order', 20, 60);

// Vérifier la méthode HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(['error' => 'Méthode non autorisée'], 405);
}

// 1. Vérifier la clé API
$apiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';
$expectedApiKey = getenv('EXTERNAL_API_KEY');

if (empty($apiKey)) {
    sendJsonResponse(['error' => 'Clé API manquante'], 401);
}

if (empty($expectedApiKey)) {
    error_log('EXTERNAL_API_KEY non configuré');
    sendJsonResponse(['error' => 'Configuration API manquante'], 500);
}

if (!hash_equals($expectedApiKey, $apiKey)) {
    logSecurityEvent('Tentative de création de commande externe avec clé API invalide');
    sendJsonResponse(['error' => 'Clé API invalide'], 401);
}

// 2. Lire et valider le JSON
$data = getJsonData();

// Validation des champs obligatoires
$requiredFields = ['phone', 'name', 'address', 'items'];
foreach ($requiredFields as $field) {
    if (empty($data[$field])) {
        sendJsonResponse(['error' => "Champ '$field' manquant"], 400);
    }
}

// Validation du téléphone
$phone = sanitize($data['phone']);
if (!preg_match('/^[0-9+]{8,15}$/', $phone)) {
    sendJsonResponse(['error' => 'Format de numéro de téléphone invalide'], 400);
}

// Validation des items
if (!is_array($data['items']) || empty($data['items'])) {
    sendJsonResponse(['error' => 'La commande doit contenir au moins un article'], 400);
}

// Validation de delivery_date si fourni
if (!empty($data['delivery_date'])) {
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['delivery_date'])) {
        sendJsonResponse(['error' => 'Format de delivery_date invalide (attendu: YYYY-MM-DD)'], 400);
    }
    // Vérifier que la date est valide
    $dateParts = explode('-', $data['delivery_date']);
    if (!checkdate($dateParts[1], $dateParts[2], $dateParts[0])) {
        sendJsonResponse(['error' => 'delivery_date n\'est pas une date valide'], 400);
    }
}

// Validation de delivery_time si fourni
if (!empty($data['delivery_time'])) {
    if (!preg_match('/^\d{2}:\d{2}$/', $data['delivery_time'])) {
        sendJsonResponse(['error' => 'Format de delivery_time invalide (attendu: HH:MM)'], 400);
    }
    // Vérifier que l'heure est valide
    $timeParts = explode(':', $data['delivery_time']);
    if ($timeParts[0] < 0 || $timeParts[0] > 23 || $timeParts[1] < 0 || $timeParts[1] > 59) {
        sendJsonResponse(['error' => 'delivery_time n\'est pas une heure valide'], 400);
    }
}

foreach ($data['items'] as $item) {
    if (empty($item['product_id']) || !isset($item['quantity'])) {
        sendJsonResponse(['error' => 'Chaque article doit avoir un product_id et une quantity'], 400);
    }
    if ($item['quantity'] < 1) {
        sendJsonResponse(['error' => 'La quantité doit être supérieure à 0'], 400);
    }
}

try {
    $pdo->beginTransaction();
    
    // 3. Rechercher ou créer le client
    $stmt = $pdo->prepare('SELECT id, first_name, last_name, address FROM users WHERE phone = ?');
    $stmt->execute([$phone]);
    $user = $stmt->fetch();
    
    if ($user) {
        // Utilisateur existant
        $userId = $user['id'];
        // Mettre à jour l'adresse si fournie
        if (!empty($data['address'])) {
            $stmt = $pdo->prepare('UPDATE users SET address = ? WHERE id = ?');
            $stmt->execute([$data['address'], $userId]);
        }
    } else {
        // Créer un nouvel utilisateur
        $nameParts = explode(' ', trim($data['name']), 2);
        $firstName = $nameParts[0];
        $lastName = $nameParts[1] ?? '';
        
        // Mot de passe temporaire (sera réinitialisé)
        $tempPassword = bin2hex(random_bytes(16));
        $hashedPassword = password_hash($tempPassword, PASSWORD_ARGON2ID);
        
        $stmt = $pdo->prepare('
            INSERT INTO users (email, password, first_name, last_name, phone, address, role, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ');
        
        // Email temporaire basé sur le téléphone
        $tempEmail = 'temp_' . $phone . '@daba.local';
        
        $stmt->execute([
            $tempEmail,
            $hashedPassword,
            $firstName,
            $lastName,
            $phone,
            $data['address'],
            'customer'
        ]);
        
        $userId = $pdo->lastInsertId();
    }
    
    // 4. Vérifier les produits et calculer le total
    // NOTE: Logique similaire à create.php mais adaptée pour recevoir les items directement
    // au lieu d'utiliser le panier de l'utilisateur
    $totalAmount = 0;
    $orderItems = [];
    
    foreach ($data['items'] as $item) {
        $productId = (int)$item['product_id'];
        $quantity = (int)$item['quantity'];
        
        // Vérifier que le produit existe et est disponible
        $stmt = $pdo->prepare('SELECT id, name, price, stock_quantity FROM products WHERE id = ? AND status = "published" FOR UPDATE');
        $stmt->execute([$productId]);
        $product = $stmt->fetch();
        
        if (!$product) {
            $pdo->rollBack();
            sendJsonResponse([
                'success' => false,
                'message' => "Produit ID $productId introuvable ou indisponible"
            ], 404);
        }
        
        // Vérifier le stock
        if ($product['stock_quantity'] < $quantity) {
            $pdo->rollBack();
            sendJsonResponse([
                'success' => false,
                'message' => "Stock insuffisant pour le produit '{$product['name']}'",
                'product_id' => $productId,
                'available_quantity' => $product['stock_quantity'],
                'requested_quantity' => $quantity
            ], 400);
        }
        
        $itemTotal = $product['price'] * $quantity;
        $totalAmount += $itemTotal;
        
        $orderItems[] = [
            'product_id' => $productId,
            'product_name' => $product['name'],
            'quantity' => $quantity,
            'price_at_purchase' => $product['price']
        ];
    }
    
    // Frais de livraison (à fournir dans la requête ou calculé ultérieurement)
    // TODO: Implémenter la logique de calcul des frais de livraison selon les règles métier
    $shippingFee = isset($data['shipping_fee']) ? (float)$data['shipping_fee'] : 0;
    $totalAmount += $shippingFee;
    
    // 5. Créer la commande
    $stmt = $pdo->prepare('
        INSERT INTO orders (user_id, total_amount, status, canal, shipping_address, shipping_fee, notes, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
    ');

    $orderStatus = 'pending';
    $notes = $data['notes'] ?? '';

    // Ajouter les informations de livraison au notes si fournies
    if (!empty($data['delivery_date']) || !empty($data['delivery_time'])) {
        $deliveryInfo = [];
        if (!empty($data['delivery_date'])) $deliveryInfo[] = 'Date: ' . $data['delivery_date'];
        if (!empty($data['delivery_time'])) $deliveryInfo[] = 'Heure: ' . $data['delivery_time'];
        if (!empty($notes)) $notes .= "\n\n";
        $notes .= 'Livraison souhaitée: ' . implode(', ', $deliveryInfo);
    }

    $stmt->execute([
        $userId,
        $totalAmount,
        $orderStatus,
        'whatsapp',
        $data['address'],
        $shippingFee,
        $notes
    ]);
    
    $orderId = $pdo->lastInsertId();
    
    // 6. Insérer les articles de la commande
    foreach ($orderItems as $item) {
        $stmt = $pdo->prepare('
            INSERT INTO order_items (order_id, product_id, product_name, quantity, price_at_purchase)
            VALUES (?, ?, ?, ?, ?)
        ');
        $stmt->execute([
            $orderId,
            $item['product_id'],
            $item['product_name'],
            $item['quantity'],
            $item['price_at_purchase']
        ]);
    }
    
    // 7. Déduire le stock
    foreach ($data['items'] as $item) {
        $stmt = $pdo->prepare('UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?');
        $stmt->execute([(int)$item['quantity'], (int)$item['product_id']]);
    }
    
    // 8. Créer l'enregistrement de paiement (en attente)
    $stmt = $pdo->prepare('
        INSERT INTO payments (order_id, transaction_id, provider, amount, currency, status, metadata)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ');

    $transactionId = 'EXT-' . time() . '-' . mt_rand(1000, 9999);
    $metadata = json_encode([
        'source' => 'external_api',
        'created_at' => date('Y-m-d H:i:s'),
        'delivery_date' => $data['delivery_date'] ?? null,
        'delivery_time' => $data['delivery_time'] ?? null
    ]);

    $stmt->execute([
        $orderId,
        $transactionId,
        'external',
        $totalAmount,
        'XOF',
        'pending',
        $metadata
    ]);
    
    $pdo->commit();
    
    // Logger la création de commande externe
    logAPI("Commande externe créée via API: Order ID $orderId, User ID $userId, Phone $phone");
    
    // Réponse JSON
    sendJsonResponse([
        'success' => true,
        'order_id' => $orderId,
        'customer_id' => $userId,
        'status' => $orderStatus,
        'total_amount' => $totalAmount,
        'shipping_fee' => $shippingFee,
        'message' => 'Commande créée avec succès'
    ], 201);
    
} catch (PDOException $e) {
    if ($pdo && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Erreur create_external: ' . $e->getMessage());
    sendJsonResponse([
        'success' => false,
        'message' => 'Erreur lors de la création de la commande'
    ], 500);
} catch (Exception $e) {
    if ($pdo && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Erreur create_external: ' . $e->getMessage());
    sendJsonResponse([
        'success' => false,
        'message' => 'Erreur lors de la création de la commande'
    ], 500);
}
?>
