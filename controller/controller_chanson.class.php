<?php

class ControllerChanson extends Controller 
{
    public function __construct(\Twig\Environment $twig, \Twig\Loader\FilesystemLoader $loader)
    {
        parent::__construct($loader, $twig);
    }

    public function afficher()
    {
        $idChanson = isset($_GET['idChanson']) ? $_GET['idChanson'] : null;

        //Récupération de la catégorie
        $managerChanson = new ChansonDao($this->getPdo());
        $chanson = $managerChanson->findId($idChanson);

        $template = $this->getTwig()->load('test.html.twig');
        echo $template->render(array(
            'page' => [
                'title' => "Chanson",
                'name' => "chanson",
                'description' => "Chanson dans Paaxio"
            ],
            'testing' => $chanson,
        ));
    }

    public function rechercherParTitre()
    {
        $titreChanson = isset($_GET['titreChanson']) ? $_GET['titreChanson'] : null;

        //Récupération de la catégorie
        $managerChanson = new ChansonDao($this->getPdo());
        $chanson = $managerChanson->rechercherParTitre($titreChanson);

        $template = $this->getTwig()->load('test.html.twig');
        echo $template->render(array(
            'page' => [
                'title' => "Chanson",
                'name' => "chanson",
                'description' => "Chanson dans Paaxio"
            ],
            'testing' => $chanson,
        ));
    }

    public function rechercherParAlbum()
    {
        $idAlbum = isset($_GET['idAlbum']) ? $_GET['idAlbum'] : null;

        //Récupération de la catégorie
        $managerChanson = new ChansonDao($this->getPdo());
        $chanson = $managerChanson->rechercherParAlbum($idAlbum);

        $template = $this->getTwig()->load('test.html.twig');
        echo $template->render(array(
            'page' => [
                'title' => "Chanson",
                'name' => "chanson",
                'description' => "Chanson dans Paaxio"
            ],
            'testing' => $chanson,
        ));
    }
    
    public function lister()
    {
        //recupération des catégories
        $managerChanson = new ChansonDao($this->getPdo());
        $chansons = $managerChanson->findAll();

        //Choix du template
        $template = $this->getTwig()->load('test.html.twig');

        //Affichage de la page
        echo $template->render(array(
            'page' => [
                'title' => "Chansons",
                'name' => "chansons",
                'description' => "Chansons dans Paaxio"
            ],
            'testing' => $chansons,
        ));
    }

    public function listerTableau()
    {
        $managerChanson = new ChansonDao($this->getPdo());
        $chansons = $managerChanson->findAll();

        //Génération de la vue
        $template = $this->getTwig()->load('test.html.twig');
        echo $template->render(array(
            'page' => [
                'title' => "Chansons tableau",
                'name' => "chansont",
                'description' => "Chansons tableau dans Paaxio"
            ],
            'testing' => $chansons,
        ));
    }

   public function filtrerChanson()
    {
        // Récupération des filtres depuis l'URL
        $idGenre = $_GET['idGenre'] ?? null;
        $idAlbum = $_GET['idAlbum'] ?? null;
        
        // Récupération de l'ordre (asc ou desc) et de la colonne de tri
        $ordre = isset($_GET['ordre']) && in_array(strtolower($_GET['ordre']), ['asc', 'desc']) 
                ? strtoupper($_GET['ordre']) 
                : 'ASC';
                
        $tri = $_GET['tri'] ?? 'titreChanson';
        $colonnesValides = ['titreChanson', 'dateTeleversementChanson', 'nbEcouteChanson'];
        $colonne = in_array($tri, $colonnesValides) ? $tri : 'titreChanson';

        // Récupération des chansons filtrées via le DAO
        $managerChanson = new ChansonDao($this->getPdo());
        $chansons = $managerChanson->filtrerChanson($idGenre, $idAlbum, $colonne, $ordre);

        // Génération de la vue
        $template = $this->getTwig()->load('test.html.twig');
        echo $template->render([
            'page' => [
                'title' => "Chansons filtrées",
                'name' => "chansons_filtrees",
                'description' => "Chansons filtrées dans Paaxio"
            ],
            'testing' => $chansons,
        ]);
    }

    public function toggleLike()
    {
        // Vérifie la connexion
        $emailUtilisateur = $_SESSION['user_email'] ?? null;
        if (!$emailUtilisateur) {
            http_response_code(401);
            echo json_encode(['error' => 'Utilisateur non connecté']);
            exit;
        }

        // Récupère l'ID de la chanson depuis POST
        $idChanson = $_POST['idChanson'] ?? null;
        if (!$idChanson) {
            http_response_code(400);
            echo json_encode(['error' => 'ID de chanson manquant']);
            exit;
        }

        $chansonDAO = new ChansonDAO($this->getPdo());

        // Vérifie l'état actuel du like avant de basculer
        $chansonsLikees = $chansonDAO->findChansonsLikees($emailUtilisateur);
        $estLikee = false;
        foreach ($chansonsLikees as $chanson) {
            if ($chanson->getIdChanson() == $idChanson) {
                $estLikee = true;
                break;
            }
        }

        // Bascule le like
        $chansonDAO->toggleLike($emailUtilisateur, $idChanson);

        // Renvoie le nouvel état du like
        header('Content-Type: application/json');
        echo json_encode(['liked' => !$estLikee]);
        exit;
    }  
}