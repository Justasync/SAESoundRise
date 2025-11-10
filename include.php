<?php

// Ajout de l'autoload de composer
include_once 'vendor/autoload.php';

// Ajout du fichier constantes qui permet de configurer le site
include_once 'config/constantes.php';

// Ajout du code pour initialiser Twig
include_once 'config/twig.php';

// Ajout du modèle qui gère la connexion mysql
include_once 'modeles/bd.class.php';

// Ajout des contrôleurs
include_once 'controller/controller.class.php';

// Ajout des modèles
include_once 'modeles/role.class.php';
include_once 'modeles/role.dao.php';
