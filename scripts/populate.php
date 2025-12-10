<?php
// set timezone paris
date_default_timezone_set('Europe/Paris');

// Ajout du fichier constantes qui permet de configurer le site
require_once __DIR__ . '/../modeles/constantes.class.php';

// Enums
require_once __DIR__ . '/../enums/Role.enum.php';

// Ajout du modèle qui gère la connexion mysql
require_once __DIR__ . '/../modeles/bd.class.php';

/**
 * Paaxio Database Simulation Script
 * run via CLI: php daily_simulation.php
 * or via Browser.
 */

// ==========================================
// 1. CONFIGURATION
// ==========================================

// The password hash used in your SQL file (Argon2)
$defaultPasswordHash = '$argon2id$v=19$m=65536,t=4,p=1$b3BNMmVpRy5MVjZCS0dlcg$bYAH2BvEB+GmEeCU2scK5eolODq4RopGBiXHmsanSm4';

// Current Date for the simulation
$currentDate = date('Y-m-d');
$scriptTag = "User created automatically by script on " . $currentDate;

// ==========================================
// 2. DATA POOLS (For random generation)
// ==========================================
$fNames = ['Alex', 'Jordan', 'Casey', 'Riley', 'Morgan', 'Taylor', 'Cameron', 'Quinn', 'Peyton', 'Avery', 'Liam', 'Noah', 'Oliver', 'Elijah', 'James', 'William', 'Benjamin', 'Lucas', 'Henry', 'Alexander', 'Mia', 'Emma', 'Ava', 'Charlotte', 'Sophia', 'Amelia', 'Isabella', 'Harper', 'Evelyn', 'Abigail'];
$lNames = ['Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis', 'Rodriguez', 'Martinez', 'Hernandez', 'Lopez', 'Gonzalez', 'Wilson', 'Anderson', 'Thomas', 'Taylor', 'Moore', 'Jackson', 'Martin'];

$albumNouns = ['Echoes', 'Vibrations', 'Shadows', 'Lights', 'Waves', 'Horizons', 'Dreams', 'Nightmares', 'Stories', 'Memories', 'Pulse', 'Rhythm', 'Soul', 'Mind', 'Heart'];
$albumAdjectives = ['Dark', 'Bright', 'Silent', 'Loud', 'Electric', 'Acoustic', 'Hidden', 'Lost', 'Found', 'Eternal', 'Fleeting', 'Urban', 'Wild', 'Deep'];

$songVerbs = ['Running', 'Flying', 'Sleeping', 'Dancing', 'Crying', 'Loving', 'Hating', 'Thinking', 'Dreaming', 'Walking'];
$songNouns = ['Sky', 'River', 'City', 'Street', 'Night', 'Day', 'Sun', 'Moon', 'Star', 'Fire', 'Rain', 'Wind'];

// ==========================================
// 3. DATABASE CONNECTION & HELPERS
// ==========================================

try {
    $pdo = bd::getInstance()->getConnexion();
    echo "[INFO] Connected to database.\n";
} catch (Exception $e) {
    die("[ERROR] Connection failed: " . $e->getMessage());
}

// Helper: Get Random Item
function pick($array)
{
    if (empty($array)) {
        throw new InvalidArgumentException('Cannot pick a random item from an empty array.');
    }
    return $array[array_rand($array)];
}

// Helper: Generate Email
function generateEmail($fname, $lname)
{
    $base = strtolower($fname . '.' . $lname);
    $rand = rand(1, 999);
    return "{$base}{$rand}@example.com";
}

// Helper: Get Random Time today
function randomTime()
{
    global $currentDate;
    $h = str_pad(rand(0, 23), 2, '0', STR_PAD_LEFT);
    $m = str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT);
    $s = str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT);
    return "$currentDate $h:$m:$s";
}

// Helper: Get Random ID from table
function getRandomIds($pdo, $table, $col, $limit = 1, $condition = "")
{
    $sql = "SELECT $col FROM $table $condition ORDER BY RAND() LIMIT $limit";
    $stmt = $pdo->query($sql);

    // CORRECCIÓN: Primero obtenemos los datos
    $datos = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Luego cerramos el cursor para liberar la conexión (Error 2014 prevención)
    $stmt->closeCursor();

    return $datos;
}

// ==========================================
// 4. MAIN LOGIC
// ==========================================

// --- A. Fetch Constraints ---
// Get existing Genre IDs
$genreIds = getRandomIds($pdo, 'genre', 'idGenre', 100);

// --- B. Create New Users (Artists & Auditeurs) ---

$numNewArtists = rand(4, 10);
$numNewAuditeurs = rand(10, 20);

$newArtistEmails = [];
$newAuditeurEmails = [];

echo "[INFO] Creating $numNewArtists new Artists...\n";
for ($i = 0; $i < $numNewArtists; $i++) {
    $fn = pick($fNames);
    $ln = pick($lNames);
    $pseudo = $fn . $ln . rand(100, 9999);
    $email = generateEmail($fn, $ln);

    // Image 1-64
    $imgNum = rand(1, 64);
    $photoUrl = "/assets/images/profile_pictures/user_exp{$imgNum}.jpeg";

    // Role 2 = Artiste
    $sql = "INSERT INTO utilisateur (emailUtilisateur, nomUtilisateur, pseudoUtilisateur, motDePasseUtilisateur, dateDeNaissanceUtilisateur, dateInscriptionUtilisateur, statutUtilisateur, estAbonnee, descriptionUtilisateur, urlPhotoUtilisateur, roleUtilisateur, genreUtilisateur, statutAbonnement, dateDebutAbonnement, dateFinAbonnement) 
            VALUES (:email, :nom, :pseudo, :pass, :dob, :dateInscr, 'actif', 1, :desc, :photo, 2, :genre, 'actif', :dateDeb, :dateFin)";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':email' => $email,
            ':nom' => "$fn $ln",
            ':pseudo' => $pseudo,
            ':pass' => $defaultPasswordHash,
            ':dob' => rand(1980, 2005) . '-' . rand(1, 12) . '-' . rand(1, 28),
            ':dateInscr' => randomTime(),
            ':desc' => "Artiste. $scriptTag",
            ':photo' => $photoUrl,
            ':genre' => pick($genreIds),
            ':dateDeb' => $currentDate,
            ':dateFin' => date('Y-m-d', strtotime('+1 year'))
        ]);
        $newArtistEmails[] = $email;
    } catch (Exception $e) {
        // Pseudo or email might exist, skip
        continue;
    }
}

echo "[INFO] Creating $numNewAuditeurs new Auditeurs...\n";
for ($i = 0; $i < $numNewAuditeurs; $i++) {
    $fn = pick($fNames);
    $ln = pick($lNames);
    $pseudo = $fn . "Fan" . rand(100, 9999);
    $email = generateEmail($fn, $ln);

    $imgNum = rand(1, 64);
    $photoUrl = "/assets/images/profile_pictures/user_exp{$imgNum}.jpeg";

    // Role 3 = Auditeur
    $sql = "INSERT INTO utilisateur (emailUtilisateur, nomUtilisateur, pseudoUtilisateur, motDePasseUtilisateur, dateDeNaissanceUtilisateur, dateInscriptionUtilisateur, statutUtilisateur, estAbonnee, descriptionUtilisateur, urlPhotoUtilisateur, roleUtilisateur, genreUtilisateur) 
            VALUES (:email, :nom, :pseudo, :pass, :dob, :dateInscr, 'actif', 0, :desc, :photo, 3, NULL)";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':email' => $email,
            ':nom' => "$fn $ln",
            ':pseudo' => $pseudo,
            ':pass' => $defaultPasswordHash,
            ':dob' => rand(1990, 2010) . '-' . rand(1, 12) . '-' . rand(1, 28),
            ':dateInscr' => randomTime(),
            ':desc' => "$scriptTag",
            ':photo' => $photoUrl
        ]);
        $newAuditeurEmails[] = $email;
    } catch (Exception $e) {
        continue;
    }
}

// --- C. Create Content (Albums & Songs) for New Artists ---

echo "[INFO] Generating Albums and Songs for new artists...\n";
foreach ($newArtistEmails as $artistEmail) {
    $numAlbums = rand(0, 2);

    for ($a = 0; $a < $numAlbums; $a++) {
        $albumName = pick($albumAdjectives) . " " . pick($albumNouns);
        $imgNum = rand(1, 40);
        $coverUrl = "/assets/images/album_{$imgNum}.jpg";

        $sqlAlb = "INSERT INTO album (nomAlbum, dateSortieAlbum, urlPochetteAlbum, artisteAlbum) VALUES (:nom, :date, :url, :artist)";
        $stmt = $pdo->prepare($sqlAlb);
        $stmt->execute([
            ':nom' => $albumName,
            ':date' => $currentDate, // Released today
            ':url' => $coverUrl,
            ':artist' => $artistEmail
        ]);
        $albumId = $pdo->lastInsertId();

        // Add Songs to this Album
        $numSongs = rand(5, 12);
        for ($s = 1; $s <= $numSongs; $s++) {
            $title = pick($songVerbs) . " " . pick($songNouns); // e.g. "Running Sky"
            $duration = rand(120, 300); // 2 to 5 mins
            $mp3Num = rand(1, 25);
            $audioUrl = "/assets/audio/song_{$mp3Num}.mp3";
            $genre = pick($genreIds);

            $sqlSong = "INSERT INTO chanson (titreChanson, dureeChanson, dateTeleversementChanson, nbEcouteChanson, albumChanson, genreChanson, urlAudioChanson, emailPublicateur) 
                        VALUES (:titre, :duree, :date, 0, :album, :genre, :url, :email)";
            $stmtS = $pdo->prepare($sqlSong);
            $stmtS->execute([
                ':titre' => $title,
                ':duree' => $duration,
                ':date' => $currentDate,
                ':album' => $albumId,
                ':genre' => $genre,
                ':url' => $audioUrl,
                ':email' => $artistEmail
            ]);

            // Add participation (Leader)
            $lastSongId = $pdo->lastInsertId();
            $sqlPart = "INSERT INTO participation (idChanson, emailArtisteParticipant, typeParticipation, ordreParticipation) VALUES (?, ?, 'leader', 1)";
            $stmtPart = $pdo->prepare($sqlPart);
            $stmtPart->execute([$lastSongId, $artistEmail]);
        }
    }
}

// --- D. Refresh Pools (Mix Old and New Data) ---
// Now that we have added new people and content, we get lists of EVERYONE to create interactions.

$allAuditeurs = getRandomIds($pdo, 'utilisateur', 'emailUtilisateur', 1000, "WHERE roleUtilisateur = 3");
$allArtistes = getRandomIds($pdo, 'utilisateur', 'emailUtilisateur', 1000, "WHERE roleUtilisateur = 2");
$allSongs = getRandomIds($pdo, 'chanson', 'idChanson', 2000);

// --- E. Create Battles ---
// Create some battles between random artists (Old or New)
$numNewBattles = rand(2, 5);
echo "[INFO] Creating $numNewBattles new Battles...\n";

for ($b = 0; $b < $numNewBattles; $b++) {
    if (count($allArtistes) < 2) break;
    $creator = $allArtistes[array_rand($allArtistes)];
    $opponent = $allArtistes[array_rand($allArtistes)];

    if ($creator == $opponent) continue;

    $titles = ['Clash of Tones', 'Melody War', 'Rhythm Fight', 'Vocal Duel', 'Bass Battle'];
    $title = pick($titles);

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

// Get Active Battles IDs for voting
$activeBattles = getRandomIds($pdo, 'battle', 'idBattle', 50, "WHERE statutBattle = 'en_cours'");

// --- F. Simulate User Actions (Auditeurs) ---
// Loop through ALL auditeurs (old and newly created) to perform actions
echo "[INFO] Simulating interactions for Auditeurs...\n";

foreach ($allAuditeurs as $auditeurEmail) {

    // 1. Abonnement Artiste (Up to 4)
    $subsCount = rand(0, 4);
    if ($subsCount > 0 && count($allArtistes) > 0) {
        $targets = array_rand(array_flip($allArtistes), min($subsCount, count($allArtistes)));
        if (!is_array($targets)) $targets = [$targets];

        foreach ($targets as $artistEmail) {
            $sqlSub = "INSERT IGNORE INTO abonnementArtiste (emailAbonne, emailArtiste, dateAbonnement) VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($sqlSub);
            $stmt->execute([$auditeurEmail, $artistEmail, randomTime()]);
        }
    }

    // 2. Like Chanson (Up to 8)
    $likeCount = rand(0, 8);
    if ($likeCount > 0 && count($allSongs) > 0) {
        $targets = array_rand(array_flip($allSongs), min($likeCount, count($allSongs)));
        if (!is_array($targets)) $targets = [$targets];

        foreach ($targets as $songId) {
            $sqlLike = "INSERT IGNORE INTO likeChanson (emailUtilisateur, idChanson, dateLike) VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($sqlLike);
            $stmt->execute([$auditeurEmail, $songId, randomTime()]);

            // Increment listen count slightly just for simulation realism
            $pdo->exec("UPDATE chanson SET nbEcouteChanson = nbEcouteChanson + 1 WHERE idChanson = $songId");
        }
    }

    // 3. Create Playlists (Up to 2)
    $plCount = rand(0, 2);
    for ($p = 0; $p < $plCount; $p++) {
        $plName = "My " . pick($albumAdjectives) . " Mix " . rand(1, 99);
        $isPublic = rand(0, 1);

        $sqlPl = "INSERT INTO playlist (nomPlaylist, estPubliquePlaylist, dateCreationPlaylist, emailProprietaire) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sqlPl);
        $stmt->execute([$plName, $isPublic, randomTime(), $auditeurEmail]);
        $plId = $pdo->lastInsertId();

        // Add 1-5 songs to playlist
        $songsInPl = rand(1, 5);
        if (count($allSongs) > 0) {
            $plSongs = array_rand(array_flip($allSongs), min($songsInPl, count($allSongs)));
            if (!is_array($plSongs)) $plSongs = [$plSongs];

            $pos = 1;
            foreach ($plSongs as $sId) {
                $sqlAdd = "INSERT IGNORE INTO chansonPlaylist (idPlaylist, idChanson, positionChanson, dateAjoutChanson) VALUES (?, ?, ?, ?)";
                $stmt = $pdo->prepare($sqlAdd);
                $stmt->execute([$plId, $sId, $pos++, randomTime()]);
            }
        }
    }

    // 4. Vote in Battles (Up to 2)
    $voteCount = rand(0, 2);
    if ($voteCount > 0 && count($activeBattles) > 0) {
        $targetBattles = array_rand(array_flip($activeBattles), min($voteCount, count($activeBattles)));
        if (!is_array($targetBattles)) $targetBattles = [$targetBattles];

        foreach ($targetBattles as $battleId) {
            // Check participants
            $stmtB = $pdo->prepare("SELECT emailCreateurBattle, emailParticipantBattle FROM battle WHERE idBattle = ?");
            $stmtB->execute([$battleId]);
            $battleData = $stmtB->fetch(PDO::FETCH_ASSOC);
            $stmtB->closeCursor(); // <--- FIX CRITIQUE : Libère la connexion pour la requête suivante

            if ($battleData && $battleData['emailParticipantBattle']) {
                // Pick random winner
                $votedFor = (rand(0, 1) == 0) ? $battleData['emailCreateurBattle'] : $battleData['emailParticipantBattle'];

                $sqlVote = "INSERT IGNORE INTO vote (emailVotant, idBattle, emailVotee, dateVote) VALUES (?, ?, ?, ?)";
                $stmt = $pdo->prepare($sqlVote);
                $stmt->execute([$auditeurEmail, $battleId, $votedFor, randomTime()]);
            }
        }
    }
}

echo "[SUCCESS] Daily simulation complete.\n";
