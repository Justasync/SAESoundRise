<?php

class ControllerPlaylist extends Controller
{
    public function __construct(\Twig\Environment $twig, \Twig\Loader\FilesystemLoader $loader)
    {
        parent::__construct($loader, $twig);
    }

    public function afficher()
    {
        $idPlaylist = isset($_GET['idPlaylist']) ? $_GET['idPlaylist'] : null;

        //Récupération de la catégorie
        $managerPlaylist = new PlaylistDao($this->getPdo());
        $playlist = $managerPlaylist->find($idPlaylist);

        $template = $this->getTwig()->load('test.html.twig');
        echo $template->render(array(
            'page' => [
                'title' => "Playlist",
                'name' => "playlist",
                'description' => "Playlist dans Paaxio"
            ],
            'testing' => $playlist,
        ));
    }

    public function lister()
    {
        //recupération des catégories
        $managerPlaylist = new PlaylistDao($this->getPdo());
        $playlists = $managerPlaylist->findAll();

        //Choix du template
        $template = $this->getTwig()->load('test.html.twig');

        //Affichage de la page
        echo $template->render(array(
            'page' => [
                'title' => "Playlists",
                'name' => "playlists",
                'description' => "Playlists dans Paaxio"
            ],
            'testing' => $playlists,
        ));
    }

    public function listerTableau()
    {
        $managerPlaylist = new PlaylistDao($this->getPdo());
        $playlists = $managerPlaylist->findAll();

        //Génération de la vue
        $template = $this->getTwig()->load('test.html.twig');
        echo $template->render(array(
            'page' => [
                'title' => "Playlists tableau",
                'name' => "playlistt",
                'description' => "Playlists tableau dans Paaxio"
            ],
            'testing' => $playlists,
        ));
    }
}