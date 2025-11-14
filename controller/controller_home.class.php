<?php

class ControllerHome extends Controller
{
    public function __construct(\Twig\Environment $twig, \Twig\Loader\FilesystemLoader $loader)
    {
        parent::__construct($loader, $twig);
    }

    public function afficher($showModalName = '')
    {
        $pdo = bd::getInstance()->getConnexion();
        $genreDAO = new GenreDAO($pdo);
        $genres = $genreDAO->findAll();

        $template = $this->getTwig()->load('index.html.twig');
        echo $template->render([
            'page' => [
                'title' => "Accueil",
                'name' => "accueil",
                'description' => "Page d'accueil de Paaxio"
            ],
            'genres' => $genres,
            'show' => $showModalName
        ]);
    }

    public function session()
    {
        $template = $this->getTwig()->load('test.html.twig');
        echo $template->render(array(
            'page' => [
                'title' => "DATA SESSION",
                'name' => "session",
                'description' => "Session dans Paaxio"
            ],
            'testing' => $_SESSION,
        ));
    }

    public function login()
    {
        $this->afficher('login');
    }

    public function signup()
    {
        $this->afficher('signup');
    }
}
