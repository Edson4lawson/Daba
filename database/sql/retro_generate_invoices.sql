-- Rétro-génération de factures pour les commandes existantes sans facture
-- Génère des factures pour les commandes créées avant l'implémentation de la génération automatique

-- D'abord, récupérer le dernier numéro de séquence utilisé
SET @last_seq = (
    SELECT CAST(SUBSTRING(invoice_number, 12) AS UNSIGNED)
    FROM invoices
    WHERE invoice_number LIKE 'FAC-2026-%'
    ORDER BY invoice_number DESC
    LIMIT 1
);

-- Si aucune facture n'existe, commencer à 1
SET @last_seq = IFNULL(@last_seq, 0);

-- Générer les factures rétroactivement
INSERT INTO invoices (order_id, invoice_number, amount, status, created_at)
SELECT 
    o.id,
    CONCAT('FAC-2026-', LPAD(CAST(@last_seq + CAST(ROW_NUMBER() OVER (ORDER BY o.id) AS UNSIGNED) AS UNSIGNED), 5, '0')),
    o.total_amount,
    'pending',
    o.created_at
FROM orders o
LEFT JOIN invoices i ON o.id = i.order_id
WHERE i.id IS NULL
ORDER BY o.id;
