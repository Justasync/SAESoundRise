<?php

//ajout de l’autoload de composer
include_once 'vendor/autoload.php';

//Ajout des constantes de configuration
include_once 'config/constantes.php';

//Ajout de la configuration de Twig
include_once 'config/twig.php';

//Ajout du modele qui gere la connexion mysql
include_once 'models/bd.class.php';

//Ajout des modeles
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