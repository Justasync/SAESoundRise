-- 1. Définition des rôles selon la structure de gestion du projet
CREATE ROLE IF NOT EXISTS 'db_administrateur', 'site_administrateur', 'site_user';


-- =====================================================
-- 2. PRIVILEGE ATTRIBUTION
-- =====================================================
-- Rôle : db_administrateur (Gestion infrastructure et structure BDD)
GRANT ALL PRIVILEGES ON paaxio_db.* TO 'db_administrateur' WITH GRANT OPTION; 
-- Rôle : site_administrateur (Tâches quotidiennes et modération) 
GRANT SELECT, INSERT, UPDATE, DELETE ON `paaxio_db`.`genre` TO 'site_administrateur'; 
GRANT SELECT ON `paaxio_db`.`role` TO 'site_administrateur'; 
GRANT SELECT, INSERT, UPDATE, DELETE ON `paaxio_db`.`utilisateur` TO 'site_administrateur'; 
GRANT SELECT, UPDATE, DELETE ON `paaxio_db`.`album` TO 'site_administrateur'; 
GRANT SELECT, UPDATE, DELETE ON `paaxio_db`.`chanson` TO 'site_administrateur'; 
GRANT SELECT, DELETE ON `paaxio_db`.`message` TO 'site_administrateur'; 
GRANT SELECT, INSERT, UPDATE, DELETE ON `paaxio_db`.`signalement` TO 'site_administrateur'; 
GRANT SELECT, UPDATE, DELETE ON `paaxio_db`.`playlist` TO 'site_administrateur'; 
GRANT SELECT, INSERT ON `paaxio_db`.`paiement` TO 'site_administrateur'; 
GRANT SELECT, DELETE ON `paaxio_db`.`newsletter` TO 'site_administrateur'; 
GRANT SELECT, UPDATE, DELETE ON `paaxio_db`.`battle` TO 'site_administrateur';
GRANT SELECT, DELETE ON `paaxio_db`.`vote` TO 'site_administrateur'; 
-- Rôle : site_user (Utilisation standard du site) [cite: 9, 35]
GRANT SELECT ON `paaxio_db`.`genre` TO 'site_user'; 
GRANT SELECT ON `paaxio_db`.`role` TO 'site_user'; 
GRANT SELECT, INSERT ON `paaxio_db`.`utilisateur` TO 'site_user'; 
-- Restriction spécifique : Mise à jour uniquement des colonnes de profil
GRANT UPDATE (nomUtilisateur, pseudoUtilisateur, motDePasseUtilisateur, dateDeNaissanceUtilisateur, genreUtilisateur, descriptionUtilisateur, siteWebUtilisateur, urlPhotoUtilisateur)
ON `paaxio_db`.`utilisateur` TO 'site_user'; 
GRANT SELECT, INSERT, UPDATE, DELETE ON `paaxio_db`.`album` TO 'site_user'; 
GRANT SELECT, INSERT, UPDATE, DELETE ON `paaxio_db`.`chanson` TO 'site_user'; 
GRANT SELECT, INSERT, DELETE ON `paaxio_db`.`likeChanson` TO 'site_user'; 
GRANT SELECT, INSERT ON `paaxio_db`.`message` TO 'site_user'; 
GRANT SELECT, INSERT ON `paaxio_db`.`signalement` TO 'site_user'; 
GRANT SELECT, INSERT, UPDATE, DELETE ON `paaxio_db`.`playlist` TO 'site_user'; 
GRANT SELECT, INSERT, DELETE ON `paaxio_db`.`chansonPlaylist` TO 'site_user'; 
GRANT SELECT, INSERT, DELETE ON `paaxio_db`.`passwordResetToken` TO 'site_user'; 


-- =====================================================
-- 3. USER CREATION & ROLE ASSIGNMENT
-- =====================================================
-- db_administrateur User
CREATE USER IF NOT EXISTS 'db_administrateur'@'localhost' IDENTIFIED BY 'db_administrateur_123*'; 
GRANT 'db_administrateur' TO 'db_administrateur'@'localhost';
SET DEFAULT ROLE 'db_administrateur' TO 'db_administrateur'@'localhost';
-- site_administrateur User
CREATE USER IF NOT EXISTS 'site_administrateur'@'localhost' IDENTIFIED BY 'site_administrateur_123*'; 
GRANT 'site_administrateur' TO 'site_administrateur'@'localhost';
SET DEFAULT ROLE 'site_administrateur' TO 'site_administrateur'@'localhost';
-- site_user User
CREATE USER IF NOT EXISTS 'site_user'@'localhost' IDENTIFIED BY 'site_user_123*'; 
GRANT 'site_user' TO 'site_user'@'localhost';
SET DEFAULT ROLE 'site_user' TO 'site_user'@'localhost';



-- Application des changements
FLUSH PRIVILEGES; 



-- =====================================================
-- 4. TESTS DE VERIFICATION
-- Test 1 (Se connecter en tant que site_user)
USE paaxio_db;
DROP TABLE paiement;
-- Erreur attendue : DROP command denied to user 'site_user'@'localhost' for table 'paiement'


-- Test 2 (Se connecter en tant que site_user)
USE paaxio_db;
UPDATE utilisateur
SET pointsDeRenommeeArtiste = 1000000
WHERE emailUtilisateur = 'yohan@paaxio.com';
-- Erreur attendue : UPDATE command denied to user 'site_user'@'localhost' for column 'pointsDeRenommeeArtiste' in table 'utilisateur'


-- Test 3 (Se connecter en tant que site_administrateur)
USE paaxio_db;
UPDATE signalement
SET statutSignalement = 'traite', emailAdminTraitant = 'admin@paaxio.com'
WHERE idSignalement = 5;
-- Résultat : Query OK, 1 row affected.


-- Test 4 (Se connecter en tant que db_administrateur)
USE paaxio_db;
ALTER TABLE genre ADD COLUMN descriptionGenre VARCHAR(255);
-- Résultat : Query OK, 0 rows affected.
-- Vérification
DESCRIBE genre;


