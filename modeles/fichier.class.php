<?php

enum TypeFichier: string
{
    case Image = 'image';
    case Audio = 'audio';
}
enum FormatFichier: string
{
    case JPG = 'jpg';
    case JPEG = 'jpeg';
    case PNG = 'png';
    case WEBP = 'webp';
    case MP3 = 'mp3';
    case WAV = 'wav';
}

class Fichier {
    private string|null $urlFichier;
    private TypeFichier|null $typeFichier;
    private FormatFichier|null $formatFichier;
    private DateTime|null $dateAjout;

    public function __construct(?string $urlFichier = null, ?TypeFichier $typeFichier = null, ?FormatFichier $formatFichier = null)
    {
        $this->urlFichier = $urlFichier;
        $this->typeFichier = $typeFichier;
        $this->formatFichier = $formatFichier;
        $this->dateAjout = new DateTime();
    }

    /**
     * Get the value of urlFichier
     */ 
    public function getUrlFichier(): ?string
    {
        return $this->urlFichier;
    }
    /**
     * Set the value of urlFichier
     *
     */ 
    public function setUrlFichier(?string $urlFichier): void
    {
        $this->urlFichier = $urlFichier;

    }

    /**
     * Get the value of typeFichier
     */
    public function getTypeFichier(): ?TypeFichier
    {
        return $this->typeFichier;
    }
    /**
     * Set the value of typeFichier
     *
     */ 
    public function setTypeFichier(?TypeFichier $typeFichier): void
    {
        $this->typeFichier = $typeFichier;

    }

    /**
     * Get the value of formatFichier
     */
    public function getFormatFichier(): ?FormatFichier
    {
        return $this->formatFichier;
    }
    /**
     * Set the value of formatFichier
     *
     */ 
    public function setFormatFichier(?FormatFichier $formatFichier): void
    {
        $this->formatFichier = $formatFichier;

    }

    /**
     * Get the value of dateAjout
     */
    public function getDateAjout(): ?DateTime
    {
        return $this->dateAjout;
    }
    /**
     * Set the value of dateAjout
     *
     */ 
    public function setDateAjout(?DateTime $dateAjout): void 
    {
        $this->dateAjout = $dateAjout;
    }

}