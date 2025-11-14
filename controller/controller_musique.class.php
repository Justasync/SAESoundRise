<?php

class ControllerMusique extends Controller
{
    public function __construct(\Twig\Environment $twig, \Twig\Loader\FilesystemLoader $loader)
    {
        parent::__construct($loader, $twig);
    }

    public function afficher()
    {
        // Récupère les musiques
        $managerChanson = new ChansonDao($this->getPdo());
        $chansons = $managerChanson->findAll();
        $managerPlaylist = new PlaylistDao($this->getPdo());
        $playlists = $managerPlaylist->findAll();

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