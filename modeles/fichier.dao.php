<?php

class FichierDAO {
    private ?PDO $pdo;

    public function __construct(?PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function find(?int $idFichier): ?Fichier {
        $sql = "SELECT * FROM fichier WHERE idFichier = :idFichier";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':idFichier', $idFichier, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return new Fichier(
                $row['urlFichier'],
                TypeProprietaireFichier::from($row['typeProprietaireFichier']),
                TypeFichier::from($row['typeFichier'])
            );
        }
        return null; 
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