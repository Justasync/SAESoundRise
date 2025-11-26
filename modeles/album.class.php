<?php

class Album
{
    private ?int $idAlbum = null;
    private ?string $nomAlbum = null;
    private ?string $dateSortieAlbum = null;
    private ?string $urlPochetteAlbum = null;
    private ?string $artisteAlbum = null;

    /**
     * @var string|null Le pseudo de l'artiste, qui n'est pas une colonne de la table 'album'.
     */
    private ?string $pseudoArtiste = null;

    public function getIdAlbum(): ?int
    {
        return $this->idAlbum;
    }

    public function setIdAlbum(?int $idAlbum): void
    {
        $this->idAlbum = $idAlbum;
    }

    public function getNomAlbum(): ?string
    {
        return $this->nomAlbum;
    }

    public function setNomAlbum(?string $nomAlbum): void
    {
        $this->nomAlbum = $nomAlbum;
    }

    // Alias pour la compatibilité avec le template
    public function getTitreAlbum(): ?string
    {
        return $this->getNomAlbum();
    }

    // Alias pour la compatibilité avec le template
    public function setTitreAlbum(?string $titreAlbum): void
    {
        $this->setNomAlbum($titreAlbum);
    }

    public function getDateSortieAlbum(): ?string
    {
        return $this->dateSortieAlbum;
    }

    public function setDateSortieAlbum(?string $dateSortieAlbum): void
    {
        $this->dateSortieAlbum = $dateSortieAlbum;
    }

    public function getUrlPochetteAlbum(): ?string
    {
        return $this->urlPochetteAlbum;
    }

    public function setUrlPochetteAlbum(?string $urlPochetteAlbum): void
    {
        $this->urlPochetteAlbum = $urlPochetteAlbum;
    }

    public function getArtisteAlbum(): ?string
    {
        return $this->artisteAlbum;
    }

    public function setArtisteAlbum(?string $artisteAlbum): void
    {
        $this->artisteAlbum = $artisteAlbum;
    }

    public function getPseudoArtiste(): ?string
    {
        return $this->pseudoArtiste;
    }

    public function setPseudoArtiste(?string $pseudoArtiste): void
    {
        $this->pseudoArtiste = $pseudoArtiste;
    }
}