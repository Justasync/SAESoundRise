<?php
require_once 'include.php';

$pdo = bd::getInstance()->getConnexion();

$template = $twig->load('musique.html.twig');
echo $template->render([
    'page' => [
        'title' => "Ma Musique",
        'name' => "maMusique",
        'description' => "Ma musique personnelle"
    ],
]);
