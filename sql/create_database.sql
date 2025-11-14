DROP DATABASE IF EXISTS paaxio_db;
CREATE DATABASE paaxio_db;
USE paaxio_db;

SET NAMES utf8mb4;
SET time_zone = '+00:00';

-- ===================== GENRE =====================
CREATE TABLE genre (
  idGenre INT PRIMARY KEY AUTO_INCREMENT,
  nomGenre VARCHAR(40) NOT NULL
);

-- ===================== ROLE =====================
CREATE TABLE role (
  idRole INT PRIMARY KEY AUTO_INCREMENT,
  typeRole VARCHAR(50) NOT NULL,
  libelleRole VARCHAR(255)
);

-- ===================== UTILISATEUR =====================
CREATE TABLE utilisateur (
  emailUtilisateur VARCHAR(191) PRIMARY KEY,

  pseudoUtilisateur VARCHAR(191) NOT NULL,
  motDePasseUtilisateur VARCHAR(255) NOT NULL,
  dateDeNaissanceUtilisateur DATE NOT NULL,
  dateInscriptionUtilisateur DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

  statutUtilisateur ENUM('actif','suspendu','supprimee') NOT NULL DEFAULT 'actif',
  genreUtilisateur INT DEFAULT NULL,
  estAbonnee BOOLEAN NOT NULL DEFAULT 0,
  descriptionUtilisateur TEXT DEFAULT NULL,
  siteWebUtilisateur VARCHAR(255) DEFAULT NULL,
  urlPhotoUtilisateur VARCHAR(191) DEFAULT NULL,
  statutAbonnement ENUM('actif','expire','annule','inactif') NOT NULL DEFAULT 'inactif',
  dateDebutAbonnement DATE,
  dateFinAbonnement DATE,

  pointsDeRenommeeArtiste INT DEFAULT 0,
  nbAbonnesArtiste INT DEFAULT 0,

  roleUtilisateur INT NOT NULL,

  UNIQUE KEY uqUtilisateurPseudo (pseudoUtilisateur),

  CONSTRAINT fkUtilisateurGenre
  FOREIGN KEY (genreUtilisateur) REFERENCES genre(idGenre)
  ON DELETE SET NULL ON UPDATE CASCADE,

  CONSTRAINT fkUtilisateurRole
    FOREIGN KEY (roleUtilisateur) REFERENCES role(idRole)
    ON DELETE RESTRICT ON UPDATE CASCADE
);

-- ===================== ALBUM =====================
CREATE TABLE album (
  idAlbum INT PRIMARY KEY AUTO_INCREMENT,
  nomAlbum VARCHAR(255) NOT NULL,
  dateSortieAlbum DATE NOT NULL,
  urlPochetteAlbum VARCHAR(191) NOT NULL
);

-- ===================== CHANSON =====================
CREATE TABLE chanson (
  idChanson INT PRIMARY KEY AUTO_INCREMENT,

  titreChanson VARCHAR(255) NOT NULL,
  descriptionChanson VARCHAR(255),
  dureeChanson INT NOT NULL,
  dateTeleversementChanson DATE NOT NULL,

  compositeurChanson VARCHAR(255),
  parolierChanson VARCHAR(255),

  estPublieeChanson BOOLEAN DEFAULT 0,
  nbEcouteChanson INT DEFAULT 0,
  urlAudioChanson VARCHAR(191) NOT NULL,

  albumChanson INT,
  genreChanson INT NOT NULL,
  emailPublicateur VARCHAR(191),

  CONSTRAINT fkAlbumChanson
    FOREIGN KEY (albumChanson) REFERENCES album(idAlbum)
    ON DELETE SET NULL ON UPDATE CASCADE,

  CONSTRAINT fkGenreChanson
    FOREIGN KEY (genreChanson) REFERENCES genre(idGenre)
    ON DELETE RESTRICT ON UPDATE CASCADE,

  CONSTRAINT fkPublicateur
    FOREIGN KEY (emailPublicateur) REFERENCES utilisateur(emailUtilisateur)
    ON DELETE SET NULL ON UPDATE CASCADE
);

-- ===================== CONNEXION =====================
CREATE TABLE connexion (
  idConnexion INT PRIMARY KEY AUTO_INCREMENT,
  dateConnexion DATETIME NOT NULL,
  adresseIpConnexion VARCHAR(45),
  emailUtilisateur VARCHAR(191),
  CONSTRAINT fkConnexionUtilisateur
    FOREIGN KEY (emailUtilisateur) REFERENCES utilisateur(emailUtilisateur)
    ON DELETE CASCADE ON UPDATE CASCADE
);

-- ===================== PARTICIPATION (U ↔ Chanson) =====================
CREATE TABLE participation (
  idChanson INT NOT NULL,
  emailArtisteParticipant VARCHAR(191) NOT NULL,
  typeParticipation ENUM('leader','feat') NOT NULL,
  ordreParticipation INT NOT NULL,
  PRIMARY KEY (idChanson, emailArtisteParticipant),
  CONSTRAINT fkPartChanson
    FOREIGN KEY (idChanson) REFERENCES chanson(idChanson)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fkPartUtilisateur
    FOREIGN KEY (emailArtisteParticipant) REFERENCES utilisateur(emailUtilisateur)
    ON DELETE RESTRICT ON UPDATE CASCADE
);

-- ===================== LIKE CHANSON (U ↔ Chanson) =====================
CREATE TABLE likeChanson (
  emailUtilisateur VARCHAR(191) NOT NULL,
  idChanson INT NOT NULL,
  dateLike DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (emailUtilisateur, idChanson),
  CONSTRAINT fkLikeUtilisateur
    FOREIGN KEY (emailUtilisateur) REFERENCES utilisateur(emailUtilisateur)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fkLikeChanson
    FOREIGN KEY (idChanson) REFERENCES chanson(idChanson)
    ON DELETE CASCADE ON UPDATE CASCADE
);

-- ===================== MESSAGE =====================
CREATE TABLE message (
  idMessage INT PRIMARY KEY AUTO_INCREMENT,
  dateMessage DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  contenuMessage VARCHAR(255) NOT NULL,
  estLuMessage BOOLEAN,
  emailExpediteur VARCHAR(191) NOT NULL,
  emailDestinataire VARCHAR(191) NOT NULL,
  CONSTRAINT fkExpediteurMessage
    FOREIGN KEY (emailExpediteur) REFERENCES utilisateur(emailUtilisateur)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fkDestinataireMessage
    FOREIGN KEY (emailDestinataire) REFERENCES utilisateur(emailUtilisateur)
    ON DELETE CASCADE ON UPDATE CASCADE
);

-- ===================== SIGNALEMENT =====================
CREATE TABLE signalement (
  idSignalement INT PRIMARY KEY AUTO_INCREMENT,
  typeSignalement VARCHAR(255),
  motifSignalement TEXT,
  statutSignalement ENUM('non_traite','en_cours_de_traitement','traite'),
  dateCreation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  dateDerniereModification DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  emailAuteur VARCHAR(191) NOT NULL,
  emailAdminTraitant VARCHAR(191),
  CONSTRAINT fkAuteur
    FOREIGN KEY (emailAuteur) REFERENCES utilisateur(emailUtilisateur)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fkAdmin
    FOREIGN KEY (emailAdminTraitant) REFERENCES utilisateur(emailUtilisateur)
    ON DELETE SET NULL ON UPDATE CASCADE
);

-- ===================== PLAYLIST =====================
CREATE TABLE playlist (
  idPlaylist INT PRIMARY KEY AUTO_INCREMENT,
  nomPlaylist VARCHAR(255) NOT NULL,
  estPubliquePlaylist BOOLEAN NOT NULL,
  dateCreationPlaylist DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  dateDerniereModification DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  emailProprietaire VARCHAR(191) NOT NULL,
  CONSTRAINT fkPlaylistUtilisateur
    FOREIGN KEY (emailProprietaire) REFERENCES utilisateur(emailUtilisateur)
    ON DELETE CASCADE ON UPDATE CASCADE
);

-- ===================== CHANSON PLAYLIST (Playlist ↔ Chanson) =====================
CREATE TABLE chansonPlaylist (
  idPlaylist INT NOT NULL,
  idChanson INT NOT NULL,
  positionChanson INT NOT NULL,
  dateAjoutChanson DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (idPlaylist, idChanson),
  UNIQUE KEY uq_playlist_pos (idPlaylist, positionChanson),
  CONSTRAINT fkPlaylist
    FOREIGN KEY (idPlaylist) REFERENCES playlist(idPlaylist)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fkChanson
    FOREIGN KEY (idChanson) REFERENCES chanson(idChanson)
    ON DELETE CASCADE ON UPDATE CASCADE
);

-- ===================== PAIEMENT =====================
CREATE TABLE paiement (
  idPaiement INT PRIMARY KEY AUTO_INCREMENT,
  datePaiement DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  montantPaiement DECIMAL(10,2) NOT NULL,
  statutPaiement ENUM('pending','succeeded','failed','refunded') NOT NULL DEFAULT 'pending',
  devisePaiement ENUM('EUR','USD','GBP','JPY','PHP') NOT NULL DEFAULT 'EUR',
  modePaiement ENUM('carte_bancaire','paypal') NOT NULL,
  emailUtilisateur VARCHAR(191) NOT NULL,
  CONSTRAINT fkPaiementUtilisateur
    FOREIGN KEY (emailUtilisateur) REFERENCES utilisateur(emailUtilisateur)
    ON DELETE CASCADE ON UPDATE CASCADE
);

-- ===================== NEWSLETTER =====================
CREATE TABLE newsletter (
  email VARCHAR(191) PRIMARY KEY,
  dateInscription DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT uqNewsletterEmail UNIQUE (email)
);

-- ===================== BATTLE =====================
CREATE TABLE battle (
  idBattle INT PRIMARY KEY AUTO_INCREMENT,
  titreBattle VARCHAR(255) NOT NULL,
  dateDebutBattle DATETIME NOT NULL,
  dateFinBattle DATETIME NOT NULL,
  statutBattle ENUM('en_attente','en_cours','terminee','annulee') NOT NULL,
  emailCreateurBattle VARCHAR(191) NOT NULL,
  emailParticipantBattle VARCHAR(191),
  CONSTRAINT fkBattleCreateur
    FOREIGN KEY (emailCreateurBattle) REFERENCES utilisateur(emailUtilisateur)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fkBattleParticipant
    FOREIGN KEY (emailParticipantBattle) REFERENCES utilisateur(emailUtilisateur)
    ON DELETE SET NULL ON UPDATE CASCADE
);

-- ===================== VOTE (U ↔ Battle ↔ U) =====================
CREATE TABLE vote (
  emailVotant VARCHAR(191) NOT NULL,
  idBattle INT NOT NULL,
  emailVotee VARCHAR(191) NOT NULL,
  dateVote DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (emailVotant, idBattle),
  KEY idxVotee (emailVotee),
  CONSTRAINT fkVoteVotant
    FOREIGN KEY (emailVotant) REFERENCES utilisateur(emailUtilisateur)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fkVoteVotee
    FOREIGN KEY (emailVotee) REFERENCES utilisateur(emailUtilisateur)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fkVoteBattle
    FOREIGN KEY (idBattle) REFERENCES battle(idBattle)
    ON DELETE CASCADE ON UPDATE CASCADE
);
