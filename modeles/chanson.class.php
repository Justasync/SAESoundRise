<?php

class Chanson {
    private int|null $idChanson;
    private string|null $titreChanson;
    private string|null $descriptionChanson;
    private int|null $dureeChanson;
    private DateTime|null $dateTeleversementChanson; //Le type dans la bdd est DATE
    private string|null $compositeurChanson;
    private string|null $parolierChanson;
    private bool|null $estPublieeChanson;
    private int|null $nbEcouteChanson;
    private Album|null $albumChanson;
    private Genre|null $genreChanson;
    private string|null $emailPublicateur;
    private string|null $urlFichierAudioChanson;


    //Constructeur

    public function __construct( ?int $idChanson = null, ?string $titreChanson = null, ?int $dureeChanson = null, 
        ?DateTime $dateTeleversementChanson = null, ?Album $albumChanson = null, ?Genre $genreChanson = null, 
        ?string $emailPublicateur = null, ?string $urlFichierAudioChanson = null, ?string $descriptionChanson = null, 
        ?string $compositeurChanson = null, ?string $parolierChanson = null,
        ?bool $estPublieeChanson = null, ?int $nbEcouteChanson = null
    ) {
        $this->idChanson = $idChanson;
        $this->titreChanson = $titreChanson;
        $this->dureeChanson = $dureeChanson;
        $this->dateTeleversementChanson = $dateTeleversementChanson;
        $this->albumChanson = $albumChanson;
        $this->genreChanson = $genreChanson;
        $this->emailPublicateur = $emailPublicateur;
        $this->urlFichierAudioChanson = $urlFichierAudioChanson;
        $this->descriptionChanson = $descriptionChanson;
        $this->compositeurChanson = $compositeurChanson;
        $this->parolierChanson = $parolierChanson;
        $this->estPublieeChanson = $estPublieeChanson;
        $this->nbEcouteChanson = $nbEcouteChanson;
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
     * Get the value of titreChanson
     */ 
    public function getTitreChanson(): ?string
    {
        return $this->titreChanson;
    }

    /**
     * Set the value of titreChanson
     *
     */ 
    public function setTitreChanson(?string $titreChanson): void
    {
        $this->titreChanson = $titreChanson;
    }

    /**
     * Get the value of descriptionChanson
     */
    public function getDescriptionChanson(): ?string
    {
        return $this->descriptionChanson;
    }

    /**
     * Set the value of descriptionChanson
     *
     */ 
    public function setDescriptionChanson(?string $descriptionChanson): void
    {
        $this->descriptionChanson = $descriptionChanson;
    }

    /**
     * Get the value of dureeChanson
     */
    public function getDureeChanson(): ?int
    {
        return $this->dureeChanson;
    }

    /**
     * Set the value of dureeChanson
     *
     */ 
    public function setDureeChanson(?int $dureeChanson): void
    {
        $this->dureeChanson = $dureeChanson;
    }
    
    /**
     * Get the value of dateTeleversementChanson
     */
    public function getDateTeleversementChanson(): ?DateTime
    {
        return $this->dateTeleversementChanson;
    }

    /**
     * Set the value of dateTeleversementChanson
     *
     */ 
    public function setDateTeleversementChanson(?DateTime $dateTeleversementChanson): void
    {
        $this->dateTeleversementChanson = $dateTeleversementChanson;
    }

    /**
     * Get the value of compositeurChanson
     */
    public function getCompositeurChanson(): ?string
    {
        return $this->compositeurChanson;
    }

    /**
     * Set the value of compositeurChanson
     *
     */ 
    public function setCompositeurChanson(?string $compositeurChanson): void
    {
        $this->compositeurChanson = $compositeurChanson;
    }

    /**
     * Get the value of parolierChanson
     */
    public function getParolierChanson(): ?string
    {
        return $this->parolierChanson;
    }

    /**
     * Set the value of parolierChanson
     *
     */ 
    public function setParolierChanson(?string $parolierChanson): void
    {
        $this->parolierChanson = $parolierChanson;
    }

    /**
     * Get the value of estPublieeChanson
     */
    public function getEstPublieeChanson(): ?bool
    {
        return $this->estPublieeChanson;
    }

    /**
     * Set the value of estPublieeChanson
     *
     */ 
    public function setEstPublieeChanson(?bool $estPublieeChanson): void
    {
        $this->estPublieeChanson = $estPublieeChanson;
    }

    /**
     * Get the value of nbEcouteChanson
     */
    public function getNbEcouteChanson(): ?int
    {
        return $this->nbEcouteChanson;
    }

    /**
     * Set the value of nbEcouteChanson
     *
     */ 
    public function setNbEcouteChanson(?int $nbEcouteChanson): void
    {
        $this->nbEcouteChanson = $nbEcouteChanson;
    }

    /**
     * Get the value of albumChanson
     */
    public function getAlbumChanson(): ?Album
    {
        return $this->albumChanson;
    }

    /**
     * Set the value of albumChanson
     *
     */
    public function setAlbumChanson(?Album $albumChanson): void
    {
        $this->albumChanson = $albumChanson;
    }

    /**
     * Get the value of genreChanson
     */
    public function getGenreChanson(): ?Genre
    {
        return $this->genreChanson;
    }

    /**
     * Set the value of genreChanson
     *
     */
    public function setGenreChanson(?Genre $genreChanson): void
    {
        $this->genreChanson = $genreChanson;
    }

    /**
     * Get the value of emailPublicateur
     */
    public function getEmailPublicateur(): string
    {
        return $this->emailPublicateur;
    }

    /**
     * Set the value of emailPublicateur
     *
     */
    public function setEmailPublicateur(?string $emailPublicateur): void
    {
        $this->emailPublicateur = $emailPublicateur;
    }

    /**
     * Get the value of urlFichierAudioChanson
     */
    public function getUrlFichierAudioChanson(): string
    {
        return $this->urlFichierAudioChanson;
    }

    /**
     * Set the value of urlFichierAudioChanson
     *
     */
    public function setUrlFichierAudioChanson(?string $urlFichierAudioChanson): void
    {
        $this->urlFichierAudioChanson = $urlFichierAudioChanson;
    }
}