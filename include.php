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
include_once 'models/chanson.class.php';
include_once 'models/chanson.dao.php';
include_once 'models/album.class.php';
include_once 'models/album.dao.php';
include_once 'models/genre.class.php';
include_once 'models/genre.dao.php';
include_once 'models/playlist.class.php';
include_once 'models/playlist.dao.php';
include_once 'models/battle.class.php';
include_once 'models/battle.dao.php';
include_once 'models/fichier.class.php';
include_once 'models/fichier.dao.php';