<?php
/**
 * Journalisation Centralisée - Bloom Chloé
 * Système de logging structuré pour les événements de sécurité
 * 
 * @author Security Audit
 * @version 1.0.0
 */

/**
 * Niveaux de log
 */
class LogLevel {
    const DEBUG = 'DEBUG';
    const INFO = 'INFO';
    const WARNING = 'WARNING';
    const ERROR = 'ERROR';
    const CRITICAL = 'CRITICAL';
}

/**
 * Catégories de log
 */
class LogCategory {
    const AUTH = 'AUTH';
    const PAYMENT = 'PAYMENT';
    const ADMIN = 'ADMIN';
    const API = 'API';
    const SECURITY = 'SECURITY';
    const DATABASE = 'DATABASE';
    const SYSTEM = 'SYSTEM';
}

/**
 * Classe de journalisation centralisée
 */
class SecurityLogger {
    private static $instance = null;
    private $logDir;
    private $context = [];
    
    private function __construct() {
        $this->logDir = __DIR__ . '/../logs';
        if (!is_dir($this->logDir)) {
            mkdir($this->logDir, 0755, true);
        }
        
        // Contexte par défaut
        $this->context = [
            'app' => 'daba',
            'environment' => getenv('APP_ENV') ?: 'development',
            'server_ip' => $_SERVER['SERVER_ADDR'] ?? 'unknown',
            'request_id' => $this->generateRequestId()
        ];
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Génère un ID de requête unique
     */
    private function generateRequestId() {
        return bin2hex(random_bytes(8));
    }
    
    /**
     * Ajoute du contexte
     */
    public function addContext($key, $value) {
        $this->context[$key] = $value;
    }
    
    /**
     * Log un événement
     */
    public function log($level, $category, $message, $context = []) {
        $logEntry = array_merge($this->context, $context);
        $logEntry['level'] = $level;
        $logEntry['category'] = $category;
        $logEntry['message'] = $message;
        $logEntry['timestamp'] = date('Y-m-d H:i:s');
        $logEntry['ip'] = $this->getClientIP();
        $logEntry['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        // Sélectionner le fichier selon la catégorie
        $logFile = $this->getLogFile($category);
        
        // Écrire le log en JSON
        $logLine = json_encode($logEntry) . "\n";
        file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);
        
        // Pour les logs critiques, envoyer une alerte
        if ($level === LogLevel::CRITICAL) {
            $this->sendAlert($logEntry);
        }
    }
    
    /**
     * Obtient le fichier de log selon la catégorie
     */
    private function getLogFile($category) {
        $date = date('Y-m-d');
        
        switch ($category) {
            case LogCategory::AUTH:
                return $this->logDir . "/auth_$date.log";
            case LogCategory::PAYMENT:
                return $this->logDir . "/payment_$date.log";
            case LogCategory::ADMIN:
                return $this->logDir . "/admin_$date.log";
            case LogCategory::SECURITY:
                return $this->logDir . "/security_$date.log";
            case LogCategory::API:
                return $this->logDir . "/api_$date.log";
            case LogCategory::DATABASE:
                return $this->logDir . "/database_$date.log";
            default:
                return $this->logDir . "/system_$date.log";
        }
    }
    
    /**
     * Obtient l'IP du client
     */
    private function getClientIP() {
        $headers = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'REMOTE_ADDR'
        ];
        
        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ips = explode(',', $_SERVER[$header]);
                return trim($ips[0]);
            }
        }
        
        return 'unknown';
    }
    
    /**
     * Envoie une alerte pour les événements critiques
     */
    private function sendAlert($logEntry) {
        // Envoyer email d'alerte
        $to = getenv('ALERT_EMAIL');
        if ($to) {
            $subject = "[ALERT] Bloom Chloé - {$logEntry['category']}: {$logEntry['level']}";
            $body = json_encode($logEntry, JSON_PRETTY_PRINT);
            
            // Utiliser mail() ou un service comme SendGrid
            // mail($to, $subject, $body);
            
            error_log("ALERT: $subject");
        }
        
        // Envoyer vers Slack/Discord si configuré
        $webhookUrl = getenv('SLACK_WEBHOOK_URL');
        if ($webhookUrl) {
            $this->sendSlackAlert($webhookUrl, $logEntry);
        }
    }
    
    /**
     * Envoie une alerte Slack
     */
    private function sendSlackAlert($webhookUrl, $logEntry) {
        $color = match($logEntry['level']) {
            LogLevel::CRITICAL => 'danger',
            LogLevel::ERROR => 'danger',
            LogLevel::WARNING => 'warning',
            default => 'good'
        };
        
        $payload = [
            'attachments' => [[
                'color' => $color,
                'title' => "[{$logEntry['category']}] {$logEntry['level']}",
                'text' => $logEntry['message'],
                'fields' => [
                    ['title' => 'IP', 'value' => $logEntry['ip'], 'short' => true],
                    ['title' => 'Environment', 'value' => $logEntry['environment'], 'short' => true],
                    ['title' => 'Timestamp', 'value' => $logEntry['timestamp'], 'short' => true]
                ]
            ]]
        ];
        
        $ch = curl_init($webhookUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close($ch);
    }
    
    /**
     * Nettoie les anciens logs
     */
    public function cleanup($days = 30) {
        $files = glob($this->logDir . '/*.log');
        $cutoff = time() - ($days * 86400);
        
        foreach ($files as $file) {
            if (filemtime($file) < $cutoff) {
                unlink($file);
            }
        }
    }
}

/**
 * Fonctions helper pour le logging
 */
function logAuth($message, $context = []) {
    SecurityLogger::getInstance()->log(LogLevel::INFO, LogCategory::AUTH, $message, $context);
}

function logAuthFailure($message, $context = []) {
    SecurityLogger::getInstance()->log(LogLevel::WARNING, LogCategory::AUTH, $message, $context);
}

function logPayment($message, $context = []) {
    SecurityLogger::getInstance()->log(LogLevel::INFO, LogCategory::PAYMENT, $message, $context);
}

function logPaymentFailure($message, $context = []) {
    SecurityLogger::getInstance()->log(LogLevel::ERROR, LogCategory::PAYMENT, $message, $context);
}

function logAdminAction($message, $context = []) {
    SecurityLogger::getInstance()->log(LogLevel::INFO, LogCategory::ADMIN, $message, $context);
}

function logSecurityEvent($message, $context = []) {
    SecurityLogger::getInstance()->log(LogLevel::WARNING, LogCategory::SECURITY, $message, $context);
}

function logSecurityCritical($message, $context = []) {
    SecurityLogger::getInstance()->log(LogLevel::CRITICAL, LogCategory::SECURITY, $message, $context);
}

function logAPI($message, $context = []) {
    SecurityLogger::getInstance()->log(LogLevel::INFO, LogCategory::API, $message, $context);
}

function logDatabase($message, $context = []) {
    SecurityLogger::getInstance()->log(LogLevel::ERROR, LogCategory::DATABASE, $message, $context);
}
