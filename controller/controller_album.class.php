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
}