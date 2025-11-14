-- populate_database.sql
USE paaxio_db;
SET FOREIGN_KEY_CHECKS = 0;
DELETE FROM vote;
DELETE FROM battle;
DELETE FROM paiement;
DELETE FROM chansonPlaylist;
DELETE FROM playlist;
DELETE FROM signalement;
DELETE FROM message;
DELETE FROM likeChanson;
DELETE FROM participation;
DELETE FROM connexion;
DELETE FROM chanson;
DELETE FROM album;
DELETE FROM abonnementArtiste;
DELETE FROM utilisateur;
DELETE FROM role;
DELETE FROM genre;
SET FOREIGN_KEY_CHECKS = 1;

-- ===== genre =====
INSERT INTO genre (idGenre, nomGenre) VALUES
(1, 'Pop'),
(2, 'Rock'),
(3, 'Hip-Hop'),
(4, 'Électro'),
(5, 'Jazz'),
(6, 'Classique'),
(7, 'Reggae'),
(8, 'Latin'),
(9, 'Indie'),
(10, 'Folk');

-- ===== role =====
INSERT INTO role (idRole, typeRole, libelleRole) VALUES
(1, 'admin', 'Administrateur'),
(2, 'artiste', 'Artiste créateur'),
(3, 'auditeur', 'Auditeur / fan'),
(4, 'producteur', 'Producteur'),
(5, 'invite', 'Invité (lecture seule)');

-- ===== utilisateur =====
INSERT INTO utilisateur (
  emailUtilisateur,
  nomUtilisateur,
  pseudoUtilisateur,
  motDePasseUtilisateur,
  dateDeNaissanceUtilisateur,
  dateInscriptionUtilisateur,
  statutUtilisateur,
  estAbonnee,
  descriptionUtilisateur,
  siteWebUtilisateur,
  statutAbonnement,
  dateDebutAbonnement,
  dateFinAbonnement,
  pointsDeRenommeeArtiste,
  nbAbonnesArtiste,
  urlPhotoUtilisateur,
  roleUtilisateur,
  genreUtilisateur
) VALUES
('admin@paaxio.com', 'Paaxio Admin', 'AdminPaax', '$2y$10$adminhash', '1990-01-10', '2024-10-01 12:00:00', 'actif', 1, 'Super administrateur du site Paaxio', 'https://paaxio.com/admin', 'actif', '2024-07-01', '2025-07-01', 0, 0, '/img/p01.webp', 1, 5),
('yohan@paaxio.com', 'Yohan Boix', 'yohan', '$2y$10$morgan', '1999-03-16', '2024-10-01 12:00:00', 'actif', 1, 'Créateur électro et co-fondateur de Paaxio', 'https://yohanmusic.com', 'actif', '2024-08-01', '2025-08-01', 350, 980, '/img/p02.webp', 2, 7),
('angel@paaxio.com', 'Angel David Ramirez Batalla', 'angel', '$2y$10$angel', '2001-10-20', '2024-10-01 12:00:00', 'actif', 1, 'Artiste Indie basque et poète urbain', 'https://angelindigo.com', 'actif', '2024-08-15', '2025-08-15', 420, 1200, '/img/p03.webp', 1, NULL),
('jarlin@paaxio.com', 'Jarlin Boussou Mouyabi', 'jarlin', '$2y$10$jean', '1995-04-04', '2024-10-01 12:00:00', 'actif', 0, 'Guitariste et saxophoniste virtuose', 'https://jarlinmusiques.fr', 'expire', '2023-09-01', '2024-09-01', 120, 300, '/img/p04.webp', 2, 2),
('christopher@paaxio.com', 'Christopher Cecilia Urra', 'christopher', '$2y$10$alice', '1997-06-21', '2024-10-01 12:00:00', 'actif', 1, 'Passionné jazz et compositeur multi-instrumentiste', 'https://chrisjazz.com', 'actif', '2024-05-10', '2025-05-10', 210, 600, '/img/p05.webp', 2, NULL),
('tim@paaxio.com', 'Tim Didelot', 'tim', '$2y$10$leo', '2000-11-12', '2024-10-01 12:00:00', 'actif', 0, 'Fan de folk et amateur de vibes acoustiques', 'https://timfolk.net', 'annule', NULL, NULL, 0, 0, '/img/p06.webp', 3, NULL),
('erwan@paaxio.com', 'Erwan Hoarau', 'erwan', '$2y$10$sara', '1998-12-01', '2024-10-01 12:00:00', 'actif', 1, 'Rappeur et producteur hip-hop', 'https://erwanflow.fr', 'actif', '2024-01-01', '2025-01-01', 0, 0, '/img/p07.webp', 3, 6),
('rteisseir@paaxio.com', 'Raphaël Teisseire', 'rteisseir', '$2y$10$mod', '1992-02-02', '2024-10-01 12:00:00', 'actif', 1, 'Producteur-exécutif et modérateur du site', 'https://prodteisseir.com', 'actif', '2024-06-01', '2025-06-01', 0, 0, '/img/p08.webp', 4, NULL),
('label@paaxio.com', 'Label Rep Paaxio', 'LabelRep', '$2y$10$label', '1988-08-08', '2024-10-01 12:00:00', 'actif', 1, 'Label indépendant partenaire de Paaxio', 'https://labelrep.com', 'actif', '2024-06-15', '2025-06-15', 0, 0, '/img/p09.webp', 5, 8),
('curator@paaxio.com', 'Curateur Officiel', 'Cur8r', '$2y$10$cur8r', '1994-09-09', '2024-10-01 12:00:00', 'actif', 1, 'Curateur officiel, sélectionneur de playlists', 'https://paaxio.com/cur8r', 'actif', '2024-03-01', '2025-03-01', 0, 0, '/img/p10.webp', 1, 1);

-- ===== album =====
INSERT INTO album (idAlbum, nomAlbum, dateSortieAlbum, urlPochetteAlbum, artisteAlbum) VALUES
(1, 'Horizons Électriques', '2024-09-20', '/img/p11.webp', 'yohan@paaxio.com'),
(2, 'Rue des Étoiles', '2024-07-11', '/img/p12.webp', 'yohan@paaxio.com'),
(3, 'Océan Indigo', '2024-05-30', '/img/p13.webp', 'christopher@paaxio.com'),
(4, 'Matin Folk', '2023-12-01', '/img/p14.webp', 'jarlin@paaxio.com'),
(5, 'Nocturne Jazz', '2024-03-14', '/img/p15.webp', 'jarlin@paaxio.com'),
(6, 'Rythmes Latins', '2024-08-05', '/img/p16.webp', 'jarlin@paaxio.com'),
(7, 'Indie Hiver', '2024-10-01', '/img/p17.webp', 'christopher@paaxio.com'),
(8, 'Paroles du Quai', '2024-04-22', '/img/p18.webp', 'christopher@paaxio.com'),
(9, 'Classique Moderne', '2024-02-10', '/img/p19.webp', 'yohan@paaxio.com'),
(10, 'Vibes Urbaines', '2024-06-18', '/img/p20.webp', 'yohan@paaxio.com');

-- ===== chanson =====
INSERT INTO chanson (idChanson, titreChanson, descriptionChanson, dureeChanson, dateTeleversementChanson, compositeurChanson, parolierChanson, estPublieeChanson, nbEcouteChanson, albumChanson, genreChanson, urlAudioChanson, emailPublicateur) VALUES
(1, 'Lumière de Bayonne', 'Une balade indie au bord de l''Adour', 182, '2024-10-02', 'Angel', 'Angel', 1, 1520, 7, 9, '/audio/tr_01.mp3', 'angel@paaxio.com'),
(2, 'Nuit Électrique', 'Beat électro moderne', 210, '2024-09-21', 'Morgan', 'Morgan', 1, 2310, 1, 4, '/audio/tr_02.mp3', 'yohan@paaxio.com'),
(3, 'Plaza del Sol', 'Rythmes latins et guitares', 198, '2024-08-06', 'Alice', 'Alice', 1, 890, 6, 8, '/audio/tr_03.mp3', 'christopher@paaxio.com'),
(4, 'Minuit Jazz', 'Sax et piano feutrés', 245, '2024-03-15', 'Jean', 'Jean', 1, 121, 5, 5, '/audio/tr_04.mp3', 'jarlin@paaxio.com'),
(5, 'Folk du Matin', 'Guitare acoustique douce', 174, '2023-12-05', 'Leo', 'Leo', 1, 67, 4, 10, '/audio/tr_05.mp3', 'tim@paaxio.com'),
(6, 'Rock sur la Nivelle', 'Guitares saturées et énergie', 203, '2024-07-12', 'Sara', 'Sara', 1, 560, 2, 2, '/audio/tr_06.mp3', 'erwan@paaxio.com'),
(7, 'Hip-Hop Indigo', 'Flow posé et nappes indigo', 199, '2024-05-31', 'Morgan', 'Angel', 1, 1940, 3, 3, '/audio/tr_07.mp3', 'yohan@paaxio.com'),
(8, 'Valse Moderne', 'Classique avec twist contemporain', 221, '2024-02-11', 'Alice', 'Alice', 1, 83, 9, 6, '/audio/tr_08.mp3', 'christopher@paaxio.com'),
(9, 'Reggae du Port', 'Vibes détendues au soleil', 207, '2024-06-20', 'Jean', 'Jean', 1, 304, 10, 7, '/audio/tr_09.mp3', 'jarlin@paaxio.com'),
(10, 'Urbain 64', 'Ambiance urbaine, flow basque', 188, '2024-06-19', 'Angel', 'Angel', 1, 1290, 10, 3, '/audio/tr_10.mp3', 'angel@paaxio.com'),
(11, 'Étoiles sur l''Adour', 'Ambient-électro nocturne', 236, '2024-07-11', 'Morgan', 'Morgan', 1, 640, 1, 4, '/audio/tr_11.mp3', 'yohan@paaxio.com'),
(12, 'Quai des Paroles', 'Poésie parlée et beats lents', 202, '2024-04-23', 'Sara', 'Alice', 1, 220, 8, 9, '/audio/tr_12.mp3', 'erwan@paaxio.com');

-- ===== participation =====
INSERT INTO participation (idChanson, emailArtisteParticipant, typeParticipation, ordreParticipation) VALUES
(1, 'angel@paaxio.com', 'leader', 1),
(1, 'yohan@paaxio.com', 'feat', 2),
(2, 'yohan@paaxio.com', 'leader', 1),
(3, 'christopher@paaxio.com', 'leader', 1),
(4, 'jarlin@paaxio.com', 'leader', 1),
(5, 'tim@paaxio.com', 'leader', 1),
(6, 'erwan@paaxio.com', 'leader', 1),
(7, 'yohan@paaxio.com', 'leader', 1),
(7, 'angel@paaxio.com', 'feat', 2),
(8, 'christopher@paaxio.com', 'leader', 1),
(9, 'jarlin@paaxio.com', 'leader', 1),
(10, 'angel@paaxio.com', 'leader', 1),
(11, 'yohan@paaxio.com', 'leader', 1),
(12, 'erwan@paaxio.com', 'leader', 1);

-- ===== likeChanson =====
INSERT INTO likeChanson (emailUtilisateur, idChanson, dateLike) VALUES
('admin@paaxio.com', 11, '2024-10-16 19:00:00'),
('admin@paaxio.com', 2, '2024-10-10 11:00:00'),
('admin@paaxio.com', 1, '2024-10-08 09:00:00'),
('admin@paaxio.com', 5, '2024-11-18 05:00:00'),
('yohan@paaxio.com', 2, '2024-10-02 14:00:00'),
('yohan@paaxio.com', 10, '2024-10-14 19:00:00'),
('yohan@paaxio.com', 7, '2024-11-03 07:00:00'),
('yohan@paaxio.com', 1, '2024-10-03 05:00:00'),
('angel@paaxio.com', 4, '2024-10-16 02:00:00'),
('angel@paaxio.com', 11, '2024-11-07 20:00:00'),
('angel@paaxio.com', 9, '2024-11-21 12:00:00'),
('angel@paaxio.com', 7, '2024-11-18 17:00:00'),
('jarlin@paaxio.com', 12, '2024-10-10 18:00:00'),
('jarlin@paaxio.com', 7, '2024-11-18 22:00:00'),
('jarlin@paaxio.com', 6, '2024-10-07 14:00:00'),
('jarlin@paaxio.com', 5, '2024-10-25 15:00:00'),
('christopher@paaxio.com', 6, '2024-11-21 13:00:00'),
('christopher@paaxio.com', 12, '2024-11-17 02:00:00'),
('christopher@paaxio.com', 10, '2024-11-04 15:00:00'),
('christopher@paaxio.com', 5, '2024-11-30 00:00:00'),
('tim@paaxio.com', 2, '2024-11-06 18:00:00'),
('tim@paaxio.com', 9, '2024-11-15 14:00:00'),
('tim@paaxio.com', 5, '2024-10-04 09:00:00'),
('tim@paaxio.com', 6, '2024-10-15 21:00:00'),
('erwan@paaxio.com', 2, '2024-10-19 02:00:00'),
('erwan@paaxio.com', 4, '2024-11-10 23:00:00'),
('erwan@paaxio.com', 12, '2024-10-11 23:00:00'),
('erwan@paaxio.com', 7, '2024-10-23 18:00:00'),
('rteisseir@paaxio.com', 11, '2024-11-05 11:00:00'),
('rteisseir@paaxio.com', 5, '2024-10-16 17:00:00'),
('rteisseir@paaxio.com', 2, '2024-10-31 00:00:00'),
('rteisseir@paaxio.com', 3, '2024-10-19 08:00:00'),
('label@paaxio.com', 12, '2024-11-23 13:00:00'),
('label@paaxio.com', 9, '2024-10-15 13:00:00'),
('label@paaxio.com', 4, '2024-11-21 22:00:00'),
('label@paaxio.com', 6, '2024-10-26 20:00:00'),
('curator@paaxio.com', 2, '2024-10-15 08:00:00'),
('curator@paaxio.com', 4, '2024-11-02 00:00:00'),
('curator@paaxio.com', 10, '2024-11-27 08:00:00'),
('curator@paaxio.com', 6, '2024-10-30 16:00:00');

-- ===== message =====
INSERT INTO message (idMessage, dateMessage, contenuMessage, estLuMessage, emailExpediteur, emailDestinataire) VALUES
(1, '2024-10-02 21:00:00', 'Bienvenue sur Paaxio !', 1, 'admin@paaxio.com', 'angel@paaxio.com'),
(2, '2024-10-03 22:00:00', 'On collab sur une track ?', 0, 'angel@paaxio.com', 'yohan@paaxio.com'),
(3, '2024-10-04 00:00:00', 'Yes, j''ai une idée électro.', 0, 'yohan@paaxio.com', 'angel@paaxio.com'),
(4, '2024-10-05 02:00:00', 'Playlist mise à jour !', 1, 'curator@paaxio.com', 'erwan@paaxio.com'),
(5, '2024-10-07 04:00:00', 'Live vendredi ?', 0, 'jarlin@paaxio.com', 'christopher@paaxio.com'),
(6, '2024-10-08 06:00:00', 'Merci pour le like !', 1, 'christopher@paaxio.com', 'tim@paaxio.com'),
(7, '2024-10-09 08:00:00', 'Moderation OK', 1, 'rteisseir@paaxio.com', 'admin@paaxio.com'),
(8, '2024-10-10 09:00:00', 'Nouveau beat en cours', 0, 'yohan@paaxio.com', 'jarlin@paaxio.com'),
(9, '2024-10-11 10:00:00', 'Let''s jam demain', 0, 'angel@paaxio.com', 'erwan@paaxio.com'),
(10, '2024-10-11 20:00:00', 'Roadmap d''octobre', 1, 'admin@paaxio.com', 'curator@paaxio.com');

-- ===== signalement =====
INSERT INTO signalement (idSignalement, typeSignalement, motifSignalement, statutSignalement, dateCreation, emailAuteur, emailAdminTraitant) VALUES
(1, 'contenu', 'Paroles offensantes (propos modérés)', 'en_cours_de_traitement', '2024-10-05 23:00:00', 'erwan@paaxio.com', 'rteisseir@paaxio.com'),
(2, 'profil', 'Photo inappropriée (avertissement)', 'traite', '2024-10-08 03:00:00', 'tim@paaxio.com', 'rteisseir@paaxio.com'),
(3, 'contenu', 'Spam dans les messages privés', 'traite', '2024-10-09 05:00:00', 'christopher@paaxio.com', 'admin@paaxio.com'),
(4, 'paiement', 'Suspicion de double débit (vérifiée)', 'traite', '2024-10-10 00:00:00', 'jarlin@paaxio.com', 'admin@paaxio.com'),
(5, 'contenu', 'Clip non conforme aux règles', 'non_traite', '2024-10-11 02:00:00', 'angel@paaxio.com', NULL),
(6, 'profil', 'Usurpation d''identité', 'en_cours_de_traitement', '2024-10-11 22:00:00', 'yohan@paaxio.com', 'rteisseir@paaxio.com'),
(7, 'contenu', 'Drois d''auteur douteux (en analyse)', 'en_cours_de_traitement', '2024-10-13 21:00:00', 'curator@paaxio.com', 'rteisseir@paaxio.com'),
(8, 'contenu', 'Langage inapproprié', 'traite', '2024-10-15 01:00:00', 'label@paaxio.com', 'admin@paaxio.com'),
(9, 'profil', 'Profil vide suspect', 'non_traite', '2024-10-17 04:00:00', 'erwan@paaxio.com', NULL),
(10, 'autre', 'Bug affichage lecteur mobile', 'en_cours_de_traitement', '2024-10-19 21:00:00', 'angel@paaxio.com', NULL);

-- ===== playlist =====
INSERT INTO playlist (idPlaylist, nomPlaylist, estPubliquePlaylist, dateCreationPlaylist, dateDerniereModification, emailProprietaire) VALUES
(1, 'Découvertes Indie', 1, '2024-10-02 20:00:00', '2024-10-06 06:00:00', 'curator@paaxio.com'),
(2, 'Énergie Électro', 1, '2024-10-03 21:00:00', '2024-10-07 00:00:00', 'yohan@paaxio.com'),
(3, 'Matin Focus', 0, '2024-10-04 19:00:00', '2024-10-07 22:00:00', 'christopher@paaxio.com'),
(4, 'Jazz de Nuit', 1, '2024-10-05 09:00:00', '2024-10-08 21:00:00', 'jarlin@paaxio.com'),
(5, 'Run & Flow', 0, '2024-10-05 18:00:00', '2024-10-09 20:00:00', 'angel@paaxio.com'),
(6, 'Vibes Latines', 1, '2024-10-06 19:00:00', '2024-10-10 19:00:00', 'christopher@paaxio.com'),
(7, 'Chill du Quai', 1, '2024-10-07 23:00:00', '2024-10-11 21:00:00', 'erwan@paaxio.com'),
(8, 'Roadtrip Basque', 0, '2024-10-08 22:00:00', '2024-10-11 23:00:00', 'tim@paaxio.com'),
(9, 'Lofi + Études', 1, '2024-10-09 21:00:00', '2024-10-13 00:00:00', 'angel@paaxio.com'),
(10, 'Hits Paaxio Octobre', 1, '2024-10-11 01:00:00', '2024-10-13 22:00:00', 'admin@paaxio.com');

-- ===== chansonPlaylist =====
INSERT INTO chansonPlaylist (idPlaylist, idChanson, positionChanson, dateAjoutChanson) VALUES
(1, 11, 1, '2024-10-02 13:00:00'),
(1, 1, 2, '2024-10-03 13:00:00'),
(1, 2, 3, '2024-10-04 13:00:00'),
(1, 7, 4, '2024-10-05 13:00:00'),
(1, 10, 5, '2024-10-06 13:00:00'),
(2, 6, 1, '2024-10-02 14:00:00'),
(2, 4, 2, '2024-10-03 14:00:00'),
(2, 12, 3, '2024-10-04 14:00:00'),
(2, 5, 4, '2024-10-05 14:00:00'),
(2, 11, 5, '2024-10-06 14:00:00'),
(3, 11, 1, '2024-10-02 15:00:00'),
(3, 4, 2, '2024-10-03 15:00:00'),
(3, 6, 3, '2024-10-04 15:00:00'),
(3, 1, 4, '2024-10-05 15:00:00'),
(3, 9, 5, '2024-10-06 15:00:00'),
(4, 7, 1, '2024-10-02 16:00:00'),
(4, 10, 2, '2024-10-03 16:00:00'),
(4, 1, 3, '2024-10-04 16:00:00'),
(4, 12, 4, '2024-10-05 16:00:00'),
(4, 8, 5, '2024-10-06 16:00:00'),
(5, 11, 1, '2024-10-02 17:00:00'),
(5, 4, 2, '2024-10-03 17:00:00'),
(5, 1, 3, '2024-10-04 17:00:00'),
(5, 10, 4, '2024-10-05 17:00:00'),
(5, 8, 5, '2024-10-06 17:00:00'),
(6, 7, 1, '2024-10-02 18:00:00'),
(6, 5, 2, '2024-10-03 18:00:00'),
(6, 2, 3, '2024-10-04 18:00:00'),
(6, 12, 4, '2024-10-05 18:00:00'),
(6, 8, 5, '2024-10-06 18:00:00'),
(7, 3, 1, '2024-10-02 19:00:00'),
(7, 7, 2, '2024-10-03 19:00:00'),
(7, 5, 3, '2024-10-04 19:00:00'),
(7, 8, 4, '2024-10-05 19:00:00'),
(7, 12, 5, '2024-10-06 19:00:00'),
(8, 1, 1, '2024-10-02 20:00:00'),
(8, 4, 2, '2024-10-03 20:00:00'),
(8, 6, 3, '2024-10-04 20:00:00'),
(8, 11, 4, '2024-10-05 20:00:00'),
(8, 8, 5, '2024-10-06 20:00:00'),
(9, 7, 1, '2024-10-02 21:00:00'),
(9, 10, 2, '2024-10-03 21:00:00'),
(9, 5, 3, '2024-10-04 21:00:00'),
(9, 6, 4, '2024-10-05 21:00:00'),
(9, 3, 5, '2024-10-06 21:00:00'),
(10, 7, 1, '2024-10-02 22:00:00'),
(10, 11, 2, '2024-10-03 22:00:00'),
(10, 5, 3, '2024-10-04 22:00:00'),
(10, 3, 4, '2024-10-05 22:00:00'),
(10, 6, 5, '2024-10-06 22:00:00');

-- ===== paiement =====
INSERT INTO paiement (idPaiement, datePaiement, montantPaiement, statutPaiement, devisePaiement, modePaiement, emailUtilisateur) VALUES
(1, '2024-10-03 01:00:00', 9.99, 'succeeded', 'EUR', 'carte_bancaire', 'angel@paaxio.com'),
(2, '2024-10-07 03:00:00', 9.99, 'succeeded', 'EUR', 'paypal', 'yohan@paaxio.com'),
(3, '2024-10-11 05:00:00', 9.99, 'failed', 'EUR', 'carte_bancaire', 'jarlin@paaxio.com'),
(4, '2024-10-13 22:00:00', 9.99, 'pending', 'EUR', 'carte_bancaire', 'erwan@paaxio.com'),
(5, '2024-10-15 21:00:00', 19.98, 'refunded', 'EUR', 'paypal', 'christopher@paaxio.com'),
(6, '2024-10-16 23:00:00', 9.99, 'succeeded', 'EUR', 'carte_bancaire', 'curator@paaxio.com'),
(7, '2024-10-20 04:00:00', 9.99, 'succeeded', 'EUR', 'paypal', 'label@paaxio.com'),
(8, '2024-10-22 06:00:00', 9.99, 'succeeded', 'EUR', 'carte_bancaire', 'rteisseir@paaxio.com'),
(9, '2024-10-23 20:00:00', 9.99, 'succeeded', 'EUR', 'paypal', 'angel@paaxio.com'),
(10, '2024-10-26 19:00:00', 9.99, 'succeeded', 'EUR', 'carte_bancaire', 'yohan@paaxio.com');

-- ===== battle =====
INSERT INTO battle (idBattle, titreBattle, dateDebutBattle, dateFinBattle, statutBattle, emailCreateurBattle, emailParticipantBattle) VALUES
(1, 'Battle Électro vs Indie', '2024-10-05 07:00:00', '2024-10-12 07:00:00', 'en_cours', 'angel@paaxio.com', 'yohan@paaxio.com'),
(2, 'Jazz Night Showdown', '2024-10-06 08:00:00', '2024-10-08 08:00:00', 'terminee', 'jarlin@paaxio.com', 'christopher@paaxio.com'),
(3, 'Flow Urbain', '2024-10-08 06:00:00', '2024-10-14 06:00:00', 'en_attente', 'yohan@paaxio.com', NULL),
(4, 'Folk vs Rock', '2024-10-09 00:00:00', '2024-10-16 00:00:00', 'annulee', 'tim@paaxio.com', 'erwan@paaxio.com'),
(5, 'Latin Heat', '2024-10-10 03:00:00', '2024-10-17 03:00:00', 'terminee', 'christopher@paaxio.com', 'jarlin@paaxio.com');

-- ===== vote =====
INSERT INTO vote (emailVotant, idBattle, emailVotee, dateVote) VALUES
('admin@paaxio.com', 1, 'yohan@paaxio.com', '2024-10-17 18:00:00'),
('admin@paaxio.com', 2, 'jarlin@paaxio.com', '2024-10-21 06:00:00'),
('admin@paaxio.com', 5, 'jarlin@paaxio.com', '2024-10-10 03:00:00'),
('yohan@paaxio.com', 1, 'angel@paaxio.com', '2024-10-08 15:00:00'),
('yohan@paaxio.com', 2, 'jarlin@paaxio.com', '2024-10-15 23:00:00'),
('yohan@paaxio.com', 5, 'jarlin@paaxio.com', '2024-10-16 02:00:00'),
('angel@paaxio.com', 1, 'yohan@paaxio.com', '2024-10-05 13:00:00'),
('angel@paaxio.com', 2, 'christopher@paaxio.com', '2024-10-12 15:00:00'),
('angel@paaxio.com', 5, 'christopher@paaxio.com', '2024-10-08 18:00:00'),
('jarlin@paaxio.com', 1, 'yohan@paaxio.com', '2024-10-07 01:00:00'),
('jarlin@paaxio.com', 2, 'christopher@paaxio.com', '2024-10-11 02:00:00'),
('jarlin@paaxio.com', 5, 'christopher@paaxio.com', '2024-10-05 02:00:00'),
('christopher@paaxio.com', 1, 'angel@paaxio.com', '2024-10-04 08:00:00'),
('christopher@paaxio.com', 2, 'jarlin@paaxio.com', '2024-10-04 19:00:00'),
('christopher@paaxio.com', 5, 'jarlin@paaxio.com', '2024-10-16 03:00:00'),
('tim@paaxio.com', 1, 'yohan@paaxio.com', '2024-10-09 00:00:00'),
('tim@paaxio.com', 2, 'jarlin@paaxio.com', '2024-10-08 00:00:00'),
('tim@paaxio.com', 5, 'christopher@paaxio.com', '2024-10-14 20:00:00'),
('erwan@paaxio.com', 1, 'yohan@paaxio.com', '2024-10-12 01:00:00'),
('erwan@paaxio.com', 2, 'christopher@paaxio.com', '2024-10-06 18:00:00'),
('erwan@paaxio.com', 5, 'jarlin@paaxio.com', '2024-10-08 13:00:00'),
('rteisseir@paaxio.com', 1, 'angel@paaxio.com', '2024-10-12 13:00:00'),
('rteisseir@paaxio.com', 2, 'jarlin@paaxio.com', '2024-10-21 03:00:00'),
('rteisseir@paaxio.com', 5, 'christopher@paaxio.com', '2024-10-04 04:00:00'),
('label@paaxio.com', 1, 'angel@paaxio.com', '2024-10-07 14:00:00'),
('label@paaxio.com', 2, 'jarlin@paaxio.com', '2024-10-10 00:00:00'),
('label@paaxio.com', 5, 'christopher@paaxio.com', '2024-10-20 19:00:00'),
('curator@paaxio.com', 1, 'angel@paaxio.com', '2024-10-21 14:00:00'),
('curator@paaxio.com', 2, 'christopher@paaxio.com', '2024-10-21 06:00:00'),
('curator@paaxio.com', 5, 'jarlin@paaxio.com', '2024-10-10 18:00:00');

-- ===== connexion =====
INSERT INTO connexion (idConnexion, dateConnexion, adresseIpConnexion, emailUtilisateur) VALUES
(1, '2024-10-02 22:00:00', '192.0.2.10', 'angel@paaxio.com'),
(2, '2024-10-03 23:00:00', '198.51.100.7', 'yohan@paaxio.com'),
(3, '2024-10-05 00:00:00', '203.0.113.12', 'christopher@paaxio.com'),
(4, '2024-10-06 01:00:00', '192.0.2.25', 'jarlin@paaxio.com'),
(5, '2024-10-07 02:00:00', '198.51.100.33', 'tim@paaxio.com'),
(6, '2024-10-08 03:00:00', '203.0.113.44', 'erwan@paaxio.com'),
(7, '2024-10-09 04:00:00', '192.0.2.55', 'curator@paaxio.com'),
(8, '2024-10-10 05:00:00', '198.51.100.66', 'label@paaxio.com'),
(9, '2024-10-11 06:00:00', '203.0.113.77', 'rteisseir@paaxio.com'),
(10, '2024-10-12 07:00:00', '192.0.2.88', 'admin@paaxio.com');

-- ===== abonnementArtiste =====
INSERT INTO abonnementArtiste (emailAbonne, emailArtiste, dateAbonnement) VALUES
-- L’admin suit les principaux artistes
('admin@paaxio.com', 'yohan@paaxio.com',        '2024-10-01 09:00:00'),
('admin@paaxio.com', 'angel@paaxio.com',        '2024-10-01 09:05:00'),
('admin@paaxio.com', 'jarlin@paaxio.com',       '2024-10-01 09:10:00'),
('admin@paaxio.com', 'christopher@paaxio.com',  '2024-10-01 09:15:00'),

-- Yohan suit les autres créateurs
('yohan@paaxio.com', 'angel@paaxio.com',        '2024-10-02 10:00:00'),
('yohan@paaxio.com', 'jarlin@paaxio.com',       '2024-10-02 10:05:00'),
('yohan@paaxio.com', 'christopher@paaxio.com',  '2024-10-02 10:10:00'),

-- Angel suit les artistes électro / jazz / rock
('angel@paaxio.com', 'yohan@paaxio.com',        '2024-10-03 11:00:00'),
('angel@paaxio.com', 'jarlin@paaxio.com',       '2024-10-03 11:05:00'),
('angel@paaxio.com', 'christopher@paaxio.com',  '2024-10-03 11:10:00'),

-- Jarlin suit Yohan et Angel
('jarlin@paaxio.com', 'yohan@paaxio.com',       '2024-10-04 12:00:00'),
('jarlin@paaxio.com', 'angel@paaxio.com',       '2024-10-04 12:05:00'),

-- Christopher suit les artistes plus “indie”
('christopher@paaxio.com', 'angel@paaxio.com',  '2024-10-05 13:00:00'),
('christopher@paaxio.com', 'yohan@paaxio.com',  '2024-10-05 13:05:00'),

-- Tim (auditeur) suit un peu tout le monde
('tim@paaxio.com', 'angel@paaxio.com',          '2024-10-06 14:00:00'),
('tim@paaxio.com', 'yohan@paaxio.com',          '2024-10-06 14:05:00'),
('tim@paaxio.com', 'jarlin@paaxio.com',         '2024-10-06 14:10:00'),
('tim@paaxio.com', 'christopher@paaxio.com',    '2024-10-06 14:15:00'),

-- Erwan suit surtout les artistes électro / jazz
('erwan@paaxio.com', 'yohan@paaxio.com',        '2024-10-07 15:00:00'),
('erwan@paaxio.com', 'christopher@paaxio.com',  '2024-10-07 15:05:00'),

-- Le producteur suit les artistes “signables”
('rteisseir@paaxio.com', 'angel@paaxio.com',    '2024-10-08 16:00:00'),
('rteisseir@paaxio.com', 'yohan@paaxio.com',    '2024-10-08 16:05:00'),
('rteisseir@paaxio.com', 'christopher@paaxio.com','2024-10-08 16:10:00'),

-- Le label suit tout le roster
('label@paaxio.com', 'angel@paaxio.com',        '2024-10-09 17:00:00'),
('label@paaxio.com', 'yohan@paaxio.com',        '2024-10-09 17:05:00'),
('label@paaxio.com', 'jarlin@paaxio.com',       '2024-10-09 17:10:00'),
('label@paaxio.com', 'christopher@paaxio.com',  '2024-10-09 17:15:00'),

-- Le curateur suit ceux qu’il met en avant
('curator@paaxio.com', 'angel@paaxio.com',      '2024-10-10 18:00:00'),
('curator@paaxio.com', 'yohan@paaxio.com',      '2024-10-10 18:05:00'),
('curator@paaxio.com', 'christopher@paaxio.com','2024-10-10 18:10:00');
