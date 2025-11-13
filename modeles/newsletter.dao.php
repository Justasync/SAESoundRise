<?php

class NewsletterDAO
{
    private ?PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo;
    }

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
