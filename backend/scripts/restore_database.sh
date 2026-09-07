#!/bin/bash
# =============================================================================
# SCRIPT DE RESTAURATION DE SAUVEGARDE CHIFFRÉE - DABA
# Restauration MySQL avec déchiffrement AES-256-GCM
# =============================================================================

# Configuration
DB_HOST="${DB_HOST:-localhost}"
DB_PORT="${DB_PORT:-3306}"
DB_NAME="${DB_NAME:-daba}"
DB_USER="${DB_USER:-root}"
DB_PASSWORD="${DB_PASSWORD:-}"
BACKUP_DIR="${BACKUP_DIR:-/var/backups/daba}"
ENCRYPTION_KEY="${ENCRYPTION_KEY}"
S3_BUCKET="${S3_BUCKET:-}"

# Vérifier les arguments
if [ $# -lt 1 ]; then
    echo "Usage: $0 <fichier_sauvegarde> [--from-s3]"
    echo "Exemple: $0 daba_20260712_120000.sql.enc"
    echo "Exemple S3: $0 daba_20260712_120000.sql.enc --from-s3"
    exit 1
fi

BACKUP_FILE="$1"
FROM_S3="${2:-}"

if [ "$FROM_S3" = "--from-s3" ]; then
    if [ -z "$S3_BUCKET" ]; then
        echo "ERREUR: S3_BUCKET non configuré"
        exit 1
    fi
    
    echo "Téléchargement depuis S3..."
    aws s3 cp "s3://$S3_BUCKET/database-backups/$BACKUP_FILE" "/tmp/$BACKUP_FILE"
    BACKUP_FILE="/tmp/$BACKUP_FILE"
else
    BACKUP_FILE="$BACKUP_DIR/$BACKUP_FILE"
fi

if [ ! -f "$BACKUP_FILE" ]; then
    echo "ERREUR: Fichier de sauvegarde non trouvé: $BACKUP_FILE"
    exit 1
fi

echo "Début de la restauration..."

# Déchiffrement
DECRYPTED_FILE="/tmp/restore_$(date +%s).sql"
openssl enc -aes-256-gcm -d -pbkdf2 -iter 100000 \
    -in "$BACKUP_FILE" \
    -out "$DECRYPTED_FILE" \
    -pass pass:"$ENCRYPTION_KEY"

if [ $? -ne 0 ]; then
    echo "ERREUR: Échec du déchiffrement"
    exit 1
fi

# Restauration MySQL
mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" < "$DECRYPTED_FILE"

if [ $? -ne 0 ]; then
    echo "ERREUR: Échec de la restauration MySQL"
    rm -f "$DECRYPTED_FILE"
    exit 1
fi

# Nettoyage
rm -f "$DECRYPTED_FILE"
if [ "$FROM_S3" = "--from-s3" ]; then
    rm -f "/tmp/$BACKUP_FILE"
fi

echo "Restauration terminée avec succès"
