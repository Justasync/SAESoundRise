<?php

class AlbumDAO {
    private ?PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo;
    }

    public function findAll(): array
    {
        $sql = "SELECT * FROM album";
        $pdoStatement = $this->pdo->prepare($sql);
        $pdoStatement->execute();
        $pdoStatement->setFetchMode(PDO::FETCH_ASSOC);
        $tableau = $pdoStatement->fetchAll();
        $album = $this->hydrateMany($tableau);
        return $album;
    }

    public function find(int $id): Album
    {
        $sql = "SELECT * FROM album WHERE idAlbum = :id";
        $pdoStatement = $this->pdo->prepare($sql);
        $pdoStatement->execute(array(
            ':id' => $id
        ));

        $pdoStatement->setFetchMode(PDO::FETCH_ASSOC);
        $tableau = $pdoStatement->fetch();
        $album = $this->hydrate($tableau);
        return $album;
    }

    public function hydrate(array $tableaAssoc): Album
    {
        $album = new Album();
        $album->setIdAlbum(isset($tableaAssoc['idAlbum']) ? (int)$tableaAssoc['idAlbum'] : null);
        $album->setTitreAlbum($tableaAssoc['titreAlbum'] ?? null);
        $album->setDateSortieAlbum($tableaAssoc['dateSortieAlbum'] ?? null);
        $album->setPochetteAlbum($tableaAssoc['pochetteAlbum'] ?? null);
        return $album;
    }

    public function hydrateMany(array $tableauxAssoc): array
    {
        $albums = [];
        foreach ($tableauxAssoc as $tableauAssoc) {
            $albums[] = $this->hydrate($tableauAssoc);
        }
        return $albums;
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