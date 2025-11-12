<?php

class ControllerChanson extends Controller 
{
    public function __construct(\Twig\Environment $twig, \Twig\Loader\FilesystemLoader $loader)
    {
        parent::__construct($loader, $twig);
    }

    public function afficher()
    {
        $idChanson = isset($_GET['idChanson']) ? $_GET['idChanson'] : null;

        //Récupération de la catégorie
        $managerChanson = new ChansonDao($this->getPdo());
        $chanson = $managerChanson->find($idChanson);

        $template = $this->getTwig()->load('test.html.twig');
        echo $template->render(array(
            'page' => [
                'title' => "Chanson",
                'name' => "chanson",
                'description' => "Chanson dans Paaxio"
            ],
            'testing' => $chanson,
        ));
    }
    
    public function lister()
    {
        //recupération des catégories
        $managerChanson = new ChansonDao($this->getPdo());
        $chansons = $managerChanson->findAll();

        //Choix du template
        $template = $this->getTwig()->load('test.html.twig');

        //Affichage de la page
        echo $template->render(array(
            'page' => [
                'title' => "Chansons",
                'name' => "chansons",
                'description' => "Chansons dans Paaxio"
            ],
            'testing' => $chansons,
        ));
    }
    public function listerTableau()
    {
        $managerChanson = new ChansonDao($this->getPdo());
        $chansons = $managerChanson->findAll();

        //Génération de la vue
        $template = $this->getTwig()->load('test.html.twig');
        echo $template->render(array(
            'page' => [
                'title' => "Chansons tableau",
                'name' => "chansont",
                'description' => "Chansons tableau dans Paaxio"
            ],
            'testing' => $chansons,
        ));
    }
}