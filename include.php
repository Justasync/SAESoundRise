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
include_once 'modeles/chanson.class.php';
include_once 'modeles/chanson.dao.php';
include_once 'modeles/album.class.php';
include_once 'modeles/album.dao.php';
include_once 'modeles/genre.class.php';
include_once 'modeles/genre.dao.php';
include_once 'modeles/playlist.class.php';
include_once 'modeles/playlist.dao.php';
include_once 'modeles/battle.class.php';
include_once 'modeles/battle.dao.php';
include_once 'modeles/fichier.class.php';
include_once 'modeles/fichier.dao.php';
