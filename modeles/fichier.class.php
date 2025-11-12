<?php

enum TypeProprietaireFichier: string
{
    case Image = 'image';
    case Audio = 'audio';
}
enum TypeFichier: string
{
    case JPG = 'jpg';
    case JPEG = 'jpeg';
    case PNG = 'png';
    case WEBP = 'webp';
    case MP3 = 'mp3';
    case WAV = 'wav';
}

class Fichier {
    private string $urlFichier;
    private TypeProprietaireFichier $typeProprietaireFichier;
    private TypeFichier $typeFichier;
    private DateTime $dateAjout;

    public function __construct(string $urlFichier, TypeProprietaireFichier $typeProprietaireFichier, TypeFichier $typeFichier)
    {
        $this->urlFichier = $urlFichier;
        $this->typeProprietaireFichier = $typeProprietaireFichier;
        $this->typeFichier = $typeFichier;
        $this->dateAjout = new DateTime();
    }

    /**
     * Get the value of urlFichier
     */
    public function getUrlFichier(): string
    {
        return $this->urlFichier;
    }
    /**
     * Set the value of urlFichier
     *
     */
    public function setUrlFichier(string $urlFichier): void
    {
        $this->urlFichier = $urlFichier;

    }

    /**
     * Get the value of typeProprietaireFichier
     */
    public function getTypeProprietaireFichier(): TypeProprietaireFichier
    {
        return $this->typeProprietaireFichier;
    }
    /**
     * Set the value of typeProprietaireFichier
     *
     */
    public function setTypeProprietaireFichier(TypeProprietaireFichier $typeProprietaireFichier): void
    {
        $this->typeProprietaireFichier = $typeProprietaireFichier;

    }

    /**
     * Get the value of typeFichier
     */
    public function getTypeFichier(): TypeFichier
    {
        return $this->typeFichier;
    }
    /**
     * Set the value of typeFichier
     *
     */
    public function setTypeFichier(TypeFichier $typeFichier): void
    {
        $this->typeFichier = $typeFichier;

    }

    /**
     * Get the value of dateAjout
     */
    public function getDateAjout(): DateTime
    {
        return $this->dateAjout;
    }
    /**
     * Set the value of dateAjout
     *
     */
    public function setDateAjout(DateTime $dateAjout): void
    {
        $this->dateAjout = $dateAjout;
    }

}