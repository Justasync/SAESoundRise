<?php

class RoleDao
{

    private ?PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo;
    }

    public function findAll(): array
    {
        $sql = "SELECT * FROM role";
        $pdoStatement = $this->pdo->prepare($sql);
        $pdoStatement->execute();
        $pdoStatement->setFetchMode(PDO::FETCH_ASSOC);
        $tableau = $pdoStatement->fetchAll();
        $roles = $this->hydrateMany($tableau);
        return $roles;
    }

    public function find(int $id): Role
    {
        $sql = "SELECT * FROM role WHERE idRole = :id";
        $pdoStatement = $this->pdo->prepare($sql);
        $pdoStatement->execute(array(
            ':id' => $id
        ));

        $pdoStatement->setFetchMode(PDO::FETCH_ASSOC);
        $tableau = $pdoStatement->fetch();
        $role = $this->hydrate($tableau);
        return $role;
    }

    public function findByType(string $typeRole): ?Role
    {
        $sql = "SELECT * FROM role WHERE typeRole = :typeRole LIMIT 1";
        $pdoStatement = $this->pdo->prepare($sql);
        $pdoStatement->execute([
            ':typeRole' => $typeRole
        ]);
        $pdoStatement->setFetchMode(PDO::FETCH_ASSOC);
        $tableau = $pdoStatement->fetch();

        if (!$tableau) {
            return null;
        }

        return $this->hydrate($tableau);
    }

    public function hydrate(array $tableaAssoc): Role
    {
        $role = new Role();
        $role->setIdRole(isset($tableaAssoc['idRole']) ? (int)$tableaAssoc['idRole'] : null);
        $role->setTypeRole($tableaAssoc['typeRole'] ?? null);
        $role->setLibelleRole($tableaAssoc['libelleRole'] ?? null);
        return $role;
    }

    public function hydrateMany(array $tableauxAssoc): array
    {
        $roles = [];
        foreach ($tableauxAssoc as $tableauAssoc) {
            $roles[] = $this->hydrate($tableauAssoc);
        }
        return $roles;
    }

    public function getPDO(): ?PDO
    {
        return $this->pdo;
    }

    public function setPDO($pdo): void
    {
        $this->pdo = $pdo;
    }
}
