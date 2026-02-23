<?php

$configPath = __DIR__ . "/../config/config.json";
$config = json_decode(file_get_contents($configPath), true);

$db = $config['db'];

try {
    $pdo = new PDO(
        "mysql:host={$db['host']};port={$db['port']};dbname={$db['dbname']};charset=utf8mb4",
        $db['username'],
        $db['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("Erreur connexion BDD : " . $e->getMessage());
}

$backupDir = realpath(__DIR__ . "/../") . "/backups";
if (!file_exists($backupDir)) {
    mkdir($backupDir, 0755, true);
}

$date = date("Y-m-d_H-i");
$backupFile = "{$backupDir}/{$db['dbname']}_{$date}.sql.gz";

$gz = gzopen($backupFile, 'w9');

// Récupération des tables
$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

foreach ($tables as $table) {

    // Structure
    $create = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_ASSOC);
    gzwrite($gz, "\n\n" . $create['Create Table'] . ";\n\n");

    // Données
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

// ===== SUPPRESSION AUTOMATIQUE DES BACKUPS > 7 JOURS =====

$files = glob($backupDir . "/*.sql.gz");

$now = time();
$limit = 7 * 24 * 60 * 60; // 7 jours en secondes

foreach ($files as $file) {

    if (!preg_match('/(\d{4}-\d{2}-\d{2}_\d{2}-\d{2})/', basename($file), $matches)) {
        continue;
    }

    $fileDate = DateTime::createFromFormat("Y-m-d_H-i", $matches[1]);

    if (!$fileDate) continue;

    if (($now - $fileDate->getTimestamp()) > $limit) {
        unlink($file);
    }
}

echo "Sauvegarde universelle réussie : " . basename($backupFile);