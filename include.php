<?php

// Ajout de l'autoload de composer
require_once 'vendor/autoload.php';

// Ajout du fichier constantes qui permet de configurer le site
require_once 'config/constantes.php';

// Ajout du code pour initialiser Twig
require_once 'config/twig.php';

// Ajout du modèle qui gère la connexion mysql
require_once 'modeles/bd.class.php';

// Ajout des contrôleurs
require_once 'controller/controller.class.php';
require_once 'controller/controller_factory.class.php';
require_once 'controller/controller_role.class.php';
require_once 'controller/controller_chanson.class.php';
require_once 'controller/controller_album.class.php';
require_once 'controller/controller_genre.class.php';
require_once 'controller/controller_playlist.class.php';
require_once 'controller/controller_battle.class.php';
require_once 'controller/controller_fichier.class.php';

// Ajout des modèles
require_once 'modeles/role.class.php';
require_once 'modeles/role.dao.php';
require_once 'modeles/chanson.class.php';
require_once 'modeles/chanson.dao.php';
require_once 'modeles/album.class.php';
require_once 'modeles/album.dao.php';
require_once 'modeles/genre.class.php';
require_once 'modeles/genre.dao.php';
require_once 'modeles/playlist.class.php';
require_once 'modeles/playlist.dao.php';
require_once 'modeles/battle.class.php';
require_once 'modeles/battle.dao.php';
require_once 'modeles/fichier.class.php';
require_once 'modeles/fichier.dao.php';
