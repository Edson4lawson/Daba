<?php
/**
 * Détection de Fraude - Daba
* Analyse les comportements suspects et détecte les activités frauduleuses
 * 
 * @author Security Audit
 * @version 1.0.0
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/logging.php';

/**
 * Classe de détection de fraude
 */
class FraudDetection {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Analyse une transaction pour détecter la fraude
     * 
     * @param int $userId ID utilisateur
     * @param float $amount Montant de la transaction
     * @param string $ipAddress IP de la transaction
     * @param array $metadata Métadonnées additionnelles
     * @return array Résultat de l'analyse
     */
    public function analyzeTransaction($userId, $amount, $ipAddress, $metadata = []) {
        $riskScore = 0;
        $riskFactors = [];
        
        // 1. Vérifier le nombre de transactions récentes
        $recentTransactions = $this->getRecentTransactionCount($userId, 3600); // 1 heure
        if ($recentTransactions > 10) {
            $riskScore += 30;
            $riskFactors[] = 'high_transaction_frequency';
        }
        
        // 2. Vérifier le montant total des transactions récentes
        $recentTotal = $this->getRecentTransactionTotal($userId, 86400); // 24 heures
        if ($recentTotal > 500000) { // 500,000 XOF
            $riskScore += 25;
            $riskFactors[] = 'high_transaction_amount';
        }
        
        // 3. Vérifier les changements d'IP
        $ipChanged = $this->hasIPChangedRecently($userId, $ipAddress);
        if ($ipChanged) {
            $riskScore += 20;
            $riskFactors[] = 'ip_address_change';
        }
        
        // 4. Vérifier le pays de l'IP
        $country = $this->getCountryFromIP($ipAddress);
        $previousCountry = $this->getUserPreviousCountry($userId);
        if ($previousCountry && $country !== $previousCountry) {
            $riskScore += 15;
            $riskFactors[] = 'country_change';
        }
        
        // 5. Vérifier les appareils multiples
        $multipleDevices = $this->hasMultipleDevices($userId);
        if ($multipleDevices) {
            $riskScore += 10;
            $riskFactors[] = 'multiple_devices';
        }
        
        // 6. Vérifier les adresses de livraison multiples
        $multipleAddresses = $this->hasMultipleShippingAddresses($userId);
        if ($multipleAddresses) {
            $riskScore += 15;
            $riskFactors[] = 'multiple_shipping_addresses';
        }
        
        // 7. Vérifier les coupons frauduleux
        if (isset($metadata['coupon_code'])) {
            $couponRisk = $this->checkCouponRisk($metadata['coupon_code'], $userId);
            $riskScore += $couponRisk['score'];
            if ($couponRisk['risk']) {
                $riskFactors[] = 'suspicious_coupon';
            }
        }
        
        // Déterminer le niveau de risque
        $riskLevel = $this->getRiskLevel($riskScore);
        
        // Logger l'analyse
        logSecurityEvent('Fraud analysis completed', [
            'user_id' => $userId,
            'amount' => $amount,
            'risk_score' => $riskScore,
            'risk_level' => $riskLevel,
            'risk_factors' => $riskFactors,
            'ip_address' => $ipAddress
        ]);
        
        return [
            'risk_score' => $riskScore,
            'risk_level' => $riskLevel,
            'risk_factors' => $riskFactors,
            'should_block' => $riskScore >= 70,
            'should_review' => $riskScore >= 50
        ];
    }
    
    /**
     * Obtient le nombre de transactions récentes
     */
    private function getRecentTransactionCount($userId, $seconds) {
        $stmt = $this->pdo->prepare('
            SELECT COUNT(*) as count
            FROM orders
            WHERE user_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL ? SECOND)
        ');
        $stmt->execute([$userId, $seconds]);
        $result = $stmt->fetch();
        return (int)$result['count'];
    }
    
    /**
     * Obtient le montant total des transactions récentes
     */
    private function getRecentTransactionTotal($userId, $seconds) {
        $stmt = $this->pdo->prepare('
            SELECT COALESCE(SUM(total_amount), 0) as total
            FROM orders
            WHERE user_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL ? SECOND)
        ');
        $stmt->execute([$userId, $seconds]);
        $result = $stmt->fetch();
        return (float)$result['total'];
    }
    
    /**
     * Vérifie si l'IP a changé récemment
     */
    private function hasIPChangedRecently($userId, $currentIP) {
        $stmt = $this->pdo->prepare('
            SELECT DISTINCT ip_address
            FROM orders
            WHERE user_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
            ORDER BY created_at DESC
            LIMIT 5
        ');
        $stmt->execute([$userId]);
        $ips = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        return count(array_unique($ips)) > 1;
    }
    
    /**
     * Obtient le pays depuis l'IP
     */
    private function getCountryFromIP($ip) {
        // Utiliser une API de géolocalisation ou une base de données locale
        // Pour cet exemple, on retourne une valeur fictive
        // En production, utiliser: GeoIP, ipinfo.io, etc.
        return 'BJ'; // Bénin par défaut
    }
    
    /**
     * Obtient le pays précédent de l'utilisateur
     */
    private function getUserPreviousCountry($userId) {
        $stmt = $this->pdo->prepare('
            SELECT ip_address
            FROM orders
            WHERE user_id = ?
            ORDER BY created_at DESC
            LIMIT 1
        ');
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        
        if ($result) {
            return $this->getCountryFromIP($result['ip_address']);
        }
        
        return null;
    }
    
    /**
     * Vérifie si l'utilisateur utilise plusieurs appareils
     */
    private function hasMultipleDevices($userId) {
        $stmt = $this->pdo->prepare('
            SELECT COUNT(DISTINCT user_agent) as count
            FROM orders
            WHERE user_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ');
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        
        return (int)$result['count'] > 2;
    }
    
    /**
     * Vérifie si l'utilisateur a plusieurs adresses de livraison
     */
    private function hasMultipleShippingAddresses($userId) {
        $stmt = $this->pdo->prepare('
            SELECT COUNT(DISTINCT shipping_address) as count
            FROM orders
            WHERE user_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
        ');
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        
        return (int)$result['count'] > 1;
    }
    
    /**
     * Vérifie le risque d'un coupon
     */
    private function checkCouponRisk($couponCode, $userId) {
        $score = 0;
        $risk = false;
        
        // Vérifier si le coupon a été utilisé plusieurs fois
        $stmt = $this->pdo->prepare('
            SELECT COUNT(*) as count
            FROM orders
            WHERE coupon_code = ? AND user_id = ?
        ');
        $stmt->execute([$couponCode, $userId]);
        $result = $stmt->fetch();
        
        if ((int)$result['count'] > 3) {
            $score += 20;
            $risk = true;
        }
        
        return ['score' => $score, 'risk' => $risk];
    }
    
    /**
     * Détermine le niveau de risque
     */
    private function getRiskLevel($score) {
        if ($score >= 70) return 'HIGH';
        if ($score >= 50) return 'MEDIUM';
        if ($score >= 30) return 'LOW';
        return 'SAFE';
    }
    
    /**
     * Marque un utilisateur comme suspect
     */
    public function flagUser($userId, $reason) {
        $stmt = $this->pdo->prepare('
            INSERT INTO fraud_flags (user_id, reason, created_at)
            VALUES (?, ?, NOW())
        ');
        $stmt->execute([$userId, $reason]);
        
        logSecurityCritical('User flagged for fraud', [
            'user_id' => $userId,
            'reason' => $reason
        ]);
    }
    
    /**
     * Vérifie si un utilisateur est marqué comme suspect
     */
    public function isUserFlagged($userId) {
        $stmt = $this->pdo->prepare('
            SELECT COUNT(*) as count
            FROM fraud_flags
            WHERE user_id = ? AND resolved = 0
        ');
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        
        return (int)$result['count'] > 0;
    }
}

// Créer la table fraud_flags si elle n'existe pas
function createFraudFlagsTable() {
    global $pdo;
    
    $sql = "
    CREATE TABLE IF NOT EXISTS fraud_flags (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        reason TEXT NOT NULL,
        resolved TINYINT(1) DEFAULT 0,
        resolved_by INT,
        resolved_at DATETIME,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (resolved_by) REFERENCES users(id),
        INDEX idx_user_id (user_id),
        INDEX idx_resolved (resolved)
    ) ENGINE=InnoDB;
    ";
    
    $pdo->exec($sql);
}
