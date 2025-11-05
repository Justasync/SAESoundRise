<?php
require_once 'include.php';
$template = $twig->load('index.html.twig');
echo $template->render([
    'pageName' => "accueil"
]);
