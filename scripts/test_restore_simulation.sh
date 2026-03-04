#!/bin/bash

# ================================================================
# SCRIPT : test_restore_simulation.sh
# OBJET  : Valider de bout en bout les scripts de backup/restauration via
#          un scénario simulé comprenant :
#          - une perte de données (DROP TABLE)
#          - une erreur de manipulation (UPDATE massif)
#          - une restauration des utilisateurs MySQL
#
# ENTREES PRINCIPALES :
# - Utilise une base de test dédiée : sae_restore_sim
# - Utilise un utilisateur de test : restore_test_user@localhost
# - Désactive explicitement les flux distants pendant le test
#
# SORTIES :
# - Rapport détaillé : scripts/logs/simulation_summary.txt
# - Code retour 0 si tous les contrôles passent, 1 sinon
#
# COMPORTEMENT :
# - Prépare un environnement isolé de test
# - Exécute backup_db.sh, recovery_db.sh, export_users.sh, recovery_users.sh
# - Vérifie les résultats intermédiaires avec requêtes SQL de contrôle
# ================================================================

# Arrêter immédiatement le script au premier échec de commande.
set -e

# Dossier absolu où se trouve ce script.
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
# Racine du projet (utilisable si besoin de chemins relatifs projet).
ROOT_DIR="$(cd "$SCRIPT_DIR/.." && pwd)"
# Dossier de logs de simulation.
LOG_DIR="$SCRIPT_DIR/logs"
# Créer logs/ si absent.
mkdir -p "$LOG_DIR"
# Rapport texte synthétique de la simulation.
SUMMARY_FILE="$LOG_DIR/simulation_summary.txt"

# Chemins des binaires MariaDB utilisés pendant le test.
MYSQL_BIN="${MYSQL:-mysql}"
MYSQLDUMP_BIN="${MYSQLDUMP:-mysqldump}"

# Variables du scénario de test.
DB_USER="root"
DB_PASSWORD=""
DB_NAME="sae_restore_sim"
BACKUP_DIR="scripts/backups/sim"
TEST_USER="restore_test_user"
TEST_USER_HOST="localhost"
TEST_USER_PASSWORD="restore_pwd_123"

# Option mot de passe mysql construite dynamiquement.
MYSQL_PWD_OPT=""
if [ -n "$DB_PASSWORD" ]; then
  MYSQL_PWD_OPT="-p$DB_PASSWORD"
fi

# Détecter mysql/mysqldump automatiquement si non disponibles dans le PATH.
if [ "$MYSQL_BIN" = "mysql" ] && ! command -v mysql &> /dev/null; then
  for candidate in \
    /c/wamp64/bin/mysql/*/bin/mysql.exe \
    /c/wamp64/bin/mariadb/*/bin/mysql.exe \
    /mingw64/bin/mysql.exe \
    /usr/bin/mysql
  do
    if [ -x "$candidate" ]; then
      MYSQL_BIN="$candidate"
      break
    fi
  done
fi

if [ "$MYSQLDUMP_BIN" = "mysqldump" ] && ! command -v mysqldump &> /dev/null; then
  for candidate in \
    /c/wamp64/bin/mysql/*/bin/mysqldump.exe \
    /c/wamp64/bin/mariadb/*/bin/mysqldump.exe \
    /mingw64/bin/mysqldump.exe \
    /usr/bin/mysqldump
  do
    if [ -x "$candidate" ]; then
      MYSQLDUMP_BIN="$candidate"
      break
    fi
  done
fi

if [ ! -x "$MYSQL_BIN" ] && ! command -v "$MYSQL_BIN" &> /dev/null; then
  echo "[ERREUR] mysql introuvable via MYSQL=$MYSQL_BIN" | tee -a "$SUMMARY_FILE"
  exit 1
fi

if [ ! -x "$MYSQLDUMP_BIN" ] && ! command -v "$MYSQLDUMP_BIN" &> /dev/null; then
  echo "[ERREUR] mysqldump introuvable via MYSQLDUMP=$MYSQLDUMP_BIN" | tee -a "$SUMMARY_FILE"
  exit 1
fi

# Ajouter le dossier des binaires détectés au PATH pour les scripts appelés.
MYSQL_BIN_DIR="$(dirname "$MYSQL_BIN")"
export PATH="$MYSQL_BIN_DIR:$PATH"
# Exporter variables attendues par les scripts backup/recovery.
export DB_USER
export DB_PASSWORD
export DB_NAME
export BACKUP_DIR
export MYSQL="$MYSQL_BIN"
export MYSQLDUMP="$MYSQLDUMP_BIN"
# Désactiver volontairement le distant pour un test local contrôlé.
export ENABLE_REMOTE_SYNC="false"
export ENABLE_REMOTE_RESTORE="false"

# Helper pour écrire à la fois dans stdout et dans le rapport.
log() {
  echo "$1" | tee -a "$SUMMARY_FILE"
}

# Vider le rapport avant de démarrer un nouveau scénario.
: > "$SUMMARY_FILE"
log "=== TEST RESTAURATION - $(date '+%Y-%m-%d %H:%M:%S') ==="

# Initialiser un jeu de données connu pour comparer avant/après restore.
log "[1/10] Initialisation base de test: $DB_NAME"
"$MYSQL_BIN" -u"$DB_USER" $MYSQL_PWD_OPT -e "DROP DATABASE IF EXISTS $DB_NAME; CREATE DATABASE $DB_NAME; USE $DB_NAME; CREATE TABLE demo_data(id INT PRIMARY KEY AUTO_INCREMENT, label VARCHAR(100)); INSERT INTO demo_data(label) VALUES ('alpha'),('beta'),('gamma');"

# Exécuter le backup DB sur la base de test.
log "[2/10] Backup base locale"
bash "$SCRIPT_DIR/backup_db.sh" >> "$SUMMARY_FILE" 2>&1

# Exécuter l'export des users pour tester ensuite la restauration users.
log "[3/10] Export users local"
bash "$SCRIPT_DIR/export_users.sh" >> "$SUMMARY_FILE" 2>&1

# Simuler une perte de données destructrice.
log "[4/10] Simulation perte de données (DROP TABLE demo_data)"
"$MYSQL_BIN" -u"$DB_USER" $MYSQL_PWD_OPT -e "USE $DB_NAME; DROP TABLE demo_data;"

# Restaurer et contrôler le nombre de lignes restaurées.
log "[5/10] Restauration BDD après perte"
bash "$SCRIPT_DIR/recovery_db.sh" >> "$SUMMARY_FILE" 2>&1
ROWS_AFTER_LOSS=$("$MYSQL_BIN" -u"$DB_USER" $MYSQL_PWD_OPT -N -e "SELECT COUNT(*) FROM $DB_NAME.demo_data;")
log "Résultat perte de données -> lignes restaurées: $ROWS_AFTER_LOSS"

# Simuler une erreur humaine de manipulation (UPDATE massif).
log "[6/10] Simulation erreur de manipulation (UPDATE massif)"
"$MYSQL_BIN" -u"$DB_USER" $MYSQL_PWD_OPT -e "USE $DB_NAME; UPDATE demo_data SET label='ERREUR_MANIP';"
LABELS_AFTER_ERROR=$("$MYSQL_BIN" -u"$DB_USER" $MYSQL_PWD_OPT -N -e "SELECT GROUP_CONCAT(DISTINCT label ORDER BY label SEPARATOR ',') FROM $DB_NAME.demo_data;")
log "Après erreur de manipulation -> labels: $LABELS_AFTER_ERROR"

# Restaurer à nouveau et vérifier le retour à l'état initial attendu.
log "[7/10] Restauration BDD après erreur de manipulation"
bash "$SCRIPT_DIR/recovery_db.sh" >> "$SUMMARY_FILE" 2>&1
LABELS_AFTER_RESTORE=$("$MYSQL_BIN" -u"$DB_USER" $MYSQL_PWD_OPT -N -e "SELECT GROUP_CONCAT(label ORDER BY id SEPARATOR ',') FROM $DB_NAME.demo_data;")
log "Après restauration -> labels: $LABELS_AFTER_RESTORE"

# Préparer un compte utilisateur de test pour le scénario users.
log "[8/10] Préparation test users (création utilisateur de test)"
"$MYSQL_BIN" -u"$DB_USER" $MYSQL_PWD_OPT -e "DROP USER IF EXISTS '$TEST_USER'@'$TEST_USER_HOST'; CREATE USER '$TEST_USER'@'$TEST_USER_HOST' IDENTIFIED BY '$TEST_USER_PASSWORD'; GRANT SELECT ON $DB_NAME.* TO '$TEST_USER'@'$TEST_USER_HOST'; FLUSH PRIVILEGES;"

# Exporter users, supprimer le user test, puis vérifier qu'il n'existe plus.
log "[9/10] Export users puis suppression utilisateur test"
bash "$SCRIPT_DIR/export_users.sh" >> "$SUMMARY_FILE" 2>&1
"$MYSQL_BIN" -u"$DB_USER" $MYSQL_PWD_OPT -e "DROP USER IF EXISTS '$TEST_USER'@'$TEST_USER_HOST'; FLUSH PRIVILEGES;"
USER_BEFORE=$("$MYSQL_BIN" -u"$DB_USER" $MYSQL_PWD_OPT -N -e "SELECT COUNT(*) FROM mysql.user WHERE user='$TEST_USER' AND host='$TEST_USER_HOST';")
log "Présence user avant restore users: $USER_BEFORE"

# Restaurer les users et vérifier que le compte de test réapparaît.
log "[10/10] Restauration users"
bash "$SCRIPT_DIR/recovery_users.sh" >> "$SUMMARY_FILE" 2>&1
USER_AFTER=$("$MYSQL_BIN" -u"$DB_USER" $MYSQL_PWD_OPT -N -e "SELECT COUNT(*) FROM mysql.user WHERE user='$TEST_USER' AND host='$TEST_USER_HOST';")
log "Présence user après restore users: $USER_AFTER"

log "=== FIN TEST RESTAURATION ==="

# Validation globale du scénario simulé.
if [ "$ROWS_AFTER_LOSS" = "3" ] && [ "$LABELS_AFTER_RESTORE" = "alpha,beta,gamma" ] && [ "$USER_AFTER" = "1" ]; then
  log "STATUT GLOBAL: SUCCÈS"
  exit 0
fi

log "STATUT GLOBAL: ÉCHEC"
exit 1
