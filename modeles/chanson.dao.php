<?php

class ChansonDAO
{
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
        $chanson->setDureechanson(isset($tableaAssoc['dureeChanson']) ? (int)$tableaAssoc['dureeChanson'] : null);

        // Conversion sécurisée des dates SQL → objets DateTime
        $chanson->setDateTeleversementChanson(
            !empty($tableaAssoc['dateTeleversementChanson']) ? new DateTime($tableaAssoc['dateTeleversementChanson']) : null
        );
        $chanson->setNbecoutechanson(isset($tableaAssoc['nbEcouteChanson']) ? (int)$tableaAssoc['nbEcouteChanson'] : null);
        $chanson->seturlAudioChanson($tableaAssoc['urlAudioChanson'] ?? null);

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

    public function create(Chanson $chanson): bool
    {
        $idAlbum = $chanson->getAlbumChanson() ? $chanson->getAlbumChanson()->getIdAlbum() : null;

        // Vérifier si une chanson avec le même titre existe déjà dans cet album
        if ($idAlbum !== null) {
            $existingChanson = $this->findByTitreExact($chanson->getTitreChanson(), $idAlbum);
            if ($existingChanson) {
                // La chanson existe déjà, on ne la crée pas.
                // On pourrait retourner false ou l'ID existant, ou même mettre à jour la chanson existante.
                // Pour l'instant, on retourne false pour indiquer que la création n'a pas eu lieu.
                return false;
            }
        }

        $sql = "INSERT INTO chanson (titreChanson, dureeChanson, dateTeleversementChanson, nbEcouteChanson, albumChanson, genreChanson, emailPublicateur, urlAudioChanson)
                VALUES (:titre, :duree, :dateTeleversement, :nbEcoute, :idAlbum, :idGenre, :email, :urlAudio)";

        $stmt = $this->pdo->prepare($sql);

        $idGenre = $chanson->getGenreChanson() ? $chanson->getGenreChanson()->getIdGenre() : null;
        $dateTeleversement = $chanson->getDateTeleversementChanson() ? $chanson->getDateTeleversementChanson()->format('Y-m-d H:i:s') : date('Y-m-d H:i:s');

        return $stmt->execute([
            ':titre' => $chanson->getTitreChanson(),
            ':duree' => $chanson->getDureeChanson(),
            ':dateTeleversement' => $dateTeleversement,
            ':nbEcoute' => $chanson->getNbEcouteChanson() ?? 0,
            ':urlAudio' => $chanson->geturlAudioChanson(),
            ':idAlbum' => $idAlbum,
            ':idGenre' => $idGenre,
            ':email' => $chanson->getEmailPublicateur(),
        ]);
    }

    public function findByTitreExact(string $titre, int $idAlbum): ?Chanson {
        $sql = "SELECT * FROM chanson WHERE titreChanson = :titre AND albumChanson = :idAlbum";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':titre' => $titre,
            ':idAlbum' => $idAlbum
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return $this->hydrate($row);
        }
        return null; // Retourne null si aucune chanson n'est trouvée
    }

    public function update(Chanson $chanson): bool
    {
        $sql = "UPDATE chanson SET 
                    titreChanson = :titre, 
                    genreChanson = :idGenre
                WHERE idChanson = :idChanson";

        $pdoStatement = $this->pdo->prepare($sql);

        $idGenre = $chanson->getGenreChanson() ? $chanson->getGenreChanson()->getIdGenre() : null;

        return $pdoStatement->execute([
            ':titre' => $chanson->getTitreChanson(),
            ':idGenre' => $idGenre,
            ':idChanson' => $chanson->getIdChanson()
        ]);
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
