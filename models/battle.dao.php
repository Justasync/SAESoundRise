<?php

class BattleDAO {
    private ?PDO $pdo;

    public function __construct(?PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function find(?int $idBattle): ?Battle {
        $sql = "SELECT * FROM battle WHERE idBattle = :idBattle";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':idBattle', $idBattle, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return new Battle(
                $row['idBattle'],
                $row['titreBattle'],
                new DateTime($row['dateDebutBattle']),
                new DateTime($row['dateFinBattle']),
                Battle::StatutBattle::from($row['statutBattle']),
                $row['emailCreateurBattle'],
                $row['emailParticipantBattle']
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