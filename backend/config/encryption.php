<?php
/**
 * Chiffrement AES-256-GCM - Daba
 * Chiffrement des données sensibles (numéros de téléphone, adresses, etc.)
 * 
 * @author Security Audit
 * @version 1.0.0
 */

/**
 * Chiffre une donnée avec AES-256-GCM
 * 
 * @param string $data Donnée à chiffrer
 * @param string $key Clé de chiffrement (32 bytes pour AES-256)
 * @return string Donnée chiffrée encodée en base64
 */
function encryptData($data, $key = null) {
    if ($key === null) {
        $key = getenv('ENCRYPTION_KEY');
        if (!$key) {
            throw new Exception('ENCRYPTION_KEY non configuré');
        }
    }
    
    // S'assurer que la clé fait 32 bytes
    $key = hash('sha256', $key, true);
    
    // Générer un IV unique (12 bytes pour GCM)
    $iv = random_bytes(12);
    
    // Chiffrer avec AES-256-GCM
    $tag = '';
    $ciphertext = openssl_encrypt(
        $data,
        'aes-256-gcm',
        $key,
        OPENSSL_RAW_DATA,
        $iv,
        $tag
    );
    
    if ($ciphertext === false) {
        throw new Exception('Échec du chiffrement');
    }
    
    // Combiner IV, tag et ciphertext pour stockage
    $encrypted = $iv . $tag . $ciphertext;
    
    return base64_encode($encrypted);
}

/**
 * Déchiffre une donnée chiffrée avec AES-256-GCM
 * 
 * @param string $encryptedData Donnée chiffrée (base64)
 * @param string $key Clé de déchiffrement
 * @return string Donnée déchiffrée
 */
function decryptData($encryptedData, $key = null) {
    if ($key === null) {
        $key = getenv('ENCRYPTION_KEY');
        if (!$key) {
            throw new Exception('ENCRYPTION_KEY non configuré');
        }
    }
    
    // S'assurer que la clé fait 32 bytes
    $key = hash('sha256', $key, true);
    
    // Décoder depuis base64
    $encrypted = base64_decode($encryptedData);
    
    // Extraire IV (12 bytes), tag (16 bytes) et ciphertext
    $iv = substr($encrypted, 0, 12);
    $tag = substr($encrypted, 12, 16);
    $ciphertext = substr($encrypted, 28);
    
    // Déchiffrer
    $decrypted = openssl_decrypt(
        $ciphertext,
        'aes-256-gcm',
        $key,
        OPENSSL_RAW_DATA,
        $iv,
        $tag
    );
    
    if ($decrypted === false) {
        throw new Exception('Échec du déchiffrement');
    }
    
    return $decrypted;
}

/**
 * Chiffre un champ spécifique d'un tableau de données
 * 
 * @param array $data Données à chiffrer
 * @param array $fields Champs à chiffrer
 * @return array Données avec champs chiffrés
 */
function encryptFields($data, $fields) {
    foreach ($fields as $field) {
        if (isset($data[$field]) && !empty($data[$field])) {
            $data[$field] = encryptData($data[$field]);
        }
    }
    return $data;
}

/**
 * Déchiffre un champ spécifique d'un tableau de données
 * 
 * @param array $data Données à déchiffrer
 * @param array $fields Champs à déchiffrer
 * @return array Données avec champs déchiffrés
 */
function decryptFields($data, $fields) {
    foreach ($fields as $field) {
        if (isset($data[$field]) && !empty($data[$field])) {
            try {
                $data[$field] = decryptData($data[$field]);
            } catch (Exception $e) {
                // Si le déchiffrement échoue, laisser la donnée telle quelle
                error_log('Déchiffrement échoué pour le champ ' . $field . ': ' . $e->getMessage());
            }
        }
    }
    return $data;
}

/**
 * Génère une clé de chiffrement sécurisée
 * 
 * @return string Clé de 64 caractères hexadécimaux
 */
function generateEncryptionKey() {
    return bin2hex(random_bytes(32));
}

/**
 * Hache une donnée pour comparaison (non réversible)
 * 
 * @param string $data Donnée à hacher
 * @return string Hash
 */
function hashData($data) {
    return hash('sha256', $data);
}
