<?php
require_once 'include.php';

try {
    if (isset($_GET['controller'])) {
        $controllerName = $_GET['controller'];
    } else {
        $controllerName = '';
    }

    if (isset($_GET['method'])) {
        $method = $_GET['method'];
    } else {
        $method = '';
    }

    // Gestion de la page d'accueil par défaut
    if ($controllerName == '' && $method == '') {

        $pdo = bd::getInstance()->getConnexion();
        $genreDAO = new GenreDAO($pdo);
        $genres = $genreDAO->findAll();

        $template = $twig->load('index.html.twig');
        echo $template->render([
            'page' => [
                'title' => "Accueil",
                'name' => "accueil",
                'description' => "Page d'accueil de Paaxio"
            ],
            'genres' => $genres,
        ]);
        exit;
    }

    if ($controllerName == '') {
        throw new Exception('Le controller n\'est pas défini');
    }

    if ($method == '') {
        throw new Exception('La méthode n\'est pas définie');
    }

    $controller = ControllerFactory::getController($controllerName, $loader, $twig);

    $controller->call($method);
} catch (Exception $e) {
    die('Erreur : ' . $e->getMessage());
}
