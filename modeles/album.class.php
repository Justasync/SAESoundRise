<?php
/**
 * @file modeles/album.class.php
 * @brief Classe représentant un album musical
 */
class Album
{
    /**
     * @var int|null $idAlbum L'identifiant unique de l'album.
     */
    private ?int $idAlbum = null;
    /**
     * @var string|null $nomAlbum Le nom de l'album.
     */
    private ?string $nomAlbum = null;
    /**
     * @var string|null $dateSortieAlbum La date de sortie de l'album.
     */
    private ?string $dateSortieAlbum = null;
    /**
     * @var string|null $urlPochetteAlbum L'URL de la pochette de l'album.
     */
    private ?string $urlPochetteAlbum = null;
    /**
     * @var string|null $artisteAlbum Le nom de l'artiste de l'album.
     */
    private ?string $artisteAlbum = null;
    /**
     * @var string|null $pseudoArtiste Le pseudo de l'artiste qui a composé l'album.
     */
    private ?string $pseudoArtiste = null;

    /**
     * Getter pour l'id de l'album
     * @return int|null
     */
    public function getIdAlbum(): ?int
    {
        return $this->idAlbum;
    }

    /**
     * Setter pour l'id de l'album
     * @param mixed $idAlbum
     * @return void
     */
    public function setIdAlbum(?int $idAlbum): void
    {
        $this->idAlbum = $idAlbum;
    }

    /**
     * Getter pour le nom de l'album
     * @return string|null
     */
    public function getNomAlbum(): ?string
    {
        return $this->nomAlbum;
    }

    /**
     * Setter pour le nom de l'album
     * @param mixed $nomAlbum
     * @return void
     */
    public function setNomAlbum(?string $nomAlbum): void
    {
        $this->nomAlbum = $nomAlbum;
    }

    /**
     * Getter pour le titre de l'album
     * @return string|null
     */
    public function getTitreAlbum(): ?string
    {
        return $this->getNomAlbum();
    }

    /**
     * Setter pour le titre de l'album
     * @param mixed $titreAlbum
     * @return void
     */
    public function setTitreAlbum(?string $titreAlbum): void
    {
        $this->setNomAlbum($titreAlbum);
    }

    /**
     * Getter pour la date de sortie de l'album
     * @return string|null
     */
    public function getDateSortieAlbum(): ?string
    {
        return $this->dateSortieAlbum;
    }

    /**
     * Setter pour la date de sortie de l'album
     * @param mixed $dateSortieAlbum
     * @return void
     */
    public function setDateSortieAlbum(?string $dateSortieAlbum): void
    {
        $this->dateSortieAlbum = $dateSortieAlbum;
    }

    /**
     * Getter pour l'URL de la pochette de l'album
     * @return string|null
     */
    public function getUrlPochetteAlbum(): ?string
    {
        return $this->urlPochetteAlbum;
    }

    /**
     * Setter pour l'URL de la pochette de l'album
     * @param mixed $urlPochetteAlbum
     * @return void
     */
    public function setUrlPochetteAlbum(?string $urlPochetteAlbum): void
    {
        $this->urlPochetteAlbum = $urlPochetteAlbum;
    }

    /**
     * Getter pour le nom de l'artiste de l'album
     * @return string|null
     */
    public function getArtisteAlbum(): ?string
    {
        return $this->artisteAlbum;
    }

    /**
     * Setter pour le nom de l'artiste de l'album
     * @param mixed $artisteAlbum
     * @return void
     */
    public function setArtisteAlbum(?string $artisteAlbum): void
    {
        $this->artisteAlbum = $artisteAlbum;
    }

    /**
     * Getter pour le pseudo de l'artiste
     * @return string|null
     */
    public function getPseudoArtiste(): ?string
    {
        return $this->pseudoArtiste;
    }

    /**
     * Setter pour le pseudo de l'artiste
     * @param mixed $pseudoArtiste
     * @return void
     */
    public function setPseudoArtiste(?string $pseudoArtiste): void
    {
        $this->pseudoArtiste = $pseudoArtiste;
    }
}