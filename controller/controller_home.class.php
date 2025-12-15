<?php

class ControllerHome extends Controller
{
    public function __construct(\Twig\Environment $twig, \Twig\Loader\FilesystemLoader $loader)
    {
        parent::__construct($loader, $twig);
    }

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
                $this->homeBienvenue();
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

    public function connect()
    {

        $redirectUrl = $_GET["redirect"] ?? "";

        // avoid URL injection
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
            header('Location: ' . (!empty($redirectUrl) ? $redirectUrl : '/?controller=home&method=afficher'));
            exit;
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

    private function artisteDashboard()
    {
        $utilisateurDAO = new UtilisateurDAO($this->getPDO());
        $artistesSuggere = $utilisateurDAO->findAllArtistes($_SESSION['user_email']);

        // On récupère les albums de l'artiste
        $albumDAO = new AlbumDAO($this->getPDO());
        $albums = $albumDAO->findAllByArtistEmail($_SESSION['user_email']);

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
        ]);
    }

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
}
