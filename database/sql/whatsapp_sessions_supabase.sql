-- =============================================================================
-- DABA - TABLE DES SESSIONS WHATSAPP (PostgreSQL / Supabase / Neon)
-- =============================================================================
-- Cette table permet à n8n (ou tout agent/bot de commande conversationnelle)
-- de stocker l'état, la mémoire du panier temporaire et les messages clients WhatsApp.
-- =============================================================================

-- Créer la table whatsapp_sessions si elle n'existe pas
CREATE TABLE IF NOT EXISTS public.whatsapp_sessions (
    id BIGSERIAL PRIMARY KEY,
    session_id VARCHAR(100) NOT NULL UNIQUE,          -- ex: numéro de téléphone format international ou chat_id
    phone_number VARCHAR(30) NOT NULL,                -- numéro normalisé (+228..., +229...)
    user_name VARCHAR(150),                           -- nom du contact WhatsApp si disponible
    current_step VARCHAR(50) DEFAULT 'idle',          -- étape courante du tunnel (ex: welcome, selecting_product, awaiting_address, confirming_order)
    cart_data JSONB DEFAULT '[]'::jsonb,              -- articles en cours dans la session [{"product_id": 1, "quantity": 2, "price": 5000}]
    session_data JSONB DEFAULT '{}'::jsonb,           -- métadonnées supplémentaires (adresse, date souhaitée, note, messages_history)
    last_interaction TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Index pour des recherches ultra-rapides lors de la réception des webhooks WhatsApp
CREATE INDEX IF NOT EXISTS idx_wa_session_id ON public.whatsapp_sessions (session_id);
CREATE INDEX IF NOT EXISTS idx_wa_phone_number ON public.whatsapp_sessions (phone_number);
CREATE INDEX IF NOT EXISTS idx_wa_last_interaction ON public.whatsapp_sessions (last_interaction);

-- Trigger pour mettre à jour automatiquement le champ updated_at lors des modifications
CREATE OR REPLACE FUNCTION update_whatsapp_sessions_updated_at()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trg_whatsapp_sessions_updated_at ON public.whatsapp_sessions;
CREATE TRIGGER trg_whatsapp_sessions_updated_at
BEFORE UPDATE ON public.whatsapp_sessions
FOR EACH ROW
EXECUTE FUNCTION update_whatsapp_sessions_updated_at();

-- Commentaire descriptif
COMMENT ON TABLE public.whatsapp_sessions IS 'Stockage de la mémoire conversationnelle et des sessions de commande WhatsApp pour les workflows n8n de Daba';
