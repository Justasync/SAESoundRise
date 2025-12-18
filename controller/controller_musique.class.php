<?php

/**
 * @file controller_musique.class.php
 * @brief Fichier contenant le contrôleur de la page "Ma Musique".
 * 
 * Ce fichier gère l'affichage de la bibliothèque musicale personnelle
 * de l'utilisateur connecté.
 * 
 */

/**
 * @class ControllerMusique
 * @brief Contrôleur dédié à la gestion de la bibliothèque musicale de l'utilisateur.
 * 
 * Cette classe gère l'affichage de la page "Ma Musique" qui contient :
 * - Les chansons de l'utilisateur
 * - Les playlists de l'utilisateur
 * 
 * @extends Controller
 */
class ControllerMusique extends Controller
{
    /**
     * @brief Constructeur du contrôleur musique.
     * 
     * @param \Twig\Environment $twig Environnement Twig pour le rendu des templates.
     * @param \Twig\Loader\FilesystemLoader $loader Chargeur de fichiers Twig.
     */
    public function __construct(\Twig\Environment $twig, \Twig\Loader\FilesystemLoader $loader)
    {
        parent::__construct($loader, $twig);
    }

    /**
     * @brief Affiche la page "Ma Musique" de l'utilisateur connecté.
     * 
     * Récupère et affiche :
     * - Les chansons publiées par l'utilisateur
     * - Les playlists créées par l'utilisateur
     * 
     * Nécessite que l'utilisateur soit authentifié.
     * 
     * @return void
     */
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
