<?php

/**
 * @file utilisateur.class.php
 * @brief Ce fichier contient la classe Utilisateur pour représenter un utilisateur.
 */

/**
 * @brief Classe Utilisateur pour représenter un compte utilisateur
 */

enum StatutUtilisateur: string{
    case Actif = 'actif';
    case Suspendu = 'suspendu';
    case Supprimee = 'supprimee';
}

enum StatutAbonnement: string{
    case Actif = 'actif';
    case Expire = 'expire';
    case Annule = 'annule';
}

class Utilisateur{
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
    private null|StatutAbonnement $statutAbonnement;
    private null|DateTime $dateDebutAbonnement;
    private null|DateTime $dateFinAbonnement;
    private null|int $pointsDeRenommeeArtiste;
    private null|int $nbAbonnesArtiste;
    private ?Fichier $photoProfilUtilisateur;
    private ?Role $roleUtilisateur;

    public function __construct(?string $emailUtilisateur=null, ?string $pseudoUtilisateur=null, ?string $motDePasseUtilisateur=null, ?DateTime $dateDeNaissanceUtilisateur=null, ?DateTime $dateInscriptionUtilisateur=null, ?StatutUtilisateur $statutUtilisateur=null, ?bool $estAbonnee=null, ?StatutAbonnement $statutAbonnement=null, ?DateTime $dateDebutAbonnement=null, ?DateTime $dateFinAbonnement=null, ?int $pointsDeRenommeeArtiste=null, ?int $nbAbonnesArtiste=null, ?Fichier $photoProfilUtilisateur=null, ?Role $roleUtilisateur=null)
    {
        $this->setEmailUtilisateur($emailUtilisateur);
        $this->setPseudoUtilisateur($pseudoUtilisateur);
        $this->setMotDePasseUtilisateur($motDePasseUtilisateur);
        $this->setDateDeNaissanceUtilisateur($dateDeNaissanceUtilisateur);
        $this->setDateInscriptionUtilisateur($dateInscriptionUtilisateur);
        $this->setStatutUtilisateur($statutUtilisateur);
        $this->setEstAbonnee($estAbonnee);
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
     *
     * @return  self
     */
    public function setEmailUtilisateur($emailUtilisateur)
    {
        $this->emailUtilisateur = $emailUtilisateur;

        return $this;
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
     *
     * @return  self
     */
    public function setPseudoUtilisateur($pseudoUtilisateur)
    {
        $this->pseudoUtilisateur = $pseudoUtilisateur;

        return $this;
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
     *
     * @return  self
     */
    public function setMotDePasseUtilisateur($motDePasseUtilisateur)
    {
        $this->motDePasseUtilisateur = $motDePasseUtilisateur;

        return $this;
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
     *
     * @return  self
     */
    public function setDateDeNaissanceUtilisateur($dateDeNaissanceUtilisateur)
    {
        $this->dateDeNaissanceUtilisateur = $dateDeNaissanceUtilisateur;

        return $this;
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
     *
     * @return  self
     */
    public function setDateInscriptionUtilisateur($dateInscriptionUtilisateur)
    {
        $this->dateInscriptionUtilisateur = $dateInscriptionUtilisateur;

        return $this;
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
     *
     * @return  self
     */
    public function setStatutUtilisateur($statutUtilisateur)
    {
        $this->statutUtilisateur = $statutUtilisateur;

        return $this;
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
     *
     * @return  self
     */
    public function setEstAbonnee($estAbonnee)
    {
        $this->estAbonnee = $estAbonnee;

        return $this;
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
     *
     * @return  self
     */
    public function setStatutAbonnement($statutAbonnement)
    {
        $this->statutAbonnement = $statutAbonnement;

        return $this;
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
     *
     * @return  self
     */
    public function setDateDebutAbonnement($dateDebutAbonnement)
    {
        $this->dateDebutAbonnement = $dateDebutAbonnement;

        return $this;
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
     *
     * @return  self
     */
    public function setDateFinAbonnement($dateFinAbonnement)
    {
        $this->dateFinAbonnement = $dateFinAbonnement;

        return $this;
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
     *
     * @return  self
     */
    public function setPointsDeRenommeeArtiste($pointsDeRenommeeArtiste)
    {
        $this->pointsDeRenommeeArtiste = $pointsDeRenommeeArtiste;

        return $this;
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
     *
     * @return  self
     */
    public function setNbAbonnesArtiste($nbAbonnesArtiste)
    {
        $this->nbAbonnesArtiste = $nbAbonnesArtiste;

        return $this;
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
     *
     * @return  self
     */
    public function setPhotoProfilUtilisateur($photoProfilUtilisateur)
    {
        $this->photoProfilUtilisateur = $photoProfilUtilisateur;

        return $this;
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
     *
     * @return  self
     */
    public function setRoleUtilisateur($roleUtilisateur)
    {
            $this->roleUtilisateur = $roleUtilisateur;

            return $this;
    }
}