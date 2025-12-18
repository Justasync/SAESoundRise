<?php

/**
 * @file Role.enum.php
 * @brief Énumération des rôles utilisateur du système Paaxio
 * 
 * @description Cette énumération définit les différents rôles qu'un utilisateur
 * peut avoir dans le système. Chaque rôle correspond à un niveau d'accès
 * et des fonctionnalités spécifiques.
 */

/**
 * @brief Énumération des rôles utilisateur
 * 
 * @details Les rôles disponibles sont :
 * - Admin : Administrateur avec accès complet au système
 * - Artiste : Utilisateur pouvant publier de la musique
 * - Auditeur : Utilisateur standard pouvant écouter de la musique
 * - Producteur : Utilisateur avec des fonctionnalités de production
 * - Invite : Utilisateur invité avec accès limité
 */
enum RoleEnum: string
{
    /** @brief Rôle administrateur avec tous les privilèges */
    case Admin = 'admin';

    /** @brief Rôle artiste permettant la publication de musique */
    case Artiste = 'artiste';

    /** @brief Rôle auditeur pour l'écoute de musique */
    case Auditeur = 'auditeur';

    /** @brief Rôle producteur avec fonctionnalités de production */
    case Producteur = 'producteur';

    /** @brief Rôle invité avec accès restreint */
    case Invite = 'invite';
}
