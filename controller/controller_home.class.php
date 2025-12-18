<?php

/**
 * @file controller_home.class.php
 * @brief Fichier contenant le contrôleur de la page d'accueil.
 * 
 * Ce fichier gère l'affichage de la page d'accueil et des différents
 * tableaux de bord selon le rôle de l'utilisateur.
 * 
 */

/**
 * @class ControllerHome
 * @brief Contrôleur dédié à la gestion de la page d'accueil.
 * 
 * Cette classe gère :
 * - L'affichage de la page d'accueil adaptée au rôle de l'utilisateur
 * - La page de connexion
 * - Les différents tableaux de bord (artiste, auditeur, admin, invité)
 * - L'affichage des mentions légales et conditions générales
 * 
 * @extends Controller
 */
class ControllerHome extends Controller
{
    /**
     * @brief Constructeur du contrôleur home.
     * 
     * @param \Twig\Environment $twig Environnement Twig pour le rendu des templates.
     * @param \Twig\Loader\FilesystemLoader $loader Chargeur de fichiers Twig.
     */
    public function __construct(\Twig\Environment $twig, \Twig\Loader\FilesystemLoader $loader)
    {
        parent::__construct($loader, $twig);
    }

    /**
     * @brief Affiche la page d'accueil selon le rôle de l'utilisateur.
     * 
     * Redirige vers le tableau de bord approprié selon le rôle :
     * - Artiste : tableau de bord artiste
     * - Admin : tableau de bord admin
     * - Auditeur : tableau de bord auditeur
     * - Producteur : page de bienvenue
     * - Invité ou non connecté : dashboard public
     * 
     * @return void
     */
    public function afficher()
    {
        if (!isset($_SESSION['user_logged_in']) || !isset($_SESSION['user_role'])) {
            $this->openDashboard();
            exit();
        }

        // Transformation du rôle utilisateur de string vers enum Role
        switch ($_SESSION['user_role']) {
            case RoleEnum::Artiste:
                $this->artisteDashboard();
                break;
            case RoleEnum::Admin:
                $this->redirectTo('admin', 'afficher');
                break;
            case RoleEnum::Auditeur:
                $this->auditeurDashboard();
                break;
            case RoleEnum::Producteur:
                $this->homeBienvenue();
                break;
            case RoleEnum::Invite:
                $this->openDashboard();
                break;
            default:
                $this->openDashboard();
                break;
        }
    }

    /**
     * @brief Affiche la page de connexion.
     * 
     * Gère la redirection après connexion si une URL de redirection est fournie.
     * Vérifie que l'URL de redirection est sûre (pas d'injection d'URL externe).
     * 
     * @return void
     */
    public function connect()
    {

        $redirectUrl = $_GET["redirect"] ?? "";
        $redirectUrl = urldecode($redirectUrl);

        // Éviter l'injection d'URL
        if (!empty($redirectUrl)) {
            // Vérifier que le redirectUrl est une URL interne valide, pour éviter toute injection (open redirect ou autre)
            // On n'autorise que les URL commençant par "/" et ne contenant pas "://"
            $redirectUrlDecoded = urldecode($redirectUrl);
            if (strpos($redirectUrlDecoded, '://') !== false || (strlen($redirectUrlDecoded) > 0 && $redirectUrlDecoded[0] !== '/')) {
                // L'URL contient un schéma ou n'est pas un chemin relatif ; on l'ignore
                $redirectUrl = '';
            }
        }

        if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']) {
            // Déjà connecté
            if (!empty($redirectUrl)) {
                // Si une URL de redirection est fournie, l'utiliser directement
                header('Location: ' . $redirectUrl);
                exit;
            } else {
                $this->redirectTo('home', 'afficher');
            }
        }

        $template = $this->getTwig()->load('connect.html.twig');
        echo $template->render([
            'page' => [
                'title' => "Connexion requise",
                'name' => "login_required",
                'description' => "Veuillez vous connecter pour continuer"
            ],
            'session' => $_SESSION,
            'redirect' => $redirectUrl
        ]);
    }

    /**
     * @brief Affiche la page d'accueil de bienvenue.
     * 
     * Page d'accueil simple pour les utilisateurs connectés.
     * 
     * @return void
     */
    public function homeBienvenue()
    {
        $template = $this->getTwig()->load('index.html.twig');
        echo $template->render([
            'page' => [
                'title' => "Accueil",
                'name' => "accueil",
                'description' => "Page d'accueil de Paaxio"
            ],
            'session' => $_SESSION
        ]);
    }

    /**
     * @brief Affiche le tableau de bord public (utilisateurs non connectés ou invités).
     * 
     * Affiche :
     * - Les artistes populaires/tendance
     * - Les chansons populaires avec le pseudo de l'artiste
     * - Les albums les plus écoutés
     * 
     * @return void
     */
    private function openDashboard()
    {

        $aristesDAO = new UtilisateurDAO($this->getPDO());
        $artistesPopulaires = $aristesDAO->findTrending(8, 7);

        $chansonsDAO = new ChansonDAO($this->getPDO());
        $chansonsPopulaires = $chansonsDAO->findTrending(8, 7);

        // Crée un nouvel array où chaque chanson garde toutes ses infos, mais on ajoute l'artiste avec son pseudo, pas son email
        $chansonsPopulairesAvecArtistePseudo = [];
        foreach ($chansonsPopulaires as $chanson) {
            // Si le dao hydrate la propriété emailPublicateur :
            $artistePseudo = null;
            // On va récupérer le pseudo correspondant à l'email publicateur (pas l'email), si possible
            if ($chanson->getEmailPublicateur()) {
                $utilisateurDAO = new UtilisateurDAO($this->getPDO());
                $utilisateur = $utilisateurDAO->find($chanson->getEmailPublicateur());
                $artistePseudo = $utilisateur ? $utilisateur->getPseudoUtilisateur() : null;
            }
            $chansonsPopulairesAvecArtistePseudo[] = [
                'chanson' => $chanson,
                'artistePseudo' => $artistePseudo,
            ];
        }

        // Récupérer les albums les plus écoutés
        $albumDAO = new AlbumDAO($this->getPDO());
        $albumsPopulaires = $albumDAO->findMostListened(8); // On récupère les 8 plus populaires

        $template = $this->getTwig()->load('open_dashboard.html.twig');
        echo $template->render([
            'page' => [
                'title' => 'Paaxio',
                'name' => "accueil",
                'description' => "Découvrez de nouveaux artistes, chansons et albums en tendance sur Paaxio!"
            ],
            'session' => $_SESSION,
            'artistes' => $artistesPopulaires,
            'chansons' => $chansonsPopulairesAvecArtistePseudo,
            'albums' => $albumsPopulaires,
        ]);
    }

    /**
     * @brief Affiche le tableau de bord de l'artiste connecté.
     * 
     * Affiche :
     * - Les suggestions d'autres artistes à suivre
     * - Les albums de l'artiste
     * - Les statistiques (reproductions, abonnés, battles gagnées)
     * 
     * @return void
     */
    private function artisteDashboard()
    {
        $utilisateurDAO = new UtilisateurDAO($this->getPDO());
        $artistesSuggere = $utilisateurDAO->findAllArtistes($_SESSION['user_email']);

        // On récupère les albums de l'artiste
        $albumDAO = new AlbumDAO($this->getPDO());
        $albums = $albumDAO->findAllByArtistEmail($_SESSION['user_email']);

        // Calcul des statistiques de l'artiste
        $chansonDAO = new ChansonDAO($this->getPDO());
        $battleDAO = new BattleDAO($this->getPDO());

        $totalReproductions = $chansonDAO->getTotalEcoutesByArtiste($_SESSION['user_email']);
        $totalAbonnes = $utilisateurDAO->countFollowers($_SESSION['user_email']);
        $battlesGagnees = $battleDAO->countBattlesWon($_SESSION['user_email']);

        $template = $this->getTwig()->load('artiste_dashboard.html.twig');
        echo $template->render([
            'page' => [
                'title' => ($_SESSION['user_pseudo'] ?? 'Artiste') . ' dashboard',
                'name' => "accueil",
                'description' => "Dashboard de " . ($_SESSION['user_pseudo'] ?? 'artiste')
            ],
            'session' => $_SESSION,
            'artistes' => $artistesSuggere,
            'albums' => $albums,
            'stats' => [
                'totalReproductions' => $totalReproductions,
                'totalAbonnes' => $totalAbonnes,
                'battlesGagnees' => $battlesGagnees,
            ],
        ]);
    }

    /**
     * @brief Affiche le tableau de bord de l'auditeur connecté.
     * 
     * Affiche :
     * - Les suggestions d'artistes à suivre
     * - Les albums les plus populaires
     * 
     * @return void
     */
    private function auditeurDashboard()
    {
        $utilisateurDAO = new UtilisateurDAO($this->getPDO());
        // Récupérer des artistes suggérés pour l'auditeur
        $artistesSuggere = $utilisateurDAO->findAllArtistes($_SESSION['user_email']);

        // Récupérer les albums les plus écoutés
        $albumDAO = new AlbumDAO($this->getPDO());
        $albumsPopulaires = $albumDAO->findMostListened(8); // On récupère les 8 plus populaires

        $template = $this->getTwig()->load('auditeur_dashboard.html.twig');
        echo $template->render([
            'page' => [
                'title' => 'Mon dashboard',
                'name' => "accueil",
                'description' => "Dashboard principal"
            ],
            'session' => $_SESSION,
            'artistes' => $artistesSuggere,
            'albums' => $albumsPopulaires,
        ]);
    }

    /**
     * @brief Affiche les données de session (méthode de débogage).
     * 
     * Affiche le contenu de la session courante à des fins de test.
     * 
     * @return void
     */
    public function session()
    {
        $template = $this->getTwig()->load('test.html.twig');
        echo $template->render(array(
            'page' => [
                'title' => "DATA SESSION",
                'name' => "session",
                'description' => "Session dans Paaxio"
            ],
            'testing' => $_SESSION,
        ));
    }

    /**
     * @brief Affiche la page des mentions légales.
     * 
     * @return void
     */
    public function afficherLegales()
    {
        $template = $this->getTwig()->load('mentionsLegales.html.twig');
        echo $template->render(array(
            'page' => [
                'title' => "Mentions légales",
                'name' => "mentionsLegales",
                'description' => "Mentions légales de Paaxio"
            ],
        ));
    }

    /**
     * @brief Affiche la page des conditions générales.
     * 
     * @return void
     */
    public function afficherGenerales()
    {
        $template = $this->getTwig()->load('conditions_generales.html.twig');
        echo $template->render(array(
            "page" => [
                'title' => "Conditions Générales",
                'name' => "conditions_generales",
                'description' => "Conditions Générales de Paaxio"
            ],
        ));
    }
}
