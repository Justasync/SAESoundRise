<?php

class Chanson {
    private int $idChanson;
    private string $titreChanson;
    private string|null $descriptionChanson;
    private int $dureeChanson;
    private string $dateTeleversementChanson; //Le type dans la bdd est DATE
    private string|null $compositeurChanson;
    private string|null $parolierChanson;
    private bool|null $estPublieeChanson;
    private int|null $nbEcouteChanson;
    private int $albumChanson;
    private int $genreChanson;
    //private $idPlaylist; // Sera implementé plus tard
    private string $emailPublicateur;
    private string $urlFichierAudioChanson;


    //Constructeur

    public function __construct( int $idChanson, string $titreChanson, int $dureeChanson, string $dateTeleversementChanson, 
        int $albumChanson, int $genreChanson, string $emailPublicateur, string $urlFichierAudioChanson,
        ?string $descriptionChanson = null, ?string $compositeurChanson = null, ?string $parolierChanson = null,
        ?bool $estPublieeChanson = null, ?int $nbEcouteChanson = null
    ) {
        $this->idChanson = $idChanson;
        $this->titreChanson = $titreChanson;
        $this->dureeChanson = $dureeChanson;
        $this->dateTeleversementChanson = $dateTeleversementChanson;
        $this->idAlbum = $albumChanson;
        $this->idGenre = $genreChanson;
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
    public function getIdChanson(): int
    {
        return $this->idChanson;
    }

    /**
     * Set the value of idChanson
     *
     */ 
    public function setIdChanson($idChanson): void
    {
        $this->idChanson = $idChanson;
    }

    /**
     * Get the value of titreChanson
     */ 
    public function getTitreChanson(): string
    {
        return $this->titreChanson;
    }

    /**
     * Set the value of titreChanson
     *
     */ 
    public function setTitreChanson($titreChanson): void
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
    public function setDescriptionChanson($descriptionChanson): void
    {
        $this->descriptionChanson = $descriptionChanson;
    }

    /**
     * Get the value of dureeChanson
     */
    public function getDureeChanson(): int
    {
        return $this->dureeChanson;
    }

    /**
     * Set the value of dureeChanson
     *
     */ 
    public function setDureeChanson($dureeChanson): void
    {
        $this->dureeChanson = $dureeChanson;
    }
    
    /**
     * Get the value of dateTeleversementChanson
     */
    public function getDateTeleversementChanson(): string
    {
        return $this->dateTeleversementChanson;
    }

    /**
     * Set the value of dateTeleversementChanson
     *
     */ 
    public function setDateTeleversementChanson($dateTeleversementChanson): void
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
    public function setCompositeurChanson($compositeurChanson): void
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
    public function setParolierChanson($parolierChanson): void
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
    public function setEstPublieeChanson($estPublieeChanson): void
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
    public function setNbEcouteChanson($nbEcouteChanson): void
    {
        $this->nbEcouteChanson = $nbEcouteChanson;
    }

    /**
     * Get the value of idAlbum
     */
    public function getIdAlbum(): int
    {
        return $this->idAlbum;
    }

    /**
     * Set the value of idAlbum
     *
     */
    public function setIdAlbum($idAlbum): void
    {
        $this->idAlbum = $idAlbum;
    }

    /**
     * Get the value of idGenre
     */
    public function getIdGenre(): int
    {
        return $this->idGenre;
    }

    /**
     * Set the value of idGenre
     *
     */
    public function setIdGenre($idGenre): void
    {
        $this->idGenre = $idGenre;
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
    public function setEmailPublicateur($emailPublicateur): void
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
    public function setUrlFichierAudioChanson($urlFichierAudioChanson): void
    {
        $this->urlFichierAudioChanson = $urlFichierAudioChanson;
    }
}