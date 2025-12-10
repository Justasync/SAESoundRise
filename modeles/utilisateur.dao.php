<?php

class UtilisateurDAO
{
    private ?PDO $pdo;

    public function __construct(?PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function find(?string $emailUtilisateur): ?Utilisateur
    {
        $sql = "SELECT * FROM utilisateur WHERE emailUtilisateur = :emailUtilisateur";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':emailUtilisateur' => $emailUtilisateur]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return $this->hydrate($row);
        }
        return null;
    }

    public function existsByEmail(string $emailUtilisateur): bool
    {
        $sql = "SELECT COUNT(*) FROM utilisateur WHERE emailUtilisateur = :emailUtilisateur";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':emailUtilisateur' => $emailUtilisateur]);
        return (bool)$stmt->fetchColumn();
    }

    public function findTrending(int $limit = 8, int $daysAgo = 7): array
    {
        // Requête SQL pour récupérer les artistes en tendance sur la plateforme
        // Les artistes sont classés selon un "score de tendance" basé sur :
        //    - le nombre de nouveaux abonnés (pondéré 5)
        //    - les utilisateurs ayant liké leurs chansons (pondéré 1)
        //    - les votes reçus lors des battles (pondéré 3)
        // Le calcul s'effectue sur les 7 derniers jours uniquement
        // On ne retient que les artistes actifs (statut = 'actif' et rôle = 2)
        // Seuls les artistes avec un score de tendance strictement positif sont affichés (HAVING)
        // La requête retourne au maximum 8 artistes, triés par leur score de tendance décroissant

        $sql = "SELECT 
                u.*,
                -- Nombre d'abonnés récents (sur les X derniers jours)
                COUNT(DISTINCT aa.emailAbonne) AS nouveaux_abonnes,
                -- Nombre d'utilisateurs ayant liké une chanson de cet artiste (sur X jours)
                COUNT(DISTINCT lc.emailUtilisateur) AS nouveaux_likes_chansons,
                -- Nombre de votes reçus lors des battles (sur X jours)
                COUNT(DISTINCT v.emailVotant) AS nouveaux_votes_battle,
                -- Score de tendance global calculé avec pondération
                (COUNT(DISTINCT aa.emailAbonne) * 5) + 
                (COUNT(DISTINCT lc.emailUtilisateur) * 1) + 
                (COUNT(DISTINCT v.emailVotant) * 3) AS score_tendence

                FROM utilisateur u

                /* Jointure avec les abonnements artistes : uniquement ceux très récents */
                LEFT JOIN abonnementArtiste aa 
                    ON u.emailUtilisateur = aa.emailArtiste 
                    AND aa.dateAbonnement >= DATE_SUB(NOW(), INTERVAL :daysAgo DAY)

                /* Jointure avec les chansons publiées par l'artiste */
                LEFT JOIN chanson c 
                    ON u.emailUtilisateur = c.emailPublicateur
                /* Jointure pour récupérer les likes sur les chansons (sur les X derniers jours) */
                LEFT JOIN likeChanson lc 
                    ON c.idChanson = lc.idChanson 
                    AND lc.dateLike >= DATE_SUB(NOW(), INTERVAL :daysAgo DAY)

                /* Jointure pour les votes de battle reçus par l'artiste (sur X jours) */
                LEFT JOIN vote v 
                    ON u.emailUtilisateur = v.emailVotee 
                    AND v.dateVote >= DATE_SUB(NOW(), INTERVAL :daysAgo DAY)

                WHERE u.statutUtilisateur = 'actif'        -- Seulement les utilisateurs actifs
                AND u.roleUtilisateur = 2                  -- Seulement les artistes

                GROUP BY u.emailUtilisateur, u.pseudoUtilisateur, u.urlPhotoUtilisateur
                HAVING score_tendence > 0                  -- Seulement les artistes ayant un score positif
                ORDER BY score_tendence DESC               -- Tri par score décroissant
                LIMIT :limit;                              -- Limite à N résultats
                ";

        if ($limit < 1) {
            $limit = 8;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':daysAgo', $daysAgo, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $artistes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        if ($artistes) {
            return $this->hydrateAll($artistes);
        }
        return [];
    }

    public function existsByPseudo(string $pseudoUtilisateur): bool
    {
        $sql = "SELECT COUNT(*) FROM utilisateur WHERE pseudoUtilisateur = :pseudoUtilisateur";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':pseudoUtilisateur' => $pseudoUtilisateur]);
        return (bool)$stmt->fetchColumn();
    }

    private function hydrate(array $row): Utilisateur
    {
        $dateDeNaissance = $row['dateDeNaissanceUtilisateur'] ? new DateTime($row['dateDeNaissanceUtilisateur']) : null;
        $dateInscription = $row['dateInscriptionUtilisateur'] ? new DateTime($row['dateInscriptionUtilisateur']) : null;
        $dateDebutAbonnement = $row['dateDebutAbonnement'] ? new DateTime($row['dateDebutAbonnement']) : null;
        $dateFinAbonnement = $row['dateFinAbonnement'] ? new DateTime($row['dateFinAbonnement']) : null;

        $statutUtilisateur = $row['statutUtilisateur'] ? StatutUtilisateur::from($row['statutUtilisateur']) : null;
        $statutAbonnement = $row['statutAbonnement'] ? StatutAbonnement::from($row['statutAbonnement']) : null;

        $role = null;
        if ($row['roleUtilisateur']) {
            $roleDAO = new RoleDao($this->pdo);
            $role = $roleDAO->find((int)$row['roleUtilisateur']);
        }

        $genre = null;
        if ($row['genreUtilisateur']) {
            $genreDAO = new GenreDAO($this->pdo);
            $genre = $genreDAO->find((int)$row['genreUtilisateur']);
        }

        return new Utilisateur(
            $row['emailUtilisateur'],
            $row['nomUtilisateur'],
            $row['pseudoUtilisateur'],
            $row['motDePasseUtilisateur'],
            $dateDeNaissance,
            $dateInscription,
            $statutUtilisateur,
            $genre,
            (bool)$row['estAbonnee'],
            $row['descriptionUtilisateur'],
            $row['siteWebUtilisateur'],
            $statutAbonnement,
            $dateDebutAbonnement,
            $dateFinAbonnement,
            (int)$row['pointsDeRenommeeArtiste'],
            (int)$row['nbAbonnesArtiste'],
            $row['urlPhotoUtilisateur'],
            $role,
        );
    }

    public function findAll(): array
    {
        $sql = "SELECT * FROM utilisateur";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $this->hydrateAll($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    private function hydrateAll(array $rows): array
    {
        $utilisateurs = [];
        foreach ($rows as $row) {
            $utilisateurs[] = $this->hydrate($row);
        }
        return $utilisateurs;
    }

    public function create(Utilisateur $utilisateur): bool
    {
        $sql = "INSERT INTO utilisateur (emailUtilisateur, pseudoUtilisateur, motDePasseUtilisateur, dateDeNaissanceUtilisateur, dateInscriptionUtilisateur, statutUtilisateur, estAbonnee, statutAbonnement, dateDebutAbonnement, dateFinAbonnement, pointsDeRenommeeArtiste, nbAbonnesArtiste, urlPhotoUtilisateur, roleUtilisateur, descriptionUtilisateur, siteWebUtilisateur, genreUtilisateur, nomUtilisateur) VALUES (:emailUtilisateur, :pseudoUtilisateur, :motDePasseUtilisateur, :dateDeNaissanceUtilisateur, :dateInscriptionUtilisateur, :statutUtilisateur, :estAbonnee, :statutAbonnement, :dateDebutAbonnement, :dateFinAbonnement, :pointsDeRenommeeArtiste, :nbAbonnesArtiste, :urlPhotoUtilisateur, :roleUtilisateur, :descriptionUtilisateur, :siteWebUtilisateur, :genreUtilisateur, :nomUtilisateur)";
        $stmt = $this->pdo->prepare($sql);

        $dateDeNaissance = $utilisateur->getDateDeNaissanceUtilisateur()?->format('Y-m-d');
        $dateInscription = $utilisateur->getDateInscriptionUtilisateur()?->format('Y-m-d H:i:s');
        $dateDebutAbonnement = $utilisateur->getDateDebutAbonnement()?->format('Y-m-d');
        $dateFinAbonnement = $utilisateur->getDateFinAbonnement()?->format('Y-m-d');
        $statutUtilisateur = $utilisateur->getStatutUtilisateur()?->value;
        $statutAbonnement = $utilisateur->getStatutAbonnement()?->value;
        $roleId = $utilisateur->getRoleUtilisateur()?->getIdRole();
        $genreId = $utilisateur->getGenreUtilisateur()?->getIdGenre();

        return $stmt->execute([
            ':emailUtilisateur' => $utilisateur->getEmailUtilisateur(),
            ':pseudoUtilisateur' => $utilisateur->getPseudoUtilisateur(),
            ':motDePasseUtilisateur' => $utilisateur->getMotDePasseUtilisateur(),
            ':dateDeNaissanceUtilisateur' => $dateDeNaissance,
            ':dateInscriptionUtilisateur' => $dateInscription,
            ':statutUtilisateur' => $statutUtilisateur,
            ':estAbonnee' => $utilisateur->getEstAbonnee() ? 1 : 0,
            ':statutAbonnement' => $statutAbonnement,
            ':dateDebutAbonnement' => $dateDebutAbonnement,
            ':dateFinAbonnement' => $dateFinAbonnement,
            ':pointsDeRenommeeArtiste' => $utilisateur->getPointsDeRenommeeArtiste(),
            ':nbAbonnesArtiste' => $utilisateur->getNbAbonnesArtiste(),
            ':urlPhotoUtilisateur' => $utilisateur->geturlPhotoUtilisateur(),
            ':roleUtilisateur' => $roleId,
            ':descriptionUtilisateur' => $utilisateur->getDescriptionUtilisateur(),
            ':siteWebUtilisateur' => $utilisateur->getSiteWebUtilisateur(),
            ':genreUtilisateur' => $genreId,
            ':nomUtilisateur' => $utilisateur->getNomUtilisateur(),
        ]);
    }

    public function update(Utilisateur $utilisateur): bool
    {
        $sql = "UPDATE utilisateur SET pseudoUtilisateur = :pseudoUtilisateur, motDePasseUtilisateur = :motDePasseUtilisateur, dateDeNaissanceUtilisateur = :dateDeNaissanceUtilisateur, dateInscriptionUtilisateur = :dateInscriptionUtilisateur, statutUtilisateur = :statutUtilisateur, estAbonnee = :estAbonnee, statutAbonnement = :statutAbonnement, dateDebutAbonnement = :dateDebutAbonnement, dateFinAbonnement = :dateFinAbonnement, pointsDeRenommeeArtiste = :pointsDeRenommeeArtiste, nbAbonnesArtiste = :nbAbonnesArtiste, urlPhotoUtilisateur = :urlPhotoUtilisateur, roleUtilisateur = :roleUtilisateur, descriptionUtilisateur = :descriptionUtilisateur, siteWebUtilisateur = :siteWebUtilisateur, genreUtilisateur = :genreUtilisateur, nomUtilisateur = :nomUtilisateur WHERE emailUtilisateur = :emailUtilisateur";
        $stmt = $this->pdo->prepare($sql);

        $dateDeNaissance = $utilisateur->getDateDeNaissanceUtilisateur()?->format('Y-m-d');
        $dateInscription = $utilisateur->getDateInscriptionUtilisateur()?->format('Y-m-d H:i:s');
        $dateDebutAbonnement = $utilisateur->getDateDebutAbonnement()?->format('Y-m-d');
        $dateFinAbonnement = $utilisateur->getDateFinAbonnement()?->format('Y-m-d');
        $statutUtilisateur = $utilisateur->getStatutUtilisateur()?->value;
        $statutAbonnement = $utilisateur->getStatutAbonnement()?->value;
        $roleId = $utilisateur->getRoleUtilisateur()?->getIdRole();
        $genreId = $utilisateur->getGenreUtilisateur()?->getIdGenre();

        return $stmt->execute([
            ':emailUtilisateur' => $utilisateur->getEmailUtilisateur(),
            ':pseudoUtilisateur' => $utilisateur->getPseudoUtilisateur(),
            ':motDePasseUtilisateur' => $utilisateur->getMotDePasseUtilisateur(),
            ':dateDeNaissanceUtilisateur' => $dateDeNaissance,
            ':dateInscriptionUtilisateur' => $dateInscription,
            ':statutUtilisateur' => $statutUtilisateur,
            ':estAbonnee' => $utilisateur->getEstAbonnee() ? 1 : 0,
            ':statutAbonnement' => $statutAbonnement,
            ':dateDebutAbonnement' => $dateDebutAbonnement,
            ':dateFinAbonnement' => $dateFinAbonnement,
            ':pointsDeRenommeeArtiste' => $utilisateur->getPointsDeRenommeeArtiste(),
            ':nbAbonnesArtiste' => $utilisateur->getNbAbonnesArtiste(),
            ':urlPhotoUtilisateur' => $utilisateur->geturlPhotoUtilisateur(),
            ':roleUtilisateur' => $roleId,
            ':descriptionUtilisateur' => $utilisateur->getDescriptionUtilisateur(),
            ':siteWebUtilisateur' => $utilisateur->getSiteWebUtilisateur(),
            ':genreUtilisateur' => $genreId,
            ':nomUtilisateur' => $utilisateur->getNomUtilisateur(),
        ]);
    }

    public function delete(?string $emailUtilisateur): bool
    {
        $sql = "DELETE FROM utilisateur WHERE emailUtilisateur = :emailUtilisateur";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':emailUtilisateur' => $emailUtilisateur]);
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

    public function findAllArtistes(string $excludeEmail): array
    {
        // First, get the current user to find their genre
        $currentUser = $this->find($excludeEmail);
        $genreId = $currentUser?->getGenreUtilisateur()?->getIdGenre();

        $params = [':excludeEmail' => $excludeEmail];

        // Prioritize artists from the same genre if the current user has a genre
        if ($genreId) {
            $sql = "SELECT u.* 
                    FROM utilisateur u
                    JOIN role r ON u.roleUtilisateur = r.idRole
                    WHERE r.typeRole = 'artiste' 
                      AND u.emailUtilisateur != :excludeEmail
                      AND u.genreUtilisateur = :genreId
                    ORDER BY u.pointsDeRenommeeArtiste DESC, u.dateInscriptionUtilisateur DESC
                    LIMIT 10";
            $params[':genreId'] = $genreId;

            try {
                $requete = $this->pdo->prepare($sql);
                $requete->execute($params);
                $artistes = $this->hydrateAll($requete->fetchAll(PDO::FETCH_ASSOC));
                if (!empty($artistes)) {
                    return $artistes;
                }
            } catch (PDOException $e) {
                error_log('Erreur DAO lors de la récupération des artistes : ' . $e->getMessage());
            }
        }

        // Fallback: 
        $sql = "SELECT u.* 
                FROM utilisateur u
                JOIN role r ON u.roleUtilisateur = r.idRole
                WHERE r.typeRole = 'artiste' 
                  AND u.emailUtilisateur != :excludeEmail
                ORDER BY u.pointsDeRenommeeArtiste DESC, u.dateInscriptionUtilisateur DESC
                LIMIT 10";

        try {
            $requete = $this->pdo->prepare($sql);
            $requete->execute([':excludeEmail' => $excludeEmail]);
            return $this->hydrateAll($requete->fetchAll(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            error_log('Erreur DAO lors de la récupération des artistes populaires : ' . $e->getMessage());
            return [];
        }
    }
}
