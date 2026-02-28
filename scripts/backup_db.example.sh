#!/bin/bash

# ===== COMMANDE POUR L'UTILISER =====
# 1. Rendre le script exécutable : chmod +x backup_db.sh
# 2. Ajouter MySQL au PATH si nécessaire (ex: export PATH=$PATH:/chemin/vers/mysql/bin) (chemin à changer si besoin)
# 3. Tester si ça à bien fonctionné : mysqldump --version
# 4. Exécuter le script : ./backup_db.sh
# 5. Pour automatiser, ajouter une tâche cron : crontab -e

# ===== CONFIGURATION =====
DB_USER="user" # changer le nom d'utilisateur
DB_PASSWORD="password" # changer le mot de passe
DB_NAME="db_name" # changer le nom de la base de données
BACKUP_DIR="dir\backup" # changer le chemin si besoin
DATE=$(date +"%Y-%m-%d_%H-%M")

# ===== VERIFICATION mysqldump =====
if ! command -v mysqldump &> /dev/null
then
    echo "ERREUR : mysqldump n'est pas installé ou n'est pas dans le PATH."
    echo "Installe MySQL ou ajoute le dossier bin de MySQL dans ton PATH."
    exit 1
fi

# ===== CREATION DOSSIER SI NON EXISTANT =====
if [ ! -d "$BACKUP_DIR" ]; then
    echo "Dossier backup inexistant. Création..."
    mkdir -p "$BACKUP_DIR" || {
        echo "ERREUR : Impossible de créer le dossier $BACKUP_DIR"
        exit 1
    }
fi

# ===== SAUVEGARDE =====
BACKUP_FILE="$BACKUP_DIR/${DB_NAME}_$DATE.sql.gz"

mysqldump -u "$DB_USER" -p"$DB_PASSWORD" \
--routines \
--triggers \
--events \
"$DB_NAME" 2>error.log | gzip > "$BACKUP_FILE"

# ===== VERIFICATION RESULTAT =====
if [ $? -eq 0 ]; then
    echo "Sauvegarde réussie : $BACKUP_FILE"
else
    echo "ERREUR lors de la sauvegarde."
    echo "Détail de l'erreur :"
    cat error.log
    rm -f "$BACKUP_FILE"
    exit 1
fi

# ===== SUPPRESSION DES SAUVEGARDES DE PLUS DE 7 JOURS =====
find "$BACKUP_DIR" -type f -name "*.sql.gz" -mtime +7 -delete

echo "Nettoyage des sauvegardes > 7 jours effectué."