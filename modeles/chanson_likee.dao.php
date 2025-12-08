<?php

class ChansonLikeeDAO
{
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
        $chansonLikee = $this->hydrateMany($tableau);
        return $chansonLikee;
    }

    public function findId(int $id): Chanson
    {
        $sql = "SELECT * FROM chanson WHERE idChanson = :id";
        $pdoStatement = $this->pdo->prepare($sql);
        $pdoStatement->execute(array(
            ':id' => $id
        ));

        $pdoStatement->setFetchMode(PDO::FETCH_ASSOC);
        $tableau = $pdoStatement->fetch();
        $chansonLikee = $this->hydrate($tableau);
        return $chansonLikee;
    }

    public function findUser(?string $email = null): array
    {
        if ($email) {
            $sql = "SELECT * FROM chanson WHERE emailPublicateur = :email";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':email' => $email]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $this->hydrateMany($results);
        } else {
            return [];
        }
    }

    public function hydrate(array $tableaAssoc): Chanson
    {
        $chanson = new Chanson();
        $chanson->setIdChanson($tableaAssoc['idChanson'] ?? null);
        $chanson->setTitreChanson($tableaAssoc['titreChanson'] ?? '');
        $chanson->setDureeChanson($tableaAssoc['dureeChanson'] ?? 0);
        $chanson->setNbEcouteChanson($tableaAssoc['nbEcouteChanson'] ?? 0);
        $chanson->seturlAudioChanson($tableaAssoc['urlAudioChanson'] ?? '');
        if (!empty($tableaAssoc['dateLike'])) {
            $chanson->setIsLiked(true);
            $chanson->setDateLike(new DateTime($tableaAssoc['dateLike']));
        } else {
            $chanson->setIsLiked(false);
            $chanson->setDateLike(null);
        }

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

    public function filtrerChanson(string $ordre = 'ASC'): array
    {
        $ordre = strtoupper($ordre) === 'DESC' ? 'DESC' : 'ASC'; // sécurité

        $sql = "SELECT * FROM likeChanson ORDER BY dateLike $ordre";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        $tableau = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $this->hydrateMany($tableau);
    }

    public function create(ChansonLikee $chansonLikee): bool
    {
        $sql = "INSERT INTO likechanson (emailUtilisateur, idChanson, dateLike)
                VALUES (:emailUtilisateur, :idChanson, :dateLike)";

        $pdoStatement = $this->pdo->prepare($sql);

        $dateLike = $chansonLikee->getDateLike() ? $chansonLikee->getDateLike()->format('Y-m-d H:i:s') : date('Y-m-d H:i:s');

        return $pdoStatement->execute([
            ':emailUtilisateur' => $chansonLikee->getEmailUtilisateur(),
            ':idChanson' => $chansonLikee->getIdChanson(),
            ':dateLike' => $dateLike,
        ]);
    }

    public function update(ChansonLikee $chansonLikee): bool
    {
        $sql = "UPDATE likechanson SET 
                    idChanson = :idChanson, 
                    emailUtilisateur = :emailUtilisateur,
                    dateLike = :dateLike
                WHERE idChanson = :idChanson";

        $pdoStatement = $this->pdo->prepare($sql);

        $dateLike = $chansonLikee->getDateLike() ? $chansonLikee->getDateLike()->format('Y-m-d H:i:s') : date('Y-m-d H:i:s');
        
        return $pdoStatement->execute([
            ':idChanson' => $chansonLikee->getIdChanson(),
            ':emailUtilisateur' => $chansonLikee->getEmailUtilisateur(),
            ':dateLike' => $dateLike,
        ]);
    }
    public function findChansonsLikees(string $email): array
    {
        $sql = "
            SELECT c.*, l.dateLike, l.emailUtilisateur
            FROM likechanson l
            JOIN chanson c ON c.idChanson = l.idChanson
            WHERE l.emailUtilisateur = :email
            ORDER BY l.dateLike DESC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':email' => $email]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $chansons = [];

        foreach ($results as $row) {
            $chansons[] = $this->hydrate($row);
        }

        return $chansons;
    }

    public function toggleLike()
    {
        // Vérifie la connexion
        $emailUtilisateur = $_SESSION['user_email'] ?? null;
        if (!$emailUtilisateur) {
            http_response_code(401);
            echo json_encode(['error' => 'Utilisateur non connecté']);
            exit;
        }

        // Récupère l'ID de la chanson depuis POST ou GET
        $idChanson = $_POST['idChanson'] ?? null;
        if (!$idChanson) {
            http_response_code(400);
            echo json_encode(['error' => 'ID chanson manquant']);
            exit;
        }

        $managerLike = new ChansonLikeeDAO($this->getPdo());

        // Vérifie si la chanson est déjà likée
        $chansonsLikees = $managerLike->findChansonsLikees($emailUtilisateur);
        $isLiked = false;
        foreach ($chansonsLikees as $chanson) {
            if ($chanson->getIdChanson() == $idChanson) {
                $isLiked = true;
                break;
            }
        }

        if ($isLiked) {
            // Supprime le like
            $sql = "DELETE FROM likechanson WHERE emailUtilisateur = :email AND idChanson = :idChanson";
            $stmt = $this->getPdo()->prepare($sql);
            $stmt->execute([
                ':email' => $emailUtilisateur,
                ':idChanson' => $idChanson
            ]);
            $etat = false; // cœur décoché
        } else {
            // Ajoute le like
            $chansonLikee = new ChansonLikee();
            $chansonLikee->setEmailUtilisateur($emailUtilisateur);
            $chansonLikee->setIdChanson($idChanson);
            $chansonLikee->setDateLike(new DateTime());
            $managerLike->create($chansonLikee);
            $etat = true; // cœur coché
        }

        // Retourne l'état du like
        echo json_encode(['liked' => $etat]);
        exit;
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
