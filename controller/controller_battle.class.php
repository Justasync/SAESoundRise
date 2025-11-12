<?php

class ControllerBattle extends Controller
{
    public function __construct(\Twig\Environment $twig, \Twig\Loader\FilesystemLoader $loader)
    {
        parent::__construct($loader, $twig);
    }

    public function afficher()
    {
        $idBattle = isset($_GET['idBattle']) ? $_GET['idBattle'] : null;

        //Récupération de la catégorie
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

    public function lister()
    {
        //recupération des catégories
        $managerBattle = new BattleDao($this->getPdo());
        $battles = $managerBattle->findAll();

        //Choix du template
        $template = $this->getTwig()->load('test.html.twig');

        //Affichage de la page
        echo $template->render(array(
            'page' => [
                'title' => "Battles",
                'name' => "battles",
                'description' => "Battles dans Paaxio"
            ],
            'testing' => $battles,
        ));
    }

    public function listerTableau()
    {
        $managerBattle = new BattleDao($this->getPdo());
        $battles = $managerBattle->findAll();

        //Génération de la vue
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