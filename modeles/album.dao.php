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

    public function findByArtiste(string $artistePseudo): array
    {
        $sql = "SELECT * FROM album WHERE artisteAlbum = :artistePseudo ORDER BY dateSortieAlbum DESC";
        $pdoStatement = $this->pdo->prepare($sql);
        $pdoStatement->execute([':artistePseudo' => $artistePseudo]);
        $pdoStatement->setFetchMode(PDO::FETCH_ASSOC);
        $tableau = $pdoStatement->fetchAll();
        $albums = $this->hydrateMany($tableau);
        return $albums;
    }


    public function create(Album $album): int
    {
        $sql = "INSERT INTO album (nomAlbum, dateSortieAlbum, urlPochetteAlbum, artisteAlbum) VALUES (:nomAlbum, :dateSortie, :pochette, :artiste)";
        $pdoStatement = $this->pdo->prepare($sql);
        $pdoStatement->execute([
            ':nomAlbum' => $album->getTitreAlbum(),
            ':dateSortie' => $album->getDateSortieAlbum(),
            ':pochette' => $album->geturlPochetteAlbum(),
            ':artiste' => $album->getArtisteAlbum()
        ]);

        return (int)$this->pdo->lastInsertId();
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
