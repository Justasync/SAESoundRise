<?php

/**
 * @file populate_interactions.php
 * @brief Script de simulation d'interactions pour la base de données Paaxio
 * 
 * @description Ce script génère des interactions simulées (battles, abonnements,
 * likes, votes) en utilisant les utilisateurs, albums, chansons et playlists
 * déjà présents dans la base de données. Aucun nouvel utilisateur, album,
 * chanson ou playlist n'est créé.
 * 
 * @usage Exécuter via CLI : php populate_interactions.php
 *        Ou via navigateur web.
 */

date_default_timezone_set('Europe/Paris');

require_once __DIR__ . '/../modeles/constantes.class.php';
require_once __DIR__ . '/../enums/Role.enum.php';
require_once __DIR__ . '/../modeles/bd.class.php';

// ==========================================
// RESTRICTION : beta et localhost (requêtes web)
// ==========================================
if (php_sapi_name() !== 'cli') {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $isBeta = ($host === 'beta.paaxio.com');
    $isLocalhost = ($host === 'localhost' || str_starts_with($host, 'localhost:'));
    $isLocalIp = ($host === '127.0.0.1' || str_starts_with($host, '127.0.0.1:'));
    if (!$isBeta && !$isLocalhost && !$isLocalIp) {
        http_response_code(403);
        header('Content-Type: text/plain; charset=utf-8');
        echo "Accès refusé : ce script n'est disponible que sur beta.paaxio.com ou en localhost.";
        exit;
    }
}

// ==========================================
// 1. CONFIGURATION
// ==========================================

$currentDate = date('Y-m-d');

// ==========================================
// 2. CONNEXION ET FONCTIONS UTILITAIRES
// ==========================================

try {
    $pdo = bd::getInstance()->getConnexion(bd::ROLE_DB_ADMINISTRATEUR);
    echo "[INFO] Connexion à la base de données établie.\n";
} catch (Exception $e) {
    die("[ERREUR] Échec de la connexion : " . $e->getMessage());
}

function pick($array)
{
    if (empty($array)) {
        throw new InvalidArgumentException('Impossible de sélectionner un élément dans un tableau vide.');
    }
    return $array[array_rand($array)];
}

function randomTime()
{
    global $currentDate;
    $h = str_pad(rand(0, 23), 2, '0', STR_PAD_LEFT);
    $m = str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT);
    $s = str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT);
    return "$currentDate $h:$m:$s";
}

function getRandomIds($pdo, $table, $col, $limit = 1, $condition = "")
{
    $sql = "SELECT $col FROM $table $condition ORDER BY RAND() LIMIT $limit";
    $stmt = $pdo->query($sql);
    $donnees = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $stmt->closeCursor();
    return $donnees;
}

// ==========================================
// 3. RÉCUPÉRATION DES DONNÉES EXISTANTES
// ==========================================

$allAuditeurs = getRandomIds($pdo, 'utilisateur', 'emailUtilisateur', 1000, "WHERE roleUtilisateur = 3");
$allArtistes = getRandomIds($pdo, 'utilisateur', 'emailUtilisateur', 1000, "WHERE roleUtilisateur = 2");
$allSongs = getRandomIds($pdo, 'chanson', 'idChanson', 2000);

echo "[INFO] Données existantes : " . count($allArtistes) . " artistes, " . count($allAuditeurs) . " auditeurs, " . count($allSongs) . " chansons.\n";

if (count($allArtistes) < 2) {
    die("[ERREUR] Il faut au moins 2 artistes en base pour créer des battles.\n");
}
if (count($allAuditeurs) === 0) {
    die("[ERREUR] Aucun auditeur trouvé en base.\n");
}
if (count($allSongs) === 0) {
    die("[ERREUR] Aucune chanson trouvée en base.\n");
}

// ==========================================
// 4. CRÉATION DE BATTLES
// ==========================================

$numNewBattles = rand(2, 5);
echo "[INFO] Création de $numNewBattles nouvelles Battles...\n";

$battleTitles = ['Clash of Tones', 'Melody War', 'Rhythm Fight', 'Vocal Duel', 'Bass Battle'];

for ($b = 0; $b < $numNewBattles; $b++) {
    $creator = $allArtistes[array_rand($allArtistes)];
    $opponent = $allArtistes[array_rand($allArtistes)];

    if ($creator == $opponent) continue;

    $title = pick($battleTitles);

    $sqlBattle = "INSERT INTO battle (titreBattle, dateDebutBattle, dateFinBattle, statutBattle, emailCreateurBattle, emailParticipantBattle) 
                  VALUES (:titre, :start, :end, 'en_cours', :c, :p)";
    $stmt = $pdo->prepare($sqlBattle);
    $stmt->execute([
        ':titre' => $title,
        ':start' => randomTime(),
        ':end' => date('Y-m-d H:i:s', strtotime($currentDate . ' + 7 days')),
        ':c' => $creator,
        ':p' => $opponent
    ]);
}

$activeBattles = getRandomIds($pdo, 'battle', 'idBattle', 50, "WHERE statutBattle = 'en_cours'");

// ==========================================
// 5. SIMULATION DES INTERACTIONS AUDITEURS
// ==========================================

echo "[INFO] Simulation des interactions pour " . count($allAuditeurs) . " auditeurs...\n";

foreach ($allAuditeurs as $auditeurEmail) {

    // 1. Abonnement à des Artistes (jusqu'à 4)
    $subsCount = rand(0, 4);
    if ($subsCount > 0) {
        $targets = array_rand(array_flip($allArtistes), min($subsCount, count($allArtistes)));
        if (!is_array($targets)) $targets = [$targets];

        foreach ($targets as $artistEmail) {
            $sqlSub = "INSERT IGNORE INTO abonnementArtiste (emailAbonne, emailArtiste, dateAbonnement) VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($sqlSub);
            $stmt->execute([$auditeurEmail, $artistEmail, randomTime()]);
        }
    }

    // 2. Like de Chansons (jusqu'à 8)
    $likeCount = rand(0, 8);
    if ($likeCount > 0) {
        $targets = array_rand(array_flip($allSongs), min($likeCount, count($allSongs)));
        if (!is_array($targets)) $targets = [$targets];

        foreach ($targets as $songId) {
            $sqlLike = "INSERT IGNORE INTO likeChanson (emailUtilisateur, idChanson, dateLike) VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($sqlLike);
            $stmt->execute([$auditeurEmail, $songId, randomTime()]);

            $pdo->exec("UPDATE chanson SET nbEcouteChanson = nbEcouteChanson + 1 WHERE idChanson = $songId");
        }
    }

    // 3. Vote dans les Battles (jusqu'à 2)
    $voteCount = rand(0, 2);
    if ($voteCount > 0 && count($activeBattles) > 0) {
        $targetBattles = array_rand(array_flip($activeBattles), min($voteCount, count($activeBattles)));
        if (!is_array($targetBattles)) $targetBattles = [$targetBattles];

        foreach ($targetBattles as $battleId) {
            $stmtB = $pdo->prepare("SELECT emailCreateurBattle, emailParticipantBattle FROM battle WHERE idBattle = ?");
            $stmtB->execute([$battleId]);
            $battleData = $stmtB->fetch(PDO::FETCH_ASSOC);
            $stmtB->closeCursor();

            if ($battleData && $battleData['emailParticipantBattle']) {
                $votedFor = (rand(0, 1) == 0) ? $battleData['emailCreateurBattle'] : $battleData['emailParticipantBattle'];

                $sqlVote = "INSERT IGNORE INTO vote (emailVotant, idBattle, emailVotee, dateVote) VALUES (?, ?, ?, ?)";
                $stmt = $pdo->prepare($sqlVote);
                $stmt->execute([$auditeurEmail, $battleId, $votedFor, randomTime()]);
            }
        }
    }
}

echo "[SUCCÈS] Simulation des interactions terminée.\n";
