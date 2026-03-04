#!/bin/bash

# ================================================================
# SCRIPT : recovery_db.example.sh
# OBJET  : Exemple prêt à copier pour restaurer une base depuis un backup
#          local (.sql.gz) ou distant (optionnel).
#
# UTILISATION TYPE :
# - cp recovery_db.example.sh recovery_db.sh
# - Adapter les variables DB et chemins de backup
# - Exécuter le script sur la base cible
#
# SORTIES :
# - Base restaurée
# - Logs horodatés dans le dossier logs/
# ================================================================

# ===== COMMANDE POUR L'UTILISER =====
# 1. Copier ce fichier : cp recovery_db.example.sh recovery_db.sh
# 2. Rendre le script exécutable : chmod +x recovery_db.sh
# 3. Ajouter MySQL au PATH si nécessaire (ex: export PATH=$PATH:/chemin/vers/mysql/bin)
# 4. Tester si ça a bien fonctionné : mysql --version
# 5. Exécuter le script : ./recovery_db.sh

# Nom du script pour le log.
SCRIPT_NAME="$(basename "$0" .sh)"
# Dossier absolu du script.
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
# Dossier logs local.
LOG_DIR="$SCRIPT_DIR/logs"
# Créer le dossier logs si besoin.
mkdir -p "$LOG_DIR"
# Fichier log journalier.
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
# Configuration DB cible de restauration.
DB_USER="NomUser" # changer le nom d'utilisateur
DB_PASSWORD="MotDePasse" # changer le mot de passe
DB_NAME="paaxio_db" # changer le nom de la base de données
BACKUP_DIR="dir/backup" # changer le chemin si besoin
MYSQL="${MYSQL:-mysql}" # chemin du client mysql (optionnel)

# ===== SERVEUR EXTERNE (OPTIONNEL) =====
ENABLE_REMOTE_RESTORE="false" # true pour télécharger le dernier backup depuis serveur externe
REMOTE_USER="backup_user"
REMOTE_HOST="backup.example.com"
REMOTE_PORT="22"
REMOTE_DIR="/srv/backups/paaxio"
SSH_KEY="" # ex: /home/user/.ssh/id_rsa (laisser vide pour clé par défaut)

# ===== VERIFICATION mysql =====
# Si MYSQL n'est pas fourni et introuvable dans le PATH, tenter des chemins locaux usuels.
if [ "$MYSQL" = "mysql" ] && ! command -v mysql &> /dev/null; then
    for candidate in \
        /c/wamp64/bin/mysql/*/bin/mysql.exe \
        /c/wamp64/bin/mariadb/*/bin/mysql.exe \
        /mingw64/bin/mysql.exe \
        /usr/bin/mysql
    do
        if [ -x "$candidate" ]; then
            MYSQL="$candidate"
            log_info "mysql détecté automatiquement: $MYSQL"
            break
        fi
    done
fi

# Vérifier mysql (import SQL).
if [ ! -x "$MYSQL" ] && ! command -v "$MYSQL" &> /dev/null
then
    log_error "mysql n'est pas accessible via MYSQL=$MYSQL"
    exit 1
fi

# ===== VERIFICATION gunzip =====
# Vérifier gunzip (lecture .sql.gz).
if ! command -v gunzip &> /dev/null
then
    log_error "gunzip n'est pas installé ou n'est pas dans le PATH."
    exit 1
fi

# Vérifier ssh/scp uniquement en mode distant.
if [ "$ENABLE_REMOTE_RESTORE" = "true" ]; then
    if ! command -v scp &> /dev/null || ! command -v ssh &> /dev/null
    then
        log_error "ssh/scp n'est pas installé ou n'est pas dans le PATH."
        exit 1
    fi
fi

# ===== VERIFICATION DOSSIER =====
# Vérifier le dossier local de backup.
if [ ! -d "$BACKUP_DIR" ]; then
    log_error "Le dossier de backup n'existe pas : $BACKUP_DIR"
    exit 1
fi

# ===== TELECHARGEMENT DEPUIS SERVEUR EXTERNE (OPTIONNEL) =====
# En mode distant, récupérer le dernier backup avant restore.
if [ "$ENABLE_REMOTE_RESTORE" = "true" ]; then
    log_info "Recherche du dernier backup distant..."

    # Préparer options SSH.
    SSH_OPTS="-p $REMOTE_PORT"
    if [ -n "$SSH_KEY" ]; then
        SSH_OPTS="$SSH_OPTS -i $SSH_KEY"
    fi

    # Sélectionner le dernier backup distant .sql.gz.
    REMOTE_BACKUP=$(ssh $SSH_OPTS "$REMOTE_USER@$REMOTE_HOST" "ls -t '$REMOTE_DIR'/*.sql.gz 2>/dev/null | head -n 1")

    if [ -z "$REMOTE_BACKUP" ]; then
        log_error "Aucun backup .sql.gz trouvé sur le serveur externe"
        exit 1
    fi

    log_info "Backup distant sélectionné : $REMOTE_BACKUP"
    # Télécharger ce backup côté local.
    scp $SSH_OPTS "$REMOTE_USER@$REMOTE_HOST:$REMOTE_BACKUP" "$BACKUP_DIR/" || {
        log_error "Échec du téléchargement du backup distant."
        exit 1
    }
fi

# ===== SELECTION DERNIER BACKUP =====
# Sélectionner le dernier backup local .sql.gz.
BACKUP_FILE=$(ls -t "$BACKUP_DIR"/*.sql.gz 2>/dev/null | head -n 1)

if [ -z "$BACKUP_FILE" ]; then
    log_error "Aucun backup .sql.gz trouvé dans $BACKUP_DIR"
    exit 1
fi

log_info "Backup sélectionné : $BACKUP_FILE"
log_info "Restauration de la base $DB_NAME en cours..."

# ===== RESTAURATION =====
# Restaurer la base cible à partir de l'archive.
gunzip -c "$BACKUP_FILE" | "$MYSQL" -u "$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" 2>error_restore_db.log

# ===== VERIFICATION RESULTAT =====
if [ $? -eq 0 ]; then
    log_info "Restauration BDD réussie."
else
    log_error "Erreur lors de la restauration BDD."
    log_error "Détail de l'erreur :"
    cat error_restore_db.log | tee -a "$LOG_FILE"
    exit 1
fi