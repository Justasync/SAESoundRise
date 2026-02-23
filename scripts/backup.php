<?php

// ===== CHEMIN DU CONFIG =====
$configPath = __DIR__ . "/../config/config.json";

if (!file_exists($configPath)) {
    die("Le fichier config.json est introuvable.");
}

// ===== LECTURE JSON =====
$configContent = file_get_contents($configPath);
$config = json_decode($configContent, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    die("Erreur dans le format du config.json.");
}

if (!isset($config['db'])) {
    die("Section 'db' manquante dans config.json.");
}

// ===== RECUPERATION DES DONNEES =====
$dbHost = $config['db']['host'] ?? 'localhost';
$dbPort = $config['db']['port'] ?? 3306;
$dbUser = $config['db']['username'] ?? null;
$dbPassword = $config['db']['password'] ?? null;
$dbName = $config['db']['dbname'] ?? null;

if (!$dbUser || !$dbName) {
    die("Configuration base de données incomplète.");
}

// ===== DOSSIER BACKUP =====
$backupDir = realpath(__DIR__ . "/../") . "/backups";

if (!file_exists($backupDir)) {
    mkdir($backupDir, 0755, true);
}

$date = date("Y-m-d_H-i");
$backupFile = "{$backupDir}/{$dbName}_{$date}.sql.gz";

// ===== COMMANDE MYSQLDUMP + GZIP =====
$mysqldumpPath = "C:\\xampp\\mysql\\bin\\mysqldump.exe";

$command = "\"$mysqldumpPath\" " .
    "-h " . escapeshellarg($dbHost) . " " .
    "-P " . escapeshellarg($dbPort) . " " .
    "-u " . escapeshellarg($dbUser) . " " .
    "--password=" . escapeshellarg($dbPassword) . " " .
    escapeshellarg($dbName) .
    " > " . escapeshellarg($backupFile);

// ===== EXECUTION AVEC CAPTURE ERREUR =====
$output = [];
$resultCode = 0;

// 2>&1 permet de rediriger les erreurs vers la sortie standard
exec($command . " 2>&1", $output, $resultCode);

if ($resultCode === 0) {    
    echo "Sauvegarde réussie : " . basename($backupFile);
} else {
    echo "Erreur lors de la sauvegarde<br><br>";
    echo "<strong>Code retour :</strong> $resultCode<br><br>";
    echo "<strong>Détail :</strong><br>";
    echo "<pre>" . implode("\n", $output) . "</pre>";
}