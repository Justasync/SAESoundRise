<?php

/**
 * @file controller_admin.class.php
 * @brief Fichier contenant le contrôleur d'administration.
 * 
 * Ce fichier gère toutes les fonctionnalités d'administration
 * de l'application Paaxio, notamment la gestion des utilisateurs.
 * 
 */

/**
 * @class ControllerAdmin
 * @brief Contrôleur dédié à la gestion de l'administration.
 * 
 * Cette classe gère les opérations d'administration telles que :
 * - Affichage du tableau de bord administrateur
 * - Suppression d'utilisateurs
 * - Modification d'utilisateurs
 * 
 * @extends Controller
 */
class ControllerAdmin extends Controller
{
    /**
     * @brief Constructeur du contrôleur admin.
     * 
     * @param \Twig\Environment $twig Environnement Twig pour le rendu des templates.
     * @param \Twig\Loader\FilesystemLoader $loader Chargeur de fichiers Twig.
     */
    public function __construct(\Twig\Environment $twig, \Twig\Loader\FilesystemLoader $loader)
    {
        parent::__construct($loader, $twig);
    }

    /**
     * @brief Affiche le tableau de bord de l'administrateur.
     * 
     * Récupère la liste de tous les utilisateurs et les affiche
     * dans le template du tableau de bord admin.
     * Nécessite le rôle Admin.
     * 
     * @return void
     */
    public function afficher()
    {
        // Vérification du rôle Admin
        $this->requireRole(RoleEnum::Admin);

        $pdo = Bd::getInstance()->getConnexion();
        $utilisateurDAO = new UtilisateurDAO($pdo);
        $utilisateurs = $utilisateurDAO->findAll();

        $successMessage = null;
        if (isset($_GET['success'])) {
            if ($_GET['success'] == 1) $successMessage = "L'utilisateur a été créé avec succès !";
            if ($_GET['success'] == 2) $successMessage = "L'utilisateur a été modifié avec succès !";
        }

        $template = $this->getTwig()->load('admin_dashboard.html.twig');
        echo $template->render([
            'page' => ['title' => "Admin Dashboard", 'name' => "admin"],
            'session' => $_SESSION,
            'utilisateurs' => $utilisateurs,
            'success' => $successMessage
        ]);
    }

    /**
     * @brief Supprime un utilisateur spécifique.
     * 
     * Supprime l'utilisateur identifié par son ID (email) passé en paramètre GET.
     * Protection : un administrateur ne peut pas se supprimer lui-même.
     * Nécessite le rôle Admin.
     * 
     * @return void
     */
    public function supprimer()
    {
        // Vérification du rôle Admin
        $this->requireRole(RoleEnum::Admin);

        if (isset($_GET['id'])) {
            $pdo = Bd::getInstance()->getConnexion();
            $utilisateurDAO = new UtilisateurDAO($pdo);

            // Protection : ne pas se supprimer soi-même
            if (isset($_SESSION['user_email']) && $_GET['id'] == $_SESSION['user_email']) {
                // Redirection propre avec la méthode du contrôleur parent
                $this->redirectTo('admin', 'afficher');
                return;
            }

            $utilisateurDAO->delete($_GET['id']);
        }

        // Redirection propre après suppression
        $this->redirectTo('admin', 'afficher');
    }

    /**
     * @brief Consulte les détails d'un utilisateur.
     * 
     * Affiche les informations complètes d'un utilisateur spécifique
     * identifié par son email passé en paramètre GET.
     * Nécessite le rôle Admin.
     * 
     * @return void
     */
    public function consulter()
    {
        // Vérification du rôle Admin
        $this->requireRole(RoleEnum::Admin);

        if (!isset($_GET['id'])) {
            $this->redirectTo('admin', 'afficher');
            return;
        }

        $pdo = Bd::getInstance()->getConnexion();
        $utilisateurDAO = new UtilisateurDAO($pdo);

        $user = $utilisateurDAO->find($_GET['id']);

        if (!$user) {
            $this->redirectTo('admin', 'afficher');
            return;
        }

        $template = $this->getTwig()->load('admin_utilisateur_consulter.html.twig');
        echo $template->render([
            'page' => ['title' => "Consulter Utilisateur - " . $user->getPseudoUtilisateur(), 'name' => "admin"],
            'session' => $_SESSION,
            'user' => $user
        ]);
    }

    /**
     * @brief Modifie un utilisateur existant.
     * 
     * Permet à l'administrateur de modifier les informations d'un utilisateur :
     * - Pseudo
     * - Rôle
     * - Mot de passe (optionnel)
     * 
     * L'identifiant de l'utilisateur est récupéré via GET (id) ou POST (original_email).
     * Nécessite le rôle Admin.
     * 
     * @return void
     */
    public function modifier()
    {
        // Sécurité : Vérification manuelle du rôle Admin
        $this->requireRole(RoleEnum::Admin);

        $pdo = $this->getPDO();
        $utilisateurDAO = new UtilisateurDAO($pdo);
        $roleDao = new RoleDAO($pdo);

        $error = null;
        $user = null;

        // Récupération de l'identifiant (Email) via GET ou POST
        $emailTarget = $_GET['id'] ?? $_POST['original_email'] ?? null;

        if (!$emailTarget) {
            $this->redirectTo('admin', 'afficher');
            return;
        }

        // Recherche de l'utilisateur
        $user = $utilisateurDAO->find($emailTarget);
        if (!$user) {
            $this->redirectTo('admin', 'afficher');
            return;
        }

        // Traitement du formulaire (POST)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $pseudo = trim($_POST['pseudo'] ?? '');
            $roleType = $_POST['role'] ?? 'auditeur';
            $newPassword = $_POST['mdp'] ?? '';

            // Vérification si le pseudo est déjà pris (sauf si c'est le sien)
            if ($pseudo !== $user->getPseudoUtilisateur() && $utilisateurDAO->existsByPseudo($pseudo)) {
                $error = "Ce pseudo est déjà utilisé par un autre membre.";
            } else {
                try {
                    // Mise à jour des infos de base
                    $user->setPseudoUtilisateur($pseudo);
                    $user->setNomUtilisateur($pseudo);

                    // Mise à jour du Rôle
                    $newRole = $roleDao->findByType($roleType);
                    if ($newRole) {
                        $user->setRoleUtilisateur($newRole);
                    }

                    // Mise à jour du Mot de passe (Uniquement si rempli)
                    if (!empty($newPassword)) {
                        $user->setMotDePasseUtilisateur(password_hash($newPassword, PASSWORD_ARGON2ID));
                    }

                    // Sauvegarde en base de données
                    if ($utilisateurDAO->update($user)) {
                        // Redirection vers le dashboard avec message de succès (success=2)
                        $this->redirectTo('admin', 'afficher', ['success' => 2]);
                        return;
                    } else {
                        $error = "Erreur lors de la mise à jour.";
                    }
                } catch (Exception $e) {
                    $error = "Erreur système : " . $e->getMessage();
                }
            }
        }

        // Affichage du formulaire
        $template = $this->getTwig()->load('utilisateur_modifier.html.twig');
        echo $template->render([
            'page' => ['title' => 'Modifier Utilisateur'],
            'session' => $_SESSION,
            'user' => $user,
            'error' => $error
        ]);
    }
}
