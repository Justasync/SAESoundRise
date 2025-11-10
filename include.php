<?php

//ajout de l’autoload de composer
include_once 'vendor/autoload.php';

//Ajout des constantes de configuration
include_once 'config/constantes.php';

//Ajout de la configuration de Twig
include_once 'config/twig.php';

//Ajout du modele qui gere la connexion mysql
include_once 'modeles/bd.class.php';

//Ajout des contrôleurs
include_once 'controller/controller.class.php';