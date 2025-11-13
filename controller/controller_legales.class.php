<?php

class ControllerLegales extends Controller
{
    public function __construct(\Twig\Environment $twig, \Twig\Loader\FilesystemLoader $loader)
    {
        parent::__construct($loader, $twig);
    }

    public function afficher()
    {
        $template = $this->getTwig()->load('mentionsLegales.html.twig');
        echo $template->render(array(
            'page' => [
                'title' => "Mentions légales",
                'name' => "mentionsLegales",
                'description' => "Mentions légales de Paaxio"
            ],
        ));
    }
}
