<?php

/**
 * Contrôleur dédié à la gestion de l'administration.
 * 
 * Ce contrôleur gère l'affichage du tableau de bord administrateur,
 * la liste des utilisateurs et les actions de suppression.
 */
class ControllerAdmin extends Controller
{
    /**
     * Constructeur du contrôleur d'administration.
     *
     * @param \Twig\Environment $twig L'environnement Twig pour le rendu des vues.
     * @param \Twig\Loader\FilesystemLoader $loader Le chargeur de fichiers de templates.
     */
    public function __construct(\Twig\Environment $twig, \Twig\Loader\FilesystemLoader $loader)
    {
        parent::__construct($loader, $twig);
    }

    /**
     * Affiche le tableau de bord de l'administrateur (Liste des utilisateurs).
     *
     * Cette méthode effectue les opérations suivantes :
     * 1. Établit la connexion à la base de données.
     * 2. Récupère la liste complète des utilisateurs via le DAO.
     * 3. Vérifie la présence d'un indicateur de succès dans l'URL (ex: après une création).
     * 4. Rend la vue 'admin_dashboard.html.twig' avec les données nécessaires.
     *
     * @return void
     */
    public function afficher()
    {
        $pdo = Bd::getInstance()->getConnexion();
        $utilisateurDAO = new UtilisateurDAO($pdo);
        $utilisateurs = $utilisateurDAO->findAll();

        $successMessage = null;
        if (isset($_GET['success']) && $_GET['success'] == 1) {
            $successMessage = "L'utilisateur a été créé avec succès !";
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
     *
     * Cette méthode récupère l'identifiant de l'utilisateur passé dans la requête GET,
     * appelle le DAO pour effectuer la suppression en base de données,
     * puis redirige l'administrateur vers le tableau de bord.
     *
     * @return void
     */
    public function supprimer()
    {
        if (isset($_GET['id'])) {
            $pdo = Bd::getInstance()->getConnexion();
            $utilisateurDAO = new UtilisateurDAO($pdo);
            
            $utilisateurDAO->delete($_GET['id']);
        }

        header('Location: ?controller=admin&method=afficher');
        exit();
    }
}