<?php

/**
 * @file backup.php
 * @brief Script de sauvegarde automatique de la base de données avec nettoyage des anciens backups
 *
 * @description
 * Ce script réalise les opérations suivantes :
 * - Génère une sauvegarde complète de la base de données au format SQL compressé (.sql.gz)
 * - Sauvegarde la structure des tables ainsi que les données
 * - Crée automatiquement le dossier de backup si nécessaire
 * - Supprime les backups datant de plus de 7 jours
 *
 * @architecture
 * - Lecture de la configuration depuis config.json
 * - Connexion sécurisée à la base via PDO
 * - Export manuel des tables MySQL
 * - Compression GZIP du fichier de sauvegarde
 * - Rotation automatique des backups
 *
 * @usage
 * - Exécution CLI ou navigateur :
 *   php backup.php
 *
 * @warning
 * - La base doit supporter les requêtes SHOW TABLES et SHOW CREATE TABLE
 * - Le script peut être coûteux en mémoire pour les très grosses bases
 */

// ==========================================
// 1. CONFIGURATION ET CONNEXION BDD
// ==========================================

$configPath = __DIR__ . "/../config/config.json";
$config = json_decode(file_get_contents($configPath), true);

/**
 * @var array $db
 * @brief Configuration de connexion à la base de données (db_administrateur pour backup)
 */
$db = $config['db'];
$dbAdmin = $db['db_administrateur'];

/**
 * Connexion à la base de données avec PDO (compte db_administrateur, ALL PRIVILEGES)
 */
try {
    $pdo = new PDO(
        "mysql:host={$db['host']};port={$db['port']};dbname={$db['dbname']};charset=utf8mb4",
        $dbAdmin['username'],
        $dbAdmin['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("Erreur connexion BDD : " . $e->getMessage());
}

// ==========================================
// 2. CONFIGURATION DU DOSSIER BACKUP
// ==========================================

/**
 * @var string $backupDir
 * @brief Chemin du dossier contenant les backups
 */
$backupDir = realpath(__DIR__ . "/../") . "/backups";

/**
 * Création automatique du dossier backup si inexistant
 */
if (!file_exists($backupDir)) {
    mkdir($backupDir, 0755, true);
}

// Nom du fichier backup avec timestamp
$date = date("Y-m-d_H-i");
$backupFile = "{$backupDir}/{$db['dbname']}_{$date}.sql.gz";

/**
 * Ouverture du fichier gzip en mode écriture
 * w9 = compression maximale
 */
$gz = gzopen($backupFile, 'w9');

// ==========================================
// 3. EXPORT COMPLET DE LA BASE
// ==========================================

/**
 * Récupération de la liste des tables
 */
$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

foreach ($tables as $table) {

    // -----------------------------
    // Structure de la table
    // -----------------------------

    $create = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_ASSOC);
    gzwrite($gz, "\n\n" . $create['Create Table'] . ";\n\n");

    // -----------------------------
    // Données de la table
    // -----------------------------

    $rows = $pdo->query("SELECT * FROM `$table`");

    while ($row = $rows->fetch(PDO::FETCH_ASSOC)) {

        $columns = array_map(fn($col) => "`$col`", array_keys($row));

        $values = array_map(
            fn($val) => isset($val) ? $pdo->quote($val) : "NULL",
            array_values($row)
        );

        $sql = "INSERT INTO `$table` (" .
            implode(",", $columns) .
            ") VALUES (" .
            implode(",", $values) .
            ");\n";

        gzwrite($gz, $sql);
    }
}

gzclose($gz);

// ==========================================
// 4. ROTATION AUTOMATIQUE DES BACKUPS (> 7 jours)
// ==========================================

/**
 * Suppression des backups anciens
 */
$files = glob($backupDir . "/*.sql.gz");

$now = time();
$limit = 7 * 24 * 60 * 60; // 7 jours en secondes

foreach ($files as $file) {

    if (!preg_match('/(\d{4}-\d{2}-\d{2}_\d{2}-\d{2})/', basename($file), $matches)) {
        continue;
    }

    $fileDate = DateTime::createFromFormat("Y-m-d_H-i", $matches[1]);

    if ($fileDate && ($now - $fileDate->getTimestamp()) > $limit) {
        unlink($file);
    }
}

// ==========================================
// 5. OUTPUT FINAL
// ==========================================

echo "Sauvegarde universelle réussie : " . basename($backupFile);