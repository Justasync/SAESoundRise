#!/bin/bash

# ================================================================
# SCRIPT : export_users.example.sh
# OBJET  : Exemple prêt à copier pour exporter les utilisateurs MySQL/
#          MariaDB (comptes + droits) dans un SQL réutilisable.
#
# UTILISATION TYPE :
# - cp export_users.example.sh export_users.sh
# - Modifier les variables de configuration
# - Lancer le script puis planifier si besoin
#
# SORTIES :
# - Fichier users_YYYY-MM-DD_HH-MM.sql
# - Logs horodatés dans le dossier logs/
# ================================================================

# ===== COMMANDE POUR L'UTILISER =====
# 1. Copier ce fichier : cp export_users.example.sh export_users.sh
# 2. Rendre le script exécutable : chmod +x export_users.sh
# 3. Adapter la configuration ci-dessous
# 4. Exécuter : ./export_users.sh

# Nom du script pour journalisation.
SCRIPT_NAME="$(basename "$0" .sh)"
# Dossier absolu du script.
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
# Dossier local des logs.
LOG_DIR="$SCRIPT_DIR/logs"
# Créer logs/ si absent.
mkdir -p "$LOG_DIR"
# Fichier de log journalier.
LOG_FILE="$LOG_DIR/${SCRIPT_NAME}_$(date +"%Y-%m-%d").log"

# Logger INFO.
log_info() {
    echo "[$(date +"%Y-%m-%d %H:%M:%S")] [INFO] $1" | tee -a "$LOG_FILE"
}

# Logger ERROR.
log_error() {
    echo "[$(date +"%Y-%m-%d %H:%M:%S")] [ERROR] $1" | tee -a "$LOG_FILE" >&2
}

# ===== CONFIGURATION =====
# Compte admin MySQL pour lire mysql.user et les grants.
DB_USER="${DB_USER:-root}"
# Mot de passe admin (optionnel).
DB_PASSWORD="${DB_PASSWORD:-}"
# Chemin du binaire mysql.
MYSQL="${MYSQL:-/c/wamp64/bin/mariadb/mariadb11.5.2/bin/mysql.exe}"
# Dossier local de sortie users_*.sql.
BACKUP_DIR="${BACKUP_DIR:-dir/backup}"
# Horodatage pour le nom de fichier.
DATE=$(date +"%Y-%m-%d_%H-%M")

# ===== SERVEUR EXTERNE (OPTIONNEL) =====
ENABLE_REMOTE_SYNC="${ENABLE_REMOTE_SYNC:-false}"
REMOTE_USER="${REMOTE_USER:-backup_user}"
REMOTE_HOST="${REMOTE_HOST:-backup.example.com}"
REMOTE_PORT="${REMOTE_PORT:-22}"
REMOTE_USERS_DIR="${REMOTE_USERS_DIR:-/srv/backups/paaxio/users}"
SSH_KEY="${SSH_KEY:-}"

# Option mot de passe construite conditionnellement.
MYSQL_PWD_OPT=""
if [ -n "$DB_PASSWORD" ]; then
    MYSQL_PWD_OPT="-p$DB_PASSWORD"
fi

# Vérifier l'accès au client mysql.
if [ ! -x "$MYSQL" ] && ! command -v "$MYSQL" &> /dev/null
then
    log_error "mysql n'est pas accessible via MYSQL=$MYSQL"
    exit 1
fi

# En mode distant, vérifier ssh/scp.
if [ "$ENABLE_REMOTE_SYNC" = "true" ]; then
    if ! command -v scp &> /dev/null || ! command -v ssh &> /dev/null
    then
        log_error "ssh/scp n'est pas installé ou n'est pas dans le PATH."
        exit 1
    fi
fi

# Créer le dossier de backup local.
mkdir -p "$BACKUP_DIR"
# Définir le fichier SQL d'export users.
OUTPUT_FILE="$BACKUP_DIR/users_$DATE.sql"

# Initialiser le fichier SQL avec en-tête et modes utiles.
echo "-- Export des utilisateurs MariaDB" > "$OUTPUT_FILE"
echo "SET FOREIGN_KEY_CHECKS=0;" >> "$OUTPUT_FILE"
echo "SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';" >> "$OUTPUT_FILE"
echo "" >> "$OUTPUT_FILE"

# Récupérer les comptes user@host hors comptes système.
USERS=$("$MYSQL" -u "$DB_USER" $MYSQL_PWD_OPT -N -e \
"SELECT CONCAT(user,'@',host)
 FROM mysql.user
 WHERE user NOT IN ('mysql.sys','mysql.session','mysql.infoschema','mariadb.sys','root');")

# Traiter compte par compte pour exporter CREATE USER + GRANTS.
for USER in $USERS
do
    USERNAME=$(echo $USER | cut -d@ -f1)
    HOST=$(echo $USER | cut -d@ -f2)

    # Ajouter un repère visuel de compte dans le SQL.
    echo "-- $USERNAME@$HOST" >> "$OUTPUT_FILE"

    # Récupérer CREATE USER et le rendre idempotent.
    CREATE_STMT=$("$MYSQL" -u "$DB_USER" $MYSQL_PWD_OPT -N -e \
    "SHOW CREATE USER '$USERNAME'@'$HOST';" | awk -F'\t' '{print $2}' | sed 's/^CREATE USER /CREATE USER IF NOT EXISTS /')

    # Écrire CREATE USER avec ';' final.
    if [ -n "$CREATE_STMT" ]; then
        if [[ "$CREATE_STMT" != *";" ]]; then
            echo "$CREATE_STMT;" >> "$OUTPUT_FILE"
        else
            echo "$CREATE_STMT" >> "$OUTPUT_FILE"
        fi
    fi

    # Écrire tous les GRANTS avec ';' final forcé.
    "$MYSQL" -u "$DB_USER" $MYSQL_PWD_OPT -N -e \
    "SHOW GRANTS FOR '$USERNAME'@'$HOST';" | sed '/;$/! s/$/;/' >> "$OUTPUT_FILE"

    # Séparer visuellement les comptes.
    echo "" >> "$OUTPUT_FILE"
done

# Recharger les privilèges lors de l'import de ce script SQL.
echo "FLUSH PRIVILEGES;" >> "$OUTPUT_FILE"
log_info "Export utilisateurs terminé : $OUTPUT_FILE"

# Envoyer l'export users vers le serveur distant si activé.
if [ "$ENABLE_REMOTE_SYNC" = "true" ]; then
    log_info "Envoi de l'export users vers serveur externe..."

    # Préparer options SSH.
    SSH_OPTS="-p $REMOTE_PORT"
    if [ -n "$SSH_KEY" ]; then
        SSH_OPTS="$SSH_OPTS -i $SSH_KEY"
    fi

    # Créer le dossier distant cible.
    ssh $SSH_OPTS "$REMOTE_USER@$REMOTE_HOST" "mkdir -p '$REMOTE_USERS_DIR'" || {
        log_error "Impossible de créer le dossier distant $REMOTE_USERS_DIR"
        exit 1
    }

    # Copier le fichier users_*.sql vers la cible distante.
    scp $SSH_OPTS "$OUTPUT_FILE" "$REMOTE_USER@$REMOTE_HOST:$REMOTE_USERS_DIR/" || {
        log_error "Échec de l'envoi de l'export users vers le serveur externe."
        exit 1
    }

    log_info "Envoi externe réussi."
fi
