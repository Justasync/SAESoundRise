<?php
/**
 * Contrôleur dédié à la gestion de l'administration.
 */
class ControllerAdmin extends Controller
{
    public function __construct(\Twig\Environment $twig, \Twig\Loader\FilesystemLoader $loader)
    {
        parent::__construct($loader, $twig);
    }

    /**
     * Affiche le tableau de bord de l'administrateur.
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
     * Supprime un utilisateur spécifique.
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
     * Modifie un utilisateur existant (Admin seulement).
     */
    public function modifier()
    {
        // 1. Sécurité : Vérification manuelle du rôle Admin
        $this->requireRole(RoleEnum::Admin);

        $pdo = $this->getPDO();
        $utilisateurDAO = new UtilisateurDAO($pdo);
        $roleDao = new RoleDao($pdo); 
        
        $error = null;
        $user = null;

        // 2. Récupération de l'identifiant (Email) via GET ou POST
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

        // 3. Traitement du formulaire (POST)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $pseudo = trim($_POST['pseudo'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $website = trim($_POST['website'] ?? '');
            $roleType = $_POST['role'] ?? 'auditeur';
            $newPassword = $_POST['mdp'] ?? '';

            // Vérification si le pseudo est déjà pris (sauf si c'est le sien)
            if ($pseudo !== $user->getPseudoUtilisateur() && $utilisateurDAO->existsByPseudo($pseudo)) {
                $error = "Ce pseudo est déjà utilisé par un autre membre.";
            } else {
                try {
                    // Mise à jour des infos de base
                    $user->setDescriptionUtilisateur($description !== '' ? $description : null);
                    $user->setSiteWebUtilisateur($website !== '' ? $website : null);
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

        // 4. Affichage du formulaire
        $template = $this->getTwig()->load('utilisateur_modifier.html.twig');
        echo $template->render([
            'page' => ['title' => 'Modifier Utilisateur'],
            'session' => $_SESSION,
            'user' => $user,
            'error' => $error
        ]);
    }
    
    /**
 * Affiche le formulaire de création d'utilisateur (Admin seulement).
 */
    public function ajouter()
    {
        // Seguridad: Solo los admins entran aquí
        $this->requireRole(RoleEnum::Admin);

        $genreDao = new GenreDAO($this->getPDO());
        $genres = $genreDao->findAll();

        $template = $this->getTwig()->load('utilisateur_ajout.html.twig');
        echo $template->render([
            'page' => ['title' => 'Ajouter un utilisateur'],
            'session' => $_SESSION,
            'genres' => $genres,
            'is_admin_context' => true // Variable para saber que estamos en el panel admin
        ]);
    }

}