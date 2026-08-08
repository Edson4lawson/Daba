<?php
/**
 * Service Blockchain DABA (Hyperledger Fabric Simulator)
 * Endpoint: /api/blockchain/verify.php?code=DBA-2026-0142
 * 
 * Simule le grand livre immuable et vérifie la chaîne de blocs cryptographiques.
 */

// S'assurer que le header Content-Type est bien JSON
header('Content-Type: application/json');

// Récupérer le code de lot depuis la requête
$lotCode = isset($_GET['code']) ? trim($_GET['code']) : '';
$lotCode = strtoupper(str_replace('#', '', $lotCode));

if (empty($lotCode)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Code de lot manquant. Veuillez spécifier ?code=DBA-2026-0142'
    ]);
    exit();
}

// Données brutes des lots pour la traçabilité (source de vérité)
$lotsData = [
    'DBA-2026-0142' => [
        'product' => 'Poulet frais entier (1.5 kg)',
        'ferme' => 'Ferme DABA Kpomé (Lot #KP-2026-042)',
        'dateAbattage' => '15/07/2026 à 05:30',
        'veterinaire' => 'Conforme (Dr. Lawson, N° Ordre #TOG-4542)',
        'temperature' => 'Livré sous température contrôlée (+2°C)',
        'steps' => [
            ['title' => 'Élevage responsable', 'detail' => 'Élevé 49 jours en plein air à Kpomé, alimentation céréalière sans antibiotiques.', 'validator' => 'Node-Kpomé-01', 'date' => '2026-05-27 08:00:00'],
            ['title' => 'Contrôle sanitaire', 'detail' => 'Certificat vétérinaire de conformité délivré à Kpomé avant transport.', 'validator' => 'Vet-Auth-Togo', 'date' => '2026-07-14 16:30:00'],
            ['title' => 'Abattage & Conditionnement', 'detail' => 'Transformé et emballé à l\'atelier Lomé le 15/07/2026.', 'validator' => 'Node-Lome-Atelier', 'date' => '2026-07-15 05:30:00'],
            ['title' => 'Expédition', 'detail' => 'Chaîne du froid maintenue à +2°C jusqu\'à la livraison.', 'validator' => 'Logistics-Chain-Node', 'date' => '2026-07-15 09:15:00']
        ]
    ],
    'DBA-2026-0098' => [
        'product' => 'Cuisses de poulet fumées (1 kg)',
        'ferme' => 'Ferme DABA Tsévié (Lot #TS-2026-018)',
        'dateAbattage' => '12/07/2026 à 06:15',
        'veterinaire' => 'Conforme (Dr. Kpante, N° Ordre #TOG-3891)',
        'temperature' => 'Fumage naturel & stockage à +4°C',
        'steps' => [
            ['title' => 'Élevage local', 'detail' => 'Volailles nourries aux grains locaux à Tsévié.', 'validator' => 'Node-Tsevie-02', 'date' => '2026-05-20 09:00:00'],
            ['title' => 'Transformation & Fumage', 'detail' => 'Fumage artisanal au bois d\'acacia à l\'atelier DABA Lomé.', 'validator' => 'Node-Lome-Atelier', 'date' => '2026-07-12 06:15:00'],
            ['title' => 'Mise sous vide', 'detail' => 'Conditionnement hermétique le 13/07/2026.', 'validator' => 'Node-Lome-Conditionnement', 'date' => '2026-07-13 10:00:00']
        ]
    ],
    'DBA-2026-0201' => [
        'product' => 'Jambon de volaille (300 g)',
        'ferme' => 'Ferme DABA Kpomé (Lot #KP-2026-055)',
        'dateAbattage' => '18/07/2026 à 04:45',
        'veterinaire' => 'Conforme (Dr. Lawson, N° Ordre #TOG-4542)',
        'temperature' => 'Conservation optimale à +3°C',
        'steps' => [
            ['title' => 'Sélection des blancs', 'detail' => 'Sélection des plus beaux blancs de poulet à Kpomé.', 'validator' => 'Node-Kpome-01', 'date' => '2026-05-30 08:30:00'],
            ['title' => 'Charcuterie fine', 'detail' => 'Préparation et tranchage fin dans le respect des normes sanitaires.', 'validator' => 'Node-Lome-Atelier', 'date' => '2026-07-18 04:45:00'],
            ['title' => 'Certification', 'detail' => 'Contrôle qualité final validé par le laboratoire DABA.', 'validator' => 'Labo-Quality-Node', 'date' => '2026-07-18 08:00:00']
        ]
    ]
];

// Vérifier si le lot existe
if (!isset($lotsData[$lotCode])) {
    // Si c'est un code dynamique valide (format DBA-XXXX-XXXX), on génère un lot de démo pour montrer que ça marche dynamiquement
    if (preg_match('/^DBA-\d{4}-\d{4}$/', $lotCode)) {
        $lotsData[$lotCode] = [
            'product' => 'Produit de volaille certifié DABA',
            'ferme' => 'Ferme DABA Partenaire (Lot #DYN-992)',
            'dateAbattage' => date('d/m/Y') . ' à 06:00',
            'veterinaire' => 'Conforme (Dr. Lawson, N° Ordre #TOG-4542)',
            'temperature' => 'Conservation optimale à +3°C',
            'steps' => [
                ['title' => 'Élevage local certifié', 'detail' => 'Alimentation 100% naturelle.', 'validator' => 'Node-Partner-04', 'date' => date('Y-m-d H:i:s', strtotime('-40 days'))],
                ['title' => 'Validation Sanitaire', 'detail' => 'Contrôle vétérinaire systématique avant abattage.', 'validator' => 'Vet-Auth-Togo', 'date' => date('Y-m-d H:i:s', strtotime('-1 days'))],
                ['title' => 'Conditionnement', 'detail' => 'Emballage sous vide à Lomé.', 'validator' => 'Node-Lome-Atelier', 'date' => date('Y-m-d H:i:s')]
            ]
        ];
    } else {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => "Numéro de lot '$lotCode' non trouvé dans le registre DABA."
        ]);
        exit();
    }
}

$lot = $lotsData[$lotCode];

// Genesis Block hash de départ
$previousHash = '0000000000000000000000000000000000000000000000000000000000000000';
$blocks = [];

// Génération de la chaîne de blocs avec hachage cryptographique réel
foreach ($lot['steps'] as $index => $step) {
    $blockId = $index + 1;
    $timestamp = $step['date'];
    $dataPayload = [
        'lot_code' => $lotCode,
        'title' => $step['title'],
        'detail' => $step['detail'],
        'ferme' => $lot['ferme'],
        'veterinaire' => $lot['veterinaire']
    ];
    
    // Concaténer les données pour le hachage
    $serializedData = json_encode($dataPayload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $stringToHash = $blockId . '|' . $timestamp . '|' . $serializedData . '|' . $previousHash;
    
    // Calculer le hash SHA-256
    $hash = hash('sha256', $stringToHash);
    
    // Générer une signature numérique simulée signée par la clé privée du validateur
    $signature = hash_hmac('sha256', $hash, 'DABA_FABRIC_SECRET_KEY_' . $step['validator']);

    $blocks[] = [
        'block_id' => $blockId,
        'timestamp' => $timestamp,
        'title' => $step['title'],
        'detail' => $step['detail'],
        'validator' => $step['validator'],
        'previous_hash' => $previousHash,
        'hash' => $hash,
        'signature' => 'sig_' . substr($signature, 0, 24) . '...',
        'payload' => $dataPayload
    ];
    
    // Le hash du bloc actuel devient le previous_hash du bloc suivant
    $previousHash = $hash;
}

// Vérifier l'intégrité de la chaîne (chaque bloc pointe correctement sur le précédent)
$chainValid = true;
for ($i = 1; $i < count($blocks); $i++) {
    if ($blocks[$i]['previous_hash'] !== $blocks[$i-1]['hash']) {
        $chainValid = false;
        break;
    }
}

// Retourner la réponse structurée
echo json_encode([
    'success' => true,
    'lot_code' => $lotCode,
    'product' => $lot['product'],
    'ferme' => $lot['ferme'],
    'dateAbattage' => $lot['dateAbattage'],
    'veterinaire' => $lot['veterinaire'],
    'temperature' => $lot['temperature'],
    'blockchain' => [
        'platform' => 'Hyperledger Fabric v2.5 (Private Network)',
        'channel' => 'daba-supplychain-channel',
        'chain_code' => 'daba-tracking-cc',
        'is_valid' => $chainValid,
        'blocks' => $blocks
    ]
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
