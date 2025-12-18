<?php

/**
 * @file controller_battle.class.php
 * @brief Fichier contenant le contrôleur de gestion des battles.
 * 
 * Ce fichier gère toutes les fonctionnalités liées aux battles musicales
 * dans l'application Paaxio.
 * 
 */

/**
 * @class ControllerBattle
 * @brief Contrôleur dédié à la gestion des battles.
 * 
 * Cette classe gère les opérations sur les battles :
 * - Affichage d'une battle spécifique
 * - Liste de toutes les battles
 * - Affichage sous forme de tableau
 * 
 * @extends Controller
 */
class ControllerBattle extends Controller
{
    /**
     * @brief Constructeur du contrôleur battle.
     * 
     * @param \Twig\Environment $twig Environnement Twig pour le rendu des templates.
     * @param \Twig\Loader\FilesystemLoader $loader Chargeur de fichiers Twig.
     */
    public function __construct(\Twig\Environment $twig, \Twig\Loader\FilesystemLoader $loader)
    {
        parent::__construct($loader, $twig);
    }

    /**
     * @brief Affiche les détails d'une battle spécifique.
     * 
     * Récupère une battle par son ID passé en paramètre GET.
     * 
     * @return void
     */
    public function afficher()
    {
        $idBattle = isset($_GET['idBattle']) ? $_GET['idBattle'] : null;

        // Récupération de la battle
        $managerBattle = new BattleDao($this->getPdo());
        $battle = $managerBattle->find($idBattle);

        $template = $this->getTwig()->load('test.html.twig');
        echo $template->render(array(
            'page' => [
                'title' => "Battle",
                'name' => "battle",
                'description' => "Battle dans Paaxio"
            ],
            'testing' => $battle,
        ));
    }

    /**
     * @brief Liste toutes les battles de la plateforme.
     * 
     * Récupère toutes les battles et les affiche dans un template de test.
     * 
     * @return void
     */
    public function lister()
    {
        // Récupération des battles
        $managerBattle = new BattleDao($this->getPdo());
        $battles = $managerBattle->findAll();

        // Choix du template
        $template = $this->getTwig()->load('test.html.twig');

        // Affichage de la page
        echo $template->render(array(
            'page' => [
                'title' => "Battles",
                'name' => "battles",
                'description' => "Battles dans Paaxio"
            ],
            'testing' => $battles,
        ));
    }

    /**
     * @brief Liste toutes les battles sous forme de tableau.
     * 
     * Récupère toutes les battles et les affiche dans un format tableau.
     * 
     * @return void
     */
    public function listerTableau()
    {
        $managerBattle = new BattleDao($this->getPdo());
        $battles = $managerBattle->findAll();

        // Génération de la vue
        $template = $this->getTwig()->load('test.html.twig');
        echo $template->render(array(
            'page' => [
                'title' => "Battles tableau",
                'name' => "battlet",
                'description' => "Battles tableau dans Paaxio"
            ],
            'testing' => $battles,
        ));
    }
}
