<?php

class PlaylistDAO {
    private ?PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo;
    }

    public function findAll(): array
    {
        $sql = "SELECT * FROM playlist";
        $pdoStatement = $this->pdo->prepare($sql);
        $pdoStatement->execute();
        $pdoStatement->setFetchMode(PDO::FETCH_ASSOC);
        $tableau = $pdoStatement->fetchAll();
        $playlist = $this->hydrateMany($tableau);
        return $playlist;
    }

    public function find(int $id): playlist
    {
        $sql = "SELECT * FROM playlist WHERE idPlaylist = :id";
        $pdoStatement = $this->pdo->prepare($sql);
        $pdoStatement->execute(array(
            ':id' => $id
        ));

        $pdoStatement->setFetchMode(PDO::FETCH_ASSOC);
        $tableau = $pdoStatement->fetch();
        $playlist = $this->hydrate($tableau);
        return $playlist;
    }

    public function findUser(?string $email = null): array {
        if ($email) {
            $sql = "SELECT * FROM playlist WHERE emailProprietaire = :email";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':email' => $email]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $this->hydrateMany($results);
        } else {
            return [];
        }
    }

    public function hydrate(array $tableaAssoc): playlist
    {
        $playlist = new Playlist();
        $playlist->setIdPlaylist(isset($tableaAssoc['idPlaylist']) ? (int)$tableaAssoc['idPlaylist'] : null);
        $playlist->setNomPlaylist($tableaAssoc['nomPlaylist'] ?? null);
        $playlist->setEstPubliquePlaylist($tableaAssoc['estPubliquePlaylist'] ?? null);

        // Conversion sécurisée des dates SQL → objets DateTime
        $playlist->setDateCreationPlaylist(
            !empty($tableaAssoc['dateCreationPlaylist']) ? new DateTime($tableaAssoc['dateCreationPlaylist']) : null
        );

        $playlist->setDateDerniereModification(
            !empty($tableaAssoc['dateDerniereModification']) ? new DateTime($tableaAssoc['dateDerniereModification']) : null
        );
        
        $playlist->setEmailProprietaire($tableaAssoc['emailProprietaire'] ?? null);
        return $playlist;
    }

    public function hydrateMany(array $tableauxAssoc): array
    {
        $playlists = [];
        foreach ($tableauxAssoc as $tableauAssoc) {
            $playlists[] = $this->hydrate($tableauAssoc);
        }
        return $playlists;
    }

    public function getChansonsByPlaylist(int $idPlaylist): array
    {
        $sql = "
            SELECT c.* 
            FROM chanson c
            JOIN chansonPlaylist cp ON c.idChanson = cp.idChanson
            WHERE cp.idPlaylist = :idPlaylist
            ORDER BY cp.positionChanson ASC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':idPlaylist' => $idPlaylist]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $chansons = [];
        foreach ($results as $row) {
            $chansonDAO = new ChansonDAO($this->pdo);
            $chansons[] = $chansonDAO->hydrate($row);
        }

        return $chansons;
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