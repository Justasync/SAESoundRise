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

    public function find(int $id): Chanson
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

    public function hydrate(array $tableaAssoc): chanson
    {
        $chanson = new chanson();
        $chanson->setIdchanson(isset($tableaAssoc['idchanson']) ? (int)$tableaAssoc['idchanson'] : null);
        $chanson->setTitrechanson($tableaAssoc['titrechanson'] ?? null);
        $chanson->setDescriptionchanson($tableaAssoc['descriptionchanson'] ?? null);
        $chanson->setDureechanson(isset($tableaAssoc['dureechanson']) ? (int)$tableaAssoc['dureechanson'] : null);
        $chanson->setDateteleversementchanson($tableaAssoc['dateteleversementchanson'] ?? null);
        $chanson->setCompositeurchanson($tableaAssoc['compositeurchanson'] ?? null);
        $chanson->setParolierchanson($tableaAssoc['parolierchanson'] ?? null);
        $chanson->setEstpublieechanson(isset($tableaAssoc['estpublieechanson']) ? (bool)$tableaAssoc['estpublieechanson'] : null);
        $chanson->setNbecoutechanson(isset($tableaAssoc['nbecoutechanson']) ? (int)$tableaAssoc['nbecoutechanson'] : null);
        //albumChanson et genreChanson sont des objets, il faut les récupérer via leur DAO respectif
        $chanson->setAlbumchanson($tableaAssoc['albumchanson'] ?? null);
        $chanson->setGenrechanson($tableaAssoc['genrechanson'] ?? null);
        $chanson->setIdplaylist($tableaAssoc['idplaylist'] ?? null);
        $chanson->setEmailpublicateur($tableaAssoc['emailpublicateur'] ?? null);
        $chanson->setUrlfichieraudiochanson($tableaAssoc['urlfichieraudiochanson'] ?? null);
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