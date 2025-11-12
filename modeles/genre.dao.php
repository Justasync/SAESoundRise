<?php

class GenreDAO {
    private ?PDO $pdo;

    public function __construct(?PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function find(?int $idGenre): ?Genre {
        $sql = "SELECT * FROM genre WHERE idGenre = :idGenre";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':idGenre', $idGenre, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return new Genre(
                $row['idGenre'],
                $row['nomgenre']
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