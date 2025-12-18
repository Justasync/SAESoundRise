<?php
/**
 * @file modeles/genre.dao.php
 * @brief DAO pour la gestion des genres musicaux
 */

class GenreDAO
{
    /**
     * @var PDO|null $pdo L'instance PDO pour la connexion à la base de données.
     */
    private ?PDO $pdo;

    /**
     * Constructeur de la classe GenreDAO.
     * @param PDO|null $pdo L'instance PDO pour la connexion à la base de données.
     */
    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo;
    }

    /**
     * Récupère tous les genres de la base de données.
     * @return array Une liste de tous les genres musicaux.
     */
    public function findAll(): array
    {
        $sql = "SELECT * FROM genre";
        $pdoStatement = $this->pdo->prepare($sql);
        $pdoStatement->execute();
        $pdoStatement->setFetchMode(PDO::FETCH_ASSOC);
        $tableau = $pdoStatement->fetchAll();
        $genre = $this->hydrateMany($tableau);
        return $genre;
    }

    public function find(int $id): ?Genre
    {
        $sql = "SELECT * FROM genre WHERE idGenre = :id";
        $pdoStatement = $this->pdo->prepare($sql);
        $pdoStatement->execute([
            ':id' => $id
        ]);

        $pdoStatement->setFetchMode(PDO::FETCH_ASSOC);
        $tableau = $pdoStatement->fetch();
        if (!$tableau) {
            return null;
        }
        $genre = $this->hydrate($tableau);
        return $genre;
    }

    public function findByName(string $nomGenre): ?Genre
    {
        $sql = "SELECT * FROM genre WHERE nomGenre = :nomGenre";
        $pdoStatement = $this->pdo->prepare($sql);
        $pdoStatement->execute([
            ':nomGenre' => $nomGenre
        ]);

        $pdoStatement->setFetchMode(PDO::FETCH_ASSOC);
        $tableau = $pdoStatement->fetch();
        if (!$tableau) {
            return null;
        }
        $genre = $this->hydrate($tableau);
        return $genre;
    }

    public function create(string $nomGenre): int
    {
        $sql = "INSERT INTO genre (nomGenre) VALUES (:nomGenre)";
        $pdoStatement = $this->pdo->prepare($sql);
        $pdoStatement->execute([
            ':nomGenre' => $nomGenre
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    public function findOrCreateByName(string $nomGenre): ?Genre
    {
        // Si le nom du genre est vide, on ne fait rien et on retourne null.
        if (trim($nomGenre) === '') {
            return null;
        }

        // On cherche d'abord si le genre existe.
        $genre = $this->findByName($nomGenre);

        if ($genre) {
            // S'il existe, on le retourne.
            return $genre;
        } else {
            // Sinon, on le crée...
            $idNouveauGenre = $this->create($nomGenre);
            // ...et on retourne le nouvel objet Genre.
            return $this->find($idNouveauGenre);
        }
    }

    public function rechercherParNom(string $nom): array
    {
        $sql = "SELECT * FROM genre WHERE nomGenre LIKE :nom";
        $pdoStatement = $this->pdo->prepare($sql);
        $likeNom = '%' . $nom . '%';
        $pdoStatement->bindParam(':nom', $likeNom, PDO::PARAM_STR);
        $pdoStatement->execute();
        $pdoStatement->setFetchMode(PDO::FETCH_ASSOC);
        $tableau = $pdoStatement->fetchAll();
        $genres = $this->hydrateMany($tableau);
        return $genres;
    }

    public function hydrate(array $tableaAssoc): genre
    {
        $genre = new Genre();
        $genre->setIdGenre(isset($tableaAssoc['idGenre']) ? (int)$tableaAssoc['idGenre'] : null);
        $genre->setNomGenre($tableaAssoc['nomGenre'] ?? null);
        return $genre;
    }

    public function hydrateMany(array $tableauxAssoc): array
    {
        $genres = [];
        foreach ($tableauxAssoc as $tableauAssoc) {
            $genres[] = $this->hydrate($tableauAssoc);
        }
        return $genres;
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
