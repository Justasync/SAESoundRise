<?php

/**
 * @file utilisateur.class.php
 * @brief Ce fichier contient la classe Utilisateur pour représenter un utilisateur.
 */

/**
 * @enum StatutUtilisateur Énumération des statuts possibles pour un utilisateur
 * @case Actif L'utilisateur est actif.
 * @case Suspendu L'utilisateur est suspendu.
 * @case Supprimee L'utilisateur est supprimé.
 */
enum StatutUtilisateur: string
{
    case Actif = 'actif';
    case Suspendu = 'suspendu';
    case Supprimee = 'supprimee';
}

/**
 * @enum StatutAbonnement Énumération des statuts possibles pour un abonnement utilisateur
 * @case Actif L'abonnement est actif.
 * @case Expire L'abonnement a expiré.
 * @case Annule L'abonnement a été annulé.
 * @case Inactif L'utilisateur n'a pas d'abonnement actif.
 */
enum StatutAbonnement: string
{
    case Actif = 'actif';
    case Expire = 'expire';
    case Annule = 'annule';
    case Inactif = 'inactif';
}

class Utilisateur
{
    /**
     * @var string|null $emailUtilisateur L'adresse email de l'utilisateur.
     */
    private ?string $emailUtilisateur;
    /**
     * @var string|null $nomUtilisateur Le nom de l'utilisateur.
     */
    private ?string $nomUtilisateur;
    /**
     * @var string|null $pseudoUtilisateur Le pseudo de l'utilisateur.
     */
    private ?string $pseudoUtilisateur;
    /**
     * @var string|null $motDePasseUtilisateur Le mot de passe de l'utilisateur.
     */
    private ?string $motDePasseUtilisateur;
    /**
     * @var DateTime|null $dateDeNaissanceUtilisateur La date de naissance de l'utilisateur.
     */
    private ?DateTime $dateDeNaissanceUtilisateur;
    /**
     * @var DateTime|null $dateInscriptionUtilisateur La date d'inscription de l'utilisateur.
     */
    private ?DateTime $dateInscriptionUtilisateur;
    /**
     * @var StatutUtilisateur|null $statutUtilisateur Le statut de l'utilisateur.
     */
    private ?StatutUtilisateur $statutUtilisateur;
    /**
     * @var Genre|null $genreUtilisateur Le genre musical préféré de l'utilisateur.
     */
    private ?Genre $genreUtilisateur;
    /**
     * @var bool|null $estAbonnee Indique si l'utilisateur est abonné.
     */
    private ?bool $estAbonnee;
    /**
     * @var string|null $descriptionUtilisateur La description de l'utilisateur.
     */
    private ?string $descriptionUtilisateur;
    /**
     * @var string|null $siteWebUtilisateur Le site web de l'utilisateur.
     */
    private ?string $siteWebUtilisateur;
    /**
     * @var StatutAbonnement|null $statutAbonnement Le statut de l'abonnement de l'utilisateur.
     */
    private ?StatutAbonnement $statutAbonnement;
    /**
     * @var DateTime|null $dateDebutAbonnement La date de début de l'abonnement.
     */
    private ?DateTime $dateDebutAbonnement;
    /**
     * @var DateTime|null $dateFinAbonnement La date de fin de l'abonnement.
     */
    private ?DateTime $dateFinAbonnement;
    /**
     * @var int|null $pointsDeRenommeeArtiste Les points de renommée de l'artiste.
     */
    private ?int $pointsDeRenommeeArtiste;
    /**
     * @var int|null $nbAbonnesArtiste Le nombre d'abonnés de l'artiste.
     */
    private ?int $nbAbonnesArtiste;
    /**
     * @var string|null $urlPhotoUtilisateur L'URL de la photo de profil de l'utilisateur.
     */
    private ?string $urlPhotoUtilisateur;
    /**
     * @var Role|null $roleUtilisateur Le rôle de l'utilisateur.
     */
    private ?Role $roleUtilisateur;

    /**
     * Constructeur de la classe Utilisateur.
     * @param string|null $emailUtilisateur L'adresse email.
     * @param string|null $nomUtilisateur Le nom.
     * @param string|null $pseudoUtilisateur Le pseudo.
     * @param string|null $motDePasseUtilisateur Le mot de passe.
     * @param DateTime|null $dateDeNaissanceUtilisateur La date de naissance.
     * @param DateTime|null $dateInscriptionUtilisateur La date d'inscription.
     * @param StatutUtilisateur|null $statutUtilisateur Le statut.
     * @param Genre|null $genreUtilisateur Le genre musical préféré.
     * @param bool|null $estAbonnee Indique si l'utilisateur est abonné.
     * @param string|null $descriptionUtilisateur La description.
     * @param string|null $siteWebUtilisateur Le site web.
     * @param StatutAbonnement|null $statutAbonnement Le statut de l'abonnement.
     * @param DateTime|null $dateDebutAbonnement La date de début de l'abonnement.
     * @param DateTime|null $dateFinAbonnement La date de fin de l'abonnement.
     * @param int|null $pointsDeRenommeeArtiste Les points de renommée de l'artiste.
     * @param int|null $nbAbonnesArtiste Le nombre d'abonnés de l'artiste.
     * @param string|null $urlPhotoUtilisateur L'URL de la photo de profil.
     * @param Role|null $roleUtilisateur Le rôle de l'utilisateur.
     */
    public function __construct(
        ?string $emailUtilisateur = null,
        ?string $nomUtilisateur = null,
        ?string $pseudoUtilisateur = null,
        ?string $motDePasseUtilisateur = null,
        ?DateTime $dateDeNaissanceUtilisateur = null,
        ?DateTime $dateInscriptionUtilisateur = null,
        ?StatutUtilisateur $statutUtilisateur = null,
        ?Genre $genreUtilisateur = null,
        ?bool $estAbonnee = null,
        ?string $descriptionUtilisateur = null,
        ?string $siteWebUtilisateur = null,
        ?StatutAbonnement $statutAbonnement = null,
        ?DateTime $dateDebutAbonnement = null,
        ?DateTime $dateFinAbonnement = null,
        ?int $pointsDeRenommeeArtiste = null,
        ?int $nbAbonnesArtiste = null,
        ?string $urlPhotoUtilisateur = null,
        ?Role $roleUtilisateur = null
    ) {
        $this->setEmailUtilisateur($emailUtilisateur);
        $this->setNomUtilisateur($nomUtilisateur);
        $this->setPseudoUtilisateur($pseudoUtilisateur);
        $this->setMotDePasseUtilisateur($motDePasseUtilisateur);
        $this->setDateDeNaissanceUtilisateur($dateDeNaissanceUtilisateur);
        $this->setDateInscriptionUtilisateur($dateInscriptionUtilisateur);
        $this->setStatutUtilisateur($statutUtilisateur);
        $this->setGenreUtilisateur($genreUtilisateur);
        $this->setEstAbonnee($estAbonnee);
        $this->setDescriptionUtilisateur($descriptionUtilisateur);
        $this->setSiteWebUtilisateur($siteWebUtilisateur);
        $this->setStatutAbonnement($statutAbonnement);
        $this->setDateDebutAbonnement($dateDebutAbonnement);
        $this->setDateFinAbonnement($dateFinAbonnement);
        $this->setPointsDeRenommeeArtiste($pointsDeRenommeeArtiste);
        $this->setNbAbonnesArtiste($nbAbonnesArtiste);
        $this->seturlPhotoUtilisateur($urlPhotoUtilisateur);
        $this->setRoleUtilisateur($roleUtilisateur);
    }

    /**
     * Getter pour le genre musical préféré
     * @return Genre|null Le genre musical préféré. 
     */
    public function getGenreUtilisateur(): ?Genre
    {
        return $this->genreUtilisateur;
    }

    /**
     * Setter pour le genre musical préféré
     * @param Genre|null $genreUtilisateur Le genre musical préféré.
     * @return void
     */
    public function setGenreUtilisateur($genreUtilisateur): void 
    {
        $this->genreUtilisateur = $genreUtilisateur;
    }

    /**
     * Getter pour le nom de l'utilisateur
     * @return string|null Le nom de l'utilisateur.
     */
    public function getNomUtilisateur(): ?string
    {
        return $this->nomUtilisateur;
    }

    /**
     * Setter pour le nom de l'utilisateur
     * @param string|null $nomUtilisateur Le nom de l'utilisateur.
     * @return void 
     */
    public function setNomUtilisateur($nomUtilisateur): void
    {
        $this->nomUtilisateur = $nomUtilisateur;
    }

    /**
     * Getter pour l'email de l'utilisateur
     * @return string|null L'adresse email de l'utilisateur.
     */
    public function getEmailUtilisateur(): ?string
    {
        return $this->emailUtilisateur;
    }

    /**
     * Setter pour l'email de l'utilisateur
     * @param string|null $emailUtilisateur L'adresse email de l'utilisateur.
     * @return void
     */
    public function setEmailUtilisateur($emailUtilisateur): void
    {
        $this->emailUtilisateur = $emailUtilisateur;
    }

    /**
     * Getter pour le pseudo de l'utilisateur
     * @return string|null Le pseudo de l'utilisateur.
     */
    public function getPseudoUtilisateur(): ?string
    {
        return $this->pseudoUtilisateur;
    }

    /**
     * Setter pour le pseudo de l'utilisateur
     * @param string|null $pseudoUtilisateur Le pseudo de l'utilisateur.
     * @return void
     */
    public function setPseudoUtilisateur($pseudoUtilisateur): void
    {
        $this->pseudoUtilisateur = $pseudoUtilisateur;
    }

    /**
     * Getter pour le mot de passe de l'utilisateur
     * @return string|null Le mot de passe de l'utilisateur.
     */
    public function getMotDePasseUtilisateur(): ?string
    {
        return $this->motDePasseUtilisateur;
    }

    /**
     * Setter pour le mot de passe de l'utilisateur
     * @param string|null $motDePasseUtilisateur Le mot de passe de l'utilisateur.
     * @return void
     */
    public function setMotDePasseUtilisateur($motDePasseUtilisateur): void
    {
        $this->motDePasseUtilisateur = $motDePasseUtilisateur;
    }

    /**
     * Getter pour la date de naissance de l'utilisateur
     * @return DateTime|null La date de naissance de l'utilisateur.
     */
    public function getDateDeNaissanceUtilisateur(): ?DateTime
    {
        return $this->dateDeNaissanceUtilisateur;
    }

    /**
     * Setter pour la date de naissance de l'utilisateur
     * @param DateTime|null $dateDeNaissanceUtilisateur La date de naissance de l'utilisateur.
     * @return void
     */
    public function setDateDeNaissanceUtilisateur($dateDeNaissanceUtilisateur): void
    {
        $this->dateDeNaissanceUtilisateur = $dateDeNaissanceUtilisateur;
    }

    /**
     * Getter pour la date d'inscription de l'utilisateur
     * @return DateTime|null La date d'inscription de l'utilisateur.
     */
    public function getDateInscriptionUtilisateur(): ?DateTime
    {
        return $this->dateInscriptionUtilisateur;
    }

    /**
     * Setter pour la date d'inscription de l'utilisateur
     * @param DateTime|null $dateInscriptionUtilisateur La date d'inscription de l'utilisateur
     * @return void
     */
    public function setDateInscriptionUtilisateur($dateInscriptionUtilisateur): void
    {
        $this->dateInscriptionUtilisateur = $dateInscriptionUtilisateur;
    }

    /**
     * Getter pour le statut de l'utilisateur
     * @return string|null Le statut de l'utilisateur.
     */
    public function getStatutUtilisateur(): ?StatutUtilisateur
    {
        return $this->statutUtilisateur;
    }

    /**
     * Setter pour le statut de l'utilisateur
     * @param StatutUtilisateur|null $statutUtilisateur Le statut de l'utilisateur.
     * @return void
     */
    public function setStatutUtilisateur($statutUtilisateur): void
    {
        $this->statutUtilisateur = $statutUtilisateur;
    }

    /**
     * Getter pour estAbonnee
     * @return bool|null L'état d'abonnement de l'utilisateur.
     */
    public function getEstAbonnee(): ?bool
    {
        return $this->estAbonnee;
    }

    /**
     * Setter pour estAbonnee
     * @param bool|null $estAbonnee L'état d'abonnement de l'utilisateur
     * @return void
     */
    public function setEstAbonnee($estAbonnee): void
    {
        $this->estAbonnee = $estAbonnee;
    }

    /**
     * Getter pour descriptionUtilisateur
     * @return string|null La description de l'utilisateur.
     */
    public function getDescriptionUtilisateur(): ?string
    {
        return $this->descriptionUtilisateur;
    }

    /**
     * Setter pour descriptionUtilisateur
     * @param string|null $descriptionUtilisateur La description de l'utilisateur.
     * @return void
     */
    public function setDescriptionUtilisateur($descriptionUtilisateur): void
    {
        $this->descriptionUtilisateur = $descriptionUtilisateur;
    }

    /**
     * Getter pour siteWebUtilisateur
     * @return string|null Le site web de l'utilisateur.
     */
    public function getSiteWebUtilisateur(): ?string
    {
        return $this->siteWebUtilisateur;
    }

    /**
     * Setter pour siteWebUtilisateur
     * @param string|null $siteWebUtilisateur Le site web de l'utilisateur.
     * @return void
     */
    public function setSiteWebUtilisateur($siteWebUtilisateur): void
    {
        $this->siteWebUtilisateur = $siteWebUtilisateur;
    }

    /**
     * Getter pour statutAbonnement
     * @return StatutAbonnement|null Le statut de l'abonnement.
     */
    public function getStatutAbonnement(): ?StatutAbonnement
    {
        return $this->statutAbonnement;
    }

    /**
     * Setter pour statutAbonnement
     * @param StatutAbonnement|null $statutAbonnement Le statut de l'abonnement.
     * @return void
     */
    public function setStatutAbonnement($statutAbonnement): void
    {
        $this->statutAbonnement = $statutAbonnement;
    }

    /**
     * Getter pour dateDebutAbonnement
     * @return DateTime|null La date de début d'abonnement.
     */
    public function getDateDebutAbonnement(): ?DateTime 
    {
        return $this->dateDebutAbonnement;
    }

    /**
     * Setter pour dateDebutAbonnement
     * @param DateTime|null $dateDebutAbonnement La date de début d'abonnement.
     * @return void
     */
    public function setDateDebutAbonnement($dateDebutAbonnement): void
    {
        $this->dateDebutAbonnement = $dateDebutAbonnement;
    }

    /**
     * Getter pour dateFinAbonnement
     * @return DateTime|null La date de fin d'abonnement.
     */
    public function getDateFinAbonnement(): ?DateTime
    {
        return $this->dateFinAbonnement;
    }

    /**
     * Setter pour dateFinAbonnement
     * @param DateTime|null $dateFinAbonnement La date de fin d'abonnement.
     * @return void
     */
    public function setDateFinAbonnement($dateFinAbonnement): void
    {
        $this->dateFinAbonnement = $dateFinAbonnement;
    }

    /**
     * Getter pour pointsDeRenommeeArtiste
     * @return int|null Les points de renommée de l'artiste.
     */
    public function getPointsDeRenommeeArtiste(): ?int
    {
        return $this->pointsDeRenommeeArtiste;
    }

    /**
     * Setter pour pointsDeRenommeeArtiste
     * @param int|null $pointsDeRenommeeArtiste Les points de renommée de l'artiste.
     * @return void
     */
    public function setPointsDeRenommeeArtiste($pointsDeRenommeeArtiste): void
    {
        $this->pointsDeRenommeeArtiste = $pointsDeRenommeeArtiste;
    }

    /**
     * Getter pour nbAbonnesArtiste
     * @return int|null Le nombre d'abonnés de l'artiste.
     */
    public function getNbAbonnesArtiste(): ?int
    {
        return $this->nbAbonnesArtiste;
    }

    /**
     * Setter pour nbAbonnesArtiste
     * @param int|null $nbAbonnesArtiste Le nombre d'abonnés de l'artiste.
     * @return void
     */
    public function setNbAbonnesArtiste($nbAbonnesArtiste): void
    {
        $this->nbAbonnesArtiste = $nbAbonnesArtiste;
    }

    /**
     * Getter pour urlPhotoUtilisateur
     * @return string|null L'URL de la photo de l'utilisateur.
     */
    public function geturlPhotoUtilisateur(): ?string
    {
        return $this->urlPhotoUtilisateur;
    }

    /**
     * Setter pour urlPhotoUtilisateur
     * @param string|null $urlPhotoUtilisateur L'URL de la photo de l'utilisateur.
     * @return void
     */
    public function seturlPhotoUtilisateur($urlPhotoUtilisateur): void
    {
        $this->urlPhotoUtilisateur = $urlPhotoUtilisateur;
    }

    /**
     * Getter pour roleUtilisateur
     * @return Role|null Le Role de l'utilisateur.
     */
    public function getRoleUtilisateur(): ?Role
    {
        return $this->roleUtilisateur;
    }

    /**
     * Setter pour roleUtilisateur
     * @param string|null $roleUtilisateur Le Role de l'utilisateur.
     * @return void
     */
    public function setRoleUtilisateur($roleUtilisateur): void
    {
        $this->roleUtilisateur = $roleUtilisateur;
    }
}
