<?php
require_once 'include.php';
$template = $twig->load('newsletter.html.twig');
echo $template->render([
    'pageName' => "newsletter"
]);
