<?php
require_once 'include.php';
$template = $twig->load('mentionsLegales.html.twig');
echo $template->render([
    'page' => [
        'title' => "Mentions légales",
        'name' => "mentionsLegales",
        'description' => "Mentions légales de Paaxio"
    ],
]);
