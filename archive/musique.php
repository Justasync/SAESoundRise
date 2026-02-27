<?php
require_once 'include.php';

$pdo = bd::getInstance()->getConnexion();

$template = $twig->load('musique.html.twig');
echo $template->render([
    'page' => [
        'title' => "Ma Bibliothèque",
        'name' => "maBiblio",
        'description' => "Ma bibliothèque"
    ],
]);
