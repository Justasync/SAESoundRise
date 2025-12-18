<?php

/**
 * @file controller_factory.class.php
 * @brief Fichier contenant la fabrique de contrôleurs.
 * 
 * Ce fichier implémente le patron de conception Factory pour créer
 * dynamiquement les instances de contrôleurs.
 * 
 */

/**
 * @class ControllerFactory
 * @brief Fabrique pour la création dynamique de contrôleurs.
 * 
 * Cette classe utilise le patron Factory pour instancier les contrôleurs
 * à partir de leur nom. Elle permet de centraliser la logique de création
 * des contrôleurs.
 */
class ControllerFactory
{
    /**
     * @brief Crée et retourne une instance du contrôleur demandé.
     * 
     * Cette méthode statique construit le nom de la classe du contrôleur
     * en utilisant la convention "Controller" + nom avec première lettre majuscule.
     * 
     * @param string $controller Nom du contrôleur à instancier (ex: "home", "album").
     * @param \Twig\Loader\FilesystemLoader $loader Chargeur de templates Twig.
     * @param \Twig\Environment $twig Environnement Twig pour le rendu.
     * @return Controller Instance du contrôleur demandé.
     * @throws Exception Si la classe du contrôleur n'existe pas.
     */
    public static function getController($controller, \Twig\Loader\FilesystemLoader $loader, \Twig\Environment $twig)
    {
        $controllerName = "Controller" . ucfirst($controller);
        if (!class_exists($controllerName)) {
            throw new Exception("Le controller $controllerName n'existe pas");
        }
        return new $controllerName($twig, $loader);
    }
}
