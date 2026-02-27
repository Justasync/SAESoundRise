#!/bin/bash

# ===== COMMANDE POUR L'UTILISER =====
# 1. Rendre le script exécutable : chmod +x backup_db.sh
# 2. Exécuter le script : ./backup_db.sh

DB_USER="user"
DB_NAME="db_name"
BACKUP_DIR="dir\backup"

# Récupère le fichier .sql.gz le plus récent
BACKUP_FILE=$(ls -t "$BACKUP_DIR"/*.sql.gz 2>/dev/null | head -n 1)

# Vérification
if [ -z "$BACKUP_FILE" ]; then
    echo "Aucun backup trouvé dans $BACKUP_DIR"
    exit 1
fi

echo "Backup sélectionné : $BACKUP_FILE"
echo "Restauration en cours..."

gunzip -c "$BACKUP_FILE" | mysql -u "$DB_USER" -p "$DB_NAME"

if [ $? -eq 0 ]; then
    echo "Restauration réussie."
else
    echo "Erreur lors de la restauration."
fi