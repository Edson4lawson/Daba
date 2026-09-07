#!/bin/bash
# =============================================================================
# SCRIPT DE SAUVEGARDE AUTOMATIQUE CHIFFRÉE - DABA
# Sauvegarde MySQL avec chiffrement AES-256-GCM
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
RETENTION_DAYS="${RETENTION_DAYS:-30}"

# Créer le répertoire de sauvegarde
mkdir -p "$BACKUP_DIR"

# Date du jour
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_FILE="$BACKUP_DIR/daba_$DATE.sql"
ENCRYPTED_FILE="$BACKUP_FILE.enc"

echo "[$(date)] Début de la sauvegarde de la base de données..."

# Sauvegarde MySQL
mysqldump -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASSWORD" \
    --single-transaction \
    --quick \
    --lock-tables=false \
    --routines \
    --triggers \
    --events \
    "$DB_NAME" > "$BACKUP_FILE"

if [ $? -ne 0 ]; then
    echo "[$(date)] ERREUR: Échec de la sauvegarde MySQL"
    exit 1
fi

echo "[$(date)] Sauvegarde MySQL terminée. Chiffrement en cours..."

# Chiffrement AES-256-GCM
openssl enc -aes-256-gcm -salt -pbkdf2 -iter 100000 \
    -in "$BACKUP_FILE" \
    -out "$ENCRYPTED_FILE" \
    -pass pass:"$ENCRYPTION_KEY"

if [ $? -ne 0 ]; then
    echo "[$(date)] ERREUR: Échec du chiffrement"
    rm -f "$BACKUP_FILE"
    exit 1
fi

# Supprimer le fichier non chiffré
rm -f "$BACKUP_FILE"

echo "[$(date)] Chiffrement terminé. Upload vers S3..."

# Upload vers S3 si configuré
if [ -n "$S3_BUCKET" ]; then
    aws s3 cp "$ENCRYPTED_FILE" "s3://$S3_BUCKET/database-backups/daba_$DATE.sql.enc"
    if [ $? -eq 0 ]; then
        echo "[$(date)] Sauvegarde uploadée sur S3 avec succès"
    else
        echo "[$(date)] ERREUR: Échec de l'upload sur S3"
    fi
fi

# Nettoyer les anciennes sauvegardes locales
echo "[$(date)] Nettoyage des sauvegardes de plus de $RETENTION_DAYS jours..."
find "$BACKUP_DIR" -name "daba_*.sql.enc" -mtime +$RETENTION_DAYS -delete

# Nettoyage S3
if [ -n "$S3_BUCKET" ]; then
    aws s3 ls "s3://$S3_BUCKET/database-backups/" | while read -r line; do
        FILE_DATE=$(echo "$line" | awk '{print $4}' | grep -oP '\d{8}_\d{6}')
        if [ -n "$FILE_DATE" ]; then
            FILE_TIMESTAMP=$(date -d "${FILE_DATE:0:4}-${FILE_DATE:4:2}-${FILE_DATE:6:2} ${FILE_DATE:9:2}:${FILE_DATE:11:2}:${FILE_DATE:13:2}" +%s 2>/dev/null)
            if [ -n "$FILE_TIMESTAMP" ]; then
                CURRENT_TIME=$(date +%s)
                DIFF_DAYS=$(( (CURRENT_TIME - FILE_TIMESTAMP) / 86400 ))
                if [ $DIFF_DAYS -gt $RETENTION_DAYS ]; then
                    FILE_NAME=$(echo "$line" | awk '{print $4}')
                    aws s3 rm "s3://$S3_BUCKET/database-backups/$FILE_NAME"
                    echo "[$(date)] Supprimé: $FILE_NAME"
                fi
            fi
        fi
    done
fi

echo "[$(date)] Sauvegarde terminée avec succès"
