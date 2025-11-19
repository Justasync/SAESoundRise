<?php

class ControllerGenre extends Controller
{
    public function __construct(\Twig\Environment $twig, \Twig\Loader\FilesystemLoader $loader)
    {
        parent::__construct($loader, $twig);
    }

    public function afficher()
    {
        $idGenre = isset($_GET['idGenre']) ? $_GET['idGenre'] : null;

        //Récupération de la catégorie
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

    public function lister()
    {
        //recupération des catégories
        $managerGenre = new GenreDao($this->getPdo());
        $genres = $managerGenre->findAll();

        //Choix du template
        $template = $this->getTwig()->load('test.html.twig');

        //Affichage de la page
        echo $template->render(array(
            'page' => [
                'title' => "Genres",
                'name' => "genres",
                'description' => "Genres dans Paaxio"
            ],
            'testing' => $genres,
        ));
    }

    public function listerTableau()
    {
        $managerGenre = new GenreDao($this->getPdo());
        $genres = $managerGenre->findAll();

        //Génération de la vue
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
            echo json_encode(['error' => 'An error occurred.']);
        }
    }
    
}