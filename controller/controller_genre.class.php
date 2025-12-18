<?php

/**
 * @file controller_genre.class.php
 * @brief Fichier contenant le contrôleur de gestion des genres musicaux.
 * 
 * Ce fichier gère toutes les fonctionnalités liées aux genres musicaux
 * dans l'application Paaxio.
 * 
 */

/**
 * @class ControllerGenre
 * @brief Contrôleur dédié à la gestion des genres musicaux.
 * 
 * Cette classe gère les opérations sur les genres :
 * - Affichage d'un genre spécifique
 * - Liste de tous les genres
 * - Recherche AJAX de genres par nom
 * 
 * @extends Controller
 */
class ControllerGenre extends Controller
{
    /**
     * @brief Constructeur du contrôleur genre.
     * 
     * @param \Twig\Environment $twig Environnement Twig pour le rendu des templates.
     * @param \Twig\Loader\FilesystemLoader $loader Chargeur de fichiers Twig.
     */
    public function __construct(\Twig\Environment $twig, \Twig\Loader\FilesystemLoader $loader)
    {
        parent::__construct($loader, $twig);
    }

    /**
     * @brief Affiche les détails d'un genre spécifique.
     * 
     * Récupère un genre par son ID passé en paramètre GET.
     * 
     * @return void
     */
    public function afficher()
    {
        $idGenre = isset($_GET['idGenre']) ? $_GET['idGenre'] : null;

        // Récupération du genre
        $managerGenre = new GenreDao($this->getPdo());
        $genre = $managerGenre->find($idGenre);

        $template = $this->getTwig()->load('test.html.twig');
        echo $template->render(array(
            'page' => [
                'title' => "Genre",
                'name' => "genre",
                'description' => "Genre dans Paaxio"
            ],
            'testing' => $genre,
        ));
    }

    /**
     * @brief Liste tous les genres musicaux de la plateforme.
     * 
     * Récupère tous les genres et les affiche dans un template de test.
     * 
     * @return void
     */
    public function lister()
    {
        // Récupération des genres
        $managerGenre = new GenreDao($this->getPdo());
        $genres = $managerGenre->findAll();

        // Choix du template
        $template = $this->getTwig()->load('test.html.twig');

        // Affichage de la page
        echo $template->render(array(
            'page' => [
                'title' => "Genres",
                'name' => "genres",
                'description' => "Genres dans Paaxio"
            ],
            'testing' => $genres,
        ));
    }

    /**
     * @brief Liste tous les genres sous forme de tableau.
     * 
     * Récupère tous les genres et les affiche dans un format tableau.
     * 
     * @return void
     */
    public function listerTableau()
    {
        $managerGenre = new GenreDao($this->getPdo());
        $genres = $managerGenre->findAll();

        // Génération de la vue
        $template = $this->getTwig()->load('test.html.twig');
        echo $template->render(array(
            'page' => [
                'title' => "Genres tableau",
                'name' => "genret",
                'description' => "Genres tableau dans Paaxio"
            ],
            'testing' => $genres,
        ));
    }

    /**
     * @brief Recherche de genres via AJAX pour l'autocomplétion.
     * 
     * Retourne une liste de genres correspondant au terme de recherche.
     * Le terme doit contenir au moins 2 caractères.
     * Retourne une réponse JSON au format compatible avec Select2.
     * 
     * @return void Retourne une réponse JSON et termine le script.
     */
    public function rechercherAjax()
    {
        header('Content-Type: application/json');
        $term = $_GET['term'] ?? '';

        if (mb_strlen($term) < 2) {
            echo json_encode([]);
            return;
        }

        try {
            $managerGenre = new GenreDao($this->getPdo());
            $genres = $managerGenre->rechercherParNom($term);

            $results = [];
            foreach ($genres as $genre) {
                $results[] = ['id' => $genre->getIdGenre(), 'text' => $genre->getNomGenre()];
            }
            echo json_encode($results);
        } catch (Exception $e) {
            echo json_encode(['error' => 'Une erreur est survenue.']);
        }
    }
}
