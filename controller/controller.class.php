<?php

/**
 * @file controller.class.php
 * @brief Fichier contenant la classe de base Controller.
 * 
 * Ce fichier définit la classe abstraite Controller qui sert de classe parente
 * pour tous les contrôleurs de l'application Paaxio.
 * 
 */

/**
 * @class Controller
 * @brief Classe de base pour tous les contrôleurs de l'application.
 * 
 * Cette classe fournit les fonctionnalités communes à tous les contrôleurs :
 * - Accès à la connexion PDO
 * - Gestion des templates Twig
 * - Récupération des données GET et POST
 * - Authentification et contrôle des rôles
 * - Redirection entre contrôleurs
 */
class Controller
{
    /**
     * @var PDO $pdo Connexion à la base de données.
     */
    private PDO $pdo;

    /**
     * @var \Twig\Loader\FilesystemLoader $loader Chargeur de fichiers Twig.
     */
    private \Twig\Loader\FilesystemLoader $loader;

    /**
     * @var \Twig\Environment $twig Environnement Twig pour le rendu des templates.
     */
    private \Twig\Environment $twig;

    /**
     * @var array|null $get Données de la requête GET.
     */
    private ?array $get = null;

    /**
     * @var array|null $post Données de la requête POST.
     */
    private ?array $post = null;

    /**
     * @brief Constructeur du contrôleur.
     * 
     * Initialise la connexion à la base de données et configure Twig.
     * Récupère également les variables GET et POST.
     * 
     * @param \Twig\Loader\FilesystemLoader $loader Chargeur de templates Twig.
     * @param \Twig\Environment $twig Environnement Twig.
     */
    public function __construct(\Twig\Loader\FilesystemLoader $loader, \Twig\Environment $twig)
    {
        $db = bd::getInstance();
        $this->pdo = $db->getConnexion();
        $this->loader = $loader;
        $this->twig = $twig;

        // Récupération des variables GET et POST
        if (isset($_GET) && !empty($_GET)) {
            $this->get = $_GET;
        }
        if (isset($_POST) && !empty($_POST)) {
            $this->post = $_POST;
        }
        $this->post = $_POST;
    }

    /**
     * @brief Appelle une méthode du contrôleur de façon dynamique.
     * 
     * Vérifie si la méthode existe avant de l'appeler.
     * Affiche une page 404 si la méthode n'existe pas.
     * 
     * @param string $method Nom de la méthode à appeler.
     * @return mixed Résultat de la méthode appelée.
     */
    public function call(string $method): mixed
    {
        if (!method_exists($this, $method)) {
            // Afficher une page 404 méthode non autorisée avec Twig
            http_response_code(404);
            $template = $this->getTwig()->load('errors/404.html.twig');
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

    /**
     * @brief Récupère la connexion PDO.
     * @return PDO|null Connexion à la base de données.
     */
    public function getPDO(): ?PDO
    {
        return $this->pdo;
    }

    /**
     * @brief Définit la connexion PDO.
     * @param PDO|null $pdo Nouvelle connexion à la base de données.
     * @return void
     */
    public function setPDO(?PDO $pdo): void
    {
        $this->pdo = $pdo;
    }

    /**
     * @brief Récupère le chargeur de templates Twig.
     * @return \Twig\Loader\FilesystemLoader|null Chargeur de fichiers Twig.
     */
    public function getLoader(): ?\Twig\Loader\FilesystemLoader
    {
        return $this->loader;
    }

    /**
     * @brief Définit le chargeur de templates Twig.
     * @param \Twig\Loader\FilesystemLoader|null $loader Nouveau chargeur de fichiers.
     * @return void
     */
    public function setLoader(?\Twig\Loader\FilesystemLoader $loader): void
    {
        $this->loader = $loader;
    }

    /**
     * @brief Récupère l'environnement Twig.
     * @return \Twig\Environment|null Environnement Twig.
     */
    public function getTwig(): ?\Twig\Environment
    {
        return $this->twig;
    }

    /**
     * @brief Définit l'environnement Twig.
     * @param \Twig\Environment|null $twig Nouvel environnement Twig.
     * @return void
     */
    public function setTwig(?\Twig\Environment $twig): void
    {
        $this->twig = $twig;
    }

    /**
     * @brief Récupère les données GET.
     * @return array|null Tableau des paramètres GET ou null.
     */
    public function getGet(): ?array
    {
        return $this->get;
    }

    /**
     * @brief Définit les données GET.
     * @param array|null $get Nouveau tableau de paramètres GET.
     * @return void
     */
    public function setGet(?array $get): void
    {
        $this->get = $get;
    }

    /**
     * @brief Récupère les données POST.
     * @return array|null Tableau des paramètres POST ou null.
     */
    public function getPost(): ?array
    {
        return $this->post;
    }

    /**
     * @brief Définit les données POST.
     * @param array|null $post Nouveau tableau de paramètres POST.
     * @return void
     */
    public function setPost(?array $post): void
    {
        $this->post = $post;
    }

    /**
     * @brief Exige que l'utilisateur soit authentifié.
     * 
     * Redirige vers la page de connexion si l'utilisateur n'est pas connecté.
     * L'URL de redirection après connexion peut être construite à partir des paramètres.
     * 
     * @param string $controller Nom du contrôleur pour la redirection après connexion (ex: "playlist").
     * @param string $method Nom de la méthode pour la redirection après connexion (ex: "afficher").
     * @param array $params Paramètres additionnels sous forme clé => valeur (facultatif).
     * @return void Quitte le script si l'utilisateur n'est pas authentifié.
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
     * @brief Exige que l'utilisateur ait un rôle spécifique.
     * 
     * Affiche une erreur 403 si le rôle de l'utilisateur ne correspond pas au rôle requis.
     * Appelle requireAuth() en interne pour vérifier l'authentification.
     * 
     * @param string|RoleEnum $requiredRole Le rôle requis (RoleEnum ou string).
     * @return void Quitte le script si l'utilisateur n'a pas le rôle requis.
     */
    protected function requireRole($requiredRole): void
    {
        $this->requireAuth();

        // Récupérer le rôle en session
        $sessionRole = $_SESSION['user_role'] ?? null;

        // Si en session on a un Objet (Enum), on prend sa valeur. Sinon, on prend la string.
        $userRoleValue = (is_object($sessionRole) && property_exists($sessionRole, 'value'))
            ? $sessionRole->value
            : $sessionRole;

        // Récupérer la valeur du rôle requis (argument)
        $requiredRoleValue = ($requiredRole instanceof RoleEnum)
            ? $requiredRole->value
            : $requiredRole;
        $userRole = $_SESSION['user_role'] ?? null;
        $userRoleValue = $userRole instanceof RoleEnum ? $userRole->value : $userRole;
        $roleValue = $requiredRole instanceof RoleEnum ? $requiredRole->value : $requiredRole;

        if ($userRoleValue !== $roleValue) {
            http_response_code(403);
            $template = $this->getTwig()->load('errors/403.html.twig');
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
     * @brief Redirige vers un contrôleur et une méthode donnés.
     * 
     * Construit une URL avec les paramètres fournis et effectue une redirection HTTP.
     * 
     * @param string $controller Nom du contrôleur (ex: "home").
     * @param string $method Nom de la méthode (ex: "afficher").
     * @param array $params Paramètres additionnels sous forme clé => valeur (facultatif).
     * @return void Quitte le script après la redirection.
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
     * @brief Affiche une erreur 405 Méthode non autorisée.
     * 
     * Affiche une page d'erreur 405 avec un message explicite et quitte le script.
     * 
     * @return void
     */
    protected function show405(): void
    {
        http_response_code(405);
        $template = $this->getTwig()->load('errors/405.html.twig');
        echo $template->render([
            'page' => [
                'title' => "Erreur 405 - Méthode non autorisée",
                'name' => "405",
                'description' => "La méthode HTTP utilisée n'est pas autorisée pour cette ressource."
            ]
        ]);
        exit();
    }

    /**
     * @brief Exige que l'utilisateur ait un des rôles spécifiés.
     * 
     * Affiche une erreur 403 si le rôle de l'utilisateur ne correspond à aucun des rôles autorisés.
     * Appelle requireAuth() en interne pour vérifier l'authentification.
     * 
     * @param array $allowedRoles Tableau des rôles autorisés (RoleEnum ou string).
     * @return void Quitte le script si l'utilisateur n'a aucun des rôles requis.
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
            $template = $this->getTwig()->load('errors/403.html.twig');
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
