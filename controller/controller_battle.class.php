<?php

/**
 * @file controller_battle.class.php
 * @brief Fichier contenant le contrôleur de gestion des battles musicales.
 */

/**
 * @class ControllerBattle
 * @brief Contrôleur dédié à la gestion des battles.
 * 
 * Cette classe gère le cycle de vie d'une battle : invitation via chat,
 * acceptation, sélection de chansons, affichage de l'arène et du dashboard.
 * 
 * @extends Controller
 */
class ControllerBattle extends Controller
{
    /**
     * @brief Constructeur du contrôleur battle.
     * @param \Twig\Environment $twig Environnement Twig.
     * @param \Twig\Loader\FilesystemLoader $loader Chargeur de templates.
     */
    public function __construct(\Twig\Environment $twig, \Twig\Loader\FilesystemLoader $loader)
    {
        parent::__construct($loader, $twig);
    }

    /**
     * @brief Affiche les détails d'une battle spécifique.
     * @return void
     */
    public function afficher(): void
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
     * @brief Liste uniquement les battles actives ('en_cours') pour l'arène publique.
     * @return void
     */
    public function lister(): void
    {
        $battleDao = new BattleDao($this->getPdo());
        $toutesLesBattles = $battleDao->findAll();

        // Filtre pour ne garder que le spectacle (combats actifs)
        $battlesEnCours = array_filter($toutesLesBattles, function($battle) {
            return $battle->getStatutBattle() === StatutBattle::En_cours;
        });

        echo $this->getTwig()->render('battle_liste.html.twig', [
            'page' => ['title' => "Arène des Battles", 'name' => "battle"],
            'battles' => $battlesEnCours,
            'session' => $_SESSION
        ]);
    }

    /**
     * @brief Affiche le tableau de bord privé pour les artistes.
     * Vérifie la connexion et le rôle avant l'accès.
     * @return void
     */
    public function gestionDashboard(): void
    {
        // SÉCURITÉ : Connexion obligatoire
        if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
            header('Location: index.php?controller=home&method=afficher');
            exit;
        }

        // SÉCURITÉ : Rôle Artiste obligatoire (comparaison de la valeur de l'Enum)
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role']->value !== 'artiste') {
            header('Location: index.php?controller=home&method=afficher&error=access_denied');
            exit;
        }

        $emailActuel = $_SESSION['user_email'];
        $battleDao = new BattleDao($this->getPdo());
        $chansonDao = new ChansonDao($this->getPdo());
        $utilisateurDao = new UtilisateurDao($this->getPdo());

        // Récupération des données pour le dashboard et les modales
        $mesBattles = $battleDao->findAllByUser($emailActuel);
        $stats = $battleDao->getStatsArtiste($emailActuel);
        $mesChansons = $chansonDao->findAllFromUser($emailActuel);
        $artistes = $utilisateurDao->findAllArtistes($emailActuel);

        echo $this->getTwig()->render('battle_dashboard.html.twig', [
            'page' => ['title' => "Gestion de mes Battles", 'name' => "battle_gestion"],
            'battles' => $mesBattles,
            'stats' => $stats,
            'mesChansons' => $mesChansons,
            'artistesDisponibles' => $artistes,
            'session' => $_SESSION
        ]);
    }

    /**
     * @brief Gère la création d'une invitation et l'envoi d'un message automatique.
     * @return void
     */
    public function inviter(): void
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_email'])) {
            
            $emailInvitado = $_POST['emailInvitado'];
            $pseudoInvitado = $_POST['pseudoInvitado'];

            $battle = new Battle();
            // Titre propre sans préfixe répétitif
            $battle->setTitreBattle($_SESSION['user_pseudo'] . " VS " . $pseudoInvitado);
            $battle->setStatutBattle(StatutBattle::En_attente);
            $battle->setEmailCreateurBattle($_SESSION['user_email']);
            $battle->setEmailParticipantBattle($emailInvitado);
            $battle->setDateDebutBattle(new DateTime());
            $battle->setDateFinBattle((new DateTime())->modify('+1 day'));

            $battleDao = new BattleDao($this->getPdo());
            if ($battleDao->insert($battle)) {
                $idBattle = $this->getPdo()->lastInsertId();

                // Envoi du message automatique dans le chat
                $messageDao = new MessageDao($this->getPdo());
                $uDao = new UtilisateurDao($this->getPdo());
                $msg = new Message();
                $msg->setContenu("[BATTLE_INVITE:" . $idBattle . "] Salut ! Je t'invite à un battle musical. Es-tu prêt ?");
                $msg->setEmailExpediteur($uDao->find($_SESSION['user_email']));
                $msg->setEmailDestinataire($uDao->find($emailInvitado));
                $messageDao->create($msg);

                echo json_encode(['status' => 'success']);
                exit;
            }
        }
        echo json_encode(['status' => 'error']);
        exit;
    }

    /**
 * @brief Permet à un artiste de choisir ou modifier sa chanson pour une battle.
 * @return void
 */
public function choisirChanson(): void
{
    header('Content-Type: application/json');
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_email'])) {
        $idBattle = (int)$_POST['idBattle'];
        $idChanson = (int)$_POST['idChanson'];
        
        $battleDao = new BattleDao($this->getPdo());
        $battle = $battleDao->find($idBattle);
        
        // Sécurité : vérifier que l'utilisateur fait partie de la battle
        $estCreateur = ($battle->getEmailCreateurBattle() === $_SESSION['user_email']);
        $estParticipant = ($battle->getEmailParticipantBattle() === $_SESSION['user_email']);

        if (!$estCreateur && !$estParticipant) {
            echo json_encode(['status' => 'error', 'message' => 'Non autorisé']);
            exit;
        }
        
        // Mise à jour de la chanson
        if ($battleDao->modifierChanson($idBattle, $idChanson, $estCreateur)) {
            
            // RECHARGEMENT : On récupère la version fraîche de la battle en BDD
            $battleAjour = $battleDao->find($idBattle);
            
            $idC = $battleAjour->getIdChansonCreateur();
            $idP = $battleAjour->getIdChansonParticipant();

            // LOGIQUE DE LANCEMENT : Si les deux chansons sont renseignées
            if ($idC !== null && $idC > 0 && $idP !== null && $idP > 0) {
                $battleDao->modifierStatut($idBattle, 'en_cours');
                
                // OPTIONNEL : On peut réinitialiser la date de début à "maintenant" 
                // pour que les 24h de vote commencent au moment où les deux sont prêts.
                $sql = "UPDATE battle SET dateDebutBattle = NOW(), dateFinBattle = DATE_ADD(NOW(), INTERVAL 1 DAY) WHERE idBattle = :id";
                $this->getPdo()->prepare($sql)->execute([':id' => $idBattle]);
            }
            
            echo json_encode(['status' => 'success']);
            exit;
        }
    }
    echo json_encode(['status' => 'error']);
    exit;
}

    /**
     * @brief Accepte une invitation depuis le chat et redirige vers le dashboard.
     * @return void
     */
    public function accepter(): void
    {
        $idBattle = isset($_GET['idBattle']) ? (int)$_GET['idBattle'] : null;
        if ($idBattle) {
            $pdo = $this->getPdo();
            (new BattleDao($pdo))->modifierStatut($idBattle, 'en_attente');
            
            // Nettoyage du message pour enlever les boutons
            $sql = "UPDATE message SET contenuMessage = 'L\'invitation au battle a été acceptée !' WHERE contenuMessage LIKE :search";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':search' => "%[BATTLE_INVITE:$idBattle]%"]);
        }
        header('Location: index.php?controller=battle&method=gestionDashboard');
        exit;
    }

    /**
     * @brief Refuse une invitation depuis le chat et annule la battle.
     * @return void
     */
    public function refuser(): void
    {
        $idBattle = isset($_GET['idBattle']) ? (int)$_GET['idBattle'] : null;
        if ($idBattle) {
            $pdo = $this->getPdo();
            (new BattleDao($pdo))->modifierStatut($idBattle, 'annulee');
            
            // Nettoyage du message pour enlever les boutons
            $sql = "UPDATE message SET contenuMessage = 'L\'invitation au battle a été refusée.' WHERE contenuMessage LIKE :search";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':search' => "%[BATTLE_INVITE:$idBattle]%"]);
        }
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
}