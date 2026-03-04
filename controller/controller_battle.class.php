<?php

/**
 * @file controller_battle.class.php
 * @brief Contrôleur de gestion des battles musicales.
 */

if (!class_exists('ControllerBattle')) {

    class ControllerBattle extends Controller
    {
        /**
         * @brief Constructeur du contrôleur battle.
         */
        public function __construct(\Twig\Environment $twig, \Twig\Loader\FilesystemLoader $loader)
        {
            parent::__construct($loader, $twig);
        }

        /**
         * @brief Liste les battles actives pour l'arène publique.
         */
        public function lister(): void
        {
            $this->requireAnyRole([RoleEnum::Artiste, RoleEnum::Auditeur]);

            $battleDao = new BattleDao($this->getPdo());
            $toutesLesBattles = $battleDao->findAll();
            $battlesEnCours = [];
            $maintenant = new DateTime();

            foreach ($toutesLesBattles as $battle) {
                if ($battle->getStatutBattle() === StatutBattle::En_cours) {
                    if ($maintenant > $battle->getDateFinBattle()) {
                        $battleDao->modifierStatut($battle->getIdBattle(), 'terminee');
                        continue;
                    }

                    $idB = (int)$battle->getIdBattle();
                    if (isset($_SESSION['user_email']) && !empty($_SESSION['user_email'])) {
                        $battle->setDejaVote($battleDao->hasUserVoted($idB, $_SESSION['user_email']));
                    }

                    $battle->setVotesCreateur($battleDao->getVotesCount($idB, $battle->getEmailCreateurBattle()));
                    $battle->setVotesParticipant($battleDao->getVotesCount($idB, $battle->getEmailParticipantBattle()));

                    $battlesEnCours[] = $battle;
                }
            }

            echo $this->getTwig()->render('battle_liste.html.twig', [
                'page' => ['title' => "Arène des Battles", 'name' => "battle"],
                'battles' => $battlesEnCours,
                'session' => $_SESSION
            ]);
        }

        /**
         * @brief Gère le choix de chanson et le lancement du combat.
         */
        public function choisirChanson(): void
        {
            header('Content-Type: application/json');
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_email'])) {
                $idBattle = (int)$_POST['idBattle'];
                $idChanson = (int)$_POST['idChanson'];

                $battleDao = new BattleDao($this->getPdo());
                $battle = $battleDao->find($idBattle);
                $estCreateur = ($battle->getEmailCreateurBattle() === $_SESSION['user_email']);

                if ($battleDao->modifierChanson($idBattle, $idChanson, $estCreateur)) {
                    $battleAjour = $battleDao->find($idBattle);
                    if ($battleAjour->getIdChansonCreateur() && $battleAjour->getIdChansonParticipant()) {
                        $battleDao->modifierStatut($idBattle, 'en_cours');
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
         * @brief Crée une invitation et envoie un message.
         */
        public function inviter(): void
        {
            header('Content-Type: application/json');
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_email'])) {
                $emailInvite = $_POST['emailInvite'];
                $pseudoInvite = $_POST['pseudoInvite'];

                $battle = new Battle();
                $battle->setTitreBattle($_SESSION['user_pseudo'] . " VS " . $pseudoInvite);
                $battle->setStatutBattle(StatutBattle::En_attente);
                $battle->setEmailCreateurBattle($_SESSION['user_email']);
                $battle->setEmailParticipantBattle($emailInvite);
                $battle->setDateDebutBattle(new DateTime());
                $battle->setDateFinBattle((new DateTime())->modify('+1 day'));

                $battleDao = new BattleDao($this->getPdo());
                if ($battleDao->insert($battle)) {
                    $idBattle = $this->getPdo()->lastInsertId();
                    $messageDao = new MessageDao($this->getPdo());
                    $uDao = new UtilisateurDao($this->getPdo());
                    $msg = new Message();
                    $msg->setContenu("[BATTLE_INVITE:" . $idBattle . "] Salut ! Je t'invite à un battle musical. Es-tu prêt ?");
                    $msg->setEmailExpediteur($uDao->find($_SESSION['user_email']));
                    $msg->setEmailDestinataire($uDao->find($emailInvite));
                    $messageDao->create($msg);
                    echo json_encode(['status' => 'success']);
                    exit;
                }
            }
            echo json_encode(['status' => 'error']);
            exit;
        }

        public function accepter(): void
        {
            $idBattle = isset($_GET['idBattle']) ? (int)$_GET['idBattle'] : null;
            if ($idBattle) {
                $pdo = $this->getPdo();
                (new BattleDao($pdo))->modifierStatut($idBattle, 'en_attente');
                $sql = "UPDATE message SET contenuMessage = 'L\'invitation au battle a été acceptée !' WHERE contenuMessage LIKE :search";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':search' => "%[BATTLE_INVITE:$idBattle]%"]);
            }
            $this->redirectTo("battle", "gestionDashboard");
            exit;
        }

        public function refuser(): void
        {
            $idBattle = isset($_GET['idBattle']) ? (int)$_GET['idBattle'] : null;
            if ($idBattle) {
                $pdo = $this->getPdo();
                (new BattleDao($pdo))->modifierStatut($idBattle, 'annulee');
                $sql = "UPDATE message SET contenuMessage = 'L\'invitation au battle a été refusée.' WHERE contenuMessage LIKE :search";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':search' => "%[BATTLE_INVITE:$idBattle]%"]);
            }
            $this->redirectTo("battle", "gestionDashboard");
        }

        public function voter(): void
        {

            $this->requireAuth("battle", "lister");

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $idBattle = (int)$_POST['idBattle'];
                $emailVotee = $_POST['emailVotee'];
                $battleDao = new BattleDao($this->getPdo());
                if (!$battleDao->hasUserVoted($idBattle, $_SESSION['user_email'])) {
                    $battleDao->addVote($_SESSION['user_email'], $idBattle, $emailVotee);
                }
            }
            $this->redirectTo("battle", "lister");
        }

        public function supprimer(): void
        {
            if (isset($_POST['idBattle']) && isset($_SESSION['user_email'])) {
                $battleDao = new BattleDao($this->getPdo());
                $battle = $battleDao->find((int)$_POST['idBattle']);
                if ($battle && $battle->getEmailCreateurBattle() === $_SESSION['user_email'] && $battle->getStatutBattle()->value === 'en_attente') {
                    $battleDao->deleteBattle($battle->getIdBattle());
                }
            }
            $this->redirectTo("battle", "gestionDashboard");
            exit;
        }

        public function gestionDashboard(): void
        {

            $this->requireRole(RoleEnum::Artiste);

            $emailActuel = $_SESSION['user_email'];
            $battleDao = new BattleDao($this->getPdo());
            $chansonDao = new ChansonDao($this->getPdo());
            $utilisateurDao = new UtilisateurDao($this->getPdo());

            echo $this->getTwig()->render('battle_dashboard.html.twig', [
                'page' => ['title' => "Gestion de mes Battles", 'name' => "battle_gestion"],
                'battles' => $battleDao->findAllByUser($emailActuel),
                'stats' => $battleDao->getStatsArtiste($emailActuel),
                'mesChansons' => $chansonDao->findAllFromUser($emailActuel),
                'artistesDisponibles' => $utilisateurDao->findAllArtistes($emailActuel),
                'session' => $_SESSION
            ]);
        }
    }
}
