-- 1. Mise à jour automatique de la date de modification d’une playlist
DELIMITER $$

CREATE TRIGGER trg_update_playlist_modif_insert
AFTER INSERT ON chansonPlaylist
FOR EACH ROW
BEGIN
  UPDATE playlist
  SET dateDerniereModification = NOW()
  WHERE idPlaylist = NEW.idPlaylist;
END$$



CREATE TRIGGER trg_update_playlist_modif_delete
AFTER DELETE ON chansonPlaylist
FOR EACH ROW
BEGIN
  UPDATE playlist
  SET dateDerniereModification = NOW()
  WHERE idPlaylist = OLD.idPlaylist;
END$$

DELIMITER ;


-- ======= Test =======
INSERT INTO chansonPlaylist (idPlaylist, idChanson, positionChanson)
VALUES (1, 3, 6);

-- ====== Vérification ======
SELECT idPlaylist, nomPlaylist, dateDerniereModification AS date_apres
FROM playlist WHERE idPlaylist = 1;



-- 2. Créer automatiquement un message après un signalement
DELIMITER $$

CREATE TRIGGER trg_message_signalement
AFTER INSERT ON signalement
FOR EACH ROW
BEGIN
   INSERT INTO message (
       dateMessage, 
       contenuMessage, 
       estLuMessage,      -- Remplace 'statutMessage'
       emailExpediteur,   -- Obligatoire (NOT NULL)
       emailDestinataire  -- Obligatoire (NOT NULL)
   )
   VALUES (
       NOW(),
       CONCAT('Nouveau signalement : ', NEW.motifSignalement),
       0,                 -- 0 pour FALSE (non lu), au lieu de 'non_lu'
       NEW.emailAuteur,   -- L'auteur du signalement envoie le message
       'admin@paaxio.com' -- Le message est envoyé à l'admin principal
   );
END$$

DELIMITER ;


-- ======= Test =======
SELECT idMessage, contenuMessage, dateMessage 
FROM message 
ORDER BY idMessage DESC 
LIMIT 1;


INSERT INTO signalement (
    typeSignalement, 
    motifSignalement, 
    statutSignalement, 
    emailAuteur
) 
VALUES (
    'bug_technique', 
    'Le lecteur audio ne se lance pas sur Firefox.', 
    'non_traite', 
    'tim@paaxio.com'
);


-- ====== Vérification ======
SELECT 
    idMessage, 
    dateMessage, 
    emailExpediteur, 
    emailDestinataire, 
    contenuMessage, 
    estLuMessage 
FROM message 
WHERE emailExpediteur = 'tim@paaxio.com' 
ORDER BY idMessage DESC 
LIMIT 1;


-- Supprimer le signalement et le message associé (via suppression manuelle ou cascade si configurée)
DELETE FROM signalement 
WHERE emailAuteur = 'tim@paaxio.com' 
AND motifSignalement = 'Le lecteur audio ne se lance pas sur Firefox.';


-- Si la suppression en cascade n'est pas activée sur le message généré par trigger :
DELETE FROM message 
WHERE contenuMessage = 'Nouveau signalement : Le lecteur audio ne se lance pas sur Firefox.';




-- 3. Re-indexation de la playlist
DELIMITER $$

DROP TRIGGER IF EXISTS tr_reindexation_playlist_delete$$
DROP TRIGGER IF EXISTS tr_reindexation_playlist_update$$

-- Trigger Suppression : Décale tout ce qui est après vers le haut (-1)

CREATE TRIGGER tr_reindexation_playlist_delete
AFTER DELETE ON chansonPlaylist
FOR EACH ROW
BEGIN
   UPDATE chansonPlaylist
   SET positionChanson = positionChanson - 1
   WHERE idPlaylist = OLD.idPlaylist
     AND positionChanson > OLD.positionChanson;
END$$

CREATE TRIGGER tr_reindexation_playlist_update
AFTER UPDATE ON chansonPlaylist
FOR EACH ROW
BEGIN
   IF OLD.positionChanson != NEW.positionChanson THEN
           IF NEW.positionChanson < OLD.positionChanson THEN
           UPDATE chansonPlaylist
           SET positionChanson = positionChanson + 1
           WHERE idPlaylist = NEW.idPlaylist
             AND positionChanson >= NEW.positionChanson
             AND positionChanson < OLD.positionChanson
             AND idChanson != NEW.idChanson; -- Important: ne pas toucher la chanson qu'on déplace
       ELSE
           UPDATE chansonPlaylist
           SET positionChanson = positionChanson - 1
           WHERE idPlaylist = NEW.idPlaylist
             AND positionChanson <= NEW.positionChanson
             AND positionChanson > OLD.positionChanson
             AND idChanson != NEW.idChanson;
       END IF;
   END IF;
END$$

DELIMITER ;


-- ======= Test Suppression =======
-- Dans le dataset, la chanson à la pos 3 de la playlist 1 est l'idChanson 2
DELETE FROM chansonPlaylist 
WHERE idPlaylist = 1 AND positionChanson = 3;




-- 4. Anti-spam votes
DELIMITER $$

CREATE TRIGGER tr_anti_spam_votes
BEFORE INSERT ON vote
FOR EACH ROW
BEGIN
   DECLARE v_ip_actuelle VARCHAR(45);
   DECLARE v_nb_votes INT;
   DECLARE v_message TEXT;


   -- 1. Récupérer la dernière IP de connexion de l'utilisateur qui tente de voter
   SELECT adresseIpConnexion
     INTO v_ip_actuelle
     FROM connexion
    WHERE emailUtilisateur = NEW.emailVotant
    ORDER BY dateConnexion DESC
    LIMIT 1;


   -- 2. Compter les votes provenant de cette IP au cours des 10 dernières minutes
   SELECT COUNT(*) INTO v_nb_votes
     FROM vote v
     JOIN connexion c
       ON v.emailVotant = c.emailUtilisateur
    WHERE c.adresseIpConnexion = v_ip_actuelle
      AND v.dateVote > (NOW() - INTERVAL 10 MINUTE);


   -- 3. Règle métier : Bloquer si plus de 3 votes en 10 minutes
   IF v_nb_votes >= 3 THEN
       -- Sanction automatique : mettre l'utilisateur sous surveillance
       UPDATE utilisateur
          SET statutUtilisateur = 'suspendu'
        WHERE emailUtilisateur = NEW.emailVotant;


       SET v_message = CONCAT('Fraude détectée : Trop de votes depuis l''IP ', v_ip_actuelle);


       SIGNAL SQLSTATE '45000'
           SET MESSAGE_TEXT = v_message;
   END IF;
END$$

DELIMITER ;




-- ======= Test Anti-Spam =======
INSERT INTO signalement ( typeSignalement, motifSignalement, statutSignalement, emailAuteur ) VALUES ( 'contenu', 'Je pense que cette chanson plagie mon œuvre originale v.', 'non_traite', 'tim@paaxio.com' );

-- 1. Vérifier que le signalement est bien créé S
ELECT idSignalement, motifSignalement FROM signalement ORDER BY idSignalement DESC LIMIT 1;


-- 2. Vérifier que le message automatique a été créé 
SELECT idMessage, emailExpediteur, emailDestinataire, contenuMessage, estLuMessage, dateMessage FROM message WHERE emailExpediteur = 'tim@paaxio.com' AND emailDestinataire = 'admin@paaxio.com' ORDER BY idMessage DESC LIMIT 1;


-- ======= Requête de nettoyage =======

-- 1. Supprimer le message généré automatiquement
DELETE FROM message 
WHERE contenuMessage LIKE 'Nouveau signalement [contenu] : Je pense que cette chanson plagie%';

-- 2. Supprimer le signalement de test
DELETE FROM signalement 
WHERE emailAuteur = 'tim@paaxio.com' 
AND motifSignalement LIKE 'Je pense que cette chanson plagie%';




-- 5. Propagation de sanction
DELIMITER $$

DROP TRIGGER IF EXISTS tr_propagation_sanction$$


CREATE TRIGGER tr_propagation_sanction
AFTER UPDATE ON signalement
FOR EACH ROW
BEGIN
   -- On ne déclenche que si le statut passe à 'traite' et qu'il a changé
   IF NEW.statutSignalement = 'traite' AND OLD.statutSignalement != 'traite' THEN
   
       -- 1. Rendre toutes ses Playlists privées (0)
       UPDATE playlist
       SET estPubliquePlaylist = 0
       WHERE emailProprietaire = NEW.emailAuteur;


       -- 2. Marquer ses chansons comme sous révision (si pas déjà fait)
       UPDATE chanson
       SET titreChanson = CONCAT('[SOUS REVISION] ', titreChanson)
       WHERE emailPublicateur = NEW.emailAuteur
       AND titreChanson NOT LIKE '[SOUS REVISION]%';


       -- 3. Retirer ses participations (Feat/Leader)
       DELETE FROM participation
       WHERE emailArtisteParticipant = NEW.emailAuteur;
       
   END IF;
END$$

DELIMITER ;


-- ======= Test Propagation Sanction =======
-- Voir ses playlists publiques (devrait en avoir 1 : "Chill du Quai") 
SELECT idPlaylist, nomPlaylist, estPubliquePlaylist FROM playlist WHERE emailProprietaire = 'erwan@paaxio.com';


-- Voir ses chansons publiées (ex: "Rock sur la Nivelle") 
SELECT idChanson, titreChanson FROM chanson WHERE emailPublicateur = 'erwan@paaxio.com';


-- Voir ses participations (Il est leader sur la chanson 6 et 12)
SELECT * FROM participation WHERE emailArtisteParticipant = 'erwan@paaxio.com';

INSERT INTO signalement (typeSignalement, motifSignalement, statutSignalement, emailAuteur) VALUES ('autocritique', 'Test de sanction automatique', 'non_traite', 'erwan@paaxio.com');

UPDATE signalement 
SET statutSignalement = 'traite' 
WHERE emailAuteur = 'erwan@paaxio.com' AND statutSignalement = 'non_traite'
ORDER BY idSignalement DESC LIMIT 1;



-- ====== Vérification ======
-- 1. Ses playlists doivent être passées à 0 (Privée) 
SELECT idPlaylist, nomPlaylist, estPubliquePlaylist FROM playlist WHERE emailProprietaire = 'erwan@paaxio.com';

-- 2. Ses chansons doivent avoir le préfixe
SELECT idChanson, titreChanson FROM chanson WHERE emailPublicateur = 'erwan@paaxio.com';

-- 3. Ses participations doivent être vides 
SELECT * FROM participation WHERE emailArtisteParticipant = 'erwan@paaxio.com';


-- ===== Requête de nettoyage ======
-- 1. Supprimer le signalement de test 
DELETE FROM signalement WHERE emailAuteur = 'erwan@paaxio.com' AND motifSignalement = 'Test de sanction automatique'; 

-- 2. Remettre les playlists d'Erwan en public (selon les données d'origine, id 7 était publique)
UPDATE playlist SET estPubliquePlaylist = 1 WHERE emailProprietaire = 'erwan@paaxio.com' AND idPlaylist = 7; 

-- 3. Retirer le préfixe [SOUS REVISION] des titres 
UPDATE chanson SET titreChanson = REPLACE(titreChanson, '[SOUS REVISION] ', '') WHERE emailPublicateur = 'erwan@paaxio.com'; 

-- 4. Restaurer les participations supprimées (Données d'origine du script populate) 
INSERT INTO participation (idChanson, emailArtisteParticipant, typeParticipation, ordreParticipation) VALUES (6, 'erwan@paaxio.com', 'leader', 1), (12, 'erwan@paaxio.com', 'leader', 1);




-- 6. Incrémenter le nombre d'abonnés d’un artiste lors qu’un utilisateur s’abonne à un artiste
DELIMITER $$

CREATE TRIGGER after_abonnement_insert
AFTER INSERT ON abonnementArtiste
FOR EACH ROW
BEGIN
   UPDATE utilisateur
   SET nbAbonnesArtiste = nbAbonnesArtiste + 1
   WHERE emailUtilisateur = NEW.emailArtiste;
END$$


CREATE TRIGGER after_abonnement_delete
AFTER DELETE ON abonnementArtiste
FOR EACH ROW
BEGIN
   UPDATE utilisateur
   SET nbAbonnesArtiste = nbAbonnesArtiste - 1
   WHERE emailUtilisateur = OLD.emailArtiste;
END$$

DELIMITER ;


-- ======= Test Abonnement Artiste =======
-- Tim s'abonne (Le trigger doit passer nbAbonnesArtiste à 1)
INSERT INTO abonnementArtiste (emailAbonne, emailArtiste) 
VALUES ('tim@paaxio.com', 'erwan@paaxio.com');

-- Tim se désabonne (Le trigger doit repasser nbAbonnesArtiste à 0)
DELETE FROM abonnementArtiste 
WHERE emailAbonne = 'tim@paaxio.com' AND emailArtiste = 'erwan@paaxio.com';



-- ====== Vérification ======
-- Vérification initiale (Erwan devrait avoir 0 abonnés au départ dans le dataset)
SELECT pseudoUtilisateur, nbAbonnesArtiste 
FROM utilisateur WHERE emailUtilisateur = 'erwan@paaxio.com';


-- Vérification après ajout
SELECT 'Après Ajout (+1)' AS Status, pseudoUtilisateur, nbAbonnesArtiste 
FROM utilisateur WHERE emailUtilisateur = 'erwan@paaxio.com';


-- Vérification après suppression
SELECT 'Après Suppression (-1)' AS Status, pseudoUtilisateur, nbAbonnesArtiste 
FROM utilisateur WHERE emailUtilisateur = 'erwan@paaxio.com';



-- 7. Ajouter des point de fame lors ce qu’un utilisateur like une chanson d’un artiste
DELIMITER $$

CREATE TRIGGER after_like_insert
AFTER INSERT ON likeChanson
FOR EACH ROW
BEGIN
UPDATE utilisateur
SET pointsDeRenommeeArtiste = pointsDeRenommeeArtiste + 10
WHERE emailUtilisateur IN (
   SELECT emailArtisteParticipant
   FROM participation
   WHERE idChanson = NEW.idChanson
);
END$$


CREATE TRIGGER after_like_delete
AFTER DELETE ON likeChanson
FOR EACH ROW
BEGIN
UPDATE utilisateur
SET pointsDeRenommeeArtiste = pointsDeRenommeeArtiste - 10
WHERE emailUtilisateur IN (
   SELECT emailArtisteParticipant
   FROM participation
   WHERE idChanson = OLD.idChanson
);
END$$

DELIMITER ;



-- Tim like la chanson n°1
INSERT INTO likeChanson (emailUtilisateur, idChanson) 
VALUES ('tim@paaxio.com', 1);

-- Tim retire son like
DELETE FROM likeChanson 
WHERE emailUtilisateur = 'tim@paaxio.com' AND idChanson = 1;


-- ====== Vérification ======
-- Vérification initiale des points de Angel et Yohan
SELECT pseudoUtilisateur, pointsDeRenommeeArtiste 
FROM utilisateur WHERE emailUtilisateur IN ('angel@paaxio.com', 'yohan@paaxio.com');


-- Vérification après ajout like par Tim (Les deux doivent avoir gagné +10 points)
SELECT 'Après Like (+10)' AS Status, pseudoUtilisateur, pointsDeRenommeeArtiste 
FROM utilisateur WHERE emailUtilisateur IN ('angel@paaxio.com', 'yohan@paaxio.com');


-- Vérification après suppression like par Tim (Retour aux points initiaux)
SELECT 'Après Dislike (-10)' AS Status, pseudoUtilisateur, pointsDeRenommeeArtiste 
FROM utilisateur WHERE emailUtilisateur IN ('angel@paaxio.com', 'yohan@paaxio.com');



-- 8. Ajouter des point de fame lors ce qu’un artiste gagne une battle
DELIMITER $$

CREATE TRIGGER after_battle_finish
AFTER UPDATE ON battle
FOR EACH ROW
BEGIN
   DECLARE votesCreateur INT DEFAULT 0;
   DECLARE votesParticipant INT DEFAULT 0;
  
   IF NEW.statutBattle = 'terminee' AND OLD.statutBattle != 'terminee' THEN
      
       SELECT COUNT(*) INTO votesCreateur
       FROM vote
       WHERE idBattle = NEW.idBattle AND emailVotee = NEW.emailCreateurBattle;
      
       IF NEW.emailParticipantBattle IS NOT NULL THEN
           SELECT COUNT(*) INTO votesParticipant
           FROM vote
           WHERE idBattle = NEW.idBattle AND emailVotee = NEW.emailParticipantBattle;
       END IF;
      
       IF votesCreateur > votesParticipant THEN
           UPDATE utilisateur
           SET pointsDeRenommeeArtiste = pointsDeRenommeeArtiste + 50
           WHERE emailUtilisateur = NEW.emailCreateurBattle;
          
       ELSEIF votesParticipant > votesCreateur THEN
           UPDATE utilisateur
           SET pointsDeRenommeeArtiste = pointsDeRenommeeArtiste + 50
           WHERE emailUtilisateur = NEW.emailParticipantBattle;
       END IF;
      
   END IF;
END$$

DELIMITER ;



-- ======= Test Battle =======
-- 1. Création d'une nouvelle battle "Test Battle" (en cours)
INSERT INTO battle (idBattle, titreBattle, dateDebutBattle, dateFinBattle, statutBattle, emailCreateurBattle, emailParticipantBattle) 
VALUES (6, 'Choc des Titans', NOW(), NOW() + INTERVAL 1 DAY, 'en_cours', 'angel@paaxio.com', 'jarlin@paaxio.com');

-- 2. Ajout de votes (Angel reçoit 2 votes, Jarlin en reçoit 1)
INSERT INTO vote (emailVotant, idBattle, emailVotee) VALUES 
('tim@paaxio.com', 6, 'angel@paaxio.com'),
('erwan@paaxio.com', 6, 'angel@paaxio.com'),
('christopher@paaxio.com', 6, 'jarlin@paaxio.com');

-- 3. CLÔTURE DE LA BATTLE (C'est ici que le trigger se déclenche)
UPDATE battle SET statutBattle = 'terminee' WHERE idBattle = 6;



-- ===== Vérification ======
-- Vérification des points AVANT la fin de la battle
SELECT pseudoUtilisateur, pointsDeRenommeeArtiste 
FROM utilisateur WHERE emailUtilisateur = 'angel@paaxio.com';


-- Vérification FINALE (Angel a gagné, il doit avoir +50 points par rapport à avant)
SELECT 'Après Victoire (+50)' AS Status, pseudoUtilisateur, pointsDeRenommeeArtiste 
FROM utilisateur WHERE emailUtilisateur = 'angel@paaxio.com';


--9. Ajouter une image par défaut à l'utilisateur
DELIMITER $$

CREATE TRIGGER utilisateurPhotoDefaut
BEFORE INSERT ON utilisateur
FOR EACH ROW
BEGIN
   IF NEW.urlPhotoUtilisateur IS NULL OR NEW.urlPhotoUtilisateur = '' THEN
       SET NEW.urlPhotoUtilisateur = '/assets/default-user.png';
   END IF;
END$$


DELIMITER ;



-- ===== Test Photo par Défaut =====
INSERT INTO utilisateur (
   emailUtilisateur,
   nomUtilisateur,
   pseudoUtilisateur,
   motDePasseUtilisateur,
   dateDeNaissanceUtilisateur,
   roleUtilisateur,
   urlPhotoUtilisateur
) VALUES (
   'test_trigger1@paaxio.com',
   'Test Trigger Photo',
   'TestPhoto1',
   '$argon2id$v=19$m=65536,t=4,p=1$test$test',
   '2001-10-20',
   3,
   NULL
);



-- ===== Vérification ======
SELECT
   emailUtilisateur,
   nomUtilisateur,
   urlPhotoUtilisateur
FROM utilisateur
WHERE emailUtilisateur = 'test_trigger1@paaxio.com';


-- ===== Nettoyage ======
DELETE FROM utilisateur WHERE emailUtilisateur = 'test_trigger1@paaxio.com';



-- 10. Ajouter une pochette par défaut à l'album
DELIMITER $$

CREATE TRIGGER albumPochetteDefaut
BEFORE INSERT ON album
FOR EACH ROW
BEGIN
   IF NEW.urlPochetteAlbum IS NULL OR NEW.urlPochetteAlbum = '' THEN
       SET NEW.urlPochetteAlbum = '/assets/default-album.png';
   END IF;
END$$

DELIMITER ;



-- ===== Test Pochette par Défaut =====
INSERT INTO album (
   nomAlbum,
   dateSortieAlbum,
   urlPochetteAlbum,
   artisteAlbum
) VALUES (
   'Album Test Trigger Pochette',
   '2024-12-01',
   '',
   'angel@paaxio.com'
);


-- ===== Vérification ======
SELECT
   idAlbum,
   nomAlbum,
   urlPochetteAlbum
FROM album
WHERE nomAlbum = 'Album Test Trigger Pochette';


-- ===== Nettoyage ======
DELETE FROM album WHERE nomAlbum = 'Album Test Trigger Pochette';



-- 11. Anonymiser les votes d'un utilisateur supprimé 
DELIMITER $$

CREATE TRIGGER utilisateurAnonymiserVotes
BEFORE DELETE ON utilisateur
FOR EACH ROW
BEGIN
   UPDATE vote
   SET emailVotant = NULL
   WHERE emailVotant = OLD.emailUtilisateur;


   UPDATE vote
   SET emailVotee = NULL
   WHERE emailVotee = OLD.emailUtilisateur;
END$$

DELIMITER ;


-- 12. Vérifier que l'email voté correspond à l'un des deux participants de la battle correspondante
DELIMITER $$

CREATE TRIGGER voteVerifierEmailVotee
BEFORE INSERT ON vote
FOR EACH ROW
BEGIN
   DECLARE createur VARCHAR(191);
   DECLARE participant VARCHAR(191);


   SELECT emailCreateurBattle, emailParticipantBattle
   INTO createur, participant
   FROM battle
   WHERE idBattle = NEW.idBattle;


   IF NOT (NEW.emailVotee = createur OR NEW.emailVotee = participant) THEN
       SIGNAL SQLSTATE '45000'
           SET MESSAGE_TEXT = "L'utilisateur voté doit être l'un des participants de la battle.";
   END IF;
END$$

DELIMITER ;


-- Test d'insertion d'un vote INVALIDE (battle déjà existante 1)
-- Emails pour battle 1: angel@paaxio.com ou yohan@paaxio.com


INSERT INTO vote (emailVotant, idBattle, emailVotee, dateVote)
VALUES ('tim@paaxio.com', 1, 'jarlin@paaxio.com', NOW());


SELECT
  'TEST VOTE INVALIDE' AS test_type,
  emailVotant,
  idBattle,
  emailVotee
FROM vote
WHERE emailVotant = 'tim@paaxio.com' AND idBattle = 1;


-- Test d'insertion d'un vote VALIDE (battle déjà existante 1)
INSERT INTO vote (emailVotant, idBattle, emailVotee, dateVote)
VALUES ('tim@paaxio.com', 1, 'angel@paaxio.com', NOW());

SELECT
   'TEST VOTE VALIDE' AS test_type,
   emailVotant,
   idBattle,
   emailVotee
FROM vote
WHERE emailVotant = 'tim@paaxio.com' AND idBattle = 1;



-- 13. Supprimer un token de réinitialisation de mot de passe lorsque estUtilise passe à 1
DELIMITER $$

CREATE TRIGGER passwordResetSupprimerSiUtilise
AFTER UPDATE ON passwordResetToken
FOR EACH ROW
BEGIN
   IF NEW.estUtilise = 1 THEN
       DELETE FROM passwordResetToken WHERE idToken = NEW.idToken;
   END IF;
END$$

DELIMITER ;


-- ======= Test et vérification =======
INSERT INTO passwordResetToken (
   idToken,
   token,
   emailUtilisateur,
   dateCreation,
   dateExpiration,
   estUtilise
) VALUES (
   99,
   'token_test_trigger5_unique_abc123',
   'admin@paaxio.com',
   NOW(),
   DATE_ADD(NOW(), INTERVAL 1 HOUR),
   0
);



UPDATE passwordResetToken
SET estUtilise = 1
WHERE idToken = 99;