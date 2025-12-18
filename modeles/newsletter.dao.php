<?php
/**
 * @file modeles/newsletter.dao.php
 * @brief DAO pour la gestion des abonnements à la newsletter
 */

class NewsletterDAO
{
    /**
     * @var PDO|null $pdo L'instance PDO pour la connexion à la base de données.
     */
    private ?PDO $pdo;

    /**
     * Constructeur de la classe NewsletterDAO.
     * @param PDO|null $pdo L'instance PDO pour la connexion à la base de données.
     */
    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo;
    }

    /**
     * Vérifie si une adresse email est déjà abonnée à la newsletter.
     * @param string $email L'adresse email à vérifier.
     * @return bool True si l'email existe déjà dans la newsletter.
     */
    public function existsByEmail(string $email): bool
    {
        $sql = "SELECT COUNT(*) FROM newsletter WHERE email = :email";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':email' => $email]);
        return (bool)$stmt->fetchColumn();
    }

    public function create(Newsletter $newsletter): bool
    {
        $sql = "INSERT INTO newsletter (email, dateInscription) VALUES (:email, :dateInscription)";
        $stmt = $this->pdo->prepare($sql);
        $dateInscription = $newsletter->getDateInscription()?->format('Y-m-d H:i:s');
        return $stmt->execute([
            ':email' => $newsletter->getEmail(),
            ':dateInscription' => $dateInscription ?? date('Y-m-d H:i:s'),
        ]);
    }

    public function hydrate(array $row): Newsletter
    {
        $n = new Newsletter();
        $n->setEmail($row['email'] ?? null);
        $n->setDateInscription(!empty($row['dateInscription']) ? new DateTime($row['dateInscription']) : null);
        return $n;
    }
}
