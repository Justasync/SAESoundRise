<?php

class RoleDao
{

    private ?PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo;
    }

    public function find(int $id): Role
    {
        $sql = "SELECT * FROM Role WHERE idRole = :id";
        $pdoStatement = $this->pdo->prepare($sql);
        $pdoStatement->execute(array(
            ':id' => $id
        ));
        $pdoStatement->setFetchMode(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, 'Role');
        $role = $pdoStatement->fetch();
        return $role;
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
