<?php

class Role
{
    private int|null $idRole;
    private string|null $typeRole;
    private string|null $libelleRole;

    public function __construct(?int $idRole = null, ?string $typeRole = null, ?string $libelleRole = null)
    {
        $this->idRole = $idRole;
        $this->typeRole = $typeRole;
        $this->libelleRole = $libelleRole;
    }

    public function getIdRole(): ?int
    {
        return $this->idRole;
    }

    public function setIdRole(?int $idRole): void
    {
        $this->idRole = $idRole;
    }

    public function getTypeRole(): ?string
    {
        return $this->typeRole;
    }

    public function setTypeRole(?string $typeRole): void
    {
        $this->typeRole = $typeRole;
    }

    public function getLibelleRole(): ?string
    {
        return $this->libelleRole;
    }

    public function setLibelleRole(?string $libelleRole): void
    {
        $this->libelleRole = $libelleRole;
    }
}
