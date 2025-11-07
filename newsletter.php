<?php
require_once 'include.php';
$template = $twig->load('newsletter.html.twig');
echo $template->render([
    "page" => [
        'title' => "Newsletter",
        'name' => "newsletter",
        'description' => "Newsletter de Paaxio"
    ]
]);
