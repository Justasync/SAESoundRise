<?php
require_once 'include.php';
$template = $twig->load('mentionsLegales.html.twig');
echo $template->render([
    'pageName' => "mentionsLegales"
]);
