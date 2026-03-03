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
MYSQL="/c/xampp/mysql/bin/mysql.exe" # changer le nom du chemin vers mysql.exe
BACKUP_DIR="dir\backup" # changer le chemin si besoin
DATE=$(date +"%Y-%m-%d_%H-%M")

mkdir -p "$BACKUP_DIR"

OUTPUT_FILE="$BACKUP_DIR/users_$DATE.sql"

echo "-- Export des utilisateurs MariaDB" > "$OUTPUT_FILE"
echo "SET FOREIGN_KEY_CHECKS=0;" >> "$OUTPUT_FILE"
echo "SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';" >> "$OUTPUT_FILE"
echo "" >> "$OUTPUT_FILE"

# Récupérer la liste des users (hors comptes système)
USERS=$("$MYSQL" -u "$DB_USER" -p"$DB_PASSWORD" -N -e \
"SELECT CONCAT(user,'@',host) 
 FROM mysql.user 
 WHERE user NOT IN ('mysql.sys','mysql.session','mysql.infoschema');")

for USER in $USERS
do
    USERNAME=$(echo $USER | cut -d@ -f1)
    HOST=$(echo $USER | cut -d@ -f2)

    echo "-- $USERNAME@$HOST" >> "$OUTPUT_FILE"

    "$MYSQL" -u "$DB_USER" -p"$DB_PASSWORD" -N -e \
    "SHOW CREATE USER '$USERNAME'@'$HOST';" >> "$OUTPUT_FILE"

    "$MYSQL" -u "$DB_USER" -p"$DB_PASSWORD" -N -e \
    "SHOW GRANTS FOR '$USERNAME'@'$HOST';" >> "$OUTPUT_FILE"

    echo "" >> "$OUTPUT_FILE"
done

echo "FLUSH PRIVILEGES;" >> "$OUTPUT_FILE"

echo "Export utilisateurs terminé : $OUTPUT_FILE"