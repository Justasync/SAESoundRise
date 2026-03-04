#!/bin/bash

# ================================================================
# SCRIPT : recovery_users.example.sh
# OBJET  : Exemple prêt à copier pour restaurer les utilisateurs MySQL/
#          MariaDB depuis un export users_*.sql local ou distant.
#
# UTILISATION TYPE :
# - cp recovery_users.example.sh recovery_users.sh
# - Adapter les variables de connexion et de chemin
# - Exécuter le script avec un compte admin MySQL
#
# SORTIES :
# - Comptes utilisateurs restaurés
# - Logs horodatés dans le dossier logs/
# ================================================================

# ===== COMMANDE POUR L'UTILISER =====
# 1. Copier ce fichier : cp recovery_users.example.sh recovery_users.sh
# 2. Rendre le script exécutable : chmod +x recovery_users.sh
# 3. Ajouter MySQL au PATH si nécessaire (ex: export PATH=$PATH:/chemin/vers/mysql/bin)
# 4. Tester si ça a bien fonctionné : mysql --version
# 5. Exécuter le script : ./recovery_users.sh

# Nom du script pour la journalisation.
SCRIPT_NAME="$(basename "$0" .sh)"
# Dossier absolu du script.
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
# Dossier local des logs.
LOG_DIR="$SCRIPT_DIR/logs"
# Créer logs/ si nécessaire.
mkdir -p "$LOG_DIR"
# Fichier log du jour.
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
# Compte admin pour exécuter le SQL users/grants.
DB_USER="root" # changer le nom d'utilisateur admin MySQL
DB_PASSWORD="" # changer le mot de passe admin MySQL
BACKUP_DIR="dir/backup" # changer le chemin si besoin

# ===== SERVEUR EXTERNE (OPTIONNEL) =====
ENABLE_REMOTE_RESTORE="false" # true pour télécharger le dernier export users depuis serveur externe
REMOTE_USER="backup_user"
REMOTE_HOST="backup.example.com"
REMOTE_PORT="22"
REMOTE_DIR="/srv/backups/paaxio"
SSH_KEY="" # ex: /home/user/.ssh/id_rsa (laisser vide pour clé par défaut)

# ===== VERIFICATION mysql =====
# Vérifier mysql.
if ! command -v mysql &> /dev/null
then
    log_error "mysql n'est pas installé ou n'est pas dans le PATH."
    log_error "Installe MySQL ou ajoute le dossier bin de MySQL dans ton PATH."
    exit 1
fi

# Vérifier ssh/scp en mode distant uniquement.
if [ "$ENABLE_REMOTE_RESTORE" = "true" ]; then
    if ! command -v scp &> /dev/null || ! command -v ssh &> /dev/null
    then
        log_error "ssh/scp n'est pas installé ou n'est pas dans le PATH."
        exit 1
    fi
fi

# ===== VERIFICATION DOSSIER =====
# Vérifier existence du dossier local de backup.
if [ ! -d "$BACKUP_DIR" ]; then
    log_error "Le dossier de backup n'existe pas : $BACKUP_DIR"
    exit 1
fi

# ===== TELECHARGEMENT DEPUIS SERVEUR EXTERNE (OPTIONNEL) =====
# Télécharger le dernier users_*.sql distant si activé.
if [ "$ENABLE_REMOTE_RESTORE" = "true" ]; then
    log_info "Recherche du dernier export users distant..."

    # Préparer options SSH.
    SSH_OPTS="-p $REMOTE_PORT"
    if [ -n "$SSH_KEY" ]; then
        SSH_OPTS="$SSH_OPTS -i $SSH_KEY"
    fi

    # Rechercher le dernier users_*.sql distant.
    REMOTE_USERS_FILE=$(ssh $SSH_OPTS "$REMOTE_USER@$REMOTE_HOST" "ls -t '$REMOTE_DIR'/users_*.sql 2>/dev/null | head -n 1")

    if [ -z "$REMOTE_USERS_FILE" ]; then
        log_error "Aucun fichier users_*.sql trouvé sur le serveur externe"
        exit 1
    fi

    log_info "Fichier distant sélectionné : $REMOTE_USERS_FILE"
    # Télécharger le fichier users distant vers BACKUP_DIR.
    scp $SSH_OPTS "$REMOTE_USER@$REMOTE_HOST:$REMOTE_USERS_FILE" "$BACKUP_DIR/" || {
        log_error "Échec du téléchargement de l'export users distant."
        exit 1
    }
fi

# ===== SELECTION DERNIER EXPORT USERS =====
# Sélectionner le dernier export users local.
USERS_FILE=$(ls -t "$BACKUP_DIR"/users_*.sql 2>/dev/null | head -n 1)

if [ -z "$USERS_FILE" ]; then
    log_error "Aucun fichier users_*.sql trouvé dans $BACKUP_DIR"
    exit 1
fi

log_info "Fichier sélectionné : $USERS_FILE"
log_info "Restauration des utilisateurs MySQL en cours..."

# ===== RESTAURATION =====
# Exécuter la restauration des users/grants.
mysql -u "$DB_USER" -p"$DB_PASSWORD" < "$USERS_FILE" 2>error_restore_users.log

# ===== VERIFICATION RESULTAT =====
if [ $? -eq 0 ]; then
    log_info "Restauration des utilisateurs réussie."
else
    log_error "Erreur lors de la restauration des utilisateurs."
    log_error "Détail de l'erreur :"
    cat error_restore_users.log | tee -a "$LOG_FILE"
    exit 1
fi
