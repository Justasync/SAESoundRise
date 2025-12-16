<?php
//Définition de la classe controller
class Controller
{
    // Code du contrôleur
    private PDO $pdo;
    private \Twig\Loader\FilesystemLoader $loader;
    private \Twig\Environment $twig;
    private ?array $get = null;
    private ?array $post = null;

    // Constructeur du contrôleur
    public function __construct(\Twig\Loader\FilesystemLoader $loader, \Twig\Environment $twig)
    {
        $db = bd::getInstance();
        $this->pdo = $db->getConnexion();
        $this->loader = $loader;
        $this->twig = $twig;

        //Récupération des variables GET et POST
        if (isset($_GET) && !empty($_GET)) {
            $this->get = $_GET;
        }
        if (isset($_POST) && !empty($_POST)) {
            $this->post = $_POST;
        }
        $this->post = $_POST;
    }
    // Méthode pour appeler une méthode du contrôleur
    public function call(string $method): mixed
    {
        if (!method_exists($this, $method)) {
            // Afficher une page 404 méthode non autorisée avec Twig
            http_response_code(404);
            $template = $this->getTwig()->load('404.html.twig');
            echo $template->render([
                'page' => [
                    'title' => "Page non trouvée",
                    'name'  => "error_404",
                    'description' => "La page ou la méthode demandée n'existe pas."
                ]
            ]);
            exit;
        } else {
            return $this->$method();
        }
    }

    public function getPDO(): ?PDO
    {
        return $this->pdo;
    }

    public function setPDO(?PDO $pdo): void
    {
        $this->pdo = $pdo;
    }

    public function getLoader(): ?\Twig\Loader\FilesystemLoader
    {
        return $this->loader;
    }

    public function setLoader(?\Twig\Loader\FilesystemLoader $loader): void
    {
        $this->loader = $loader;
    }

    public function getTwig(): ?\Twig\Environment
    {
        return $this->twig;
    }

    public function setTwig(?\Twig\Environment $twig): void
    {
        $this->twig = $twig;
    }


    public function getGet(): ?array
    {
        return $this->get;
    }


    public function setGet(?array $get): void
    {
        $this->get = $get;
    }

    public function getPost(): ?array
    {
        return $this->post;
    }

    public function setPost(?array $post): void
    {
        $this->post = $post;
    }

    /**
     * Exige que l'utilisateur soit authentifié. Redirige vers la page de connexion si non connecté.
     * @param string $controller Le nom du contrôleur pour rediriger après connexion (ex: "playlist")
     * @param string $method Le nom de la méthode pour rediriger après connexion (ex: "afficher")
     * @param array $params Paramètres additionnels sous forme clé => valeur (facultatif)
     * @return void Quitte si l'utilisateur n'est pas authentifié
     */
    protected function requireAuth(string $controller = '', string $method = '', array $params = []): void
    {
        if (!isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
            // Construction de l'URL de redirection
            if (empty($controller) && empty($method)) {
                // Si aucun paramètre n'est fourni, utiliser l'URL actuelle
                $redirectUrl = $_SERVER['REQUEST_URI'] ?? '/';
            } else {
                // Construire l'URL à partir des paramètres fournis
                $redirectParams = array_merge(['controller' => $controller, 'method' => $method], $params);
                $redirectUrl = '/?' . http_build_query($redirectParams);
            }

            // Éviter l'injection d'URL - n'autorise que les chemins relatifs commençant par /
            if (!empty($redirectUrl)) {
                $redirectUrlDecoded = urldecode($redirectUrl);
                if (strpos($redirectUrlDecoded, '://') !== false || (strlen($redirectUrlDecoded) > 0 && $redirectUrlDecoded[0] !== '/')) {
                    $redirectUrl = '/';
                }
            }

            $redirectToEncoded = urlencode($redirectUrl);

            // Redirection vers la page de connexion avec l'URL de retour
            $this->redirectTo('home', 'connect', ['redirect' => $redirectToEncoded]);
        }
    }

    /**
     * Exige que l'utilisateur ait un rôle spécifique. Affiche une erreur 403 si le rôle ne correspond pas.
     * @param string|RoleEnum $requiredRole Le rôle requis (RoleEnum ou string)
     * @return void Quitte si l'utilisateur n'a pas le rôle requis
     */
    protected function requireRole($requiredRole): void
    {
        $this->requireAuth();

        $userRole = $_SESSION['user_role'] ?? null;
        $userRoleValue = $userRole instanceof RoleEnum ? $userRole->value : $userRole;
        $roleValue = $requiredRole instanceof RoleEnum ? $requiredRole->value : $requiredRole;

        if ($userRoleValue !== $roleValue) {
            http_response_code(403);
            $template = $this->getTwig()->load('403.html.twig');
            echo $template->render([
                'page' => [
                    'title' => "Erreur 403 - Accès refusé",
                    'name' => "403",
                    'description' => "Vous n'avez pas l'autorisation d'accéder à cette ressource."
                ]
            ]);
            exit();
        }
    }

    /**
     * Redirige vers un contrôleur et une méthode donnés, avec des paramètres additionnels.
     *
     * @param string $controller Le nom du contrôleur (ex: "home")
     * @param string $method Le nom de la méthode (ex: "afficher")
     * @param array $params Paramètres additionnels sous forme clé => valeur (facultatif)
     */
    protected function redirectTo(string $controller, string $method, array $params = []): void
    {
        $query = [
            'controller' => $controller,
            'method' => $method
        ];

        if (!empty($params)) {
            $query = array_merge($query, $params);
        }

        $queryString = http_build_query($query);
        header('Location: /?' . $queryString);
        exit();
    }

    /**
     * Exige que l'utilisateur ait un des rôles spécifiés. Affiche une erreur 403 si aucun ne correspond.
     * @param array $allowedRoles Tableau des rôles autorisés (RoleEnum ou string)
     * @return void Quitte si l'utilisateur n'a aucun des rôles requis
     */
    protected function requireAnyRole(array $allowedRoles): void
    {
        $this->requireAuth();

        $userRole = $_SESSION['user_role'] ?? null;
        $userRoleValue = $userRole instanceof RoleEnum ? $userRole->value : $userRole;
        $allowedRoleValues = array_map(function ($role) {
            return $role instanceof RoleEnum ? $role->value : $role;
        }, $allowedRoles);

        if (!in_array($userRoleValue, $allowedRoleValues, true)) {
            http_response_code(403);
            $template = $this->getTwig()->load('403.html.twig');
            echo $template->render([
                'page' => [
                    'title' => "Erreur 403 - Accès refusé",
                    'name' => "403",
                    'description' => "Vous n'avez pas l'autorisation d'accéder à cette ressource."
                ]
            ]);
            exit();
        }
    }
}


//?array: on attend un tableau ou null