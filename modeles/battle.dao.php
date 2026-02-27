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
     * @brief Constructeur de la classe BattleDAO.
     * @param PDO|null $pdo L'instance PDO pour la connexion à la base de données.
     */
    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo;
    }

    /**
     * @brief Récupère toutes les battles de la base de données.
     * @return array Une liste d'objets Battle.
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
     * @brief Récupère une battle par son identifiant.
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
     * @brief Hydrate une battle à partir d'un tableau associatif.
     * 
     * Cette méthode remplit l'objet Battle avec ses données de base et hydrate également
     * les objets Utilisateur et Chanson liés pour un affichage sécurisé.
     * 
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
        $battle->setIdChansonCreateur($data['idChansonCreateur'] ?? null);
        $battle->setIdChansonParticipant($data['idChansonParticipant'] ?? null);

        // --- HYDRATATION DES OBJETS LIÉS (SÉCURITÉ PSEUDO/PHOTO) ---
        $uDao = new UtilisateurDAO($this->pdo);
        $cDao = new ChansonDAO($this->pdo);

        // Hydratation du créateur
        if (!empty($data['emailCreateurBattle'])) {
            $battle->setCreateur($uDao->find($data['emailCreateurBattle']));
        }

        // Hydratation du participant
        if (!empty($data['emailParticipantBattle'])) {
            $battle->setParticipant($uDao->find($data['emailParticipantBattle']));
        }

        // Hydratation de la chanson du créateur
        if (!empty($data['idChansonCreateur'])) {
            $battle->setChansonCreateurObj($cDao->findId((int)$data['idChansonCreateur']));
        }

        // Hydratation de la chanson du participant
        if (!empty($data['idChansonParticipant'])) {
            $battle->setChansonParticipantObj($cDao->findId((int)$data['idChansonParticipant']));
        }

        return $battle;
    }

    /**
     * @brief Hydrate plusieurs battles à partir d'un tableau de tableaux associatifs.
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
     * @brief Getter pour la pdo
     * @return PDO|null
     */
    public function getPdo(): ?PDO
    {
        return $this->pdo;
    }

    /**
     * @brief Setter pour la pdo
     * @param PDO|null $pdo
     * @return void
     */
    public function setPdo(?PDO $pdo): void
    {
        $this->pdo = $pdo;
    }

    /**
     * @brief Compte le nombre de battles gagnées par un artiste.
     * Une battle est gagnée si l'artiste a reçu plus de votes que son adversaire.
     * @param string $emailArtiste L'email de l'artiste.
     * @return int Le nombre de battles gagnées.
     */
    public function countBattlesWon(string $emailArtiste): int
    {
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

    /**
     * @brief Met à jour les informations d'une battle existante.
     * @param Battle $battle L'objet Battle contenant les nouvelles données.
     * @return bool Vrai si la mise à jour a réussi, faux sinon.
     */
    public function update(Battle $battle): bool {
        $sql = "UPDATE battle SET 
                statutBattle = :statut, 
                emailParticipantBattle = :participant,
                idChansonCreateur = :chansonCreateur,
                idChansonParticipant = :chansonPart
                WHERE idBattle = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':statut' => $battle->getStatutBattle()->value,
            ':participant' => $battle->getEmailParticipantBattle(),
            ':chansonCreateur' => $battle->getIdChansonCreateur(),
            ':chansonPart' => $battle->getIdChansonParticipant(),
            ':id' => $battle->getIdBattle()
        ]);
    }

    /**
     * @brief Enregistre un vote pour une battle.
     * @param string $emailVotant L'email de l'utilisateur qui vote.
     * @param int $idBattle L'identifiant de la battle.
     * @param string $emailVotee L'email de l'artiste pour qui le vote est émis.
     * @return bool Vrai si le vote a été enregistré, faux sinon.
     */
    public function addVote(string $emailVotant, int $idBattle, string $emailVotee): bool {
        $sql = "INSERT INTO vote (emailVotant, idBattle, emailVotee) VALUES (:votant, :idB, :votee)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':votant' => $emailVotant, ':idB' => $idBattle, ':votee' => $emailVotee]);
    }

    /**
     * @brief Insère une nouvelle battle dans la base de données.
     * @param Battle $battle L'objet Battle à insérer.
     * @return bool Vrai si l'insertion a réussi, faux sinon.
     */
    public function insert(Battle $battle): bool {
        $sql = "INSERT INTO battle (titreBattle, dateDebutBattle, dateFinBattle, statutBattle, emailCreateurBattle, emailParticipantBattle) 
                VALUES (:titre, :debut, :fin, :statut, :createur, :participant)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':titre' => $battle->getTitreBattle(),
            ':debut' => $battle->getDateDebutBattle()?->format('Y-m-d H:i:s'),
            ':fin' => $battle->getDateFinBattle()?->format('Y-m-d H:i:s'),
            ':statut' => $battle->getStatutBattle()->value,
            ':createur' => $battle->getEmailCreateurBattle(),
            ':participant' => $battle->getEmailParticipantBattle()
        ]);
    }

    /**
     * @brief Met à jour les chansons liées à une battle.
     * @param int $idBattle L'identifiant de la battle.
     * @param int|null $idChansonCreateur L'identifiant de la chanson du créateur.
     * @param int|null $idChansonParticipant L'identifiant de la chanson du participant.
     * @return bool Vrai si la mise à jour a réussi, faux sinon.
     */
    public function updateSongs(int $idBattle, ?int $idChansonCreateur, ?int $idChansonParticipant): bool {
        $sql = "UPDATE battle SET idChansonCreateur = :c, idChansonParticipant = :p WHERE idBattle = :id";
        return $this->pdo->prepare($sql)->execute([':c' => $idChansonCreateur, ':p' => $idChansonParticipant, ':id' => $idBattle]);
    }

    /**
     * @brief Récupère le nombre de votes pour un artiste dans une battle spécifique.
     * @param int $idBattle L'identifiant de la battle.
     * @param string $emailArtiste L'email de l'artiste.
     * @return int Le nombre de votes.
     */
    public function getVotesCount(int $idBattle, string $emailArtiste): int {
        $sql = "SELECT COUNT(*) FROM vote WHERE idBattle = :idB AND emailVotee = :email";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':idB' => $idBattle, ':email' => $emailArtiste]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * @brief Vérifie si un utilisateur a déjà voté dans une battle spécifique.
     * @param int $idBattle L'identifiant de la battle.
     * @param string $emailVotant L'email de l'utilisateur votant.
     * @return bool Vrai si l'utilisateur a déjà voté, faux sinon.
     */
    public function hasUserVoted(int $idBattle, string $emailVotant): bool {
        $sql = "SELECT COUNT(*) FROM vote WHERE idBattle = :idB AND emailVotant = :emailV";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':idB' => $idBattle, ':email' => $emailVotant]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * @brief Met à jour la chanson d'un participant (créateur ou invité).
     * @param int $idBattle L'identifiant de la battle.
     * @param int $idChanson L'identifiant de la chanson choisie.
     * @param bool $estCreateur Indique si le participant est le créateur (true) ou l'invité (false).
     * @return bool Vrai si la mise à jour a réussi, faux sinon.
     */
    public function modifierChanson(int $idBattle, int $idChanson, bool $estCreateur): bool {
        $colonne = $estCreateur ? 'idChansonCreateur' : 'idChansonParticipant';
        $sql = "UPDATE battle SET $colonne = :idC WHERE idBattle = :idB";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':idC' => $idChanson, ':idB' => $idBattle]);
    }

    /**
     * @brief Met à jour le statut d'une battle.
     * @param int $idBattle L'identifiant de la battle.
     * @param string $nouveauStatut Le nouveau statut à appliquer.
     * @return bool Vrai si la mise à jour a réussi, faux sinon.
     */
    public function modifierStatut(int $idBattle, string $nouveauStatut): bool {
        $sql = "UPDATE battle SET statutBattle = :statut WHERE idBattle = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':statut' => $nouveauStatut, 
            ':id' => $idBattle
        ]);
    }
}