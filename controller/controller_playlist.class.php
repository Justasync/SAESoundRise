<?php

/**
 * @file controller_playlist.class.php
 * @brief Fichier contenant le contrôleur de gestion des playlists.
 * 
 * Ce fichier gère toutes les fonctionnalités liées aux playlists
 * dans l'application Paaxio.
 * 
 */

/**
 * @class ControllerPlaylist
 * @brief Contrôleur dédié à la gestion des playlists.
 * 
 * Cette classe gère les opérations sur les playlists :
 * - Affichage d'une playlist avec ses chansons
 * - Liste de toutes les playlists
 * - Affichage sous forme de tableau
 * 
 * @extends Controller
 */
class ControllerPlaylist extends Controller
{
    /**
     * @brief Constructeur du contrôleur playlist.
     * 
     * @param \Twig\Environment $twig Environnement Twig pour le rendu des templates.
     * @param \Twig\Loader\FilesystemLoader $loader Chargeur de fichiers Twig.
     */
    public function __construct(\Twig\Environment $twig, \Twig\Loader\FilesystemLoader $loader)
    {
        parent::__construct($loader, $twig);
    }

    /**
     * @brief Affiche une playlist avec ses chansons.
     * 
     * Récupère la playlist de l'utilisateur connecté et affiche ses chansons.
     * Génère un token CSRF pour la protection des formulaires.
     * Nécessite que l'utilisateur soit authentifié.
     * 
     * @return void
     */
    public function afficher()
    {

        $idPlaylist = isset($_GET['idPlaylist']) ? (int)$_GET['idPlaylist'] : null;

        if (!$idPlaylist) {
            $this->redirectTo('home', 'afficher');
        }

        $this->requireAuth();

        // Récupération de la playlist
        $managerPlaylist = new PlaylistDAO($this->getPdo());
        $playlist = $managerPlaylist->findFromUser($idPlaylist, $_SESSION['user_email'] ?? null);

        if (!$playlist) {
            $this->redirectTo('home', 'afficher');
        }

        // Récupération des chansons de la playlist
        $chansons = $managerPlaylist->getChansonsByPlaylist($idPlaylist, $_SESSION['user_email'] ?? null);

        // Conversion de la playlist en objet stdClass pour utiliser avec le template
        $playlistObj = (object) [
            "getTitreAlbum" => function () use ($playlist) {
                return $playlist->getNomPlaylist();
            },
            "getUrlImageAlbum" => function () {
                return null;
            },
            "getArtisteAlbum" => function () {
                return "Ma Playlist";
            },
            "getDateSortieAlbum" => function () {
                return null;
            },
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

    /**
     * @brief Liste toutes les playlists de la plateforme.
     * 
     * Récupère toutes les playlists et les affiche dans un template de test.
     * 
     * @return void
     */
    public function lister()
    {
        // Récupération des playlists
        $managerPlaylist = new PlaylistDao($this->getPdo());
        $playlists = $managerPlaylist->findAll();

        // Choix du template
        $template = $this->getTwig()->load('test.html.twig');

        // Affichage de la page
        echo $template->render(array(
            'page' => [
                'title' => "Playlists",
                'name' => "playlists",
                'description' => "Playlists dans Paaxio"
            ],
            'testing' => $playlists,
        ));
    }

    /**
     * @brief Liste toutes les playlists sous forme de tableau.
     * 
     * Récupère toutes les playlists et les affiche dans un format tableau.
     * 
     * @return void
     */
    public function listerTableau()
    {
        $managerPlaylist = new PlaylistDao($this->getPdo());
        $playlists = $managerPlaylist->findAll();

        // Génération de la vue
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
