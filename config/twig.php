<?php

/**
 * @file twig.php
 * @brief Configuration et initialisation du moteur de templates Twig
 * 
 * @description Ce fichier configure l'environnement Twig pour le rendu des templates.
 * Il définit les paramètres de debug, les variables globales accessibles dans tous
 * les templates, et charge les extensions nécessaires.
 */

// Ajout de la classe IntlExtension et création de l'alias IntlExtension
use Twig\Extra\Intl\IntlExtension;

/**
 * @var Twig\Loader\FilesystemLoader $loader
 * @brief Chargeur de fichiers Twig
 * 
 * Initialise le chargeur Twig en spécifiant le dossier contenant les templates.
 */
$loader = new Twig\Loader\FilesystemLoader('templates');

/**
 * @var Twig\Environment $twig
 * @brief Environnement Twig configuré
 * 
 * @details Configuration de l'environnement Twig avec les options suivantes :
 * - debug : Active le mode debug (à désactiver en production)
 *   Permet d'utiliser {{ dump(variable) }} dans les templates
 *   Nécessite l'activation de l'extension DebugExtension
 */
$twig = new Twig\Environment($loader, [
    'debug' => true,
    // Il est possible de définir d'autres variables d'environnement ici
]);

/**
 * @brief Ajout des variables globales accessibles dans tous les templates
 * 
 * - config : Configuration du site (depuis Constantes)
 * - session : Données de la session PHP courante
 */
$twig->addGlobal('config', Constantes::getInstance()->getConfig());
$twig->addGlobal('session', $_SESSION);

/**
 * @brief Définition du fuseau horaire pour les filtres de date
 * 
 * Configure le fuseau horaire français (Europe/Paris) pour que les filtres
 * de date dans les templates affichent l'heure locale correcte.
 */
$twig->getExtension(\Twig\Extension\CoreExtension::class)->setTimezone('Europe/Paris');

/**
 * @brief Ajout de l'extension de debug
 * 
 * Permet l'utilisation de la fonction dump() dans les templates
 * pour déboguer les variables.
 */
$twig->addExtension(new \Twig\Extension\DebugExtension());

/**
 * @brief Ajout de l'extension d'internationalisation (commentée)
 * 
 * Cette extension permet d'utiliser les filtres de formatage de date
 * internationalisés dans les templates Twig.
 * Décommenter la ligne ci-dessous pour l'activer.
 */
// $twig->addExtension(new IntlExtension());
