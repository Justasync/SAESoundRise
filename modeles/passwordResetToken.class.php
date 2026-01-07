<?php

/**
 * @file passwordResetToken.class.php
 * @brief Classe représentant un token de réinitialisation de mot de passe.
 * 
 * Cette classe modélise un token de réinitialisation de mot de passe
 * utilisé pour permettre aux utilisateurs de récupérer l'accès à leur compte.
 * Les tokens ont une durée de validité limitée (1 heure).
 * 
 */

/**
 * @class PasswordResetToken
 * @brief Entité représentant un token de réinitialisation de mot de passe.
 * 
 * Cette classe encapsule toutes les informations relatives à un token
 * de réinitialisation de mot de passe :
 * - Identifiant unique du token
 * - Valeur du token (chaîne sécurisée)
 * - Email de l'utilisateur associé
 * - Dates de création et d'expiration
 * - État d'utilisation du token
 */
class PasswordResetToken
{
    /**
     * @var int|null $idToken Identifiant unique du token en base de données.
     */
    private ?int $idToken;

    /**
     * @var string $token Valeur du token de réinitialisation (chaîne hexadécimale sécurisée).
     */
    private string $token;

    /**
     * @var string $emailUtilisateur Adresse email de l'utilisateur associé au token.
     */
    private string $emailUtilisateur;

    /**
     * @var DateTime $dateCreation Date et heure de création du token.
     */
    private DateTime $dateCreation;

    /**
     * @var DateTime $dateExpiration Date et heure d'expiration du token.
     */
    private DateTime $dateExpiration;

    /**
     * @var bool $estUtilise Indique si le token a déjà été utilisé.
     */
    private bool $estUtilise;

    /**
     * @brief Constructeur de la classe PasswordResetToken.
     * 
     * Initialise un nouveau token de réinitialisation de mot de passe
     * avec les valeurs fournies ou des valeurs par défaut.
     * 
     * @param int|null $idToken Identifiant unique du token (null pour un nouveau token).
     * @param string $token Valeur du token de réinitialisation.
     * @param string $emailUtilisateur Email de l'utilisateur associé.
     * @param DateTime|null $dateCreation Date de création (maintenant par défaut).
     * @param DateTime|null $dateExpiration Date d'expiration (1 heure après création par défaut).
     * @param bool $estUtilise État d'utilisation du token (false par défaut).
     */
    public function __construct(
        ?int $idToken = null,
        string $token = '',
        string $emailUtilisateur = '',
        ?DateTime $dateCreation = null,
        ?DateTime $dateExpiration = null,
        bool $estUtilise = false
    ) {
        $this->idToken = $idToken;
        $this->token = $token;
        $this->emailUtilisateur = $emailUtilisateur;
        $this->dateCreation = $dateCreation ?? new DateTime();
        $this->dateExpiration = $dateExpiration ?? (new DateTime())->modify('+1 hour');
        $this->estUtilise = $estUtilise;
    }

    /**
     * @brief Récupère l'identifiant du token.
     * @return int|null L'identifiant du token ou null si non défini.
     */
    public function getIdToken(): ?int
    {
        return $this->idToken;
    }

    /**
     * @brief Définit l'identifiant du token.
     * @param int|null $idToken Le nouvel identifiant du token.
     * @return void
     */
    public function setIdToken(?int $idToken): void
    {
        $this->idToken = $idToken;
    }

    /**
     * @brief Récupère la valeur du token.
     * @return string La valeur du token de réinitialisation.
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @brief Définit la valeur du token.
     * @param string $token La nouvelle valeur du token.
     * @return void
     */
    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    /**
     * @brief Récupère l'email de l'utilisateur associé.
     * @return string L'adresse email de l'utilisateur.
     */
    public function getEmailUtilisateur(): string
    {
        return $this->emailUtilisateur;
    }

    /**
     * @brief Définit l'email de l'utilisateur associé.
     * @param string $emailUtilisateur La nouvelle adresse email.
     * @return void
     */
    public function setEmailUtilisateur(string $emailUtilisateur): void
    {
        $this->emailUtilisateur = $emailUtilisateur;
    }

    /**
     * @brief Récupère la date de création du token.
     * @return DateTime La date et heure de création.
     */
    public function getDateCreation(): DateTime
    {
        return $this->dateCreation;
    }

    /**
     * @brief Définit la date de création du token.
     * @param DateTime $dateCreation La nouvelle date de création.
     * @return void
     */
    public function setDateCreation(DateTime $dateCreation): void
    {
        $this->dateCreation = $dateCreation;
    }

    /**
     * @brief Récupère la date d'expiration du token.
     * @return DateTime La date et heure d'expiration.
     */
    public function getDateExpiration(): DateTime
    {
        return $this->dateExpiration;
    }

    /**
     * @brief Définit la date d'expiration du token.
     * @param DateTime $dateExpiration La nouvelle date d'expiration.
     * @return void
     */
    public function setDateExpiration(DateTime $dateExpiration): void
    {
        $this->dateExpiration = $dateExpiration;
    }

    /**
     * @brief Vérifie si le token a été utilisé.
     * @return bool True si le token a été utilisé, false sinon.
     */
    public function getEstUtilise(): bool
    {
        return $this->estUtilise;
    }

    /**
     * @brief Définit l'état d'utilisation du token.
     * @param bool $estUtilise Le nouvel état d'utilisation.
     * @return void
     */
    public function setEstUtilise(bool $estUtilise): void
    {
        $this->estUtilise = $estUtilise;
    }

    /**
     * @brief Vérifie si le token est encore valide.
     * 
     * Un token est considéré comme valide si :
     * - Il n'a pas encore été utilisé
     * - La date d'expiration n'est pas dépassée
     * 
     * @return bool True si le token est valide, false sinon.
     */
    public function estValide(): bool
    {
        if ($this->estUtilise) {
            return false;
        }

        $maintenant = new DateTime();
        return $maintenant < $this->dateExpiration;
    }

    /**
     * @brief Génère un nouveau token sécurisé.
     * 
     * Utilise random_bytes pour générer un token cryptographiquement sécurisé
     * de 32 octets (64 caractères hexadécimaux).
     * 
     * @return string Le token généré sous forme hexadécimale.
     */
    public static function genererToken(): string
    {
        return bin2hex(random_bytes(32));
    }
}
