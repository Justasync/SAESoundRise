<?php

class ControllerRole extends Controller
{
    public function __construct(\Twig\Environment $twig, \Twig\Loader\FilesystemLoader $loader)
    {
        parent::__construct($loader, $twig);
    }

    public function afficher()
    {
        $idRole = isset($_GET['idRole']) ? $_GET['idRole'] : null;

        //Récupération de la catégorie
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

    public function lister()
    {
        //recupération des catégories
        $managerRole = new RoleDao($this->getPdo());
        $roles = $managerRole->findAll();

        //Choix du template
        $template = $this->getTwig()->load('test.html.twig');

        //Affichage de la page
        echo $template->render(array(
            'page' => [
                'title' => "Roles",
                'name' => "roles",
                'description' => "Roles dans Paaxio"
            ],
            'testing' => $roles,
        ));
    }

    public function listerTableau()
    {
        $managerRole = new RoleDao($this->getPdo());
        $roles = $managerRole->findAll();

        //Génération de la vue
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
