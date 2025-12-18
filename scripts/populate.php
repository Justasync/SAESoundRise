<?php

/**
 * @file populate.php
 * @brief Script de simulation de données pour la base de données Paaxio
 * 
 * @description Ce script génère automatiquement des données simulées pour peupler
 * la base de données. Il crée des utilisateurs (artistes et auditeurs), des albums,
 * des chansons, des playlists, des battles et simule des interactions utilisateurs.
 * 
 * @usage Exécuter via CLI : php populate.php
 *        Ou via navigateur web.
 */

// Configuration du fuseau horaire Paris
date_default_timezone_set('Europe/Paris');

// Ajout du fichier constantes qui permet de configurer le site
require_once __DIR__ . '/../modeles/constantes.class.php';

// Énumérations
require_once __DIR__ . '/../enums/Role.enum.php';

// Ajout du modèle qui gère la connexion MySQL
require_once __DIR__ . '/../modeles/bd.class.php';

// ==========================================
// 1. CONFIGURATION
// ==========================================

/**
 * @var string $defaultPasswordHash
 * @brief Hash du mot de passe par défaut utilisé pour tous les utilisateurs créés
 * 
 * Hash Argon2 du mot de passe utilisé dans le fichier SQL de peuplement.
 */
$defaultPasswordHash = '$argon2id$v=19$m=65536,t=4,p=1$b3BNMmVpRy5MVjZCS0dlcg$bYAH2BvEB+GmEeCU2scK5eolODq4RopGBiXHmsanSm4';

/**
 * @var string $currentDate
 * @brief Date actuelle pour la simulation
 */
$currentDate = date('Y-m-d');

/**
 * @var string $scriptTag
 * @brief Tag identifiant les utilisateurs créés automatiquement
 */
$scriptTag = "Utilisateur créé automatiquement par le script le " . $currentDate;

// ==========================================
// 2. DONNÉES POUR LA GÉNÉRATION ALÉATOIRE
// ==========================================

/**
 * @var array $fNames
 * @brief Liste des prénoms disponibles pour la génération d'utilisateurs
 */
$fNames = ['Alex', 'Jordan', 'Casey', 'Riley', 'Morgan', 'Taylor', 'Cameron', 'Quinn', 'Peyton', 'Avery', 'Liam', 'Noah', 'Oliver', 'Elijah', 'James', 'William', 'Benjamin', 'Lucas', 'Henry', 'Alexander', 'Mia', 'Emma', 'Ava', 'Charlotte', 'Sophia', 'Amelia', 'Isabella', 'Harper', 'Evelyn', 'Abigail'];

/**
 * @var array $lNames
 * @brief Liste des noms de famille disponibles pour la génération d'utilisateurs
 */
$lNames = ['Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis', 'Rodriguez', 'Martinez', 'Hernandez', 'Lopez', 'Gonzalez', 'Wilson', 'Anderson', 'Thomas', 'Taylor', 'Moore', 'Jackson', 'Martin'];

/**
 * @var array $albumNouns
 * @brief Liste des noms pour la génération de titres d'albums
 */
$albumNouns = ['Echoes', 'Vibrations', 'Shadows', 'Lights', 'Waves', 'Horizons', 'Dreams', 'Nightmares', 'Stories', 'Memories', 'Pulse', 'Rhythm', 'Soul', 'Mind', 'Heart'];

/**
 * @var array $albumAdjectives
 * @brief Liste des adjectifs pour la génération de titres d'albums
 */
$albumAdjectives = ['Dark', 'Bright', 'Silent', 'Loud', 'Electric', 'Acoustic', 'Hidden', 'Lost', 'Found', 'Eternal', 'Fleeting', 'Urban', 'Wild', 'Deep'];

/**
 * @var array $songVerbs
 * @brief Liste des verbes pour la génération de titres de chansons
 */
$songVerbs = ['Running', 'Flying', 'Sleeping', 'Dancing', 'Crying', 'Loving', 'Hating', 'Thinking', 'Dreaming', 'Walking'];

/**
 * @var array $songNouns
 * @brief Liste des noms pour la génération de titres de chansons
 */
$songNouns = ['Sky', 'River', 'City', 'Street', 'Night', 'Day', 'Sun', 'Moon', 'Star', 'Fire', 'Rain', 'Wind'];

// ==========================================
// 3. CONNEXION À LA BASE DE DONNÉES ET FONCTIONS UTILITAIRES
// ==========================================

try {
    $pdo = bd::getInstance()->getConnexion();
    echo "[INFO] Connexion à la base de données établie.\n";
} catch (Exception $e) {
    die("[ERREUR] Échec de la connexion : " . $e->getMessage());
}

/**
 * @brief Sélectionne un élément aléatoire dans un tableau
 * 
 * @param array $array Tableau source
 * @return mixed Élément sélectionné aléatoirement
 * @throws InvalidArgumentException Si le tableau est vide
 */
function pick($array)
{
    if (empty($array)) {
        throw new InvalidArgumentException('Impossible de sélectionner un élément dans un tableau vide.');
    }
    return $array[array_rand($array)];
}

/**
 * @brief Génère une adresse email à partir d'un prénom et nom
 * 
 * @param string $fname Prénom de l'utilisateur
 * @param string $lname Nom de famille de l'utilisateur
 * @return string Adresse email générée au format prenom.nom123@example.com
 */
function generateEmail($fname, $lname)
{
    $base = strtolower($fname . '.' . $lname);
    $rand = rand(1, 999);
    return "{$base}{$rand}@example.com";
}

/**
 * @brief Génère une date/heure aléatoire pour la journée courante
 * 
 * @return string Date/heure au format AAAA-MM-JJ HH:MM:SS
 */
function randomTime()
{
    global $currentDate;
    $h = str_pad(rand(0, 23), 2, '0', STR_PAD_LEFT);
    $m = str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT);
    $s = str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT);
    return "$currentDate $h:$m:$s";
}

/**
 * @brief Récupère des identifiants aléatoires depuis une table
 * 
 * @param PDO $pdo Instance de connexion PDO
 * @param string $table Nom de la table
 * @param string $col Nom de la colonne à récupérer
 * @param int $limit Nombre maximum d'éléments à récupérer
 * @param string $condition Condition WHERE optionnelle
 * @return array Tableau des identifiants récupérés
 */
function getRandomIds($pdo, $table, $col, $limit = 1, $condition = "")
{
    $sql = "SELECT $col FROM $table $condition ORDER BY RAND() LIMIT $limit";
    $stmt = $pdo->query($sql);

    // Récupération des données
    $dados = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Fermeture du curseur pour libérer la connexion (prévention de l'erreur 2014)
    $stmt->closeCursor();

    return $dados;
}

// ==========================================
// 4. LOGIQUE PRINCIPALE
// ==========================================

// --- A. Récupération des contraintes ---
// Récupération des identifiants de genres existants
$genreIds = getRandomIds($pdo, 'genre', 'idGenre', 100);

// --- B. Création de nouveaux utilisateurs (Artistes et Auditeurs) ---

$numNewArtists = rand(4, 10);
$numNewAuditeurs = rand(10, 20);

/** @var array $newArtistEmails Liste des emails des nouveaux artistes créés */
$newArtistEmails = [];

/** @var array $newAuditeurEmails Liste des emails des nouveaux auditeurs créés */
$newAuditeurEmails = [];

echo "[INFO] Création de $numNewArtists nouveaux Artistes...\n";
for ($i = 0; $i < $numNewArtists; $i++) {
    $fn = pick($fNames);
    $ln = pick($lNames);
    $pseudo = $fn . $ln . rand(100, 9999);
    $email = generateEmail($fn, $ln);

    // Image de profil aléatoire (1-64)
    $imgNum = rand(1, 64);
    $photoUrl = "/assets/images/profile_pictures/user_exp{$imgNum}.jpeg";

    // Rôle 2 = Artiste
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
        // Le pseudo ou l'email existe peut-être déjà, on passe au suivant
        continue;
    }
}

echo "[INFO] Création de $numNewAuditeurs nouveaux Auditeurs...\n";
for ($i = 0; $i < $numNewAuditeurs; $i++) {
    $fn = pick($fNames);
    $ln = pick($lNames);
    $pseudo = $fn . "Fan" . rand(100, 9999);
    $email = generateEmail($fn, $ln);

    $imgNum = rand(1, 64);
    $photoUrl = "/assets/images/profile_pictures/user_exp{$imgNum}.jpeg";

    // Rôle 3 = Auditeur
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

// --- C. Création de contenu (Albums et Chansons) pour les nouveaux artistes ---

echo "[INFO] Génération des Albums et Chansons pour les nouveaux artistes...\n";
foreach ($newArtistEmails as $artistEmail) {
    $numAlbums = rand(0, 2);

    for ($a = 0; $a < $numAlbums; $a++) {
        $albumName = pick($albumAdjectives) . " " . pick($albumNouns);
        $imgNum = rand(1, 40);
        $coverUrl = "/assets/images/albums/album_{$imgNum}.jpg";

        $sqlAlb = "INSERT INTO album (nomAlbum, dateSortieAlbum, urlPochetteAlbum, artisteAlbum) VALUES (:nom, :date, :url, :artist)";
        $stmt = $pdo->prepare($sqlAlb);
        $stmt->execute([
            ':nom' => $albumName,
            ':date' => $currentDate, // Sortie aujourd'hui
            ':url' => $coverUrl,
            ':artist' => $artistEmail
        ]);
        $albumId = $pdo->lastInsertId();

        // Ajout de chansons à cet album
        $numSongs = rand(5, 12);
        for ($s = 1; $s <= $numSongs; $s++) {
            $title = pick($songVerbs) . " " . pick($songNouns); // Ex: "Running Sky"
            $duration = rand(120, 300); // 2 à 5 minutes
            $mp3Num = rand(1, 28);
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

            // Ajout de la participation (Leader)
            $lastSongId = $pdo->lastInsertId();
            $sqlPart = "INSERT INTO participation (idChanson, emailArtisteParticipant, typeParticipation, ordreParticipation) VALUES (?, ?, 'leader', 1)";
            $stmtPart = $pdo->prepare($sqlPart);
            $stmtPart->execute([$lastSongId, $artistEmail]);
        }
    }
}

// --- D. Actualisation des listes (mélange anciennes et nouvelles données) ---
// Maintenant que nous avons ajouté de nouvelles personnes et du contenu,
// nous récupérons les listes de TOUS pour créer des interactions.

$allAuditeurs = getRandomIds($pdo, 'utilisateur', 'emailUtilisateur', 1000, "WHERE roleUtilisateur = 3");
$allArtistes = getRandomIds($pdo, 'utilisateur', 'emailUtilisateur', 1000, "WHERE roleUtilisateur = 2");
$allSongs = getRandomIds($pdo, 'chanson', 'idChanson', 2000);

// --- E. Création de Battles ---
// Création de battles entre artistes aléatoires (anciens ou nouveaux)
$numNewBattles = rand(2, 5);
echo "[INFO] Création de $numNewBattles nouvelles Battles...\n";

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

// Récupération des identifiants des Battles actives pour le vote
$activeBattles = getRandomIds($pdo, 'battle', 'idBattle', 50, "WHERE statutBattle = 'en_cours'");

// --- F. Simulation des actions utilisateurs (Auditeurs) ---
// Parcours de TOUS les auditeurs (anciens et nouveaux) pour effectuer des actions
echo "[INFO] Simulation des interactions pour les Auditeurs...\n";

foreach ($allAuditeurs as $auditeurEmail) {

    // 1. Abonnement à des Artistes (jusqu'à 4)
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

    // 2. Like de Chansons (jusqu'à 8)
    $likeCount = rand(0, 8);
    if ($likeCount > 0 && count($allSongs) > 0) {
        $targets = array_rand(array_flip($allSongs), min($likeCount, count($allSongs)));
        if (!is_array($targets)) $targets = [$targets];

        foreach ($targets as $songId) {
            $sqlLike = "INSERT IGNORE INTO likeChanson (emailUtilisateur, idChanson, dateLike) VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($sqlLike);
            $stmt->execute([$auditeurEmail, $songId, randomTime()]);

            // Incrémente légèrement le compteur d'écoutes pour le réalisme de la simulation
            $pdo->exec("UPDATE chanson SET nbEcouteChanson = nbEcouteChanson + 1 WHERE idChanson = $songId");
        }
    }

    // 3. Création de Playlists (jusqu'à 2)
    $plCount = rand(0, 2);
    for ($p = 0; $p < $plCount; $p++) {
        $plName = "My " . pick($albumAdjectives) . " Mix " . rand(1, 99);
        $isPublic = rand(0, 1);

        $sqlPl = "INSERT INTO playlist (nomPlaylist, estPubliquePlaylist, dateCreationPlaylist, emailProprietaire) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sqlPl);
        $stmt->execute([$plName, $isPublic, randomTime(), $auditeurEmail]);
        $plId = $pdo->lastInsertId();

        // Ajout de 1 à 5 chansons à la playlist
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

    // 4. Vote dans les Battles (jusqu'à 2)
    $voteCount = rand(0, 2);
    if ($voteCount > 0 && count($activeBattles) > 0) {
        $targetBattles = array_rand(array_flip($activeBattles), min($voteCount, count($activeBattles)));
        if (!is_array($targetBattles)) $targetBattles = [$targetBattles];

        foreach ($targetBattles as $battleId) {
            // Vérification des participants de la battle
            $stmtB = $pdo->prepare("SELECT emailCreateurBattle, emailParticipantBattle FROM battle WHERE idBattle = ?");
            $stmtB->execute([$battleId]);
            $battleData = $stmtB->fetch(PDO::FETCH_ASSOC);
            $stmtB->closeCursor(); // FIX CRITIQUE : Libère la connexion pour la requête suivante

            if ($battleData && $battleData['emailParticipantBattle']) {
                // Sélection aléatoire du gagnant
                $votedFor = (rand(0, 1) == 0) ? $battleData['emailCreateurBattle'] : $battleData['emailParticipantBattle'];

                $sqlVote = "INSERT IGNORE INTO vote (emailVotant, idBattle, emailVotee, dateVote) VALUES (?, ?, ?, ?)";
                $stmt = $pdo->prepare($sqlVote);
                $stmt->execute([$auditeurEmail, $battleId, $votedFor, randomTime()]);
            }
        }
    }
}

echo "[SUCCÈS] Simulation quotidienne terminée.\n";
