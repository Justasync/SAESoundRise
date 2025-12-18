<?php
/**
 * @file modeles/role.class.php
 * @brief Classe représentant un rôle utilisateur
 */

class Role
{
    /**
     * @var int|null $idRole L'identifiant unique du rôle.
     */
    private int|null $idRole;

    /**
     * @var string|null $typeRole Le type/code du rôle.
     */
    private string|null $typeRole;

    /**
     * @var string|null $libelleRole La description/libellé du rôle.
     */
    private string|null $libelleRole;

    /**
     * Constructeur de la classe Role.
     * @param int|null $idRole L'identifiant unique.
     * @param string|null $typeRole Le type du rôle.
     * @param string|null $libelleRole La description du rôle.
     */
    public function __construct(?int $idRole = null, ?string $typeRole = null, ?string $libelleRole = null)
    {
        $this->idRole = $idRole;
        $this->typeRole = $typeRole;
        $this->libelleRole = $libelleRole;
    }

    /**
     * Getter pour l'id du rôle
     * @return int|null
     */
    public function getIdRole(): ?int
    {
        return $this->idRole;
    }

    /**
     * Setter pour l'id du rôle
     * @param int|null $idRole
     * @return void
     */
    public function setIdRole(?int $idRole): void
    {
        $this->idRole = $idRole;
    }

    /**
     * Getter pour le rôle en tant qu'enum
     * @return RoleEnum|null
     */
    public function getRoleEnum(): ?RoleEnum
    {
        if ($this->typeRole !== null) {
            return RoleEnum::from($this->typeRole);
        }
        return null;
    }

    /**
     * Getter pour le type du rôle
     * @return string|null
     */
    public function getTypeRole(): ?string
    {
        return $this->typeRole;
    }

    /**
     * Setter pour le type du rôle
     * @param string|null $typeRole
     * @return void
     */
    public function setTypeRole(?string $typeRole): void
    {
        $this->typeRole = $typeRole;
    }

    /**
     * Getter pour le libellé du rôle
     * @return string|null
     */
    public function getLibelleRole(): ?string
    {
        return $this->libelleRole;
    }

    /**
     * Setter pour le libellé du rôle
     * @param string|null $libelleRole
     * @return void
     */
    public function setLibelleRole(?string $libelleRole): void
    {
        $this->libelleRole = $libelleRole;
    }
}

