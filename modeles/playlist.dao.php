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
                $pochette = $this->recupererPochetteAuto($playlist->getIdPlaylist());
                $playlist->setUrlPochetteAuto($pochette);
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

    /**
     * Pour un ensemble de chansons et un utilisateur, retourne un tableau
     * chansonId => [playlistId, ...] indiquant dans quelles playlists
     * de l'utilisateur chaque chanson se trouve déjà.
     *
     * @param array $chansonIds Liste d'IDs de chansons.
     * @param string $emailUtilisateur Email du propriétaire des playlists.
     * @return array<int, int[]> Map chansonId => tableau de playlistIds.
     */
    public function getPlaylistIdsForChansons(array $chansonIds, string $emailUtilisateur): array
    {
        if (empty($chansonIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($chansonIds), '?'));
        $sql = "
            SELECT cp.idChanson, cp.idPlaylist
            FROM chansonPlaylist cp
            JOIN playlist p ON cp.idPlaylist = p.idPlaylist
            WHERE p.emailProprietaire = ?
              AND cp.idChanson IN ($placeholders)
        ";

        $params = array_merge([$emailUtilisateur], array_map('intval', $chansonIds));
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $map = [];
        foreach ($rows as $row) {
            $map[(int)$row['idChanson']][] = (int)$row['idPlaylist'];
        }
        return $map;
    }

    /**
     * Ajoute une chanson à une playlist.
     * La position est calculée automatiquement (dernière position + 1).
     *
     * @param int $idPlaylist L'ID de la playlist.
     * @param int $idChanson L'ID de la chanson à ajouter.
     * @return bool true si l'ajout a réussi, false si la chanson est déjà dans la playlist.
     */
    public function ajouterChansonPlaylist(int $idPlaylist, int $idChanson): bool
    {
        $sqlCheck = "SELECT 1 FROM chansonPlaylist WHERE idPlaylist = :idPlaylist AND idChanson = :idChanson LIMIT 1";
        $stmtCheck = $this->pdo->prepare($sqlCheck);
        $stmtCheck->execute([':idPlaylist' => $idPlaylist, ':idChanson' => $idChanson]);
        if ($stmtCheck->fetchColumn()) {
            return false;
        }

        $sqlPos = "SELECT COALESCE(MAX(positionChanson), 0) + 1 AS nextPos FROM chansonPlaylist WHERE idPlaylist = :idPlaylist";
        $stmtPos = $this->pdo->prepare($sqlPos);
        $stmtPos->execute([':idPlaylist' => $idPlaylist]);
        $nextPos = (int)$stmtPos->fetchColumn();

        $sql = "INSERT INTO chansonPlaylist (idPlaylist, idChanson, positionChanson) VALUES (:idPlaylist, :idChanson, :position)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':idPlaylist' => $idPlaylist,
            ':idChanson' => $idChanson,
            ':position' => $nextPos
        ]);

        $sqlUpdate = "UPDATE playlist SET dateDerniereModification = NOW() WHERE idPlaylist = :idPlaylist";
        $stmtUpdate = $this->pdo->prepare($sqlUpdate);
        $stmtUpdate->execute([':idPlaylist' => $idPlaylist]);

        return true;
    }

    /**
     * @param int $idPlaylist L'ID de la playlist pour laquelle récupérer la pochette automatique.
     * @return string|null L'URL de la pochette automatique ou null si aucune chanson n'est associée à la playlist.
     */
    public function recupererPochetteAuto(int $idPlaylist): ?string
    {
        $sql = "
            SELECT a.urlPochetteAlbum
            FROM chansonPlaylist cp
            JOIN chanson c ON cp.idChanson = c.idChanson
            JOIN album a ON c.albumChanson = a.idAlbum
            WHERE cp.idPlaylist = :idPlaylist
            ORDER BY cp.positionChanson ASC
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['idPlaylist' => $idPlaylist]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ? $result['urlPochetteAlbum'] : null;
    }
}
