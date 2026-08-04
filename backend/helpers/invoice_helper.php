<?php
/**
 * Helper pour la génération automatique de factures
 * Centralise la logique de création de factures après création de commande
 */

/**
 * Génère une facture automatiquement pour une commande
 * 
 * @param PDO $pdo Instance de connexion à la base de données
 * @param int $orderId ID de la commande
 * @param float $amount Montant total de la commande
 * @return int|false ID de la facture créée ou false en cas d'erreur
 */
function generateInvoice($pdo, $orderId, $amount) {
    try {
        // Générer le numéro de facture unique: FAC-YYYY-XXXXX
        $year = date('Y');
        
        // Récupérer le dernier numéro de facture de l'année
        $stmt = $pdo->prepare("
            SELECT invoice_number 
            FROM invoices 
            WHERE invoice_number LIKE ? 
            ORDER BY invoice_number DESC 
            LIMIT 1
        ");
        $stmt->execute(["FAC-$year-%"]);
        $lastInvoice = $stmt->fetch();
        
        $sequence = 1;
        if ($lastInvoice) {
            // Extraire le numéro séquentiel (ex: FAC-2026-00001 -> 1)
            $parts = explode('-', $lastInvoice['invoice_number']);
            $sequence = (int)end($parts) + 1;
        }
        
        // Formater avec 5 chiffres (ex: 00001)
        $invoiceNumber = sprintf("FAC-%s-%05d", $year, $sequence);
        
        // Insérer la facture
        $stmt = $pdo->prepare("
            INSERT INTO invoices (order_id, invoice_number, amount, status, created_at)
            VALUES (?, ?, ?, 'pending', NOW())
        ");
        $stmt->execute([$orderId, $invoiceNumber, $amount]);
        
        return $pdo->lastInsertId();
        
    } catch (Exception $e) {
        error_log('Erreur génération facture: ' . $e->getMessage());
        return false;
    }
}
