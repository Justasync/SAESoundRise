<?php
/**
 * @file modeles/battle.dao.php
 * @brief DAO pour la table battle
 */
class BattleDAO {
    /**
     * @var PDO|null $pdo L'instance PDO pour la connexion à la base de données.
     */
    private ?PDO $pdo;

    /**
     * Constructeur de la classe BattleDAO.
     * @param PDO|null $pdo L'instance PDO pour la connexion à la base de données.
     */
    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo;
    }

    /**
     * Récupère toutes les battles de la base de données.
     * @return array Une liste de battles.
     */
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

    /**
     * Récupère une battle par son identifiant.
     * @param int $id L'identifiant de la battle.
     * @return Battle La Battle correspondante.
     */
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

    /**
     * Hydrate une battle à partir d'un tableau associatif.
     * @param array $data Le tableau associatif contenant les données de la battle.
     * @return Battle La Battle hydratée.
     */
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

    /**
     * Hydrate plusieurs battles à partir d'un tableau de tableaux associatifs.
     * @param array $rows Le tableau de tableaux associatifs contenant les données des battles.
     * @return array Une liste de battles hydratées.
     */
    public function hydrateMany(array $rows): array
    {
        $battles = [];
        foreach ($rows as $row) {
            $battles[] = $this->hydrate($row);
        }
        return $battles;
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

    /**
     * Compte le nombre de battles gagnées par un artiste
     * Une battle est gagnée si l'artiste a reçu plus de votes que son adversaire
     * @param string $emailArtiste L'email de l'artiste
     * @return int Le nombre de battles gagnées
     */
    public function countBattlesWon(string $emailArtiste): int
    {
        // Cette requête compte les battles terminées où l'artiste a plus de votes que son adversaire
        $sql = "SELECT COUNT(*) as wins FROM (
                    SELECT 
                        b.idBattle,
                        b.emailCreateurBattle,
                        b.emailParticipantBattle,
                        COALESCE(SUM(CASE WHEN v.emailVotee = b.emailCreateurBattle THEN 1 ELSE 0 END), 0) as votes_createur,
                        COALESCE(SUM(CASE WHEN v.emailVotee = b.emailParticipantBattle THEN 1 ELSE 0 END), 0) as votes_participant
                    FROM battle b
                    LEFT JOIN vote v ON b.idBattle = v.idBattle
                    WHERE b.statutBattle = 'terminee'
                      AND (b.emailCreateurBattle = :email1 OR b.emailParticipantBattle = :email2)
                    GROUP BY b.idBattle, b.emailCreateurBattle, b.emailParticipantBattle
                ) AS battle_stats
                WHERE 
                    (emailCreateurBattle = :email3 AND votes_createur > votes_participant)
                    OR (emailParticipantBattle = :email4 AND votes_participant > votes_createur)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':email1' => $emailArtiste,
            ':email2' => $emailArtiste,
            ':email3' => $emailArtiste,
            ':email4' => $emailArtiste
        ]);
        return (int)$stmt->fetchColumn();
    }
}