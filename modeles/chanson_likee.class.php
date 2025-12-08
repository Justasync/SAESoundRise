<?php

class ChansonLikee
{
    private int|null $idChanson;
    private DateTime|null $dateLike; //Le type dans la bdd est DATE
    private string|null $emailUtilisateur;


    //Constructeur

    public function __construct(
        ?int $idChanson = null,
        ?DateTime $dateLike = null,
        ?string $emailUtilisateur = null
    ) {
        $this->idChanson = $idChanson;
        $this->dateLike = $dateLike;
        $this->emailUtilisateur = $emailUtilisateur;
    }

    //Getteurs et Setteurs

    /**
     * Get the value of idChanson
     */
    public function getIdChanson(): ?int
    {
        return $this->idChanson;
    }

    /**
     * Set the value of idChanson
     *
     */
    public function setIdChanson(?int $idChanson): void
    {
        $this->idChanson = $idChanson;
    }

    /**
     * Get the value of dateLike
     */ 
    public function getDateLike(): ?DateTime
    {
        return $this->dateLike;
    }
    /**
     * Set the value of dateLike
     *
     */
    public function setDateLike(?DateTime $dateLike): void
    {
        $this->dateLike = $dateLike;
    }

    /**
     * Get the value of emailUtilisateur
     */
    public function getEmailUtilisateur(): ?string
    {
        return $this->emailUtilisateur;
    }
    /**
     * Set the value of emailUtilisateur
     *
     */
    public function setEmailUtilisateur(?string $emailUtilisateur): void
    {
        $this->emailUtilisateur = $emailUtilisateur;
    }
}