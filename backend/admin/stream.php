<?php
// backend/admin/stream.php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../middleware/auth.php';

// SSE headers FIRST — avant toute tentative de sendJsonResponse
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('X-Accel-Buffering: no');

// CORS pour SSE - validation de l'origine
$allowedOrigins = getenv('ALLOWED_ORIGINS') ?: 'http://localhost:5173';
$allowedOriginsArray = array_map('trim', explode(',', $allowedOrigins));
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if (in_array($origin, $allowedOriginsArray) || empty($origin)) {
    header("Access-Control-Allow-Origin: " . ($origin ?: $allowedOriginsArray[0]));
    header('Access-Control-Allow-Credentials: true');
}

// Authentifier sans utiliser sendJsonResponse (qui casserait le flux SSE)
$token = $_GET['token'] ?? $_GET['access_token'] ?? '';
if (empty($token)) {
    echo "event: auth_error\ndata: {\"error\":\"Token manquant\"}\n\n";
    flush();
    exit();
}

// Vérifier le token manuellement sans appeler authenticate()
$stmt = $pdo->prepare('SELECT id, email, first_name, last_name, role FROM users WHERE token = ? AND token_expires_at > NOW()');
$stmt->execute([$token]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "event: auth_error\ndata: {\"error\":\"Token invalide ou expiré\"}\n\n";
    flush();
    exit();
}

if ($user['role'] !== 'admin') {
    echo "event: auth_error\ndata: {\"error\":\"Accès refusé\"}\n\n";
    flush();
    exit();
}


// We need a way to check for "real orders". For simplicity, we check if the max order id changed.
$lastOrderId = 0;
try {
    $stmt = $pdo->query("SELECT MAX(id) as max_id FROM orders");
    $lastOrderId = (int) $stmt->fetchColumn();
} catch (Exception $e) {
    // ignore
}

// Some fake activity messages for the dashboard
$fakeActivities = [
    "Une cliente ajoute 'Mini Valise de Maquillage' au panier.",
    "Un visiteur consulte la catégorie 'Accessoire de beauté'.",
    "Une cliente est sur la page de paiement...",
    "Nouveau visiteur depuis Abidjan.",
    "Un avis 5 étoiles vient d'être soumis !",
    "La 'Trousse de Toilette Daba' est très demandée aujourd'hui."
];

$counter = 0;
$startTime = time();
$maxExecutionTime = 20; // Reconnect every 20s to free the thread on single-threaded servers

while (time() - $startTime < $maxExecutionTime) {
    $events = [];

    // 1. Check for real new orders
    try {
        $stmt = $pdo->query("SELECT id, total_amount, CONCAT(u.first_name, ' ', u.last_name) as user_name FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE o.id > $lastOrderId ORDER BY o.id ASC");
        $newOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($newOrders as $order) {
            $events[] = [
                'type' => 'new_order',
                'message' => "Nouvelle commande #" . $order['id'] . " de " . $order['user_name'] . " (" . $order['total_amount'] . " FCFA)!"
            ];
            $lastOrderId = $order['id'];
        }
    } catch (Exception $e) {
        // ignore
    }

    // 2. Random fake activity (every ~10-15 seconds)
    if ($counter % 5 == 0) {
        // Just a random simulated live event
        $events[] = [
            'type' => 'activity',
            'message' => $fakeActivities[array_rand($fakeActivities)],
            'visitors' => rand(8, 25) // Fake active visitors count
        ];
    }

    // Send events
    foreach ($events as $event) {
        echo "data: " . json_encode($event) . "\n\n";
    }

    // Output buffer flush
    if (ob_get_level() > 0) {
        ob_flush();
    }
    flush();

    // Wait 3 seconds before next check
    sleep(3);
    $counter++;
}
?>
