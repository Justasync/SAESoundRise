<?php

class Playlist {
    private int $idPlaylist;
    private string $nomPlaylist;
    private bool $estPubliquePlaylist;
    private DateTime $dateCreationPlaylist;
    private DateTime $dateDerniereModification;
    private string $emailProprietaire;

    public function __construct(
        int $idPlaylist, string $nomPlaylist, bool $estPubliquePlaylist, DateTime $dateCreationPlaylist,
        DateTime $dateDerniereModification, string $emailProprietaire
    ) {
        $this->idPlaylist = $idPlaylist;
        $this->nomPlaylist = $nomPlaylist;
        $this->estPubliquePlaylist = $estPubliquePlaylist;
        $this->dateCreationPlaylist = $dateCreationPlaylist;
        $this->dateDerniereModification = $dateDerniereModification;
        $this->emailProprietaire = $emailProprietaire;
    }

    /**
     * Get the value of idPlaylist
     */
    public function getIdPlaylist(): int
    {
        return $this->idPlaylist;
    }
    /**
     * Set the value of idPlaylist
     */
    public function setIdPlaylist(int $idPlaylist): void
    {
        $this->idPlaylist = $idPlaylist;
    }

    /**
     * Get the value of nomPlaylist
     */
    public function getNomPlaylist(): string
    {
        return $this->nomPlaylist;
    }
    /**
     * Set the value of nomPlaylist
     */
    public function setNomPlaylist(string $nomPlaylist): void
    {
        $this->nomPlaylist = $nomPlaylist;
    }

    /**
     * Get the value of estPubliquePlaylist
     */
    public function isEstPubliquePlaylist(): bool
    {
        return $this->estPubliquePlaylist;
    }
    /**
     * Set the value of estPubliquePlaylist
     */
    public function setEstPubliquePlaylist(bool $estPubliquePlaylist): void
    {
        $this->estPubliquePlaylist = $estPubliquePlaylist;
    }

    /**
     * Get the value of dateCreationPlaylist
     */
    public function getDateCreationPlaylist(): DateTime
    {
        return $this->dateCreationPlaylist;
    }
    /**
     * Set the value of dateCreationPlaylist
     */
    public function setDateCreationPlaylist(DateTime $dateCreationPlaylist): void
    {
        $this->dateCreationPlaylist = $dateCreationPlaylist;
    }

    /**
     * Get the value of dateDerniereModification
     */
    public function getDateDerniereModification(): DateTime
    {
        return $this->dateDerniereModification;
    }
    /**
     * Set the value of dateDerniereModification
     */
    public function setDateDerniereModification(DateTime $dateDerniereModification): void
    {
        $this->dateDerniereModification = $dateDerniereModification;
    }

    /**
     * Get the value of emailProprietaire
     */
    public function getEmailProprietaire(): string
    {
        return $this->emailProprietaire;
    }
    /**
     * Set the value of emailProprietaire
     */
    public function setEmailProprietaire(string $emailProprietaire): void
    {
        $this->emailProprietaire = $emailProprietaire;
    }
}