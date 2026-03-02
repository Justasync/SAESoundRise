<?php

/**
 * @file controller_battle.class.php
 * @brief Fichier contenant le contrôleur de gestion des battles.
 */

/**
 * @class ControllerBattle
 * @brief Contrôleur dédié à la gestion des battles musicales.
 * 
 * @extends Controller
 */
class ControllerBattle extends Controller
{
    /**
     * @brief Constructeur du contrôleur battle.
     * 
     * @param \Twig\Environment $twig Environnement Twig pour le rendu des templates.
     * @param \Twig\Loader\FilesystemLoader $loader Chargeur de fichiers Twig.
     */
    public function __construct(\Twig\Environment $twig, \Twig\Loader\FilesystemLoader $loader)
    {
        parent::__construct($loader, $twig);
    }

    /**
     * @brief Affiche les détails d'une battle spécifique.
     * 
     * @return void
     */
    public function afficher()
    {
        $idBattle = isset($_GET['idBattle']) ? (int)$_GET['idBattle'] : null;

        $managerBattle = new BattleDao($this->getPdo());
        $battle = $managerBattle->find($idBattle);

        echo $this->getTwig()->render('test.html.twig', [
            'page' => [
                'title' => "Détails Battle",
                'name' => "battle"
            ],
            'testing' => $battle,
        ]);
    }

    /**
     * @brief Liste toutes les battles et gère l'affichage de la page principale.
     * 
     * @return void
     */
    public function lister(): void
    {
        if (!isset($_SESSION['user_logged_in'])) {
            header('Location: index.php?controller=home&method=afficher');
            exit;
        }

        $battleDao = new BattleDao($this->getPdo());
        $utilisateurDao = new UtilisateurDao($this->getPdo());
        $chansonDao = new ChansonDao($this->getPdo()); // Nouveau

        $battles = $battleDao->findAll();
        $emailActuel = $_SESSION['user_email'];
        $artistes = $utilisateurDao->findAllArtistes($emailActuel);
        
        // On récupère les chansons de l'utilisateur pour qu'il puisse choisir
        $mesChansons = $chansonDao->findAllFromUser($emailActuel);

        echo $this->getTwig()->render('battle_liste.html.twig', [
            'page' => ['title' => "Battles", 'name' => "battle"],
            'battles' => $battles,
            'artistesDisponibles' => $artistes,
            'mesChansons' => $mesChansons, // Envoyé au template
            'session' => $_SESSION
        ]);
    }

    public function choisirChanson(): void
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_email'])) {
            $idBattle = (int)$_POST['idBattle'];
            $idChanson = (int)$_POST['idChanson'];
            $emailActuel = $_SESSION['user_email'];

            $battleDao = new BattleDao($this->getPdo());
            $battle = $battleDao->find($idBattle);

            // On détermine si l'utilisateur est le créateur ou l'invité
            $estCreateur = ($battle->getEmailCreateurBattle() === $emailActuel);
            
            if ($battleDao->modifierChanson($idBattle, $idChanson, $estCreateur)) {
                // Vérification : si les deux chansons sont là, on lance la battle
                $battleAjour = $battleDao->find($idBattle);
                if ($battleAjour->getIdChansonCreateur() && $battleAjour->getIdChansonParticipant()) {
                    $battleDao->modifierStatut($idBattle, 'en_cours');
                }
                echo json_encode(['status' => 'success']);
                exit;
            }
        }
        echo json_encode(['status' => 'error']);
        exit;
    }

    /**
     * @brief Gère la création d'une nouvelle battle (Invitation).
     * Appelé via AJAX depuis le modal de sélection d'artiste.
     * 
     * @return void
     */
    public function inviter(): void
    {
    header('Content-Type: application/json');
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_email'])) {
        
        $emailInvitado = $_POST['emailInvitado'];
        $pseudoInvitado = $_POST['pseudoInvitado'];
        $emailCreateur = $_SESSION['user_email'];

        $battleDao = new BattleDao($this->getPdo());
        $messageDao = new MessageDao($this->getPdo());
        $uDao = new UtilisateurDao($this->getPdo());

        // 1. Créer la Battle en base de données
        $battle = new Battle();
        $battle->setTitreBattle("Battle : " . $_SESSION['user_pseudo'] . " VS " . $pseudoInvitado);
        $battle->setStatutBattle(StatutBattle::En_attente);
        $battle->setEmailCreateurBattle($emailCreateur);
        $battle->setEmailParticipantBattle($emailInvitado);
        $battle->setDateDebutBattle(new DateTime());
        $battle->setDateFinBattle((new DateTime())->modify('+1 day'));

        if ($battleDao->insert($battle)) {
            // Récupérer l'ID de la battle qui vient d'être créée
            $idBattle = $this->getPdo()->lastInsertId();

            // 2. Envoyer le message d'invitation automatique
            $expediteur = $uDao->find($emailCreateur);
            $destinataire = $uDao->find($emailInvitado);

            $msg = new Message();
            // On utilise un marqueur spécial [BATTLE_INVITE:ID] pour que Twig le reconnaisse
            $msg->setContenu("[BATTLE_INVITE:" . $idBattle . "] Salut ! Je t'invite à un battle musical. Es-tu prêt ?");
            $msg->setDateEnvoi(new DateTime());
            $msg->setEstLu(false);
            $msg->setEmailExpediteur($expediteur);
            $msg->setEmailDestinataire($destinataire);

            if ($messageDao->create($msg)) {
                echo json_encode(['status' => 'success']);
                exit;
            }
        }
    }
    echo json_encode(['status' => 'error']);
    exit;
    }

    /**
    * Accepte une invitation de battle depuis le chat
    */
    public function accepter(): void
    {
    $idBattle = isset($_GET['idBattle']) ? (int)$_GET['idBattle'] : null;

    if ($idBattle) {
        $pdo = $this->getPdo();
        $battleDao = new BattleDao($pdo);
        
        // 1. On s'assure que le statut est prêt
        $battleDao->modifierStatut($idBattle, 'en_attente');

        // 2. On nettoie le message pour faire disparaître les boutons
        $sql = "UPDATE message SET contenuMessage = 'L\'invitation au battle a été acceptée !' 
                WHERE contenuMessage LIKE :search";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':search' => "%[BATTLE_INVITE:$idBattle]%"]);
    }

    // 3. Redirection vers le Dashboard privé
    header('Location: index.php?controller=battle&method=gestionDashboard');
    exit;
    }

    /**
     * Annule une battle suite à un refus de l'invité
     */
    public function refuser(): void
    {
    // 1. On récupère l'ID de la battle depuis l'URL
    $idBattle = isset($_GET['idBattle']) ? (int)$_GET['idBattle'] : null;

    if ($idBattle) {
        $pdo = $this->getPdo();
        $battleDao = new BattleDao($pdo);
        
        // 2. On change le statut de la battle en 'annulee' dans la base de données
        $battleDao->modifierStatut($idBattle, 'annulee');

        // 3. On modifie le texte du message dans le chat pour supprimer le code [BATTLE_INVITE:...]
        // Cela permet de faire disparaître les boutons "Accepter/Refuser" visuellement
        $sql = "UPDATE message SET contenuMessage = 'L\'invitation au battle a été refusée.' 
                WHERE contenuMessage LIKE :search";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':search' => "%[BATTLE_INVITE:$idBattle]%"]);
    }

    // 4. On redirige l'utilisateur vers la conversation
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
    }
    /**
     * @brief Affiche le tableau de bord de gestion des battles pour l'artiste connecté.
     * @return void
     */
    public function gestionDashboard(): void
    {
        // Sécurité : Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
            header('Location: index.php?controller=home&method=afficher');
            exit;
        }

        $emailActuel = $_SESSION['user_email'];
        $battleDao = new BattleDao($this->getPdo());
        $chansonDao = new ChansonDao($this->getPdo());

        // Récupération des données spécifiques à l'artiste
        $mesBattles = $battleDao->findAllByUser($emailActuel);
        $stats = $battleDao->getStatsArtiste($emailActuel);
        $mesChansons = $chansonDao->findAllFromUser($emailActuel);

        echo $this->getTwig()->render('battle_dashboard.html.twig', [
            'page' => [
                'title' => "Gestion de mes Battles",
                'name' => "battle_gestion"
            ],
            'battles' => $mesBattles,
            'stats' => $stats,
            'mesChansons' => $mesChansons,
            'session' => $_SESSION
        ]);
    }

}