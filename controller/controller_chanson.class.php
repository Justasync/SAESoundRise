<?php

/**
 * @file controller_chanson.class.php
 * @brief Fichier contenant le contrôleur de gestion des chansons.
 * 
 * Ce fichier gère toutes les fonctionnalités liées aux chansons
 * dans l'application Paaxio.
 * 
 */

/**
 * @class ControllerChanson
 * @brief Contrôleur dédié à la gestion des chansons.
 * 
 * Cette classe gère les opérations sur les chansons :
 * - Affichage d'une chanson spécifique
 * - Recherche par titre ou par album
 * - Liste de toutes les chansons
 * - Filtrage et tri des chansons
 * - Gestion des likes
 * - Incrémentation du nombre d'écoutes
 * 
 * @extends Controller
 */
class ControllerChanson extends Controller
{
    /**
     * @brief Constructeur du contrôleur chanson.
     * 
     * @param \Twig\Environment $twig Environnement Twig pour le rendu des templates.
     * @param \Twig\Loader\FilesystemLoader $loader Chargeur de fichiers Twig.
     */
    public function __construct(\Twig\Environment $twig, \Twig\Loader\FilesystemLoader $loader)
    {
        parent::__construct($loader, $twig);
    }

    /**
     * @brief Affiche les détails d'une chanson spécifique.
     * 
     * Récupère une chanson par son ID passé en paramètre GET.
     * 
     * @return void
     */
    public function afficher()
    {
        $idChanson = isset($_GET['idChanson']) ? $_GET['idChanson'] : null;

        // Récupération de la chanson
        $managerChanson = new ChansonDao($this->getPdo());
        $chanson = $managerChanson->findId($idChanson);

        $template = $this->getTwig()->load('test.html.twig');
        echo $template->render(array(
            'page' => [
                'title' => "Chanson",
                'name' => "chanson",
                'description' => "Chanson dans Paaxio"
            ],
            'testing' => $chanson,
        ));
    }

    /**
     * @brief Recherche des chansons par leur titre.
     * 
     * Récupère les chansons correspondant au titre passé en paramètre GET.
     * 
     * @return void
     */
    public function rechercherParTitre()
    {
        $titreChanson = isset($_GET['titreChanson']) ? $_GET['titreChanson'] : null;

        // Récupération des chansons par titre
        $managerChanson = new ChansonDao($this->getPdo());
        $chanson = $managerChanson->rechercherParTitre($titreChanson);

        $template = $this->getTwig()->load('test.html.twig');
        echo $template->render(array(
            'page' => [
                'title' => "Chanson",
                'name' => "chanson",
                'description' => "Chanson dans Paaxio"
            ],
            'testing' => $chanson,
        ));
    }

    /**
     * @brief Recherche des chansons par album.
     * 
     * Récupère toutes les chansons d'un album spécifique via son ID.
     * 
     * @return void
     */
    public function rechercherParAlbum()
    {
        $idAlbum = isset($_GET['idAlbum']) ? $_GET['idAlbum'] : null;

        // Récupération des chansons de l'album
        $managerChanson = new ChansonDao($this->getPdo());
        $chanson = $managerChanson->rechercherParAlbum($idAlbum);

        $template = $this->getTwig()->load('test.html.twig');
        echo $template->render(array(
            'page' => [
                'title' => "Chanson",
                'name' => "chanson",
                'description' => "Chanson dans Paaxio"
            ],
            'testing' => $chanson,
        ));
    }

    /**
     * @brief Liste toutes les chansons de la plateforme.
     * 
     * Récupère toutes les chansons et les affiche dans un template de test.
     * 
     * @return void
     */
    public function lister()
    {
        // Récupération des chansons
        $managerChanson = new ChansonDao($this->getPdo());
        $chansons = $managerChanson->findAll();

        // Choix du template
        $template = $this->getTwig()->load('test.html.twig');

        // Affichage de la page
        echo $template->render(array(
            'page' => [
                'title' => "Chansons",
                'name' => "chansons",
                'description' => "Chansons dans Paaxio"
            ],
            'testing' => $chansons,
        ));
    }

    /**
     * @brief Liste toutes les chansons sous forme de tableau.
     * 
     * Récupère toutes les chansons et les affiche dans un format tableau.
     * 
     * @return void
     */
    public function listerTableau()
    {
        $managerChanson = new ChansonDao($this->getPdo());
        $chansons = $managerChanson->findAll();

        // Génération de la vue
        $template = $this->getTwig()->load('test.html.twig');
        echo $template->render(array(
            'page' => [
                'title' => "Chansons tableau",
                'name' => "chansont",
                'description' => "Chansons tableau dans Paaxio"
            ],
            'testing' => $chansons,
        ));
    }

    /**
     * @brief Filtre les chansons selon différents critères.
     * 
     * Permet de filtrer les chansons par :
     * - Genre musical (idGenre)
     * - Album (idAlbum)
     * - Colonne de tri (titreChanson, dateTeleversementChanson, nbEcouteChanson)
     * - Ordre de tri (ASC ou DESC)
     * 
     * @return void
     */
    public function filtrerChanson()
    {
        // Récupération des filtres depuis l'URL
        $idGenre = $_GET['idGenre'] ?? null;
        $idAlbum = $_GET['idAlbum'] ?? null;

        // Récupération de l'ordre (asc ou desc) et de la colonne de tri
        $ordre = isset($_GET['ordre']) && in_array(strtolower($_GET['ordre']), ['asc', 'desc'])
            ? strtoupper($_GET['ordre'])
            : 'ASC';

        $tri = $_GET['tri'] ?? 'titreChanson';
        $colonnesValides = ['titreChanson', 'dateTeleversementChanson', 'nbEcouteChanson'];
        $colonne = in_array($tri, $colonnesValides) ? $tri : 'titreChanson';

        // Récupération des chansons filtrées via le DAO
        $managerChanson = new ChansonDao($this->getPdo());
        $chansons = $managerChanson->filtrerChanson($idGenre, $idAlbum, $colonne, $ordre);

        // Génération de la vue
        $template = $this->getTwig()->load('test.html.twig');
        echo $template->render([
            'page' => [
                'title' => "Chansons filtrées",
                'name' => "chansons_filtrees",
                'description' => "Chansons filtrées dans Paaxio"
            ],
            'testing' => $chansons,
        ]);
    }

    /**
     * @brief Bascule l'état "j'aime" d'une chanson pour l'utilisateur connecté.
     * 
     * Cette méthode AJAX permet à un utilisateur authentifié de liker ou
     * unliker une chanson. Retourne une réponse JSON avec le nouvel état.
     * 
     * @return void Retourne une réponse JSON et termine le script.
     */
    public function toggleLike()
    {
        // Vérifie la connexion
        $this->requireAuth();

        $emailUtilisateur = $_SESSION['user_email'] ?? null;

        // Récupère l'ID de la chanson depuis POST
        $idChanson = $_POST['idChanson'] ?? null;
        if (!$idChanson) {
            http_response_code(400);
            echo json_encode(['error' => 'ID de chanson manquant']);
            exit;
        }

        $chansonDAO = new ChansonDAO($this->getPdo());

        // Vérifie l'état actuel du like avant de basculer
        $chansonsLikees = $chansonDAO->findChansonsLikees($emailUtilisateur);
        $estLikee = false;
        foreach ($chansonsLikees as $chanson) {
            if ($chanson->getIdChanson() == $idChanson) {
                $estLikee = true;
                break;
            }
        }

        // Bascule le like
        $chansonDAO->toggleLike($emailUtilisateur, $idChanson);

        // Renvoie le nouvel état du like
        header('Content-Type: application/json');
        echo json_encode(['liked' => !$estLikee]);
        exit;
    }

    /**
     * @brief Incrémente le compteur d'écoutes d'une chanson.
     * 
     * Cette méthode AJAX incrémente le nombre d'écoutes d'une chanson
     * lorsqu'un utilisateur connecté l'écoute.
     * Vérifie le token CSRF pour la sécurité.
     * 
     * @return void Retourne une réponse JSON et termine le script.
     */
    public function incrementEcoute()
    {
        // Vérification de l'authentification
        $emailUtilisateur = $_SESSION['user_email'] ?? null;
        if (!$emailUtilisateur) {
            http_response_code(401);
            echo json_encode(['error' => 'Utilisateur non connecté']);
            exit;
        }

        // Vérification du token CSRF
        $csrfToken = $_POST['csrfToken'] ?? null;
        $sessionToken = $_SESSION['csrf_token'] ?? null;

        if (!$csrfToken || !$sessionToken || $csrfToken !== $sessionToken) {
            http_response_code(403);
            echo json_encode(['error' => 'Token CSRF invalide']);
            exit;
        }

        $idChanson = $_POST['idChanson'] ?? null;
        if (!$idChanson) {
            http_response_code(400);
            echo json_encode(['error' => 'ID de chanson manquant']);
            exit;
        }

        $chansonDAO = new ChansonDAO($this->getPdo());
        $nouveau = $chansonDAO->incrementNbEcoute((int)$idChanson);

        if ($nouveau === null) {
            http_response_code(500);
            echo json_encode(['error' => 'Impossible d\'incrémenter']);
            exit;
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'nbEcoute' => $nouveau]);
        exit;
    }
}
