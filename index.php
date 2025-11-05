<?php

require_once 'include.php';

        //Connexion à la base de données en pdo
        $pdo = new PDO('mysql:host=localhost;dbname=projet_php', 'root', '');

        $sql = "SELECT * FROM cds";
        $pdoStatement = $pdo->prepare($sql);
        $pdoStatement->execute();
        $cds = $pdoStatement->fetchAll(PDO::FETCH_ASSOC);

$template = $twig->load('index.html.twig');

echo $template->render([

]);
