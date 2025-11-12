<?php

class Album {
    private int|null $idAlbum;
    private string|null $titreAlbum;
    private string|null $dateSortieAlbum;
    private string|null $pochetteAlbum;

    public function __construct(?int $idAlbum = null, ?string $titreAlbum = null, ?string $dateSortieAlbum = null, ?string $pochetteAlbum = null) {
        $this->idAlbum = $idAlbum;
        $this->titreAlbum = $titreAlbum;
        $this->dateSortieAlbum = $dateSortieAlbum;
        $this->pochetteAlbum = $pochetteAlbum;
    }

    /**
     * Get the value of idAlbum
     */
    public function getIdAlbum(): ?int
    {
        return $this->idAlbum;
    }
    /**
     * Set the value of idAlbum
     *
     */
    public function setIdAlbum(?int $idAlbum): void
    {
        $this->idAlbum = $idAlbum;
    }

    /**
     * Get the value of titreAlbum
     */
    public function getTitreAlbum(): ?string
    {
        return $this->titreAlbum;
    }
    /**
     * Set the value of titreAlbum
     *
     */
    public function setTitreAlbum(?string $titreAlbum): void
    {
        $this->titreAlbum = $titreAlbum;
    }

    /**
     * Get the value of dateSortieAlbum
     */
    public function getDateSortieAlbum(): ?string
    {
        return $this->dateSortieAlbum;
    }
    /**
     * Set the value of dateSortieAlbum
     *
     */
    public function setDateSortieAlbum(?string $dateSortieAlbum): void
    {
        $this->dateSortieAlbum = $dateSortieAlbum;
    }

    /**
     * Get the value of pochetteAlbum
     */
    public function getPochetteAlbum(): ?string
    {
        return $this->pochetteAlbum;
    }
    /**
     * Set the value of pochetteAlbum
     *
     */
    public function setPochetteAlbum(?string $pochetteAlbum): void
    {
        $this->pochetteAlbum = $pochetteAlbum;
    }
}