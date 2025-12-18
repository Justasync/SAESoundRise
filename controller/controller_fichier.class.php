<?php

/**
 * @file controller_fichier.class.php
 * @brief Fichier contenant le contrôleur de gestion des fichiers.
 * 
 * Ce fichier gère les opérations sur les fichiers dans l'application Paaxio.
 * 
 */

/**
 * @class ControllerFichier
 * @brief Contrôleur dédié à la gestion des fichiers.
 * 
 * Cette classe gère les opérations sur les fichiers :
 * - Affichage d'un fichier spécifique
 * - Liste de tous les fichiers
 * - Affichage sous forme de tableau
 * 
 * @extends Controller
 */
class ControllerFichier extends Controller
{
    /**
     * @brief Constructeur du contrôleur fichier.
     * 
     * @param \Twig\Environment $twig Environnement Twig pour le rendu des templates.
     * @param \Twig\Loader\FilesystemLoader $loader Chargeur de fichiers Twig.
     */
    public function __construct(\Twig\Environment $twig, \Twig\Loader\FilesystemLoader $loader)
    {
        parent::__construct($loader, $twig);
    }

    /**
     * @brief Affiche les détails d'un fichier spécifique.
     * 
     * Récupère un fichier par son ID passé en paramètre GET.
     * 
     * @return void
     */
    public function afficher()
    {
        $idFichier = isset($_GET['idFichier']) ? $_GET['idFichier'] : null;

        // Récupération du fichier
        $managerFichier = new FichierDao($this->getPdo());
        $fichier = $managerFichier->find($idFichier);

        $template = $this->getTwig()->load('test.html.twig');
        echo $template->render(array(
            'page' => [
                'title' => "Fichier",
                'name' => "fichier",
                'description' => "Fichier dans Paaxio"
            ],
            'testing' => $fichier,
        ));
    }

    /**
     * @brief Liste tous les fichiers de la plateforme.
     * 
     * Récupère tous les fichiers et les affiche dans un template de test.
     * 
     * @return void
     */
    public function lister()
    {
        // Récupération des fichiers
        $managerFichier = new FichierDao($this->getPdo());
        $fichiers = $managerFichier->findAll();

        // Choix du template
        $template = $this->getTwig()->load('test.html.twig');

        // Affichage de la page
        echo $template->render(array(
            'page' => [
                'title' => "Fichiers",
                'name' => "fichiers",
                'description' => "Fichiers dans Paaxio"
            ],
            'testing' => $fichiers,
        ));
    }

    /**
     * @brief Liste tous les fichiers sous forme de tableau.
     * 
     * Récupère tous les fichiers et les affiche dans un format tableau.
     * 
     * @return void
     */
    public function listerTableau()
    {
        $managerFichier = new FichierDao($this->getPdo());
        $fichiers = $managerFichier->findAll();

        // Génération de la vue
        $template = $this->getTwig()->load('test.html.twig');
        echo $template->render(array(
            'page' => [
                'title' => "Fichiers tableau",
                'name' => "fichiert",
                'description' => "Fichiers tableau dans Paaxio"
            ],
            'testing' => $fichiers,
        ));
    }
}
