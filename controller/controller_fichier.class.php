<?php

class ControllerFichier extends Controller
{
    public function __construct(\Twig\Environment $twig, \Twig\Loader\FilesystemLoader $loader)
    {
        parent::__construct($loader, $twig);
    }

    public function afficher()
    {
        $idFichier = isset($_GET['idFichier']) ? $_GET['idFichier'] : null;

        //Récupération de la catégorie
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

    public function lister()
    {
        //recupération des catégories
        $managerFichier = new FichierDao($this->getPdo());
        $fichiers = $managerFichier->findAll();

        //Choix du template
        $template = $this->getTwig()->load('test.html.twig');

        //Affichage de la page
        echo $template->render(array(
            'page' => [
                'title' => "Fichiers",
                'name' => "fichiers",
                'description' => "Fichiers dans Paaxio"
            ],
            'testing' => $fichiers,
        ));
    }

    public function listerTableau()
    {
        $managerFichier = new FichierDao($this->getPdo());
        $fichiers = $managerFichier->findAll();

        //Génération de la vue
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