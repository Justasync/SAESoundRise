<?php

/**
 * @file utilisateur.class.php
 * @brief Ce fichier contient la classe Utilisateur pour représenter un utilisateur.
 */

/**
 * @brief Classe Utilisateur pour représenter un compte utilisateur
 */

enum StatutUtilisateur: string
{
    case Actif = 'actif';
    case Suspendu = 'suspendu';
    case Supprimee = 'supprimee';
}

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
     * @brief Adresse email de l'utilisateur.
     */
    private ?string $emailUtilisateur;
    private ?string $nomUtilisateur;
    private ?string $pseudoUtilisateur;
    private ?string $motDePasseUtilisateur;
    private ?DateTime $dateDeNaissanceUtilisateur;
    private ?DateTime $dateInscriptionUtilisateur;
    private ?StatutUtilisateur $statutUtilisateur;
    private ?Genre $genreUtilisateur;
    private ?bool $estAbonnee;
    private ?string $descriptionUtilisateur;
    private ?string $siteWebUtilisateur;
    private ?StatutAbonnement $statutAbonnement;
    private ?DateTime $dateDebutAbonnement;
    private ?DateTime $dateFinAbonnement;
    private ?int $pointsDeRenommeeArtiste;
    private ?int $nbAbonnesArtiste;
    private ?string $urlPhotoUtilisateur;
    private ?Role $roleUtilisateur;

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
     * Get the value of genreUtilisateur
     */
    public function getGenreUtilisateur()
    {
        return $this->genreUtilisateur;
    }

    /**
     * Set the value of genreUtilisateur
     */
    public function setGenreUtilisateur($genreUtilisateur)
    {
        $this->genreUtilisateur = $genreUtilisateur;
    }

    /**
     * Get the value of nomUtilisateur
     */
    public function getNomUtilisateur()
    {
        return $this->nomUtilisateur;
    }

    /**
     * Set the value of nomUtilisateur
     */
    public function setNomUtilisateur($nomUtilisateur)
    {
        $this->nomUtilisateur = $nomUtilisateur;
    }

    /**
     * Get the value of emailUtilisateur
     */
    public function getEmailUtilisateur()
    {
        return $this->emailUtilisateur;
    }

    /**
     * Set the value of emailUtilisateur
     */
    public function setEmailUtilisateur($emailUtilisateur)
    {
        $this->emailUtilisateur = $emailUtilisateur;
    }

    /**
     * Get the value of pseudoUtilisateur
     */
    public function getPseudoUtilisateur()
    {
        return $this->pseudoUtilisateur;
    }

    /**
     * Set the value of pseudoUtilisateur
     */
    public function setPseudoUtilisateur($pseudoUtilisateur)
    {
        $this->pseudoUtilisateur = $pseudoUtilisateur;
    }

    /**
     * Get the value of motDePasseUtilisateur
     */
    public function getMotDePasseUtilisateur()
    {
        return $this->motDePasseUtilisateur;
    }

    /**
     * Set the value of motDePasseUtilisateur
     */
    public function setMotDePasseUtilisateur($motDePasseUtilisateur)
    {
        $this->motDePasseUtilisateur = $motDePasseUtilisateur;
    }

    /**
     * Get the value of dateDeNaissanceUtilisateur
     */
    public function getDateDeNaissanceUtilisateur()
    {
        return $this->dateDeNaissanceUtilisateur;
    }

    /**
     * Set the value of dateDeNaissanceUtilisateur
     */
    public function setDateDeNaissanceUtilisateur($dateDeNaissanceUtilisateur)
    {
        $this->dateDeNaissanceUtilisateur = $dateDeNaissanceUtilisateur;
    }

    /**
     * Get the value of dateInscriptionUtilisateur
     */
    public function getDateInscriptionUtilisateur()
    {
        return $this->dateInscriptionUtilisateur;
    }

    /**
     * Set the value of dateInscriptionUtilisateur
     */
    public function setDateInscriptionUtilisateur($dateInscriptionUtilisateur)
    {
        $this->dateInscriptionUtilisateur = $dateInscriptionUtilisateur;
    }

    /**
     * Get the value of statutUtilisateur
     */
    public function getStatutUtilisateur()
    {
        return $this->statutUtilisateur;
    }

    /**
     * Set the value of statutUtilisateur
     */
    public function setStatutUtilisateur($statutUtilisateur)
    {
        $this->statutUtilisateur = $statutUtilisateur;
    }

    /**
     * Get the value of estAbonnee
     */
    public function getEstAbonnee()
    {
        return $this->estAbonnee;
    }

    /**
     * Set the value of estAbonnee
     */
    public function setEstAbonnee($estAbonnee)
    {
        $this->estAbonnee = $estAbonnee;
    }

    /**
     * Get the value of descriptionUtilisateur
     */
    public function getDescriptionUtilisateur()
    {
        return $this->descriptionUtilisateur;
    }

    /**
     * Set the value of descriptionUtilisateur
     */
    public function setDescriptionUtilisateur($descriptionUtilisateur)
    {
        $this->descriptionUtilisateur = $descriptionUtilisateur;
    }

    /**
     * Get the value of siteWebUtilisateur
     */
    public function getSiteWebUtilisateur()
    {
        return $this->siteWebUtilisateur;
    }

    /**
     * Set the value of siteWebUtilisateur
     */
    public function setSiteWebUtilisateur($siteWebUtilisateur)
    {
        $this->siteWebUtilisateur = $siteWebUtilisateur;
    }

    /**
     * Get the value of statutAbonnement
     */
    public function getStatutAbonnement()
    {
        return $this->statutAbonnement;
    }

    /**
     * Set the value of statutAbonnement
     */
    public function setStatutAbonnement($statutAbonnement)
    {
        $this->statutAbonnement = $statutAbonnement;
    }

    /**
     * Get the value of dateDebutAbonnement
     */
    public function getDateDebutAbonnement()
    {
        return $this->dateDebutAbonnement;
    }

    /**
     * Set the value of dateDebutAbonnement
     */
    public function setDateDebutAbonnement($dateDebutAbonnement)
    {
        $this->dateDebutAbonnement = $dateDebutAbonnement;
    }

    /**
     * Get the value of dateFinAbonnement
     */
    public function getDateFinAbonnement()
    {
        return $this->dateFinAbonnement;
    }

    /**
     * Set the value of dateFinAbonnement
     */
    public function setDateFinAbonnement($dateFinAbonnement)
    {
        $this->dateFinAbonnement = $dateFinAbonnement;
    }

    /**
     * Get the value of pointsDeRenommeeArtiste
     */
    public function getPointsDeRenommeeArtiste()
    {
        return $this->pointsDeRenommeeArtiste;
    }

    /**
     * Set the value of pointsDeRenommeeArtiste
     */
    public function setPointsDeRenommeeArtiste($pointsDeRenommeeArtiste)
    {
        $this->pointsDeRenommeeArtiste = $pointsDeRenommeeArtiste;
    }

    /**
     * Get the value of nbAbonnesArtiste
     */
    public function getNbAbonnesArtiste()
    {
        return $this->nbAbonnesArtiste;
    }

    /**
     * Set the value of nbAbonnesArtiste
     */
    public function setNbAbonnesArtiste($nbAbonnesArtiste)
    {
        $this->nbAbonnesArtiste = $nbAbonnesArtiste;
    }

    /**
     * Get the value of urlPhotoUtilisateur
     */
    public function geturlPhotoUtilisateur()
    {
        return $this->urlPhotoUtilisateur;
    }

    /**
     * Set the value of urlPhotoUtilisateur
     */
    public function seturlPhotoUtilisateur($urlPhotoUtilisateur)
    {
        $this->urlPhotoUtilisateur = $urlPhotoUtilisateur;
    }

    /**
     * Get the value of roleUtilisateur
     */
    public function getRoleUtilisateur()
    {
        return $this->roleUtilisateur;
    }

    /**
     * Set the value of roleUtilisateur
     */
    public function setRoleUtilisateur($roleUtilisateur)
    {
        $this->roleUtilisateur = $roleUtilisateur;
    }
}
