<?php
//Définition de la classe controller
class Controller {
    // Code du contrôleur
    private PDO $pdo;
    private \Twig\Loader\FilesystemLoader $loader;
    private \Twig\Environment $twig;
    private ?array $get = null;
    private ?array $post = null;

    // Constructeur du contrôleur
    public function __construct(\Twig\Loader\FilesystemLoader $loader, \Twig\Environment $twig) {
        $db = bd::getInstance();
        $this->pdo = $db->getConnexion();
        $this->loader = $loader;
        $this->twig = $twig;

        //Récupération des variables GET et POST
        if (isset($_GET) && !empty($_GET)) {
            $this->get = $_GET;
        }
        if (isset($_POST) && !empty($_POST)) {
            $this->post = $_POST;
        }
        $this->post = $_POST;
    }
    // Méthode pour appeler une méthode du contrôleur
    public function call(string $methode): mixed {
        if(!method_exists($this, $methode)) {
            throw new Exception("La méthode $methode n'existe pas dans le contrôleur __CLASS__");
        } else {
            return $this->$methode();
        }

    }

    public function getPDO(): ?PDO
    {
        return $this->pdo;
    }

    public function setPDO(?PDO $pdo): void
    {
        $this->pdo = $pdo;
    
    }

    public function getLoader(): ?\Twig\Loader\FilesystemLoader
    {
        return $this->loader;
    }

    public function setLoader(?\Twig\Loader\FilesystemLoader $loader): void
    {
        $this->loader = $loader;
    }

    public function getTwig(): ?\Twig\Environment
    {
        return $this->twig;
    }

    public function setTwig(?\Twig\Environment $twig): void
    {
        $this->twig = $twig;
    }


    public function getGet(): ?array
    {
        return $this->get;
    }


    public function setGet(?array $get): void
    {
        $this->get = $get;
    }

    public function getPost(): ?array
    {
        return $this->post;
    }

    public function setPost(?array $post): void
    {
        $this->post = $post;
    }
}


//?array: on attend un tableau ou null