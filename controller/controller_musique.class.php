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
        $emailUtilisateur = $_SESSION['user_email'] ?? null;

        // Récupère les musiques de l'utilisateur connecté
        $managerChanson = new ChansonDao($this->getPdo());
        $chansons = $managerChanson->findUser($emailUtilisateur);

        // Récupère les playlists de l'utilisateur connecté
        $managerPlaylist = new PlaylistDao($this->getPdo());
        $playlists = $managerPlaylist->findUser($emailUtilisateur);

        // Charge la page musique
        $template = $this->getTwig()->load('musique.html.twig');
        echo $template->render([
            'page' => [
                'title' => "Musique",
                'name' => "musique",
                'description' => "Page musique de Paaxio"
            ],
            'chansons' => $chansons,
            'playlists' => $playlists,
        ]);
    }
}