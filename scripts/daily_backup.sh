#!/bin/bash

# ================================================================
# SCRIPT : daily_backup.sh
# OBJET  : Orchestrer la sauvegarde quotidienne complète :
#          1) backup de la base
#          2) export des utilisateurs
#
# ENTREES PRINCIPALES :
# - Aucune obligatoire ici : ce script délègue la configuration aux
#   scripts appelés (backup_db.sh et export_users.sh).
#
# SORTIES :
# - Exécution des 2 scripts de sauvegarde
# - Fichier log : logs/daily_backup_YYYY-MM-DD.log
#
# COMPORTEMENT :
# - Arrête la chaîne dès qu'une étape échoue
# - Retourne code 0 si les 2 étapes réussissent, 1 sinon
# ================================================================

# Nom du script utilisé pour le fichier de log.
SCRIPT_NAME="$(basename "$0" .sh)"
# Dossier absolu du script pour éviter les erreurs de chemin.
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
# Dossier des logs.
LOG_DIR="$SCRIPT_DIR/logs"
# Créer logs/ si absent.
mkdir -p "$LOG_DIR"
# Fichier de log quotidien de l'orchestration.
LOG_FILE="$LOG_DIR/${SCRIPT_NAME}_$(date +"%Y-%m-%d").log"

# Logger INFO.
log_info() {
    echo "[$(date +"%Y-%m-%d %H:%M:%S")] [INFO] $1" | tee -a "$LOG_FILE"
}

# Logger ERROR.
log_error() {
    echo "[$(date +"%Y-%m-%d %H:%M:%S")] [ERROR] $1" | tee -a "$LOG_FILE" >&2
}

# Base_DIR utilisé pour lancer les scripts enfants depuis leur emplacement.
BASE_DIR="$SCRIPT_DIR"

log_info "Début sauvegarde quotidienne (locale + externe)."

# Lancer la sauvegarde DB et écrire la sortie détaillée dans le log global.
bash "$BASE_DIR/backup_db.sh" >> "$LOG_FILE" 2>&1
# Arrêter l'orchestration si backup DB échoue.
if [ $? -ne 0 ]; then
    log_error "Échec backup base de données."
    exit 1
fi

# Lancer ensuite l'export users.
bash "$BASE_DIR/export_users.sh" >> "$LOG_FILE" 2>&1
# Arrêter l'orchestration si export users échoue.
if [ $? -ne 0 ]; then
    log_error "Échec export users."
    exit 1
fi

log_info "Sauvegarde quotidienne terminée avec succès."
