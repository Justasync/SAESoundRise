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
        $controllerName = 'home';
        $method = 'afficher';
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
