<?php

class FichierDAO {
    private ?PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo;
    }

    public function findAll(): array
    {
        $sql = "SELECT * FROM fichier";
        $pdoStatement = $this->pdo->prepare($sql);
        $pdoStatement->execute();
        $pdoStatement->setFetchMode(PDO::FETCH_ASSOC);
        $tableau = $pdoStatement->fetchAll();
        $fichier = $this->hydrateMany($tableau);
        return $fichier;
    }

    public function find(int $id): Fichier
    {
        $sql = "SELECT * FROM fichier WHERE idFichier = :id";
        $pdoStatement = $this->pdo->prepare($sql);
        $pdoStatement->execute(array(
            ':id' => $id
        ));

        $pdoStatement->setFetchMode(PDO::FETCH_ASSOC);
        $tableau = $pdoStatement->fetch();
        $fichier = $this->hydrate($tableau);
        return $fichier;
    }

    public function hydrate(array $tableaAssoc): Fichier
    {
        $fichier = new Fichier();
        $fichier->setUrlFichier(isset($tableaAssoc['urlFichier']) ? (int)$tableaAssoc['urlFichier'] : null);

        // Conversion du type (enum)
        if (!empty($tableaAssoc['typeFichier'])) {
            $fichier->setTypeFichier(TypeFichier::from($tableaAssoc['typeFichier']));
        }
        // Conversion du format (enum)
        if (!empty($tableaAssoc['formatFichier'])) {
            $fichier->setFormatFichier(FormatFichier::from($tableaAssoc['formatFichier']));
        }

        // Conversion sécurisée des dates SQL → objets DateTime
        $fichier->setDateAjout(
            !empty($tableaAssoc['dateAjout']) ? new DateTime($tableaAssoc['dateAjout']) : null
        );

        return $fichier;
    }

    public function hydrateMany(array $tableauxAssoc): array
    {
        $fichiers = [];
        foreach ($tableauxAssoc as $tableauAssoc) {
            $fichiers[] = $this->hydrate($tableauAssoc);
        }
        return $fichiers;
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