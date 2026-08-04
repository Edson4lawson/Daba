<?php
require_once __DIR__ . '/../../config/headers.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../middleware/auth.php';

// Vérifier si la requête est de type GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendJsonResponse(['error' => 'Méthode non autorisée'], 405);
}

// Authentifier l'utilisateur
$user = authenticate();
$allowedRoles = ['admin', 'commercial', 'comptable'];
if (!in_array($user['role'], $allowedRoles)) {
    sendJsonResponse(['error' => 'Accès refusé'], 403);
}

// Récupérer les paramètres de requête
$orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : null;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
$status = isset($_GET['status']) ? $_GET['status'] : null;
$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;

// Valider les paramètres
$page = max(1, $page);
$perPage = max(1, min(50, $perPage));
$offset = ($page - 1) * $perPage;

try {
    // Si un ID de commande est fourni, récupérer les détails d'une commande spécifique
    if ($orderId) {
        // Récupérer la commande avec infos client
        $stmt = $pdo->prepare('
            SELECT 
                o.*,
                u.email as user_email,
                u.first_name as user_first_name,
                u.last_name as user_last_name,
                p.status as payment_status,
                p.transaction_id,
                p.payment_details
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.id
            LEFT JOIN payments p ON o.id = p.order_id
            WHERE o.id = ?
        ');
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();
        
        if (!$order) {
            sendJsonResponse(['error' => 'Commande non trouvée'], 404);
        }
        
        // Récupérer les articles de la commande
        $stmt = $pdo->prepare('
            SELECT 
                oi.*,
                p.slug as product_slug,
                p.image_url,
                p.name as product_name
            FROM order_items oi
            LEFT JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = ?
        ');
        $stmt->execute([$orderId]);
        $orderItems = $stmt->fetchAll();
        
        // Formater la réponse
        $order['items'] = $orderItems;
        
        // Si les détails de paiement sont stockés sous forme de JSON, les décoder
        if (!empty($order['payment_details'])) {
            $order['payment_details'] = json_decode($order['payment_details'], true);
        }
        
        sendJsonResponse($order);
    } 
    // Sinon, récupérer la liste de toutes les commandes avec pagination
    else {
        // Construire la requête de base
        $whereClause = '1=1';
        $params = [];

        // Filtrer par user_id si fourni
        if ($userId) {
            $whereClause .= ' AND o.user_id = ?';
            $params[] = $userId;
        }

        // Filtrer par statut si fourni
        if ($status) {
            $whereClause .= ' AND o.status = ?';
            $params[] = $status;
        }
        
        // Compter le nombre total de commandes
        $countQuery = "SELECT COUNT(*) as total FROM orders o WHERE $whereClause";
        $countStmt = $pdo->prepare($countQuery);
        $countStmt->execute($params);
        $total = $countStmt->fetch()['total'];
        
        // Récupérer les commandes avec pagination
        $query = "
            SELECT 
                o.*,
                u.email as user_email,
                u.first_name as user_first_name,
                u.last_name as user_last_name,
                MAX(p.status) as payment_status,
                COUNT(oi.id) as item_count
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.id
            LEFT JOIN order_items oi ON o.id = oi.order_id
            LEFT JOIN payments p ON o.id = p.order_id
            WHERE $whereClause
            GROUP BY o.id, o.created_at, u.email, u.first_name, u.last_name, o.canal
            ORDER BY o.created_at DESC
            LIMIT ? OFFSET ?
        ";
        
        $stmt = $pdo->prepare($query);
        foreach ($params as $i => $param) {
            $stmt->bindValue($i + 1, $param);
        }
        $stmt->bindValue(count($params) + 1, $perPage, PDO::PARAM_INT);
        $stmt->bindValue(count($params) + 2, $offset, PDO::PARAM_INT);
        $stmt->execute();
        $orders = $stmt->fetchAll();
        
        // Formater la réponse avec la pagination
        $response = [
            'orders' => $orders,
            'pagination' => [
                'total' => (int)$total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => ceil($total / $perPage),
                'from' => $offset + 1,
                'to' => min($offset + $perPage, $total)
            ]
        ];
        
        sendJsonResponse($response);
    }
    
} catch (PDOException $e) {
    error_log('Erreur lors de la récupération des commandes admin: ' . $e->getMessage());
    sendJsonResponse(['error' => 'Erreur lors de la récupération des commandes'], 500);
}
?>
