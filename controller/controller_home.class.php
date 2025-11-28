<?php

class ControllerHome extends Controller
{
    public function __construct(\Twig\Environment $twig, \Twig\Loader\FilesystemLoader $loader)
    {
        parent::__construct($loader, $twig);
    }

    public function afficher($showModalName = '')
    {
        $pdo = bd::getInstance()->getConnexion();

        if (!isset($_SESSION['user_logged_in']) || !isset($_SESSION['user_role'])) {

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
            exit();
        }

        // Transformation du rôle utilisateur de string vers enum Role
        switch (RoleEnum::from($_SESSION['user_role'])) {
            case RoleEnum::Artiste:
                $this->artisteDashboard();
                break;
            case RoleEnum::Admin:
                // Actions spécifiques à l'administrateur
                break;
            case RoleEnum::Auditeur:
                // Actions spécifiques à l'auditeur
                $this->auditeurDashboard();
                break;
            case RoleEnum::Producteur:
                // Actions spécifiques au producteur
                break;
            case RoleEnum::Invite:
                // Actions spécifiques à l'invité
                break;
            default:
                # code...
                break;
        }
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
        $this->afficher('login');
    }

    public function signup()
    {
        $this->afficher('signup');
    }
}
