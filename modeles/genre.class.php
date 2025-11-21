<?php

class Genre {
    private int|null $idGenre;
    private string|null $nomGenre;

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
