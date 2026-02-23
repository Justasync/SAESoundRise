<?php
/** 
 * @file modeles/message.dao.php
 * @brief DAO pour la gestion des messages.
 */

class MessageDAO
{
    /**
     * @var PDO|null $pdo L'instance PDO pour la connexion à la base de données.
     */
    private ?PDO $pdo;

    /**
     * Constructeur de la classe MessageDAO.
     * @param PDO|null $pdo L'instance PDO pour la connexion à la base de données.
     */
    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo;
    }

    /**
     * Crée un nouveau message dans la base de données.
     *
     * @param Message $message
     * @return boolean
     */
    public function create(Message $message): bool
    {
        $sql = "INSERT INTO message (dateMessage, contenuMessage, estLuMessage, emailExpediteur, emailDestinataire) VALUES (:dateMessage, :contenuMessage, :estLuMessage, :emailExpediteur, :emailDestinataire)";
        $stmt = $this->pdo->prepare($sql);
        $dateEnvoi = $message->getDateEnvoi()?->format('Y-m-d H:i:s');
        $emailExpediteur = $message->getEmailExpediteur()?->getEmailUtilisateur();
        $emailDestinataire = $message->getEmailDestinataire()?->getEmailUtilisateur();

        return $stmt->execute([
            ':dateMessage' => $dateEnvoi ?? date('Y-m-d H:i:s'),
            ':contenuMessage' => $message->getContenu(),
            ':estLuMessage' => $message->getEstLu() ? 1 : 0,
            ':emailExpediteur' => $emailExpediteur,
            ':emailDestinataire' => $emailDestinataire
        ]);
    }

    /**
     * Récupère la liste des utilisateurs avec qui l'utilisateur courant a discuté (Inbox).
     * @param string $myEmail Email de l'utilisateur connecté
     * @return array Tableau d'objets Utilisateur (les contacts)
     */
    public function getConversations(string $myEmail): array
    {
        $sql = "
            SELECT DISTINCT u.*
            FROM utilisateur u
            JOIN message m ON (u.emailUtilisateur = m.emailExpediteur OR u.emailUtilisateur = m.emailDestinataire)
            WHERE (m.emailExpediteur = :myEmail OR m.emailDestinataire = :myEmail)
            AND u.emailUtilisateur != :myEmail
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':myEmail' => $myEmail]);
        
        $contacts = [];
        // On suppose que tu as une classe UtilisateurDAO ou que tu hydrates manuellement
        // Ici, je fais une hydration simple basée sur ta classe Utilisateur
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Note: Idéalement, utilise ton UtilisateurDAO pour créer l'objet proprement
            $user = new Utilisateur($row['emailUtilisateur']);
            $user->setNomUtilisateur($row['nomUtilisateur']);
            $user->setPseudoUtilisateur($row['pseudoUtilisateur']);
            $user->setUrlPhotoUtilisateur($row['urlPhotoUtilisateur']);
            $contacts[] = $user;
        }
        return $contacts;
    }

    /**
     * Marque un message comme lu.
     *
     * @param int $idMessage
     * @return bool
     */
    public function markAsRead(int $idMessage): bool
    {
        $sql = "UPDATE message SET estLuMessage = 1 WHERE idMessage = :idMessage";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':idMessage' => $idMessage]);
    }

    /**
     * Récupère l'historique de discussion entre deux personnes.
     * @param string $myEmail
     * @param string $contactEmail
     * @return array Tableau d'objets Message
     */
    public function getMessagesConversation(string $myEmail, string $contactEmail): array
    {
        $sql = "SELECT * FROM message 
                WHERE (emailExpediteur = :me AND emailDestinataire = :other)
                   OR (emailExpediteur = :other AND emailDestinataire = :me)
                ORDER BY dateMessage ASC"; // ASC pour lire du plus vieux au plus récent (flux de chat)

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':me' => $myEmail, ':other' => $contactEmail]);

        $messages = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // On recrée les objets Utilisateur juste avec l'email pour l'instant
            $expediteur = new Utilisateur($row['emailExpediteur']);
            $destinataire = new Utilisateur($row['emailDestinataire']);

            $messages[] = new Message(
                (int)$row['idMessage'],
                $row['contenuMessage'],
                new DateTime($row['dateMessage']),
                (bool)$row['estLuMessage'],
                $expediteur,
                $destinataire
            );
        }
        return $messages;
    }
}

?>