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

        // Vérification de la méthode POST et de la session
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_email'])) {
            
            $emailInvitado = $_POST['emailInvitado'] ?? null;
            $pseudoInvitado = $_POST['pseudoInvitado'] ?? null;
            $emailCreateur = $_SESSION['user_email'];

            if (!$emailInvitado || !$pseudoInvitado) {
                echo json_encode(['status' => 'error', 'message' => 'Données manquantes']);
                exit;
            }

            // 1. Création de l'objet Battle
            $battle = new Battle();
            $battle->setTitreBattle("Battle : " . $_SESSION['user_pseudo'] . " VS " . $pseudoInvitado);
            $battle->setStatutBattle(StatutBattle::En_attente);
            $battle->setEmailCreateurBattle($emailCreateur);
            $battle->setEmailParticipantBattle($emailInvitado);
            
            // Définition des dates (début maintenant, fin dans 24h)
            $dateDebut = new DateTime();
            $dateFin = (clone $dateDebut)->modify('+1 day');
            
            $battle->setDateDebutBattle($dateDebut);
            $battle->setDateFinBattle($dateFin);

            // 2. Enregistrement en base de données via le DAO
            $battleDao = new BattleDao($this->getPdo());
            
            if ($battleDao->insert($battle)) {
                // Ici, on pourrait ajouter l'envoi d'un email de notification plus tard
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Erreur lors de l\'enregistrement']);
            }
            exit;
        }

        echo json_encode(['status' => 'error', 'message' => 'Requête invalide']);
        exit;
    }
}