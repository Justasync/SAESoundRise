<?php
require_once 'include.php';

$pdo = bd::getInstance()->getConnexion();

$template = $twig->load('index.html.twig');
echo $template->render([
    'page' => [
        'title' => "Accueil",
        'name' => "accueil",
        'description' => "Page d'accueil de Paaxio"
    ],
]);
