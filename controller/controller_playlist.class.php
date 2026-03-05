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

        $nomPlaylist = $playlist->getNomPlaylist();
        $imagePlaylist = $managerPlaylist->recupererPochetteAuto($idPlaylist);
        $playlistObj = new class($nomPlaylist, $imagePlaylist) {
            private string $nom;
            private ?string $image;
            public function __construct(string $nom, ?string $image) { $this->nom = $nom; $this->image = $image; }
            public function getTitreAlbum(): string { return $this->nom; }
            public function getUrlImageAlbum(): ?string { return $this->image; }
            public function getArtisteAlbum(): string { return ''; }
            public function getDateSortieAlbum(): ?string { return null; }
        };

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
     * @brief Ajoute une chanson à une playlist de l'utilisateur connecté (AJAX).
     *
     * Attend une requête POST avec idPlaylist et idChanson.
     * Retourne une réponse JSON.
     *
     * @return void
     */
    public function ajouterChanson()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            exit;
        }

        if (!isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Non authentifié']);
            exit;
        }

        $idPlaylist = isset($_POST['idPlaylist']) ? (int)$_POST['idPlaylist'] : 0;
        $idChanson = isset($_POST['idChanson']) ? (int)$_POST['idChanson'] : 0;

        if (!$idPlaylist || !$idChanson) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Paramètres manquants']);
            exit;
        }

        $managerPlaylist = new PlaylistDAO($this->getPdo());
        $playlist = $managerPlaylist->findFromUser($idPlaylist, $_SESSION['user_email'] ?? null);

        if (!$playlist) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Playlist introuvable ou accès refusé']);
            exit;
        }

        $added = $managerPlaylist->ajouterChansonPlaylist($idPlaylist, $idChanson);

        if ($added) {
            echo json_encode(['success' => true, 'message' => 'Chanson ajoutée à la playlist']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Cette chanson est déjà dans la playlist']);
        }
        exit;
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
