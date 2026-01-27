<?php
/** 
 * @file Classe Message
 * @brief Représente un message dans le système de messagerie
 */

class MessageDAO
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

    public function create(Message $message): bool
    {
        $sql = "INSERT INTO message (dateMessage, contenuMessage, estLuMessage, expediteurMessage, destinataireMessage) VALUES (:dateMessage, :contenuMessage, :estLuMessage, :expediteurMessage, :destinataireMessage)";
        $stmt = $this->pdo->prepare($sql);
        $dateEnvoi = $message->getDateEnvoi()?->format('Y-m-d H:i:s');
        $emailExpediteur = $message->getEmailExpediteur()?->getEmailUtilisateur();
        $emailDestinataire = $message->getEmailDestinataire()?->getEmailUtilisateur();

        return $stmt->execute([
            ':dateMessage' => $dateEnvoi ?? date('Y-m-d H:i:s'),
            ':contenuMessage' => $message->getContenu(),
            ':estLuMessage' => $message->getEstLu() ? 1 : 0,
            ':expediteurMessage' => $emailExpediteur,
            ':destinataireMessage' => $emailDestinataire
        ]);
    }

    public function getConversation(Utilisateur $utilisateur1, Utilisateur $utilisateur2): array
    {
        $sql = "SELECT * FROM message WHERE (expediteurMessage = :utilisateur1 AND destinataireMessage = :utilisateur2) OR (expediteurMessage = :utilisateur2 AND destinataireMessage = :utilisateur1) ORDER BY dateMessage ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':utilisateur1' => $utilisateur1->getEmailUtilisateur(),
            ':utilisateur2' => $utilisateur2->getEmailUtilisateur()
        ]);

        $messages = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $message = new Message(
                (int)$row['idMessage'],
                $row['contenuMessage'],
                new DateTime($row['dateMessage']),
                (bool)$row['estLuMessage'],
                new Utilisateur($row['expediteurMessage']),
                new Utilisateur($row['destinataireMessage'])
            );
            $messages[] = $message;
        }
        return $messages;
    }

    public function markAsRead(int $idMessage): bool
    {
        $sql = "UPDATE message SET estLuMessage = 1 WHERE idMessage = :idMessage";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':idMessage' => $idMessage]);
    }

    public function getListeDiscutions(Utilisateur $utilisateur): array
    {
        $sql = "SELECT * FROM message WHERE expediteurMessage = :utilisateur OR destinataireMessage = :utilisateur ORDER BY dateMessage DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':utilisateur' => $utilisateur->getEmailUtilisateur()]);

        $messages = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $message = new Message(
                (int)$row['idMessage'],
                $row['contenuMessage'],
                new DateTime($row['dateMessage']),
                (bool)$row['estLuMessage'],
                new Utilisateur($row['expediteurMessage']),
                new Utilisateur($row['destinataireMessage'])
            );
            $messages[] = $message;
        }
        return $messages;
    }
}

?>