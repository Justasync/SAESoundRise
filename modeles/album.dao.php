<?php

class AlbumDAO
{
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
        $album->setTitreAlbum($tableaAssoc['nomAlbum'] ?? null);
        $album->setDateSortieAlbum($tableaAssoc['dateSortieAlbum'] ?? null);
        $album->seturlPochetteAlbum($tableaAssoc['urlPochetteAlbum'] ?? null);
        $album->setArtisteAlbum($tableaAssoc['artisteAlbum'] ?? null);
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

    public function create(Album $album): bool
    {
        $sql = "INSERT INTO album (nomAlbum, dateSortieAlbum, urlPochetteAlbum, artisteAlbum) VALUES (:titre, :dateSortie, :urlPochetteAlbum, :artisteAlbum)";
        $pdoStatement = $this->pdo->prepare($sql);

        $params = [
            ':titre' => $album->getTitreAlbum(),
            ':dateSortie' => $album->getDateSortieAlbum(),
            ':urlPochetteAlbum' => $album->geturlPochetteAlbum(),
            ':artisteAlbum' => $album->getArtisteAlbum(),

        ];

        return $pdoStatement->execute($params);
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
