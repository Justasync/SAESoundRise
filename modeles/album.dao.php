<?php
/**
 * @file modeles/album.dao.php
 * @brief DAO pour la gestion des albums musicaux
 */
class AlbumDAO
{
    /**
     * @var PDO|null $pdo L'instance PDO pour la connexion à la base de données.
     */
    private ?PDO $pdo;

    /**
     * Constructeur de la classe AlbumDAO.
     * @param PDO|null $pdo L'instance PDO pour la connexion à la base de données.
     */
    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo;
    }

    /**
     * Récupère tous les albums de la base de données.
     * @return array Une liste d'albums.
     */
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

    /**
     * Récupère un album par son identifiant.
     * @param int $id L'identifiant de l'album.
     * @return Album L'Album correspondant.
     */
    public function find(int $id): Album
    {
        $sql = "SELECT a.*, u.pseudoUtilisateur 
                FROM album a
                JOIN utilisateur u ON a.artisteAlbum = u.emailUtilisateur
                WHERE a.idAlbum = :id";
        $pdoStatement = $this->pdo->prepare($sql);
        // Correction mineure : utilisation de la syntaxe [] au lieu de array()
        $pdoStatement->execute([
            ':id' => $id
        ]);

        $pdoStatement->setFetchMode(PDO::FETCH_ASSOC);
        $tableau = $pdoStatement->fetch();
        $album = $this->hydrate($tableau);
        // Set the pseudo if available
        if (isset($tableau['pseudoUtilisateur'])) {
            $album->setPseudoArtiste($tableau['pseudoUtilisateur']);
        }
        return $album;
    }

    /**
     * Récupère les albums d'un artiste en utilisant son pseudo.
     * @param string $artistePseudo Le pseudo de l'artiste.
     * @return array Une liste d'albums.
     */
    public function findByArtiste(string $artistePseudo): array
    {
        $sql = "SELECT a.* FROM album a
                JOIN utilisateur u ON a.artisteAlbum = u.emailUtilisateur -- CORRECTION : Jointure sur l'email
                WHERE u.pseudoUtilisateur = :artistePseudo 
                ORDER BY a.dateSortieAlbum DESC";
        $pdoStatement = $this->pdo->prepare($sql);
        $pdoStatement->execute([':artistePseudo' => $artistePseudo]);
        $pdoStatement->setFetchMode(PDO::FETCH_ASSOC);
        $tableau = $pdoStatement->fetchAll();
        $albums = $this->hydrateMany($tableau);
        return $albums;
    }

    /**
     * Récupère les albums d'un artiste en utilisant son email.
     * @param string $email L'email de l'artiste.
     * @return array Une liste d'albums.
     */
    public function findAllByArtistEmail(string $email): array
    {
        $sql = "SELECT a.* FROM album a
                JOIN utilisateur u ON a.artisteAlbum = u.emailUtilisateur -- CORRECTION : Jointure sur l'email (était sur u.pseudoUtilisateur)
                WHERE u.emailUtilisateur = :email
                ORDER BY a.dateSortieAlbum DESC";
        $pdoStatement = $this->pdo->prepare($sql);
        $pdoStatement->execute([':email' => $email]);
        $pdoStatement->setFetchMode(PDO::FETCH_ASSOC);
        $tableau = $pdoStatement->fetchAll();
        $albums = $this->hydrateMany($tableau);
        return $albums;
    }

    /**
     * Crée un nouvel album dans la base de données.
     * @param Album $album L'Album à créer.
     * @return int L'identifiant de l'album créé.
     */
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

    /**
     * Hydrate un album à partir d'un tableau associatif.
     * @param array $tableaAssoc Le tableau associatif contenant les données de l'album.
     * @return Album L'Album hydraté.
     */
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

    /**
     * Hydrate plusieurs albums à partir d'un tableau de tableaux associatifs.
     * @param array $tableauxAssoc Le tableau de tableaux associatifs contenant les données des albums.
     * @return array Une liste d'albums hydratés.
     */
    public function hydrateMany(array $tableauxAssoc): array
    {
        $albums = [];
        foreach ($tableauxAssoc as $tableauAssoc) {
            $albums[] = $this->hydrate($tableauAssoc);
        }
        return $albums;
    }

    /**
     * Récupère les albums les plus écoutés.
     *
     * @param int $limit Le nombre d'albums à récupérer.
     * @return array Une liste d'albums.
     * @throws PDOException En cas d'erreur lors de l'exécution de la requête.
     */
    public function findMostListened(int $limit = 8): array
    {
        // Cette requête calcule la somme des écoutes de toutes les chansons d'un album
        // pour déterminer la popularité de cet album.
        $sql = "SELECT 
                    a.*,
                    u.pseudoUtilisateur,
                    SUM(c.nbEcouteChanson) as totalEcoutes
                FROM album a
                JOIN chanson c ON a.idAlbum = c.albumChanson
                JOIN utilisateur u ON a.artisteAlbum = u.emailUtilisateur -- CORRECTION : Jointure sur l'email (était sur u.pseudoUtilisateur)
                GROUP BY a.idAlbum
                ORDER BY totalEcoutes DESC
                LIMIT :limit";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $albums = [];

            foreach ($results as $row) {
                $album = $this->hydrate($row);
                // Le setter setPseudoArtiste doit exister dans votre classe Album
                $album->setPseudoArtiste($row['pseudoUtilisateur']);
                $albums[] = $album;
            }
            return $albums;
        } catch (PDOException $e) {
            error_log('Erreur DAO lors de la récupération des albums les plus écoutés : ' . $e->getMessage());
            return [];
        }
    }


    /**
     * Getter pour la pdo
     * @return PDO|null
     */
    public function getPdo(): ?PDO
    {
        return $this->pdo;
    }

    /**
     * Setter pour la pdo
     * @param PDO|null $pdo
     */
    public function setPdo(?PDO $pdo): void
    {
        $this->pdo = $pdo;
    }
}
