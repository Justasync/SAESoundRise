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
}