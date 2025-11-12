<?php

class GenreDAO
{
    private ?PDO $pdo;

    public function __construct(?PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findAll(): array
    {
        $sql = "SELECT * FROM genre";
        $stmt = $this->pdo->query($sql);
        $genres = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $genres[] = new Genre(
                $row['idGenre'],
                $row['nomGenre']
            );
        }
        return $genres;
    }

    public function find(?int $idGenre): ?Genre
    {
        $sql = "SELECT * FROM genre WHERE idGenre = :idGenre";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':idGenre', $idGenre, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return new Genre(
                $row['idGenre'],
                $row['nomGenre']
            );
        }
        return null;
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
