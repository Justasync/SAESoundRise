<?php

/**
 * @file passwordResetToken.dao.php
 * @brief DAO pour la gestion des tokens de réinitialisation de mot de passe.
 * 
 * Ce fichier contient la classe PasswordResetTokenDAO qui gère toutes les
 * opérations de base de données relatives aux tokens de réinitialisation
 * de mot de passe : création, recherche, validation et suppression.
 * 
 * @package Paaxio\Modeles
 */

/**
 * @class PasswordResetTokenDAO
 * @brief Data Access Object pour les tokens de réinitialisation de mot de passe.
 * 
 * Cette classe fournit les méthodes CRUD et utilitaires pour manipuler
 * les tokens de réinitialisation de mot de passe en base de données :
 * - Création de nouveaux tokens
 * - Recherche par token ou email
 * - Validation et expiration des tokens
 * - Mise à jour du mot de passe utilisateur
 * - Nettoyage des tokens expirés
 */
class PasswordResetTokenDAO
{
    /**
     * @var PDO|null $pdo Instance PDO pour la connexion à la base de données.
     */
    private ?PDO $pdo;

    /**
     * @brief Constructeur de la classe PasswordResetTokenDAO.
     * 
     * @param PDO|null $pdo Instance PDO pour la connexion à la base de données.
     */
    public function __construct(?PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @brief Crée un nouveau token de réinitialisation de mot de passe.
     * 
     * Invalide tous les tokens précédents de l'utilisateur avant d'en créer un nouveau.
     * Le token est valide pendant 1 heure après sa création.
     * 
     * @param string $email Adresse email de l'utilisateur.
     * @return PasswordResetToken|null Le token créé ou null en cas d'échec.
     */
    public function create(string $email): ?PasswordResetToken
    {
        // Invalider tous les tokens existants pour cet utilisateur
        $this->invalidateTokensForUser($email);

        // Générer un nouveau token
        $tokenValue = PasswordResetToken::genererToken();
        $dateCreation = new DateTime();
        $dateExpiration = (clone $dateCreation)->modify('+1 hour');

        $sql = "INSERT INTO passwordResetToken (token, emailUtilisateur, dateCreation, dateExpiration, estUtilise)
                VALUES (:token, :email, :dateCreation, :dateExpiration, 0)";

        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute([
            ':token' => $tokenValue,
            ':email' => $email,
            ':dateCreation' => $dateCreation->format('Y-m-d H:i:s'),
            ':dateExpiration' => $dateExpiration->format('Y-m-d H:i:s')
        ]);

        if ($result) {
            return new PasswordResetToken(
                (int)$this->pdo->lastInsertId(),
                $tokenValue,
                $email,
                $dateCreation,
                $dateExpiration,
                false
            );
        }

        return null;
    }

    /**
     * @brief Recherche un token par sa valeur.
     * 
     * @param string $token La valeur du token à rechercher.
     * @return PasswordResetToken|null Le token trouvé ou null si inexistant.
     */
    public function findByToken(string $token): ?PasswordResetToken
    {
        $sql = "SELECT * FROM passwordResetToken WHERE token = :token";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':token' => $token]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return $this->hydrate($row);
        }

        return null;
    }

    /**
     * @brief Recherche un token valide par sa valeur.
     * 
     * Un token est considéré comme valide si :
     * - Il existe en base de données
     * - Il n'a pas été utilisé
     * - Sa date d'expiration n'est pas dépassée
     * 
     * @param string $token La valeur du token à rechercher.
     * @return PasswordResetToken|null Le token valide trouvé ou null.
     */
    public function findValidToken(string $token): ?PasswordResetToken
    {
        $sql = "SELECT * FROM passwordResetToken 
                WHERE token = :token 
                AND estUtilise = 0 
                AND dateExpiration > NOW()";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':token' => $token]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return $this->hydrate($row);
        }

        return null;
    }

    /**
     * @brief Recherche tous les tokens actifs d'un utilisateur.
     * 
     * @param string $email Adresse email de l'utilisateur.
     * @return array Liste des tokens actifs de l'utilisateur.
     */
    public function findActiveTokensByEmail(string $email): array
    {
        $sql = "SELECT * FROM passwordResetToken 
                WHERE emailUtilisateur = :email 
                AND estUtilise = 0 
                AND dateExpiration > NOW()
                ORDER BY dateCreation DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':email' => $email]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $this->hydrateAll($rows);
    }

    /**
     * @brief Marque un token comme utilisé.
     * 
     * Cette méthode est appelée après qu'un utilisateur a réinitialisé
     * son mot de passe avec succès. Le token ne peut plus être réutilisé.
     * 
     * @param string $token La valeur du token à marquer comme utilisé.
     * @return bool True si la mise à jour a réussi, false sinon.
     */
    public function markAsUsed(string $token): bool
    {
        $sql = "UPDATE passwordResetToken SET estUtilise = 1 WHERE token = :token";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':token' => $token]);
    }

    /**
     * @brief Invalide tous les tokens d'un utilisateur.
     * 
     * Marque tous les tokens non utilisés de l'utilisateur comme utilisés.
     * Utile pour s'assurer qu'il n'y a qu'un seul token actif par utilisateur.
     * 
     * @param string $email Adresse email de l'utilisateur.
     * @return bool True si l'opération a réussi, false sinon.
     */
    public function invalidateTokensForUser(string $email): bool
    {
        $sql = "UPDATE passwordResetToken SET estUtilise = 1 
                WHERE emailUtilisateur = :email AND estUtilise = 0";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':email' => $email]);
    }

    /**
     * @brief Supprime les tokens expirés de la base de données.
     * 
     * Nettoie la table en supprimant tous les tokens dont la date
     * d'expiration est dépassée. À exécuter périodiquement via un cron.
     * 
     * @return int Le nombre de tokens supprimés.
     */
    public function deleteExpiredTokens(): int
    {
        $sql = "DELETE FROM passwordResetToken WHERE dateExpiration < NOW()";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->rowCount();
    }

    /**
     * @brief Met à jour le mot de passe d'un utilisateur.
     * 
     * Utilise l'algorithme ARGON2ID pour le hachage du nouveau mot de passe.
     * Le mot de passe doit être fourni en clair et sera haché automatiquement.
     * 
     * @param string $email Adresse email de l'utilisateur.
     * @param string $nouveauMotDePasse Le nouveau mot de passe en clair.
     * @return bool True si la mise à jour a réussi, false sinon.
     */
    public function updatePassword(string $email, string $nouveauMotDePasse): bool
    {
        $hashedPassword = password_hash($nouveauMotDePasse, PASSWORD_ARGON2ID);

        $sql = "UPDATE utilisateur SET motDePasseUtilisateur = :password 
                WHERE emailUtilisateur = :email";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':password' => $hashedPassword,
            ':email' => $email
        ]);
    }

    /**
     * @brief Hydrate un tableau de données en objet PasswordResetToken.
     * 
     * @param array $row Tableau associatif contenant les données du token.
     * @return PasswordResetToken L'objet PasswordResetToken hydraté.
     */
    private function hydrate(array $row): PasswordResetToken
    {
        return new PasswordResetToken(
            (int)$row['idToken'],
            $row['token'],
            $row['emailUtilisateur'],
            new DateTime($row['dateCreation']),
            new DateTime($row['dateExpiration']),
            (bool)$row['estUtilise']
        );
    }

    /**
     * @brief Hydrate un tableau de lignes en tableau d'objets PasswordResetToken.
     * 
     * @param array $rows Tableau de tableaux associatifs.
     * @return array Tableau d'objets PasswordResetToken.
     */
    private function hydrateAll(array $rows): array
    {
        $tokens = [];
        foreach ($rows as $row) {
            $tokens[] = $this->hydrate($row);
        }
        return $tokens;
    }

    /**
     * @brief Récupère l'instance PDO.
     * @return PDO|null L'instance PDO.
     */
    public function getPdo(): ?PDO
    {
        return $this->pdo;
    }

    /**
     * @brief Définit l'instance PDO.
     * @param PDO|null $pdo La nouvelle instance PDO.
     * @return void
     */
    public function setPdo(?PDO $pdo): void
    {
        $this->pdo = $pdo;
    }
}

