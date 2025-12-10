<?php

class ControllerPlaylist extends Controller
{
    public function __construct(\Twig\Environment $twig, \Twig\Loader\FilesystemLoader $loader)
    {
        parent::__construct($loader, $twig);
    }

    public function afficher()
    {
        $idPlaylist = isset($_GET['idPlaylist']) ? (int)$_GET['idPlaylist'] : null;

        // Récupération de la playlist
        $managerPlaylist = new PlaylistDAO($this->getPdo());
        $playlist = $managerPlaylist->find($idPlaylist);

        if (!$playlist || !$idPlaylist) {
            header('Location: /?controller=home&method=afficher');
            exit;
        }

        // Récupération des chansons de la playlist
        $chansons = $managerPlaylist->getChansonsByPlaylist($idPlaylist);

        // Conversion de la playlist en objet stdClass pour utiliser avec le template
        $playlistObj = (object) [
            "getTitreAlbum" => function() use ($playlist) { return $playlist->getNomPlaylist(); },
            "getUrlImageAlbum" => function() { return null; },
            "getArtisteAlbum" => function() { return "Ma Playlist"; },
            "getDateSortieAlbum" => function() { return null; },
        ];

        // Chargement du template
        $template = $this->getTwig()->load('chanson_album.html.twig');
        echo $template->render([
            'page' => [
                'title' => $playlist->getNomPlaylist(),
                'name' => "playlist",
                'description' => "Playlist dans Paaxio"
            ],
            'album' => $playlistObj,
            'chansons' => $chansons
        ]);
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