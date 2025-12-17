<?php

class ControllerMusique extends Controller
{
    public function __construct(\Twig\Environment $twig, \Twig\Loader\FilesystemLoader $loader)
    {
        parent::__construct($loader, $twig);
    }
    public function afficher()
    {
        // Récupère l'utilisateur connecté
        $this->requireAuth();

        $emailUtilisateur = $_SESSION['user_email'] ?? null;

        // Récupère les musiques de l'utilisateur connecté
        $managerChanson = new ChansonDao($this->getPdo());
        $chansons = $managerChanson->findAllFromUser($emailUtilisateur);

        // Récupère les playlists de l'utilisateur connecté
        $managerPlaylist = new PlaylistDao($this->getPdo());
        $playlists = $managerPlaylist->findAllFromUser($emailUtilisateur);

        // Charge la page musique
        $template = $this->getTwig()->load('musique.html.twig');
        echo $template->render([
            'page' => [
                'title' => "Musique",
                'name' => "maMusique",
                'description' => "Page musique de Paaxio"
            ],
            'chansons' => $chansons,
            'playlists' => $playlists,
        ]);
    }
}
