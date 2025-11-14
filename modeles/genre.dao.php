<?php

class GenreDAO
{
    private ?PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo;
    }

    public function findAll(): array
    {
        $sql = "SELECT * FROM genre";
        $pdoStatement = $this->pdo->prepare($sql);
        $pdoStatement->execute();
        $pdoStatement->setFetchMode(PDO::FETCH_ASSOC);
        $tableau = $pdoStatement->fetchAll();
        $genre = $this->hydrateMany($tableau);
        return $genre;
    }

    public function find(int $id): ?Genre
    {
        $sql = "SELECT * FROM genre WHERE idGenre = :id";
        $pdoStatement = $this->pdo->prepare($sql);
        $pdoStatement->execute([
            ':id' => $id
        ]);

        $pdoStatement->setFetchMode(PDO::FETCH_ASSOC);
        $tableau = $pdoStatement->fetch();
        if (!$tableau) {
            return null;
        }
        $genre = $this->hydrate($tableau);
        return $genre;
    }

    public function findByName(string $nomGenre): ?Genre
    {
        $sql = "SELECT * FROM genre WHERE nomGenre = :nomGenre";
        $pdoStatement = $this->pdo->prepare($sql);
        $pdoStatement->execute([
            ':nomGenre' => $nomGenre
        ]);

        $pdoStatement->setFetchMode(PDO::FETCH_ASSOC);
        $tableau = $pdoStatement->fetch();
        if (!$tableau) {
            return null;
        }
        $genre = $this->hydrate($tableau);
        return $genre;
    }

    public function hydrate(array $tableaAssoc): genre
    {
        $genre = new Genre();
        $genre->setIdGenre(isset($tableaAssoc['idGenre']) ? (int)$tableaAssoc['idGenre'] : null);
        $genre->setNomGenre($tableaAssoc['nomGenre'] ?? null);
        return $genre;
    }

    public function hydrateMany(array $tableauxAssoc): array
    {
        $genres = [];
        foreach ($tableauxAssoc as $tableauAssoc) {
            $genres[] = $this->hydrate($tableauAssoc);
        }
        return $genres;
    }

    /**
     * Get the value of pdo
     */
    public function getPdo(): ?PDO
    {
        return $this->pdo;
    }
    /**
     * Set the value of pdo
     *
     */
    public function setPdo(?PDO $pdo): void
    {
        $this->pdo = $pdo;
    }
}
