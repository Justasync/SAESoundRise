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
        $sql = "SELECT * FROM playlist WHERE idplaylist = :id";
        $pdoStatement = $this->pdo->prepare($sql);
        $pdoStatement->execute(array(
            ':id' => $id
        ));

        $pdoStatement->setFetchMode(PDO::FETCH_ASSOC);
        $tableau = $pdoStatement->fetch();
        $playlist = $this->hydrate($tableau);
        return $playlist;
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