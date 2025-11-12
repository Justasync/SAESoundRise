<?php

class Genre {
    private int|null $idGenre;
    private string|null $nomgenre;

    public function __construct(?int $idGenre = null, ?string $nomgenre = null) {
        $this->idGenre = $idGenre;
        $this->nomgenre = $nomgenre;
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
     * Get the value of nomgenre
     */ 
    public function getNomgenre(): ?string
    {
        return $this->nomgenre;
    }

    /**
     * Set the value of nomgenre
     *
     */ 
    public function setNomgenre(?string $nomgenre): void
    {
        $this->nomgenre = $nomgenre;
    }
}
