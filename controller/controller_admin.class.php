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
     * Méthode de sécurité pour vérifier le rôle.
     * Inclut la correction pour lire la valeur de l'Enum en session.
     */
    protected function requireRole($requiredRole): void
    {
        // 1. Vérifier l'authentification (Redirige si non connecté)
        $this->requireAuth();

        // 2. Récupérer le rôle en session
        $sessionRole = $_SESSION['user_role'] ?? null;

        // --- CORRECTION IMPORTANTE ---
        // Extraction de la valeur (string) si c'est un objet Enum
        $userRoleValue = (is_object($sessionRole) && property_exists($sessionRole, 'value')) 
                         ? $sessionRole->value 
                         : $sessionRole;

        // 3. Récupérer la valeur du rôle requis
        $requiredRoleValue = ($requiredRole instanceof RoleEnum) 
                             ? $requiredRole->value 
                             : $requiredRole;

        // 4. Comparaison
        if ($userRoleValue !== $requiredRoleValue) {
            http_response_code(403);
            
            // Tentative de chargement du template 403
            try {
                $template = $this->getTwig()->load('403.html.twig');
                echo $template->render([
                    'page' => [
                        'title' => "Erreur 403 - Accès refusé",
                        'name' => "403",
                        'description' => "Vous n'avez pas l'autorisation d'accéder à cette ressource."
                    ],
                    'session' => $_SESSION
                ]);
            } catch (\Exception $e) {
                // Fallback si le template n'existe pas
                die("Erreur 403 : Accès refusé.");
            }
            exit();
        }
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