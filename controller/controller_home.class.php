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

    public function homeBienvenue($showModalName = '')
    {
        $pdo = bd::getInstance()->getConnexion();
        $genreDAO = new GenreDAO($pdo);
        $genres = $genreDAO->findAll();

        $template = $this->getTwig()->load('index.html.twig');
        echo $template->render([
            'page' => [
                'title' => "Accueil",
                'name' => "accueil",
                'description' => "Page d'accueil de Paaxio"
            ],
            'genres' => $genres,
            'show' => $showModalName,
            'session' => $_SESSION
        ]);
    }

    private function openDashboard()
    {

        $aristesDAO = new UtilisateurDAO($this->getPDO());
        $artistesPopulaires = $aristesDAO->findTrending(8, 7);

        $chansonsDAO = new ChansonDAO($this->getPDO());
        $chansonsPopulaires = $chansonsDAO->findTrending(8, 7);

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
            'chansons' => $chansonsPopulaires,
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

    public function login()
    {
        $this->homeBienvenue('login');
    }

    public function signup()
    {
        $this->homeBienvenue('signup');
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

    public function getHeader()
    {
        header('Content-Type: application/json');
        $template = $this->getTwig()->load('_header.html.twig');
        $headerHtml = $template->render(['session' => $_SESSION]);
        echo json_encode(['header' => $headerHtml]);
    }
}
