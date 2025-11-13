<?php

class ControllerUtilisateur extends Controller
{
    public function __construct(\Twig\Environment $twig, \Twig\Loader\FilesystemLoader $loader)
    {
        parent::__construct($loader, $twig);
    }

    public function afficher()
    {
        $emailUtilisateur = isset($_GET['emailUtilisateur']) ? $_GET['emailUtilisateur'] : null;

        //Récupération de la catégorie
        $managerUtilisateur = new UtilisateurDAO($this->getPdo());
        $utilisateur = $managerUtilisateur->find($emailUtilisateur);

        $template = $this->getTwig()->load('test.html.twig');
        echo $template->render(array(
            'page' => [
                'title' => "Utilisateur",
                'name' => "utilisateur",
                'description' => "Détails de l'utilisateur"
            ],
            'testing' => $utilisateur,
        ));
    }

    public function lister()
    {
        //recupération des catégories
        $managerUtilisateur = new UtilisateurDAO($this->getPdo());
        $utilisateurs = $managerUtilisateur->findAll();

        //Choix du template
        $template = $this->getTwig()->load('test.html.twig');

        //Affichage de la page
        echo $template->render(array(
            'page' => [
                'title' => "Utilisateurs",
                'name' => "utilisateurs",
                'description' => "Liste des utilisateurs"
            ],
            'testing' => $utilisateurs,
        ));
    }

    public function listerTableau()
    {
        $managerUtilisateur = new UtilisateurDAO($this->getPdo());
        $utilisateurs = $managerUtilisateur->findAll();

        //Génération de la vue
        $template = $this->getTwig()->load('test.html.twig');
        echo $template->render(array(
            'page' => [
                'title' => "Utilisateurs tableau",
                'name' => "utilisateursTableau",
                'description' => "Tableau des utilisateurs"
            ],
            'testing' => $utilisateurs,
        ));
    }

}