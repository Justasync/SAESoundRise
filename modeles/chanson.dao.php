<?php

class ChansonDAO {
    private ?PDO $pdo;

    public function __construct(?PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function find(?int $idChanson): ?Chanson {
        $sql = "SELECT * FROM chanson WHERE idChanson = :idChanson";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':idChanson', $idChanson, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return new Chanson(
                $row['idChanson'],
                $row['titreChanson'],
                $row['descriptionChanson'],
                $row['dureeChanson'],
                $row['dateTeleversementChanson'],
                $row['compositeurChanson'],
                $row['parolierChanson'],
                $row['estPublieeChanson'],
                $row['nbEcouteChanson'],
                $row['idAlbum'],
                $row['idGenre'],
                $row['emailUtilisateur'],
                $row['urlFichier']
            );
        }        return null;
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
    public function setPdo($pdo): void
    {
        $this->pdo = $pdo;
    }
}