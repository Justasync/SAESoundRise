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
}

class Utilisateur
{
    /**
     * @brief Adresse email de l'utilisateur.
     */
    private null|string $emailUtilisateur;
    private null|string $pseudoUtilisateur;
    private null|string $motDePasseUtilisateur;
    private null|DateTime $dateDeNaissanceUtilisateur;
    private null|DateTime $dateInscriptionUtilisateur;
    private null|StatutUtilisateur $statutUtilisateur;
    private null|bool $estAbonnee;
    private null|string $descriptionUtilisateur;
    private null|string $siteWebUtilisateur;
    private null|StatutAbonnement $statutAbonnement;
    private null|DateTime $dateDebutAbonnement;
    private null|DateTime $dateFinAbonnement;
    private null|int $pointsDeRenommeeArtiste;
    private null|int $nbAbonnesArtiste;
    private ?Fichier $photoProfilUtilisateur;
    private ?Role $roleUtilisateur;

    public function __construct(?string $emailUtilisateur = null, ?string $pseudoUtilisateur = null, ?string $motDePasseUtilisateur = null, ?DateTime $dateDeNaissanceUtilisateur = null, ?DateTime $dateInscriptionUtilisateur = null, ?StatutUtilisateur $statutUtilisateur = null, ?bool $estAbonnee = null, ?string $descriptionUtilisateur = null, ?string $siteWebUtilisateur = null, ?StatutAbonnement $statutAbonnement = null, ?DateTime $dateDebutAbonnement = null, ?DateTime $dateFinAbonnement = null, ?int $pointsDeRenommeeArtiste = null, ?int $nbAbonnesArtiste = null, ?Fichier $photoProfilUtilisateur = null, ?Role $roleUtilisateur = null)
    {
        $this->setEmailUtilisateur($emailUtilisateur);
        $this->setPseudoUtilisateur($pseudoUtilisateur);
        $this->setMotDePasseUtilisateur($motDePasseUtilisateur);
        $this->setDateDeNaissanceUtilisateur($dateDeNaissanceUtilisateur);
        $this->setDateInscriptionUtilisateur($dateInscriptionUtilisateur);
        $this->setStatutUtilisateur($statutUtilisateur);
        $this->setEstAbonnee($estAbonnee);
        $this->setDescriptionUtilisateur($descriptionUtilisateur);
        $this->setSiteWebUtilisateur($siteWebUtilisateur);
        $this->setStatutAbonnement($statutAbonnement);
        $this->setDateDebutAbonnement($dateDebutAbonnement);
        $this->setDateFinAbonnement($dateFinAbonnement);
        $this->setPointsDeRenommeeArtiste($pointsDeRenommeeArtiste);
        $this->setNbAbonnesArtiste($nbAbonnesArtiste);
        $this->setPhotoProfilUtilisateur($photoProfilUtilisateur);
        $this->setRoleUtilisateur($roleUtilisateur);
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
     * Get the value of photoProfilUtilisateur
     */
    public function getPhotoProfilUtilisateur()
    {
        return $this->photoProfilUtilisateur;
    }

    /**
     * Set the value of photoProfilUtilisateur
     */
    public function setPhotoProfilUtilisateur($photoProfilUtilisateur)
    {
        $this->photoProfilUtilisateur = $photoProfilUtilisateur;
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
