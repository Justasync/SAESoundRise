<?php

class BattleDAO {
    private ?PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo;
    }

    public function findAll(): array
    {
        $sql = "SELECT * FROM battle";
        $pdoStatement = $this->pdo->prepare($sql);
        $pdoStatement->execute();
        $pdoStatement->setFetchMode(PDO::FETCH_ASSOC);
        $tableau = $pdoStatement->fetchAll();
        $battle = $this->hydrateMany($tableau);
        return $battle;
    }

    public function find(int $id): Battle
    {
        $sql = "SELECT * FROM battle WHERE idBattle = :id";
        $pdoStatement = $this->pdo->prepare($sql);
        $pdoStatement->execute(array(
            ':id' => $id
        ));

        $pdoStatement->setFetchMode(PDO::FETCH_ASSOC);
        $tableau = $pdoStatement->fetch();
        $battle = $this->hydrate($tableau);
        return $battle;
    }

    public function hydrate(array $data): Battle
    {
        $battle = new Battle();

        $battle->setIdBattle(isset($data['idBattle']) ? (int)$data['idBattle'] : null);
        $battle->setTitreBattle($data['titreBattle'] ?? null);

        // Conversion sécurisée des dates SQL → objets DateTime
        $battle->setDateDebutBattle(
            !empty($data['dateDebutBattle']) ? new DateTime($data['dateDebutBattle']) : null
        );

        $battle->setDateFinBattle(
            !empty($data['dateFinBattle']) ? new DateTime($data['dateFinBattle']) : null
        );

        // Conversion du statut (enum)
        if (!empty($data['statutBattle'])) {
            $battle->setStatutBattle(StatutBattle::from($data['statutBattle']));
        }

        $battle->setEmailCreateurBattle($data['emailCreateurBattle'] ?? null);
        $battle->setEmailParticipantBattle($data['emailParticipantBattle'] ?? null);

        return $battle;
    }

    public function hydrateMany(array $rows): array
    {
        $battles = [];
        foreach ($rows as $row) {
            $battles[] = $this->hydrate($row);
        }
        return $battles;
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