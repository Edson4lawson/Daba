<?php
/**
 * daba — Script de seed intelligent
 * Migre les 95 produits statiques vers la base MySQL
 * 
 * Usage: php seed_products.php
 */

require_once __DIR__ . '/api/config/db.php';

echo "=== daba — Seed des Produits ===\n\n";

// db.php crée directement $pdo
require_once __DIR__ . '/api/config/db.php';

if (!isset($pdo)) {
    die("[ERREUR] Impossible de se connecter à la base de données\n");
}
$db = $pdo;
echo "[OK] Connexion à la base de données\n";

// Vérifier combien de produits existent déjà
$stmt = $db->query("SELECT COUNT(*) as total FROM products");
$existing = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
echo "[INFO] Produits existants: $existing\n";

if ($existing >= 80) {
    echo "[SKIP] La base contient déjà $existing produits. Seed non nécessaire.\n";
    echo "[TIP] Pour forcer: php seed_products.php --force\n";
    if (!in_array('--force', $argv ?? [])) {
        exit(0);
    }
    echo "[FORCE] Ajout des produits manquants...\n\n";
}

// Récupérer les catégories existantes
$stmt = $db->query("SELECT id, name FROM categories ORDER BY id");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
$catMap = [];
foreach ($categories as $cat) {
    $catMap[$cat['name']] = $cat['id'];
}
echo "[INFO] " . count($catMap) . " catégories trouvées\n\n";

// Définition des 95 produits
$products = [
    // Bien-être et relaxation
    ['name' => 'Masseur cervical électrique', 'category' => 'Bien-être et relaxation', 'price' => 15000, 'image' => 'produit1.jpg', 'desc' => 'Masseur cervical intelligent avec chaleur infrarouge et vibration pour soulager les tensions du cou et des épaules.'],
    ['name' => 'Oreiller de massage chauffant', 'category' => 'Bien-être et relaxation', 'price' => 12000, 'image' => 'produit2.jpg', 'desc' => 'Oreiller de massage Shiatsu avec fonction chauffante, idéal pour le dos et les jambes.'],
    ['name' => 'Tapis d\'acupression', 'category' => 'Bien-être et relaxation', 'price' => 8500, 'image' => 'produit3.jpg', 'desc' => 'Tapis d\'acupression avec coussin pour soulager les douleurs musculaires et favoriser la relaxation.'],
    ['name' => 'Diffuseur d\'huiles essentielles', 'category' => 'Bien-être et relaxation', 'price' => 9500, 'image' => 'produit4.jpg', 'desc' => 'Diffuseur ultrasonique 300ml avec LED 7 couleurs pour aromathérapie relaxante.'],
    ['name' => 'Lampe de luminothérapie', 'category' => 'Bien-être et relaxation', 'price' => 18000, 'image' => 'produit5.jpg', 'desc' => 'Lampe de luminothérapie 10000 lux pour lutter contre la fatigue saisonnière.'],
    
    // Accessoire de coiffure
    ['name' => 'Lisseur vapeur professionnel', 'category' => 'Accessoire de coiffure', 'price' => 22000, 'image' => 'produit6.jpg', 'desc' => 'Lisseur à vapeur en céramique tourmaline pour un lissage parfait sans abîmer les cheveux.'],
    ['name' => 'Boucleur automatique', 'category' => 'Accessoire de coiffure', 'price' => 16000, 'image' => 'produit7.jpg', 'desc' => 'Boucleur automatique rotatif avec 3 réglages de température et minuterie.'],
    ['name' => 'Sèche-cheveux ionique', 'category' => 'Accessoire de coiffure', 'price' => 14000, 'image' => 'produit8.jpg', 'desc' => 'Sèche-cheveux professionnel 2200W avec technologie ionique pour des cheveux lisses et brillants.'],
    ['name' => 'Kit de coiffure multifonction', 'category' => 'Accessoire de coiffure', 'price' => 25000, 'image' => 'produit9.jpg', 'desc' => 'Set complet de coiffure 5 en 1 avec lisseur, boucleur et brosses interchangeables.'],
    ['name' => 'Brosse démêlante magique', 'category' => 'Accessoire de coiffure', 'price' => 3500, 'image' => 'produit10.jpg', 'desc' => 'Brosse démêlante spéciale pour tous types de cheveux, minimize les cassures.'],
    
    // Soin personnel
    ['name' => 'Brosse nettoyante visage', 'category' => 'Soin personnel', 'price' => 8000, 'image' => 'produit11.jpg', 'desc' => 'Brosse nettoyante sonique pour le visage avec 5 modes de vibration pour un nettoyage en profondeur.'],
    ['name' => 'Appareil anti-rides LED', 'category' => 'Soin personnel', 'price' => 35000, 'image' => 'produit12.jpg', 'desc' => 'Appareil de thérapie LED 7 couleurs pour rajeunir la peau et réduire les rides.'],
    ['name' => 'Rouleau de jade double', 'category' => 'Soin personnel', 'price' => 5500, 'image' => 'produit13.jpg', 'desc' => 'Rouleau de jade naturel double face pour le massage du visage et drainage lymphatique.'],
    ['name' => 'Épilateur électrique', 'category' => 'Soin personnel', 'price' => 12000, 'image' => 'produit14.jpg', 'desc' => 'Épilateur rechargeable étanche avec lumière LED pour une épilation précise.'],
    ['name' => 'Appareil de microdermabrasion', 'category' => 'Soin personnel', 'price' => 28000, 'image' => 'produit15.jpg', 'desc' => 'Appareil de microdermabrasion à diamant pour exfolier et rajeunir la peau du visage.'],
    
    // Beauté et soin personnel
    ['name' => 'Kit manucure électrique', 'category' => 'Beauté et soin personnel', 'price' => 9000, 'image' => 'produit16.jpg', 'desc' => 'Kit de manucure électrique 11 en 1 avec sèche-ongles UV LED intégré.'],
    ['name' => 'Miroir grossissant lumineux', 'category' => 'Beauté et soin personnel', 'price' => 7500, 'image' => 'produit17.jpg', 'desc' => 'Miroir grossissant x10 avec éclairage LED naturel et ventouse puissante.'],
    ['name' => 'Palette maquillage professionnelle', 'category' => 'Beauté et soin personnel', 'price' => 11000, 'image' => 'produit18.jpg', 'desc' => 'Palette de 88 couleurs fard à paupières mat et shimmer, qualité professionnelle.'],
    ['name' => 'Set de pinceaux maquillage', 'category' => 'Beauté et soin personnel', 'price' => 8500, 'image' => 'produit19.jpg', 'desc' => 'Set de 15 pinceaux de maquillage professionnels avec étui en cuir synthétique.'],
    ['name' => 'Spray fixateur de maquillage', 'category' => 'Beauté et soin personnel', 'price' => 4000, 'image' => 'produit20.jpg', 'desc' => 'Spray fixateur longue tenue qui garde votre maquillage intact pendant 16 heures.'],
    
    // Accessoire tech
    ['name' => 'Support téléphone flexible', 'category' => 'Accessoire tech', 'price' => 4500, 'image' => 'produit21.jpg', 'desc' => 'Support flexible pour smartphone avec pince universelle et rotation 360°.'],
    ['name' => 'Ring light selfie', 'category' => 'Accessoire tech', 'price' => 6000, 'image' => 'produit22.jpg', 'desc' => 'Ring light LED 10 pouces avec trépied et support téléphone pour selfies et vidéos.'],
    ['name' => 'Écouteurs sans fil Bluetooth', 'category' => 'Accessoire tech', 'price' => 15000, 'image' => 'produit23.jpg', 'desc' => 'Écouteurs TWS Bluetooth 5.3 avec réduction de bruit active et autonomie 30h.'],
    ['name' => 'Mini ventilateur portable', 'category' => 'Accessoire tech', 'price' => 3000, 'image' => 'produit24.jpg', 'desc' => 'Mini ventilateur rechargeable USB avec 3 vitesses, design ultra-portable.'],
    ['name' => 'Chargeur sans fil rapide', 'category' => 'Accessoire tech', 'price' => 8000, 'image' => 'produit25.jpg', 'desc' => 'Chargeur à induction 15W compatible tous smartphones Qi avec indicateur LED.'],
    
    // Santé féminine
    ['name' => 'Ceinture chauffante menstruelle', 'category' => 'Santé féminine', 'price' => 9500, 'image' => 'produit26.jpg', 'desc' => 'Ceinture chauffante vibrante pour soulager les douleurs menstruelles, 3 niveaux de chaleur.'],
    ['name' => 'Coussin bouillotte électrique', 'category' => 'Santé féminine', 'price' => 6500, 'image' => 'produit27.jpg', 'desc' => 'Bouillotte électrique rechargeable en peluche douce pour soulager les crampes.'],
    ['name' => 'Tisane bien-être féminin', 'category' => 'Santé féminine', 'price' => 4000, 'image' => 'produit28.jpg', 'desc' => 'Infusion bio 100% naturelle pour le confort féminin, mélange de camomille et gingembre.'],
    
    // Accessoire de cuisine
    ['name' => 'Mixeur portable USB', 'category' => 'Accessoire de cuisine', 'price' => 7500, 'image' => 'produit29.jpg', 'desc' => 'Blender portable rechargeable 380ml pour smoothies et jus frais en déplacement.'],
    ['name' => 'Balance cuisine digitale', 'category' => 'Accessoire de cuisine', 'price' => 5000, 'image' => 'produit30.jpg', 'desc' => 'Balance de cuisine numérique haute précision avec écran LCD, capacité 5kg.'],
    ['name' => 'Set d\'ustensiles silicone', 'category' => 'Accessoire de cuisine', 'price' => 12000, 'image' => 'produit31.jpg', 'desc' => 'Set de 12 ustensiles de cuisine en silicone alimentaire avec support en bois.'],
    ['name' => 'Organisateur tiroir cuisine', 'category' => 'Accessoire de cuisine', 'price' => 4500, 'image' => 'produit32.jpg', 'desc' => 'Organisateur extensible pour tiroir de cuisine, rangement optimal des couverts.'],
    
    // Accessoire de douche
    ['name' => 'Pommeau de douche LED', 'category' => 'Accessoire de douche', 'price' => 8000, 'image' => 'produit33.jpg', 'desc' => 'Pommeau de douche avec LED qui change de couleur selon la température de l\'eau.'],
    ['name' => 'Distributeur savon automatique', 'category' => 'Accessoire de douche', 'price' => 6500, 'image' => 'produit34.jpg', 'desc' => 'Distributeur de savon infrarouge sans contact, capacité 350ml, rechargeable USB.'],
    ['name' => 'Étagère douche d\'angle', 'category' => 'Accessoire de douche', 'price' => 5500, 'image' => 'produit35.jpg', 'desc' => 'Étagère de douche d\'angle en aluminium inoxydable sans perçage, 3 niveaux.'],
    ['name' => 'Brosse exfoliante corps', 'category' => 'Accessoire de douche', 'price' => 4000, 'image' => 'produit36.jpg', 'desc' => 'Brosse exfoliante électrique pour le corps avec 4 têtes interchangeables.'],
    
    // Accessoire
    ['name' => 'Organisateur maquillage rotatif', 'category' => 'Accessoire', 'price' => 7000, 'image' => 'produit37.jpg', 'desc' => 'Organisateur de maquillage rotatif 360° en acrylique transparent avec 8 compartiments.'],
    ['name' => 'Sac cosmétique voyage', 'category' => 'Accessoire', 'price' => 5500, 'image' => 'produit38.jpg', 'desc' => 'Trousse de toilette portable imperméable avec compartiments multiples pour voyage.'],
    ['name' => 'Porte-bijoux arbre', 'category' => 'Accessoire', 'price' => 4500, 'image' => 'produit39.jpg', 'desc' => 'Porte-bijoux en forme d\'arbre doré pour ranger colliers, boucles et bracelets.'],
    ['name' => 'Boîte rangement bijoux', 'category' => 'Accessoire', 'price' => 9000, 'image' => 'produit40.jpg', 'desc' => 'Boîte à bijoux en velours avec miroir et tiroirs multiples, design élégant.'],
    
    // Accessoire de tournage
    ['name' => 'Trépied extensible smartphone', 'category' => 'Accessoire de tournage', 'price' => 8500, 'image' => 'produit41.jpg', 'desc' => 'Trépied extensible 170cm avec rotule et support smartphone pour photos et vidéos.'],
    ['name' => 'Micro cravate sans fil', 'category' => 'Accessoire de tournage', 'price' => 15000, 'image' => 'produit42.jpg', 'desc' => 'Microphone lavalier sans fil avec réduction de bruit pour enregistrement professionnel.'],
    ['name' => 'Stabilisateur téléphone', 'category' => 'Accessoire de tournage', 'price' => 25000, 'image' => 'produit43.jpg', 'desc' => 'Gimbal stabilisateur 3 axes pour smartphone avec suivi intelligent du visage.'],
    ['name' => 'Kit éclairage vidéo LED', 'category' => 'Accessoire de tournage', 'price' => 18000, 'image' => 'produit44.jpg', 'desc' => 'Kit de 2 panneaux LED bicolores avec trépieds pour éclairage professionnel de vidéos.'],
    
    // Accessoire de beauté
    ['name' => 'Gua Sha en quartz rose', 'category' => 'Accessoire de beauté', 'price' => 4500, 'image' => 'produit45.jpg', 'desc' => 'Pierre de Gua Sha en quartz rose naturel pour massage facial et tonification.'],
    ['name' => 'Appareil à vapeur facial', 'category' => 'Accessoire de beauté', 'price' => 11000, 'image' => 'produit46.jpg', 'desc' => 'Vapeur facial professionnel avec nano-vapeur ionique pour ouvrir les pores.'],
    ['name' => 'Bandeau spa en microfibre', 'category' => 'Accessoire de beauté', 'price' => 2500, 'image' => 'produit47.jpg', 'desc' => 'Bandeau de spa en microfibre douce avec nœud, parfait pour les routines de soin.'],
    ['name' => 'Patchs yeux anti-cernes', 'category' => 'Accessoire de beauté', 'price' => 3500, 'image' => 'produit48.jpg', 'desc' => 'Lot de 60 patchs en hydrogel à l\'or et au collagène pour réduire les cernes.'],
    
    // Bien-être
    ['name' => 'Masque yeux chauffant', 'category' => 'Bien-être', 'price' => 6000, 'image' => 'produit49.jpg', 'desc' => 'Masque pour les yeux avec vapeur chauffante USB pour détente et soulagement de la fatigue visuelle.'],
    ['name' => 'Balle de massage fasciale', 'category' => 'Bien-être', 'price' => 3000, 'image' => 'produit50.jpg', 'desc' => 'Balle de massage vibrante à 4 vitesses pour décontraction musculaire profonde.'],
    
    // Soin corporel
    ['name' => 'Appareil anti-cellulite', 'category' => 'Soin corporel', 'price' => 15000, 'image' => 'produit51.jpg', 'desc' => 'Masseur anti-cellulite infrarouge avec 3 modes de massage pour sculpter le corps.'],
    ['name' => 'Gommage corps café', 'category' => 'Soin corporel', 'price' => 4500, 'image' => 'produit52.jpg', 'desc' => 'Gommage corporel au café et au beurre de karité pour une peau douce et tonifiée.'],
    ['name' => 'Huile de soin multi-usage', 'category' => 'Soin corporel', 'price' => 6000, 'image' => 'produit53.jpg', 'desc' => 'Huile naturelle multi-usage à l\'argan et à la rose pour visage, corps et cheveux.'],
    
    // Esthétique et soin personnel
    ['name' => 'Kit pédicure électrique', 'category' => 'Esthétique et soin personnel', 'price' => 8500, 'image' => 'produit54.jpg', 'desc' => 'Râpe électrique pour pieds avec 3 rouleaux interchangeables et lumière LED.'],
    ['name' => 'Appareil blanchiment dentaire', 'category' => 'Esthétique et soin personnel', 'price' => 12000, 'image' => 'produit55.jpg', 'desc' => 'Kit de blanchiment dentaire LED professionnel avec gel et gouttière.'],
    
    // Bien-être et plaisir personnel  
    ['name' => 'Coussin ergonomique bureau', 'category' => 'Bien-être et plaisir personnel', 'price' => 9000, 'image' => 'produit56.jpg', 'desc' => 'Coussin ergonomique en mousse à mémoire de forme pour le confort au bureau.'],
    ['name' => 'Veilleuse projection étoiles', 'category' => 'Bien-être et plaisir personnel', 'price' => 7500, 'image' => 'produit57.jpg', 'desc' => 'Projecteur de ciel étoilé avec 15 modes d\'ambiance et haut-parleur Bluetooth.'],
    
    // Bien-être et soin de la peau
    ['name' => 'Appareil lifting ultrason', 'category' => 'Bien-être et soin de la peau', 'price' => 30000, 'image' => 'produit58.jpg', 'desc' => 'Appareil de lifting facial par ultrasons pour raffermir et tonifier la peau.'],
    ['name' => 'Masque LED visage', 'category' => 'Bien-être et soin de la peau', 'price' => 25000, 'image' => 'produit59.jpg', 'desc' => 'Masque LED 7 couleurs thérapeutique pour traitement anti-âge et acné.'],
    
    // Soin personnel et beauté
    ['name' => 'Tondeuse nez/oreilles', 'category' => 'Soin personnel et beauté', 'price' => 4500, 'image' => 'produit60.jpg', 'desc' => 'Tondeuse de précision rechargeable pour nez et oreilles avec lame hypoallergénique.'],
    ['name' => 'Aspirateur points noirs', 'category' => 'Soin personnel et beauté', 'price' => 7000, 'image' => 'produit61.jpg', 'desc' => 'Aspirateur à points noirs avec 5 niveaux d\'aspiration et 4 embouts différents.'],
    
    // Jardinage et lavage
    ['name' => 'Pulvérisateur électrique', 'category' => 'Jardinage et lavage', 'price' => 8000, 'image' => 'produit62.jpg', 'desc' => 'Pulvérisateur électrique rechargeable 2L pour jardinage et nettoyage.'],
    
    // Sport et bien-être
    ['name' => 'Pistolet de massage', 'category' => 'Sport et bien-être', 'price' => 20000, 'image' => 'produit63.jpg', 'desc' => 'Pistolet de massage musculaire profond avec 6 têtes et 30 vitesses.'],
    ['name' => 'Bande de résistance fitness', 'category' => 'Sport et bien-être', 'price' => 5000, 'image' => 'produit64.jpg', 'desc' => 'Set de 5 bandes élastiques de résistance avec poignées pour entraînement complet.'],
    ['name' => 'Tapis de yoga antidérapant', 'category' => 'Sport et bien-être', 'price' => 7000, 'image' => 'produit65.jpg', 'desc' => 'Tapis de yoga TPE 6mm double face antidérapant avec sangle de transport.'],
    
    // Accessoire de bureau
    ['name' => 'Lampe bureau tactile LED', 'category' => 'Accessoire de bureau', 'price' => 9500, 'image' => 'produit66.jpg', 'desc' => 'Lampe de bureau LED avec port USB, 5 modes de luminosité et minuterie.'],
    ['name' => 'Organisateur bureau bambou', 'category' => 'Accessoire de bureau', 'price' => 6000, 'image' => 'produit67.jpg', 'desc' => 'Organisateur de bureau en bambou naturel avec compartiments et tiroir.'],
    
    // Ménage
    ['name' => 'Aspirateur portable voiture', 'category' => 'Ménage', 'price' => 11000, 'image' => 'produit68.jpg', 'desc' => 'Mini aspirateur portable rechargeable 120W pour voiture et maison.'],
    ['name' => 'Robot nettoyeur vitres', 'category' => 'Ménage', 'price' => 35000, 'image' => 'produit69.jpg', 'desc' => 'Robot nettoyeur de vitres magnétique avec télécommande et détection des bords.'],
    
    // Accessoire de sortie
    ['name' => 'Parapluie inversé automatique', 'category' => 'Accessoire de sortie', 'price' => 5000, 'image' => 'produit70.jpg', 'desc' => 'Parapluie à ouverture inversée avec couche anti-UV et anti-vent renforcé.'],
    ['name' => 'Sac à dos antivol', 'category' => 'Accessoire de sortie', 'price' => 14000, 'image' => 'produit71.jpg', 'desc' => 'Sac à dos avec port USB intégré, fermeture cachée et matière imperméable.'],
    
    // Accessoire de chambre
    ['name' => 'Humidificateur d\'air 2L', 'category' => 'Accessoire de chambre', 'price' => 8000, 'image' => 'produit72.jpg', 'desc' => 'Humidificateur ultrasonique 2L avec veilleuse et mode nuit silencieux.'],
    ['name' => 'Cadre photo numérique', 'category' => 'Accessoire de chambre', 'price' => 18000, 'image' => 'produit73.jpg', 'desc' => 'Cadre photo numérique 10 pouces avec WiFi et stockage cloud pour partage familial.'],
    
    // Bien-être et santé
    ['name' => 'Tensiomètre poignet digital', 'category' => 'Bien-être et santé', 'price' => 10000, 'image' => 'produit74.jpg', 'desc' => 'Tensiomètre numérique de poignet avec mémoire 120 mesures et détection d\'arythmie.'],
    ['name' => 'Thermomètre frontal sans contact', 'category' => 'Bien-être et santé', 'price' => 6500, 'image' => 'produit75.jpg', 'desc' => 'Thermomètre infrarouge sans contact avec écran LCD et alerte fièvre.'],
    
    // Accessoire personnel
    ['name' => 'Portefeuille RFID anti-vol', 'category' => 'Accessoire personnel', 'price' => 7000, 'image' => 'produit76.jpg', 'desc' => 'Portefeuille en cuir véritable avec protection RFID contre le vol de données.'],
    ['name' => 'Porte-clés traqueur GPS', 'category' => 'Accessoire personnel', 'price' => 5500, 'image' => 'produit77.jpg', 'desc' => 'Tracker Bluetooth pour localiser vos clés et objets via application mobile.'],
    
    // Mobilier de maison
    ['name' => 'Étagère murale flottante', 'category' => 'Mobilier de maison', 'price' => 8500, 'image' => 'produit78.jpg', 'desc' => 'Set de 3 étagères murales flottantes en bois avec fixation invisible.'],
    ['name' => 'Tabouret pliable gain de place', 'category' => 'Mobilier de maison', 'price' => 6000, 'image' => 'produit79.jpg', 'desc' => 'Tabouret pliable en plastique renforcé, capacité 150kg, design compact.'],
    
    // Art de la table
    ['name' => 'Set de couverts dorés', 'category' => 'Art de la table', 'price' => 15000, 'image' => 'produit80.jpg', 'desc' => 'Set de 24 couverts en acier inoxydable finition dorée dans coffret élégant.'],
    ['name' => 'Service à thé japonais', 'category' => 'Art de la table', 'price' => 12000, 'image' => 'produit81.jpg', 'desc' => 'Service à thé en céramique japonaise 6 pièces avec théière et tasses.'],
    
    // Accessoire anti-insecte
    ['name' => 'Lampe anti-moustique UV', 'category' => 'Accessoire anti-insecte', 'price' => 7500, 'image' => 'produit82.jpg', 'desc' => 'Lampe anti-moustiques UV LED silencieuse, sans produit chimique, rechargeable USB.'],
    ['name' => 'Bracelet anti-moustique', 'category' => 'Accessoire anti-insecte', 'price' => 2000, 'image' => 'produit83.jpg', 'desc' => 'Bracelet répulsif naturel à la citronnelle, réglable, pour toute la famille.'],
    
    // Bricolage
    ['name' => 'Perceuse sans fil 21V', 'category' => 'Bricolage', 'price' => 22000, 'image' => 'produit84.jpg', 'desc' => 'Perceuse visseuse sans fil 21V lithium avec 25 embouts et coffret de transport.'],
    ['name' => 'Kit outils multifonction', 'category' => 'Bricolage', 'price' => 18000, 'image' => 'produit85.jpg', 'desc' => 'Coffret outils 120 pièces avec tournevis, pinces, marteau et clés.'],
    
    // Accessoire High-Tech
    ['name' => 'Montre connectée sport', 'category' => 'Accessoire High-Tech', 'price' => 25000, 'image' => 'produit86.jpg', 'desc' => 'Smartwatch avec écran AMOLED, suivi santé, GPS et autonomie 14 jours.'],
    ['name' => 'Enceinte Bluetooth étanche', 'category' => 'Accessoire High-Tech', 'price' => 12000, 'image' => 'produit87.jpg', 'desc' => 'Enceinte Bluetooth 5.0 portable étanche IPX7 avec basses profondes et 24h d\'autonomie.'],
    
    // Collection Daba
    ['name' => 'Coffret beauté Daba Essentiel', 'category' => 'Collection Daba', 'price' => 35000, 'image' => 'produit88.jpg', 'desc' => 'Coffret exclusif Daba avec sélection de nos meilleurs produits beauté.'],
    ['name' => 'Eau de parfum Daba Signature', 'category' => 'Collection Daba', 'price' => 28000, 'image' => 'produit89.jpg', 'desc' => 'Eau de parfum exclusive Daba Signature — notes florales de jasmin, rose et iris.'],
    ['name' => 'Crème visage Daba Éclat', 'category' => 'Collection Daba', 'price' => 15000, 'image' => 'produit90.jpg', 'desc' => 'Crème hydratante visage à l\'extrait de rose et acide hyaluronique, teint éclatant.'],
    
    // Range vaisselle
    ['name' => 'Égouttoir vaisselle 2 niveaux', 'category' => 'Range vaisselle', 'price' => 8000, 'image' => 'produit52.jpg', 'desc' => 'Égouttoir vaisselle en acier inox 2 niveaux avec bac récupérateur d\'eau.'],
];

echo "=== Insertion de " . count($products) . " produits ===\n\n";

$insertStmt = $db->prepare("INSERT INTO products (category_id, name, slug, description, price, stock, stock_quantity, image_url, rating, source, status) 
    VALUES (:cat_id, :name, :slug, :desc, :price, :stock, :stock, :image, :rating, 'produit', 'published')
    ON DUPLICATE KEY UPDATE name=VALUES(name), description=VALUES(description), price=VALUES(price), image_url=VALUES(image_url)");

$inserted = 0;
$skipped = 0;

foreach ($products as $p) {
    $catId = $catMap[$p['category']] ?? null;
    
    if (!$catId) {
        echo "  [WARN] Catégorie '{$p['category']}' non trouvée, skip: {$p['name']}\n";
        $skipped++;
        continue;
    }
    
    $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', trim(
        iconv('UTF-8', 'ASCII//TRANSLIT', $p['name'])
    )));
    $slug = trim($slug, '-');
    
    $stock = rand(5, 50);
    $rating = round(3.5 + (mt_rand(0, 15) / 10), 1);
    $imageUrl = 'src/assets/' . $p['image'];
    
    try {
        $insertStmt->execute([
            ':cat_id' => $catId,
            ':name' => $p['name'],
            ':slug' => $slug,
            ':desc' => $p['desc'],
            ':price' => $p['price'],
            ':stock' => $stock,
            ':image' => $imageUrl,
            ':rating' => $rating
        ]);
        $inserted++;
        echo "  [+] {$p['name']} ({$p['price']} FCFA) — Cat: {$p['category']}\n";
    } catch (PDOException $e) {
        echo "  [ERR] {$p['name']}: " . $e->getMessage() . "\n";
        $skipped++;
    }
}

echo "\n=== RÉSULTAT ===\n";
echo "Insérés: $inserted\n";
echo "Ignorés/Erreurs: $skipped\n";

$stmt = $db->query("SELECT COUNT(*) as total FROM products");
$total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
echo "Total produits en base: $total\n";

echo "\n✅ Seed terminé avec succès !\n";
