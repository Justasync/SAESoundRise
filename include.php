<?php

/**
 * @file include.php
 * @brief Fichier d'inclusion principal de l'application Paaxio
 * 
 * @description Ce fichier centralise toutes les inclusions nécessaires au fonctionnement
 * de l'application. Il initialise la session, charge les dépendances Composer,
 * configure Twig, et inclut tous les contrôleurs et modèles du projet.
 * 
 * @note Ce fichier doit être inclus en premier dans tous les points d'entrée de l'application.
 */

// ==========================================
// CONFIGURATION DU FUSEAU HORAIRE
// ==========================================

/**
 * @brief Configuration du fuseau horaire par défaut
 * 
 * Définit le fuseau horaire sur Europe/Paris pour toutes les opérations
 * de date et heure de l'application.
 */
date_default_timezone_set('Europe/Paris');

// ==========================================
// CHARGEMENT DES DÉPENDANCES
// ==========================================

/**
 * @brief Autoloader de Composer
 * 
 * Charge automatiquement les classes des packages installés via Composer
 * (Twig, PHPMailer, etc.)
 */
require_once 'vendor/autoload.php';

/**
 * @brief Fichier de constantes de configuration
 * 
 * Charge la classe Constantes qui gère la configuration du site
 * (connexion BDD, paramètres email, etc.)
 */
require_once 'modeles/constantes.class.php';

// ==========================================
// ÉNUMÉRATIONS
// ==========================================

/**
 * @brief Énumération des rôles utilisateur
 * 
 * Définit les différents rôles possibles dans l'application
 * (Admin, Artiste, Auditeur, etc.)
 */
require_once 'enums/Role.enum.php';

// ==========================================
// GESTION DE LA SESSION
// ==========================================

/**
 * @brief Démarrage de la session PHP
 * 
 * Initialise la session si elle n'est pas déjà démarrée.
 * Permet de stocker les informations de l'utilisateur connecté.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==========================================
// CONFIGURATION DE TWIG
// ==========================================

/**
 * @brief Initialisation du moteur de templates Twig
 * 
 * Configure l'environnement Twig avec les paramètres du projet,
 * les variables globales et les extensions nécessaires.
 */
require_once 'config/twig.php';

// ==========================================
// MODÈLE DE BASE DE DONNÉES
// ==========================================

/**
 * @brief Classe de gestion de la connexion MySQL
 * 
 * Singleton permettant d'obtenir une connexion PDO à la base de données.
 */
require_once 'modeles/bd.class.php';

// ==========================================
// CONTRÔLEURS
// ==========================================

/**
 * @brief Classe de base des contrôleurs
 */
require_once 'controller/controller.class.php';

/**
 * @brief Fabrique de contrôleurs
 * 
 * Permet d'instancier le bon contrôleur selon la requête.
 */
require_once 'controller/controller_factory.class.php';

/**
 * @brief Contrôleur de la page d'accueil
 */
require_once 'controller/controller_home.class.php';

/**
 * @brief Contrôleur de l'administration
 */
require_once 'controller/controller_admin.class.php';

/**
 * @brief Contrôleur de la newsletter
 */
require_once 'controller/controller_newsletter.class.php';

/**
 * @brief Contrôleur de la page musique
 */
require_once 'controller/controller_musique.class.php';

/**
 * @brief Contrôleur des utilisateurs
 * 
 * Gère l'inscription, la connexion, la modification de profil, etc.
 */
require_once 'controller/controller_utilisateur.class.php';

/**
 * @brief Contrôleur des rôles
 */
require_once 'controller/controller_role.class.php';

/**
 * @brief Contrôleur des chansons
 * 
 * Gère les opérations sur les chansons (lecture, like, etc.)
 */
require_once 'controller/controller_chanson.class.php';

/**
 * @brief Contrôleur des albums
 */
require_once 'controller/controller_album.class.php';

/**
 * @brief Contrôleur des genres musicaux
 */
require_once 'controller/controller_genre.class.php';

/**
 * @brief Contrôleur des playlists
 */
require_once 'controller/controller_playlist.class.php';

/**
 * @brief Contrôleur des battles
 * 
 * Gère les duels musicaux entre artistes.
 */
require_once 'controller/controller_battle.class.php';

/**
 * @brief Contrôleur des fichiers
 * 
 * Gère l'upload et la gestion des fichiers (audio, images).
 */
require_once 'controller/controller_fichier.class.php';

// ==========================================
// MODÈLES (CLASSES MÉTIER ET DAO)
// ==========================================

/**
 * @brief Classe de validation des données
 */
require_once 'modeles/validator.class.php';

/**
 * @brief Classe d'envoi d'emails
 * 
 * Utilise PHPMailer pour l'envoi d'emails (confirmation, newsletter, etc.)
 */
require_once 'modeles/email.class.php';

/**
 * @brief Classe métier des rôles
 */
require_once 'modeles/role.class.php';

/**
 * @brief DAO des rôles
 */
require_once 'modeles/role.dao.php';

/**
 * @brief Classe métier des utilisateurs
 */
require_once 'modeles/utilisateur.class.php';

/**
 * @brief DAO des utilisateurs
 */
require_once 'modeles/utilisateur.dao.php';

/**
 * @brief Classe métier de la newsletter
 */
require_once 'modeles/newsletter.class.php';

/**
 * @brief DAO de la newsletter
 */
require_once 'modeles/newsletter.dao.php';

/**
 * @brief Classe métier des chansons
 */
require_once 'modeles/chanson.class.php';

/**
 * @brief DAO des chansons
 */
require_once 'modeles/chanson.dao.php';

/**
 * @brief Classe métier des albums
 */
require_once 'modeles/album.class.php';

/**
 * @brief DAO des albums
 */
require_once 'modeles/album.dao.php';

/**
 * @brief Classe métier des genres
 */
require_once 'modeles/genre.class.php';

/**
 * @brief DAO des genres
 */
require_once 'modeles/genre.dao.php';

/**
 * @brief Classe métier des playlists
 */
require_once 'modeles/playlist.class.php';

/**
 * @brief DAO des playlists
 */
require_once 'modeles/playlist.dao.php';

/**
 * @brief Classe métier des battles
 */
require_once 'modeles/battle.class.php';

/**
 * @brief DAO des battles
 */
require_once 'modeles/battle.dao.php';

/**
 * @brief Classe métier des fichiers
 */
require_once 'modeles/fichier.class.php';

/**
 * @brief DAO des fichiers
 */
require_once 'modeles/fichier.dao.php';

// ==========================================
// VARIABLES GLOBALES TWIG
// ==========================================

/**
 * @brief Ajout des genres comme variable globale Twig
 * 
 * Récupère tous les genres musicaux de la base de données et les rend
 * disponibles dans tous les templates Twig via la variable 'genres'.
 * Utile pour les menus de navigation et les filtres.
 */
$pdo = bd::getInstance()->getConnexion();
$genreDAO = new GenreDAO($pdo);
$genres = $genreDAO->findAll();
$twig->addGlobal('genres', $genres);
