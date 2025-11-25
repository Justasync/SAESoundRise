<?php

class ControllerAdmin extends Controller
{
    public function __construct(\Twig\Environment $twig, \Twig\Loader\FilesystemLoader $loader)
    {
        parent::__construct($loader, $twig);
    }

    public function afficher()
    {
        $template = $this->getTwig()->load('admin_dashboard.html.twig');
        echo $template->render([
            'page' => [
                'title' => "Admin",
                'name' => "admin",
                'description' => "Page admin de Paaxio"
            ]
        ]);
    }
}
