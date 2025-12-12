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

    public function findTrending(int $limit = 8, int $daysAgo = 7): array
    {
        $sql = "SELECT 
                    c.*, 
                    u.pseudoUtilisateur AS artistePseudoUtilisateur,
                    -- Métriques récentes (période paramétrable)
                    COUNT(DISTINCT lc.emailUtilisateur) AS nouveaux_likes,
                    COUNT(DISTINCT cp.idPlaylist) AS nouvelles_playlists,
                    -- SCORE DE TENDANCE : 1 Like = 2 pts, 1 Playlist = 3 pts
                    (COUNT(DISTINCT lc.emailUtilisateur) * 2) + 
                    (COUNT(DISTINCT cp.idPlaylist) * 3) AS score_tendance
                FROM chanson c
                JOIN utilisateur u ON c.emailPublicateur = u.emailUtilisateur
                LEFT JOIN likeChanson lc 
                    ON c.idChanson = lc.idChanson 
                    AND lc.dateLike >= DATE_SUB(NOW(), INTERVAL :daysAgo DAY)
                LEFT JOIN chansonPlaylist cp 
                    ON c.idChanson = cp.idChanson 
                    AND cp.dateAjoutChanson >= DATE_SUB(NOW(), INTERVAL :daysAgo DAY)
                GROUP BY c.idChanson, u.pseudoUtilisateur
                HAVING score_tendance > 0
                ORDER BY score_tendance DESC
                LIMIT :limit;
        ";

        if ($limit < 1) {
            $limit = 8;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':daysAgo', $daysAgo, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $chansons = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        if ($chansons) {
            return $this->hydrateMany($chansons);
        }
        return [];
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

    public function createChanson(Chanson $chanson): bool
    {
        $sql = "INSERT INTO chanson (titreChanson, dureeChanson, dateTeleversementChanson, nbEcouteChanson, albumChanson, genreChanson, emailPublicateur, urlAudioChanson)
                VALUES (:titre, :duree, :dateTeleversement, :nbEcoute, :album, :genre, :emailPublicateur, :urlAudio)";

        $pdoStatement = $this->pdo->prepare($sql);

        $idAlbum = $chanson->getAlbumChanson() ? $chanson->getAlbumChanson()->getIdAlbum() : null;
        $idGenre = $chanson->getGenreChanson() ? $chanson->getGenreChanson()->getIdGenre() : null;
        $dateTeleversement = $chanson->getDateTeleversementChanson() ? $chanson->getDateTeleversementChanson()->format('Y-m-d H:i:s') : date('Y-m-d H:i:s');

        return $pdoStatement->execute([
            ':titre' => $chanson->getTitreChanson(),
            ':duree' => $chanson->getDureeChanson(),
            ':dateTeleversement' => $dateTeleversement,
            ':nbEcoute' => $chanson->getNbEcouteChanson() ?? 0,
            ':urlAudio' => $chanson->geturlAudioChanson(),
            ':album' => $idAlbum,
            ':genre' => $idGenre,
            ':emailPublicateur' => $chanson->getEmailPublicateur()
        ]);
    }

    public function findByTitreExact(string $titre, int $idAlbum): ?Chanson
    {
        // Implémentation future si nécessaire pour éviter les doublons
        return null;
    }

    public function updateChanson(Chanson $chanson): bool
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
     * Récupère les chansons likées par un utilisateur
     */
    public function findChansonsLikees(string $email): array
    {
        $sql = "
            SELECT c.*, l.dateLike, l.emailUtilisateur
            FROM likechanson l
            JOIN chanson c ON c.idChanson = l.idChanson
            WHERE l.emailUtilisateur = :email
            ORDER BY l.dateLike DESC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':email' => $email]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $chansons = [];
        foreach ($results as $row) {
            $chansons[] = $this->hydrate($row);
        }

        return $chansons;
    }

    /**
     * Ajoute un like pour une chanson (user + chanson)
     */
    public function addChansonLikee(string $emailUtilisateur, int $idChanson): bool
    {
        $sql = "INSERT INTO likechanson (emailUtilisateur, idChanson, dateLike)
                VALUES (:emailUtilisateur, :idChanson, :dateLike)";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':emailUtilisateur' => $emailUtilisateur,
            ':idChanson' => $idChanson,
            ':dateLike' => (new DateTime())->format('Y-m-d H:i:s')
        ]);
    }

    /**
     * Met à jour un like (change la date)
     */
    public function updateChansonLikee(string $emailUtilisateur, int $idChanson): bool
    {
        $sql = "UPDATE likechanson SET dateLike = :dateLike
                WHERE emailUtilisateur = :emailUtilisateur AND idChanson = :idChanson";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':emailUtilisateur' => $emailUtilisateur,
            ':idChanson' => $idChanson,
            ':dateLike' => (new DateTime())->format('Y-m-d H:i:s')
        ]);
    }

    /**
     * Bascule le like d'une chanson (ajoute ou supprime)
     */
    public function toggleLike(string $emailUtilisateur, int $idChanson): bool
    {
        // Vérifie si la chanson est déjà likée
        $sql = "SELECT COUNT(*) FROM likechanson WHERE emailUtilisateur = :emailUtilisateur AND idChanson = :idChanson";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':emailUtilisateur' => $emailUtilisateur,
            ':idChanson' => $idChanson
        ]);
        $isLiked = $stmt->fetchColumn() > 0;

        if ($isLiked) {
            // Supprime le like
            $sql = "DELETE FROM likechanson WHERE emailUtilisateur = :emailUtilisateur AND idChanson = :idChanson";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':emailUtilisateur' => $emailUtilisateur,
                ':idChanson' => $idChanson
            ]);
        } else {
            // Ajoute le like
            return $this->addChansonLikee($emailUtilisateur, $idChanson);
        }
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
