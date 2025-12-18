<?php
/**
 * @file modeles/genre.class.php
 * @brief Classe représentant un genre musical
 */

class Genre {
    /**
     * @var int|null $idGenre L'identifiant unique du genre.
     */
    private int|null $idGenre;

    /**
     * @var string|null $nomGenre Le nom du genre musical.
     */
    private string|null $nomGenre;

    /**
     * Constructeur de la classe Genre.
     * @param int|null $idGenre L'identifiant unique.
     * @param string|null $nomGenre Le nom du genre.
     */
    public function __construct(?int $idGenre = null, ?string $nomGenre = null) {
        $this->idGenre = $idGenre;
        $this->nomGenre = $nomGenre;
    }



    /**
     * Get the value of idGenre
     */ 
    public function getIdGenre(): ?int
    {
        return $this->idGenre;
    }

    /**
     * Set the value of idGenre
     *
     */ 
    public function setIdGenre(?int $idGenre): void
    {
        $this->idGenre = $idGenre;
    }

    /**
     * Get the value of nomGenre
     */ 
    public function getNomGenre(): ?string
    {
        return $this->nomGenre;
    }

    /**
     * Set the value of nomGenre
     *
     */ 
    public function setNomGenre(?string $nomGenre): void
    {
        $this->nomGenre = $nomGenre;
    }
}
