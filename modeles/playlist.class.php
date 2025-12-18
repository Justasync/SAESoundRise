<?php
/**
 * @file modeles/playlist.class.php
 * @brief Classe représentant une playlist musicale
 */

class Playlist {
    /**
     * @var int|null $idPlaylist L'identifiant unique de la playlist.
     */
    private int|null $idPlaylist;

    /**
     * @var string|null $nomPlaylist Le nom de la playlist.
     */
    private string|null $nomPlaylist;

    /**
     * @var bool|null $estPubliquePlaylist Indique si la playlist est publique.
     */
    private bool|null $estPubliquePlaylist;

    /**
     * @var DateTime|null $dateCreationPlaylist La date de création de la playlist.
     */
    private DateTime|null $dateCreationPlaylist;

    /**
     * @var DateTime|null $dateDerniereModification La date de dernière modification.
     */
    private DateTime|null $dateDerniereModification;

    /**
     * @var string|null $emailProprietaire L'email du propriétaire de la playlist.
     */
    private string|null $emailProprietaire;
    
    /**
     * Constructeur de la classe Playlist.
     * @param int|null $idPlaylist L'identifiant unique.
     * @param string|null $nomPlaylist Le nom.
     * @param bool|null $estPubliquePlaylist Si la playlist est publique.
     * @param DateTime|null $dateCreationPlaylist La date de création.
     * @param DateTime|null $dateDerniereModification La date de dernière modification.
     * @param string|null $emailProprietaire L'email du propriétaire.
     */
    public function __construct(
        ?int $idPlaylist = null, ?string $nomPlaylist = null, ?bool $estPubliquePlaylist = null, ?DateTime $dateCreationPlaylist = null,
        ?DateTime $dateDerniereModification = null, ?string $emailProprietaire = null
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