<?php

class Chanson
{
    private int|null $idChanson;
    private string|null $titreChanson;
    private int|null $dureeChanson;
    private DateTime|null $dateTeleversementChanson; //Le type dans la bdd est DATE
    private int|null $nbEcouteChanson;
    private Album|null $albumChanson;
    private Genre|null $genreChanson;
    private string|null $emailPublicateur;
    private string|null $urlAudioChanson;


    //Constructeur

    public function __construct(
        ?int $idChanson = null,
        ?string $titreChanson = null,
        ?int $dureeChanson = null,
        ?DateTime $dateTeleversementChanson = null,
        ?Album $albumChanson = null,
        ?Genre $genreChanson = null,
        ?string $emailPublicateur = null,
        ?string $urlAudioChanson = null,
        ?int $nbEcouteChanson = null
    ) {
        $this->idChanson = $idChanson;
        $this->titreChanson = $titreChanson;
        $this->dureeChanson = $dureeChanson;
        $this->dateTeleversementChanson = $dateTeleversementChanson;
        $this->albumChanson = $albumChanson;
        $this->genreChanson = $genreChanson;
        $this->emailPublicateur = $emailPublicateur;
        $this->urlAudioChanson = $urlAudioChanson;
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
     * Get the value of urlAudioChanson
     */
    public function geturlAudioChanson(): string
    {
        return $this->urlAudioChanson;
    }

    /**
     * Set the value of urlAudioChanson
     *
     */
    public function seturlAudioChanson(?string $urlAudioChanson): void
    {
        $this->urlAudioChanson = $urlAudioChanson;
    }
}
