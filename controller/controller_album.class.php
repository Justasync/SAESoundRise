<?php

class ControllerAlbum extends Controller
{
    public function __construct(\Twig\Environment $twig, \Twig\Loader\FilesystemLoader $loader)
    {
        parent::__construct($loader, $twig);
    }

    public function afficher()
    {
        $idAlbum = isset($_GET['idAlbum']) ? $_GET['idAlbum'] : null;

        //Récupération de la catégorie
        $managerAlbum = new AlbumDao($this->getPdo());
        $album = $managerAlbum->find($idAlbum);

        $template = $this->getTwig()->load('test.html.twig');
        echo $template->render(array(
            'page' => [
                'title' => "Album",
                'name' => "album",
                'description' => "Album dans Paaxio"
            ],
            'testing' => $album,
        ));
    }

    public function lister()
    {
        //recupération des catégories
        $managerAlbum = new AlbumDao($this->getPdo());
        $albums = $managerAlbum->findAll();

        //Choix du template
        $template = $this->getTwig()->load('test.html.twig');

        //Affichage de la page
        echo $template->render(array(
            'page' => [
                'title' => "Albums",
                'name' => "albums",
                'description' => "Albums dans Paaxio"
            ],
            'testing' => $albums,
        ));
    }

    public function listerTableau()
    {
        $managerAlbum = new AlbumDao($this->getPdo());
        $albums = $managerAlbum->findAll();

        //Génération de la vue
        $template = $this->getTwig()->load('test.html.twig');
        echo $template->render(array(
            'page' => [
                'title' => "Albums tableau",
                'name' => "albumt",
                'description' => "Albums tableau dans Paaxio"
            ],
            'testing' => $albums,
        ));
    }

    public function ajouter()
    {
        $error = isset($_GET['error']);

        //Génération de la vue
        $template = $this->getTwig()->load('album_form.html.twig');
        echo $template->render(array(
            'page' => [
                'title' => "Ajouter un album",
                'name' => "album_add",
                'description' => "Ajouter un nouvel album dans Paaxio"
            ],
            'error' => $error
        ));
    }

    public function ajouterAlbum()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $titre = $_POST['titreAlbum'] ?? null;
            $dateSortie = $_POST['dateSortieAlbum'] ?? null;
            $urlPochetteAlbum = $_POST['urlPochetteAlbum'] ?? null;
            $artisteAlbum = $_SESSION['user_email'] ?? null;

            if (!empty($titre) && !empty($dateSortie) && !empty($urlPochetteAlbum)) {
                $album = new Album(null, $titre, $dateSortie, trim($urlPochetteAlbum), trim($artisteAlbum));

                $managerAlbum = new AlbumDao($this->getPdo());
                $success = $managerAlbum->create($album);

                if ($success) {
                    header('Location: index.php?controller=album&method=lister'); // Redirection vers la liste des albums
                    exit;
                }
            }
        }
        // Gérer l'échec ou l'accès direct ici, peut-être rediriger vers le formulaire avec un message d'erreur.
        header('Location: index.php?controller=album&method=ajouter&error=1');
        exit;
    }
}