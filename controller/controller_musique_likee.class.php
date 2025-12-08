<?php

class ControllerMusiqueLikee extends Controller
{
    public function __construct(\Twig\Environment $twig, \Twig\Loader\FilesystemLoader $loader)
    {
        parent::__construct($loader, $twig);
    }

    public function afficher()
    {
        // Vérifie la connexion
        $emailUtilisateur = $_SESSION['user_email'] ?? null;

        if (!$emailUtilisateur) {
            header("Location: /?controller=auth&method=login");
            exit;
        }

        // DAO → Récupération des chansons likées de l’utilisateur
        $managerLike = new ChansonLikeeDAO($this->getPdo());
        $chansonsLikees = $managerLike->findChansonsLikees($emailUtilisateur);

        $albumVirtuel = (object) [
            "getTitreAlbum" => function() { return "Chansons Likées"; },
            "getUrlImageAlbum" => function() { return "public/assets/like_default.png"; },
            "getArtisteAlbum" => function() { return "Moi"; },
            "getDateSortieAlbum" => function() { return null; },
        ];

        // Chargement du template
        $template = $this->getTwig()->load('chanson_album.html.twig');

        echo $template->render([
            'page' => [
                'title' => "Chansons likées",
                'name'  => "musique_likee",
                'description' => "Chansons likées par l'utilisateur"
            ],
            'album' => $albumVirtuel,
            'chansons' => $chansonsLikees
        ]);
    }

    public function toggleLike()
    {
        // Vérifie la connexion
        $emailUtilisateur = $_SESSION['user_email'] ?? null;
        if (!$emailUtilisateur) {
            http_response_code(401);
            echo json_encode(['error' => 'Utilisateur non connecté']);
            exit;
        }

        // Récupère l'ID de la chanson depuis POST
        $idChanson = $_POST['idChanson'] ?? null;
        if (!$idChanson) {
            http_response_code(400);
            echo json_encode(['error' => 'ID de chanson manquant']);
            exit;
        }

        $likeDAO = new ChansonLikeeDAO($this->getPdo());

        // Vérifie si la chanson est déjà likée
        $chansonsLikees = $likeDAO->findChansonsLikees($emailUtilisateur);
        $estLikee = false;
        foreach ($chansonsLikees as $chanson) {
            if ($chanson->getIdChanson() == $idChanson) {
                $estLikee = true;
                break;
            }
        }

        if ($estLikee) {
            // Supprime le like
            $sql = "DELETE FROM likechanson WHERE emailUtilisateur = :email AND idChanson = :id";
            $stmt = $this->getPdo()->prepare($sql);
            $stmt->execute([':email' => $emailUtilisateur, ':id' => $idChanson]);
            $liked = false;
        } else {
            // Ajoute le like
            $chansonLike = new ChansonLikee();
            $chansonLike->setIdChanson($idChanson);
            $chansonLike->setEmailUtilisateur($emailUtilisateur);
            $chansonLike->setDateLike(new DateTime());
            $likeDAO->create($chansonLike);
            $liked = true;
        }

        // Renvoie le résultat en JSON
        header('Content-Type: application/json');
        echo json_encode(['liked' => $liked]);
        exit;
    }
}
