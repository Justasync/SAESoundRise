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

        $photoProfil = null;
        if ($row['photoProfilUtilisateur']) {
            $photoProfil = $row['photoProfilUtilisateur'];
        }

        $role = null;
        if ($row['roleUtilisateur']) {
            $roleDAO = new RoleDao($this->pdo);
            $role = $roleDAO->find((int)$row['roleUtilisateur']);
        }

        return new Utilisateur(
            $row['emailUtilisateur'],
            $row['pseudoUtilisateur'],
            $row['motDePasseUtilisateur'],
            $dateDeNaissance,
            $dateInscription,
            $statutUtilisateur,
            (bool)$row['estAbonnee'],
            $statutAbonnement,
            $dateDebutAbonnement,
            $dateFinAbonnement,
            (int)$row['pointsDeRenommeeArtiste'],
            (int)$row['nbAbonnesArtiste'],
            $photoProfil,
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
        $sql = "INSERT INTO utilisateur (emailUtilisateur, pseudoUtilisateur, motDePasseUtilisateur, dateDeNaissanceUtilisateur, dateInscriptionUtilisateur, statutUtilisateur, estAbonnee, statutAbonnement, dateDebutAbonnement, dateFinAbonnement, pointsDeRenommeeArtiste, nbAbonnesArtiste, photoProfilUtilisateur, roleUtilisateur) VALUES (:emailUtilisateur, :pseudoUtilisateur, :motDePasseUtilisateur, :dateDeNaissanceUtilisateur, :dateInscriptionUtilisateur, :statutUtilisateur, :estAbonnee, :statutAbonnement, :dateDebutAbonnement, :dateFinAbonnement, :pointsDeRenommeeArtiste, :nbAbonnesArtiste, :photoProfilUtilisateur, :roleUtilisateur)";
        $stmt = $this->pdo->prepare($sql);

        $dateDeNaissance = $utilisateur->getDateDeNaissanceUtilisateur()?->format('Y-m-d');
        $dateInscription = $utilisateur->getDateInscriptionUtilisateur()?->format('Y-m-d H:i:s');
        $dateDebutAbonnement = $utilisateur->getDateDebutAbonnement()?->format('Y-m-d');
        $dateFinAbonnement = $utilisateur->getDateFinAbonnement()?->format('Y-m-d');
        $statutUtilisateur = $utilisateur->getStatutUtilisateur()?->value;
        $statutAbonnement = $utilisateur->getStatutAbonnement()?->value;
        $photoProfil = $utilisateur->getPhotoProfilUtilisateur()?->getUrlFichier();
        $roleId = $utilisateur->getRoleUtilisateur()?->getIdRole();

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
            ':photoProfilUtilisateur' => $photoProfil,
            ':roleUtilisateur' => $roleId,
        ]);
    }

    public function update(Utilisateur $utilisateur): bool
    {
        $sql = "UPDATE utilisateur SET pseudoUtilisateur = :pseudoUtilisateur, motDePasseUtilisateur = :motDePasseUtilisateur, dateDeNaissanceUtilisateur = :dateDeNaissanceUtilisateur, dateInscriptionUtilisateur = :dateInscriptionUtilisateur, statutUtilisateur = :statutUtilisateur, estAbonnee = :estAbonnee, statutAbonnement = :statutAbonnement, dateDebutAbonnement = :dateDebutAbonnement, dateFinAbonnement = :dateFinAbonnement, pointsDeRenommeeArtiste = :pointsDeRenommeeArtiste, nbAbonnesArtiste = :nbAbonnesArtiste, photoProfilUtilisateur = :photoProfilUtilisateur, roleUtilisateur = :roleUtilisateur WHERE emailUtilisateur = :emailUtilisateur";
        $stmt = $this->pdo->prepare($sql);

        $dateDeNaissance = $utilisateur->getDateDeNaissanceUtilisateur()?->format('Y-m-d');
        $dateInscription = $utilisateur->getDateInscriptionUtilisateur()?->format('Y-m-d H:i:s');
        $dateDebutAbonnement = $utilisateur->getDateDebutAbonnement()?->format('Y-m-d');
        $dateFinAbonnement = $utilisateur->getDateFinAbonnement()?->format('Y-m-d');
        $statutUtilisateur = $utilisateur->getStatutUtilisateur()?->value;
        $statutAbonnement = $utilisateur->getStatutAbonnement()?->value;
        $photoProfil = $utilisateur->getPhotoProfilUtilisateur()?->getUrlFichier();
        $roleId = $utilisateur->getRoleUtilisateur()?->getIdRole();

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
            ':photoProfilUtilisateur' => $photoProfil,
            ':roleUtilisateur' => $roleId,
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
}
