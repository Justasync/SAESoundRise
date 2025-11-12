<?php

class AlbumDAO {
    private ?PDO $pdo;

    public function __construct(?PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function find(?int $idAlbum): ?Album {
        $sql = "SELECT * FROM album WHERE idAlbum = :idAlbum";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':idAlbum', $idAlbum, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return new Album(
                $row['idAlbum'],
                $row['titreAlbum'],
                $row['dateSortieAlbum'],
                $row['pochetteAlbum']
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