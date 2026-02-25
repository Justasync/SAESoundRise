<?php
/**
 * @file modeles/playlist.dao.php
 * @brief DAO pour la gestion des playlists
 */

class PlaylistDAO
{
    /**
     * @var PDO|null $pdo L'instance PDO pour la connexion à la base de données.
     */
    private ?PDO $pdo;

    /**
     * Constructeur de la classe PlaylistDAO.
     * @param PDO|null $pdo L'instance PDO pour la connexion à la base de données.
     */
    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo;
    }

    /**
     * Récupère toutes les playlists de la base de données.
     * @return array Une liste de toutes les playlists.
     */
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

    public function findFromUser(int $id, ?string $email): ?playlist
    {
        $sql = "SELECT * FROM playlist WHERE idPlaylist = :id 
        AND emailProprietaire = :email";
        $pdoStatement = $this->pdo->prepare($sql);
        $pdoStatement->execute(array(
            ':id' => $id,
            ':email' => $email
        ));

        $pdoStatement->setFetchMode(PDO::FETCH_ASSOC);
        $tableau = $pdoStatement->fetch();
        if (!$tableau) {
            return null;
        }
        $playlist = $this->hydrate($tableau);
        return $playlist;
    }

    public function findAllFromUser(?string $email = null): array
    {
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

    public function hydrate(array $tableaAssoc): ?playlist
    {
        if (empty($tableaAssoc)) {
            return null;
        }

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
            $playlist = $this->hydrate($tableauAssoc);
            if ($playlist !== null) {
                $playlists[] = $playlist;
            }
        }
        return $playlists;
    }

    public function getChansonsByPlaylist(int $idPlaylist, ?string $emailUtilisateur = null): array
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
            $chanson = $chansonDAO->hydrate($row);
            // Vérifier si la chanson est likée par l'utilisateur connecté
            $isLiked = false;
            if ($emailUtilisateur) {
                $sqlLike = "SELECT 1 FROM likeChanson WHERE idChanson = :idChanson AND emailUtilisateur = :emailUtilisateur LIMIT 1";
                $stmtLike = $this->pdo->prepare($sqlLike);
                $stmtLike->execute([
                    ':idChanson' => $chanson->getIdChanson(),
                    ':emailUtilisateur' => $emailUtilisateur
                ]);
                $isLiked = $stmtLike->fetchColumn() ? true : false;
            }
            $chanson->setIsLiked($isLiked);
            $chansons[] = $chanson;
        }

        return $chansons;
    }

    public function ajouterChansonDansPlaylistUtilisateur(int $idPlaylist, int $idChanson, string $emailUtilisateur): array
    {
        $sqlPlaylist = "SELECT idPlaylist FROM playlist WHERE idPlaylist = :idPlaylist AND emailProprietaire = :emailUtilisateur LIMIT 1";
        $stmtPlaylist = $this->pdo->prepare($sqlPlaylist);
        $stmtPlaylist->execute([
            ':idPlaylist' => $idPlaylist,
            ':emailUtilisateur' => $emailUtilisateur
        ]);

        if (!$stmtPlaylist->fetchColumn()) {
            return [
                'success' => false,
                'alreadyExists' => false,
                'message' => 'Playlist introuvable ou non autorisée.'
            ];
        }

        $sqlChanson = "SELECT idChanson FROM chanson WHERE idChanson = :idChanson LIMIT 1";
        $stmtChanson = $this->pdo->prepare($sqlChanson);
        $stmtChanson->execute([':idChanson' => $idChanson]);

        if (!$stmtChanson->fetchColumn()) {
            return [
                'success' => false,
                'alreadyExists' => false,
                'message' => 'Chanson introuvable.'
            ];
        }

        $sqlExiste = "SELECT 1 FROM chansonPlaylist WHERE idPlaylist = :idPlaylist AND idChanson = :idChanson LIMIT 1";
        $stmtExiste = $this->pdo->prepare($sqlExiste);
        $stmtExiste->execute([
            ':idPlaylist' => $idPlaylist,
            ':idChanson' => $idChanson
        ]);

        if ($stmtExiste->fetchColumn()) {
            return [
                'success' => true,
                'alreadyExists' => true,
                'message' => 'Cette chanson est déjà dans la playlist.'
            ];
        }

        $sqlPosition = "SELECT COALESCE(MAX(positionChanson), 0) + 1 FROM chansonPlaylist WHERE idPlaylist = :idPlaylist";
        $stmtPosition = $this->pdo->prepare($sqlPosition);
        $stmtPosition->execute([':idPlaylist' => $idPlaylist]);
        $nouvellePosition = (int) $stmtPosition->fetchColumn();

        $sqlInsert = "INSERT INTO chansonPlaylist (idPlaylist, idChanson, positionChanson) VALUES (:idPlaylist, :idChanson, :positionChanson)";
        $stmtInsert = $this->pdo->prepare($sqlInsert);
        $stmtInsert->execute([
            ':idPlaylist' => $idPlaylist,
            ':idChanson' => $idChanson,
            ':positionChanson' => $nouvellePosition
        ]);

        return [
            'success' => true,
            'alreadyExists' => false,
            'message' => 'Chanson ajoutée à la playlist.'
        ];
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
