<?php

/**
 * @file index.php
 * @brief Point d'entrée principal de l'application Paaxio
 * 
 * @description Ce fichier est le front controller de l'application.
 * Il reçoit toutes les requêtes HTTP, détermine le contrôleur et la méthode
 * à appeler en fonction des paramètres GET, et exécute l'action correspondante.
 * 
 * @usage Les URLs suivent le format : index.php?controller=xxx&method=yyy
 *        Par défaut (sans paramètres), affiche la page d'accueil.
 * 
 * @example
 * - index.php?controller=home&method=afficher → Page d'accueil
 * - index.php?controller=utilisateur&method=signin → Connexion
 * - index.php?controller=chanson&method=toggleLike → Like d'une chanson
 */

/**
 * @brief Inclusion du fichier principal
 * 
 * Charge toutes les dépendances, contrôleurs et modèles nécessaires.
 */
require_once 'include.php';

try {
    // ==========================================
    // RÉCUPÉRATION DES PARAMÈTRES DE ROUTAGE
    // ==========================================

    /**
     * @var string $controllerName
     * @brief Nom du contrôleur à instancier
     * 
     * Récupéré depuis le paramètre GET 'controller'.
     * Vide par défaut si non spécifié.
     */
    if (isset($_GET['controller'])) {
        $controllerName = $_GET['controller'];
    } else {
        $controllerName = '';
    }

    /**
     * @var string $method
     * @brief Nom de la méthode à appeler sur le contrôleur
     * 
     * Récupéré depuis le paramètre GET 'method'.
     * Vide par défaut si non spécifié.
     */
    if (isset($_GET['method'])) {
        $method = $_GET['method'];
    } else {
        $method = '';
    }

    // ==========================================
    // GESTION DE LA PAGE D'ACCUEIL PAR DÉFAUT
    // ==========================================

    /**
     * @brief Redirection vers la page d'accueil si aucun paramètre
     * 
     * Si ni le contrôleur ni la méthode ne sont définis,
     * on affiche la page d'accueil par défaut.
     */
    if ($controllerName == '' && $method == '') {
        $controllerName = 'home';
        $method = 'afficher';
    }

    // ==========================================
    // VALIDATION DES PARAMÈTRES
    // ==========================================

    /**
     * @brief Vérification de la présence du contrôleur
     * 
     * @throws Exception Si le contrôleur n'est pas défini
     */
    if ($controllerName == '') {
        throw new Exception('Le controller n\'est pas défini');
    }

    /**
     * @brief Vérification de la présence de la méthode
     * 
     * @throws Exception Si la méthode n'est pas définie
     */
    if ($method == '') {
        throw new Exception('La méthode n\'est pas définie');
    }

    // ==========================================
    // EXÉCUTION DE L'ACTION
    // ==========================================

    /**
     * @brief Instanciation du contrôleur via la fabrique
     * 
     * Utilise ControllerFactory pour créer l'instance du contrôleur
     * correspondant au nom demandé.
     * 
     * @var Controller $controller Instance du contrôleur
     */
    $controller = ControllerFactory::getController($controllerName, $loader, $twig);

    /**
     * @brief Appel de la méthode demandée
     * 
     * Exécute la méthode spécifiée sur le contrôleur.
     * La méthode call() vérifie les permissions et gère le routage interne.
     */
    $controller->call($method);
} catch (Exception $e) {
    /**
     * @brief Gestion des erreurs
     * 
     * En cas d'exception, affiche un message d'erreur et arrête l'exécution.
     * 
     * @todo En production, rediriger vers une page d'erreur personnalisée
     * au lieu d'afficher le message brut.
     */
    die('Erreur : ' . $e->getMessage());
}
