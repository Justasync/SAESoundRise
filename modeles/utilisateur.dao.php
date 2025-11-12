<?php

class RecetteDAO{
    private ?PDO $pdo;

    public function __construct(?PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function find(?string $emailUtilisateur) {
        $sql = "SELECT * FROM utilisateur WHERE emailUtilisateur = :emailUtilisateur";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':emailUtilisateur', $emailUtilisateur, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return new Utilisateur(
                $row['emailUtilisateur'],
                $row['pseudoUtilisateur'],
                $row['motDePasseUtilisateur'],
                $row['dateDeNaissanceUtilisateur'],
                $row['dateInscriptionUtilisateur'],
                $row['statutUtilisateur'],
                $row['estAbonnee'],
                $row['statutAbonnement'],
                $row['dateDebutAbonnement'],
                $row['dateFinAbonnement'],
                $row['pointsDeRenommeeArtiste'],
                $row['nbAbonnesArtiste'],
                $row['photoProfilUtilisateur'],
                $row['roleUtilisateur'],
            );
        }
    }

    public function findAll(): array {
        $sql = "SELECT * FROM utilisateur";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $utilisateurs = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $utilisateurs[] = new Utilisateur(
                $row['emailUtilisateur'],
                $row['pseudoUtilisateur'],
                $row['motDePasseUtilisateur'],
                $row['dateDeNaissanceUtilisateur'],
                $row['dateInscriptionUtilisateur'],
                $row['statutUtilisateur'],
                $row['estAbonnee'],
                $row['statutAbonnement'],
                $row['dateDebutAbonnement'],
                $row['dateFinAbonnement'],
                $row['pointsDeRenommeeArtiste'],
                $row['nbAbonnesArtiste'],
                $row['photoProfilUtilisateur'],
                $row['roleUtilisateur'],
            );
        }
        return $utilisateurs;
    }

    public function create(Utilisateur $utilisateur): bool {
        $sql = "INSERT INTO utilisateur (emailUtilisateur, pseudoUtilisateur, motDePasseUtilisateur, dateDeNaissanceUtilisateur, dateInscriptionUtilisateur, statutUtilisateur, estAbonnee, statutAbonnement, dateDebutAbonnement, dateFinAbonnement, pointsDeRenommeeArtiste, nbAbonnesArtiste, photoProfilUtilisateur, roleUtilisateur) VALUES (:emailUtilisateur, :pseudoUtilisateur, :motDePasseUtilisateur, :dateDeNaissanceUtilisateur, :dateInscriptionUtilisateur, :statutUtilisateur, :estAbonnee, :statutAbonnement, :dateDebutAbonnement, :dateFinAbonnement, :pointsDeRenommeeArtiste, :nbAbonnesArtiste, :photoProfilUtilisateur, :roleUtilisateur)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':emailUtilisateur' => $utilisateur->getEmailUtilisateur(),
            ':pseudoUtilisateur' => $utilisateur->getPseudoUtilisateur(),
            ':motDePasseUtilisateur' => $utilisateur->getMotDePasseUtilisateur(),
            ':dateDeNaissanceUtilisateur' => $utilisateur->getDateDeNaissanceUtilisateur(),
            ':dateInscriptionUtilisateur' => $utilisateur->getDateInscriptionUtilisateur(),
            ':statutUtilisateur' => $utilisateur->getStatutUtilisateur(),
            ':estAbonnee' => $utilisateur->getEstAbonnee(),
            ':statutAbonnement' => $utilisateur->getStatutAbonnement(),
            ':dateDebutAbonnement' => $utilisateur->getDateDebutAbonnement(),
            ':dateFinAbonnement' => $utilisateur->getDateFinAbonnement(),
            ':pointsDeRenommeeArtiste' => $utilisateur->getPointsDeRenommeeArtiste(),
            ':nbAbonnesArtiste' => $utilisateur->getNbAbonnesArtiste(),
            ':photoProfilUtilisateur' => $utilisateur->getPhotoProfilUtilisateur(),
            ':roleUtilisateur' => $utilisateur->getRoleUtilisateur(),
        ]);
    }

    public function update(Utilisateur $utilisateur): bool {
        $sql = "UPDATE utilisateur SET pseudoUtilisateur = :pseudoUtilisateur, motDePasseUtilisateur = :motDePasseUtilisateur, dateDeNaissanceUtilisateur = :dateDeNaissanceUtilisateur, dateInscriptionUtilisateur = :dateInscriptionUtilisateur, statutUtilisateur = :statutUtilisateur, estAbonnee = :estAbonnee, statutAbonnement = :statutAbonnement, dateDebutAbonnement = :dateDebutAbonnement, dateFinAbonnement = :dateFinAbonnement, pointsDeRenommeeArtiste = :pointsDeRenommeeArtiste, nbAbonnesArtiste = :nbAbonnesArtiste, photoProfilUtilisateur = :photoProfilUtilisateur, roleUtilisateur = :roleUtilisateur WHERE emailUtilisateur = :emailUtilisateur";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':emailUtilisateur' => $utilisateur->getEmailUtilisateur(),
            ':pseudoUtilisateur' => $utilisateur->getPseudoUtilisateur(),
            ':motDePasseUtilisateur' => $utilisateur->getMotDePasseUtilisateur(),
            ':dateDeNaissanceUtilisateur' => $utilisateur->getDateDeNaissanceUtilisateur(),
            ':dateInscriptionUtilisateur' => $utilisateur->getDateInscriptionUtilisateur(),
            ':statutUtilisateur' => $utilisateur->getStatutUtilisateur(),
            ':estAbonnee' => $utilisateur->getEstAbonnee(),
            ':statutAbonnement' => $utilisateur->getStatutAbonnement(),
            ':dateDebutAbonnement' => $utilisateur->getDateDebutAbonnement(),
            ':dateFinAbonnement' => $utilisateur->getDateFinAbonnement(),
            ':pointsDeRenommeeArtiste' => $utilisateur->getPointsDeRenommeeArtiste(),
            ':nbAbonnesArtiste' => $utilisateur->getNbAbonnesArtiste(),
            ':photoProfilUtilisateur' => $utilisateur->getPhotoProfilUtilisateur(),
            ':roleUtilisateur' => $utilisateur->getRoleUtilisateur(),
        ]);
    }

    public function delete(?string $emailUtilisateur): bool {
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