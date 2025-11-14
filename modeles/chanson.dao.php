<?php

class ChansonDAO {
    private ?PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo;
    }

    public function findAll(): array
    {
        $sql = "SELECT * FROM chanson";
        $pdoStatement = $this->pdo->prepare($sql);
        $pdoStatement->execute();
        $pdoStatement->setFetchMode(PDO::FETCH_ASSOC);
        $tableau = $pdoStatement->fetchAll();
        $chanson = $this->hydrateMany($tableau);
        return $chanson;
    }

    public function findId(int $id): Chanson
    {
        $sql = "SELECT * FROM chanson WHERE idChanson = :id";
        $pdoStatement = $this->pdo->prepare($sql);
        $pdoStatement->execute(array(
            ':id' => $id
        ));

        $pdoStatement->setFetchMode(PDO::FETCH_ASSOC);
        $tableau = $pdoStatement->fetch();
        $chanson = $this->hydrate($tableau);
        return $chanson;
    }

    public function findUser(?string $email = null): array
    {
        if ($email) {
            $sql = "SELECT * FROM chanson WHERE emailPublicateur = :email";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':email' => $email]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $this->hydrateMany($results);
        } else {
            return [];
        }
    }

    public function hydrate(array $tableaAssoc): chanson
    {
        $chanson = new chanson();
        $chanson->setIdchanson(isset($tableaAssoc['idChanson']) ? (int)$tableaAssoc['idChanson'] : null);
        $chanson->setTitrechanson($tableaAssoc['titreChanson'] ?? null);
        $chanson->setDescriptionchanson($tableaAssoc['descriptionChanson'] ?? null);
        $chanson->setDureechanson(isset($tableaAssoc['dureeChanson']) ? (int)$tableaAssoc['dureeChanson'] : null);

        // Conversion sécurisée des dates SQL → objets DateTime
        $chanson->setDateTeleversementChanson(
            !empty($tableaAssoc['dateTeleversementChanson']) ? new DateTime($tableaAssoc['dateTeleversementChanson']) : null
        );

        $chanson->setCompositeurchanson($tableaAssoc['compositeurChanson'] ?? null);
        $chanson->setParolierchanson($tableaAssoc['parolierChanson'] ?? null);
        $chanson->setEstpublieechanson(isset($tableaAssoc['estPublieeChanson']) ? (bool)$tableaAssoc['estPublieeChanson'] : null);
        $chanson->setNbecoutechanson(isset($tableaAssoc['nbEcouteChanson']) ? (int)$tableaAssoc['nbEcouteChanson'] : null);
        $chanson->setUrlfichieraudiochanson($tableaAssoc['urlFichierAudioChanson'] ?? null);
        
        //albumChanson et genreChanson sont des objets, il faut les récupérer via leur DAO respectif
        // Album : création d’un objet minimal
        if (!empty($tableaAssoc['albumChanson'])) {
            $albumDAO = new AlbumDAO($this->pdo);
            $album = $albumDAO->find((int)$tableaAssoc['albumChanson']);
            $chanson->setAlbumChanson($album);
        } else {
            $chanson->setAlbumChanson(null);
        }

        // Genre : création d’un objet minimal
        if (!empty($tableaAssoc['genreChanson'])) {
            $genre = new GenreDAO($this->pdo);
            $genre = $genre->find((int)$tableaAssoc['genreChanson']);
            $chanson->setGenreChanson($genre);
        } else {
            $chanson->setGenreChanson(null);
        }

        $chanson->setEmailPublicateur($tableaAssoc['emailPublicateur'] ?? null);
        
        return $chanson;
    }

    public function hydrateMany(array $tableauxAssoc): array
    {
        $chansons = [];
        foreach ($tableauxAssoc as $tableauAssoc) {
            $chansons[] = $this->hydrate($tableauAssoc);
        }
        return $chansons;
    }

    public function rechercherParTitre(string $titre): array
    {
        $sql = "SELECT * FROM chanson WHERE titreChanson LIKE :titre";
        $pdoStatement = $this->pdo->prepare($sql);
        $likeTitre = '%' . $titre . '%';
        $pdoStatement->bindParam(':titre', $likeTitre, PDO::PARAM_STR);
        $pdoStatement->execute();
        $pdoStatement->setFetchMode(PDO::FETCH_ASSOC);
        $tableau = $pdoStatement->fetchAll();
        $chansons = $this->hydrateMany($tableau);
        return $chansons;
    }

    public function rechercherParAlbum(int $idAlbum): array
    {
        $sql = "SELECT * FROM chanson WHERE albumChanson = :idAlbum";
        $pdoStatement = $this->pdo->prepare($sql);
        $pdoStatement->bindParam(':idAlbum', $idAlbum, PDO::PARAM_INT);
        $pdoStatement->execute();
        $pdoStatement->setFetchMode(PDO::FETCH_ASSOC);
        $tableau = $pdoStatement->fetchAll();
        $chansons = $this->hydrateMany($tableau);
        return $chansons;
    }

    public function filtrerChanson(?int $idGenre = null, ?int $idAlbum = null, string $colonne = 'titreChanson', string $ordre = 'ASC'): array
    {
        $sql = "SELECT * FROM chanson WHERE 1=1";
        
        if ($idGenre !== null) {
            $sql .= " AND genreChanson = :idGenre";
        }
        if ($idAlbum !== null) {
            $sql .= " AND albumChanson = :idAlbum";
        }

        $sql .= " ORDER BY $colonne $ordre";

        $stmt = $this->pdo->prepare($sql);

        if ($idGenre !== null) {
            $stmt->bindValue(':idGenre', $idGenre, PDO::PARAM_INT);
        }
        if ($idAlbum !== null) {
            $stmt->bindValue(':idAlbum', $idAlbum, PDO::PARAM_INT);
        }

        $stmt->execute();
        $tableau = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $this->hydrateMany($tableau);
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
    public function setPdo($pdo): void
    {
        $this->pdo = $pdo;
    }
}