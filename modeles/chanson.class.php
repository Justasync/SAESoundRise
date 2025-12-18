<?php
/**
 * @file modeles/chanson.class.php
 * @brief Classe représentant une chanson
 */

class Chanson
{
    /**
     * @var int|null $idChanson L'identifiant unique de la chanson.
     */
    private int|null $idChanson;

    /**
     * @var string|null $titreChanson Le titre de la chanson.
     */
    private string|null $titreChanson;

    /**
     * @var int|null $dureeChanson La durée de la chanson en secondes.
     */
    private int|null $dureeChanson;

    /**
     * @var DateTime|null $dateTeleversementChanson La date de mise en ligne de la chanson.
     */
    private DateTime|null $dateTeleversementChanson;

    /**
     * @var int|null $nbEcouteChanson Le nombre d'écoutes de la chanson.
     */
    private int|null $nbEcouteChanson;

    /**
     * @var Album|null $albumChanson L'album auquel appartient la chanson.
     */
    private Album|null $albumChanson;

    /**
     * @var Genre|null $genreChanson Le genre musical de la chanson.
     */
    private Genre|null $genreChanson;

    /**
     * @var string|null $emailPublicateur L'email de l'artiste qui a publié la chanson.
     */
    private string|null $emailPublicateur;

    /**
     * @var string|null $urlAudioChanson L'URL du fichier audio de la chanson.
     */
    private string|null $urlAudioChanson;

    /**
     * @var bool $isLiked Indique si la chanson est likée par l'utilisateur connecté.
     */
    private bool $isLiked = false;

    /**
     * @var DateTime|null $dateLike La date du like par l'utilisateur connecté.
     */
    private ?DateTime $dateLike = null;

    /**
     * Constructeur de la classe Chanson.
     * @param int|null $idChanson L'identifiant unique.
     * @param string|null $titreChanson Le titre.
     * @param int|null $dureeChanson La durée en secondes.
     * @param DateTime|null $dateTeleversementChanson La date de publication.
     * @param Album|null $albumChanson L'album associé.
     * @param Genre|null $genreChanson Le genre musical.
     * @param string|null $emailPublicateur L'email de l'artiste.
     * @param string|null $urlAudioChanson L'URL du fichier audio.
     * @param int|null $nbEcouteChanson Le nombre d'écoutes.
     */
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
    public function getUrlAudioChanson(): ?string
    {
        return $this->urlAudioChanson;
    }

    /**
     * Set the value of urlAudioChanson
     *
     */
    public function setUrlAudioChanson(?string $urlAudioChanson): void
    {
        $this->urlAudioChanson = $urlAudioChanson;
    }

    /**
     * Get the value of isLiked
     */
    public function getIsLiked(): bool
    {
        return $this->isLiked;
    }
    /**
     * Set the value of isLiked
     *
     */
    public function setIsLiked(bool $isLiked): void
    {
        $this->isLiked = $isLiked;
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
}
