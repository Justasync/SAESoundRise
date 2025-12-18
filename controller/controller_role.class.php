<?php

/**
 * @file controller_role.class.php
 * @brief Fichier contenant le contrôleur de gestion des rôles.
 * 
 * Ce fichier gère les opérations sur les rôles utilisateurs
 * dans l'application Paaxio.
 * 
 */

/**
 * @class ControllerRole
 * @brief Contrôleur dédié à la gestion des rôles utilisateurs.
 * 
 * Cette classe gère les opérations sur les rôles :
 * - Affichage d'un rôle spécifique
 * - Liste de tous les rôles
 * - Affichage sous forme de tableau
 * 
 * @extends Controller
 */
class ControllerRole extends Controller
{
    /**
     * @brief Constructeur du contrôleur role.
     * 
     * @param \Twig\Environment $twig Environnement Twig pour le rendu des templates.
     * @param \Twig\Loader\FilesystemLoader $loader Chargeur de fichiers Twig.
     */
    public function __construct(\Twig\Environment $twig, \Twig\Loader\FilesystemLoader $loader)
    {
        parent::__construct($loader, $twig);
    }

    /**
     * @brief Affiche les détails d'un rôle spécifique.
     * 
     * Récupère un rôle par son ID passé en paramètre GET.
     * 
     * @return void
     */
    public function afficher()
    {
        $idRole = isset($_GET['idRole']) ? $_GET['idRole'] : null;

        // Récupération du rôle
        $managerRole = new RoleDao($this->getPdo());
        $role = $managerRole->find($idRole);

        $template = $this->getTwig()->load('test.html.twig');
        echo $template->render(array(
            'page' => [
                'title' => "Role",
                'name' => "role",
                'description' => "Role dans Paaxio"
            ],
            'testing' => $role,
        ));
    }

    /**
     * @brief Liste tous les rôles de la plateforme.
     * 
     * Récupère tous les rôles et les affiche dans un template de test.
     * 
     * @return void
     */
    public function lister()
    {
        // Récupération des rôles
        $managerRole = new RoleDao($this->getPdo());
        $roles = $managerRole->findAll();

        // Choix du template
        $template = $this->getTwig()->load('test.html.twig');

        // Affichage de la page
        echo $template->render(array(
            'page' => [
                'title' => "Roles",
                'name' => "roles",
                'description' => "Roles dans Paaxio"
            ],
            'testing' => $roles,
        ));
    }

    /**
     * @brief Liste tous les rôles sous forme de tableau.
     * 
     * Récupère tous les rôles et les affiche dans un format tableau.
     * 
     * @return void
     */
    public function listerTableau()
    {
        $managerRole = new RoleDao($this->getPdo());
        $roles = $managerRole->findAll();

        // Génération de la vue
        $template = $this->getTwig()->load('test.html.twig');
        echo $template->render(array(
            'page' => [
                'title' => "Roles tableau",
                'name' => "rolest",
                'description' => "Roles tableau dans Paaxio"
            ],
            'testing' => $roles
        ));
    }
}
