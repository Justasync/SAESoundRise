<?php

require_once 'include.php';

        $sql = "SELECT * FROM cds";
        $pdoStatement = $pdo->prepare($sql);
        $pdoStatement->execute();
        $cds = $pdoStatement->fetchAll(PDO::FETCH_ASSOC);

$template = $twig->load('index.html.twig');

echo $template->render([

]);
