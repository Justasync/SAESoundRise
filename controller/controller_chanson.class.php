<?php

class ControllerChanson extends Controller 
{
    public function __construct(\Twig\Environment $twig, \Twig\Loader\FilesystemLoader $loader)
    {
        parent::__construct($loader, $twig);
    }

    public function afficher()
    {
        $idChanson = isset($_GET['idChanson']) ? $_GET['idChanson'] : null;

        //Récupération de la catégorie
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

    public function rechercherParTitre()
    {
        $titreChanson = isset($_GET['titreChanson']) ? $_GET['titreChanson'] : null;

        //Récupération de la catégorie
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

    public function rechercherParAlbum()
    {
        $idAlbum = isset($_GET['idAlbum']) ? $_GET['idAlbum'] : null;

        //Récupération de la catégorie
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
    
    public function lister()
    {
        //recupération des catégories
        $managerChanson = new ChansonDao($this->getPdo());
        $chansons = $managerChanson->findAll();

        //Choix du template
        $template = $this->getTwig()->load('test.html.twig');

        //Affichage de la page
        echo $template->render(array(
            'page' => [
                'title' => "Chansons",
                'name' => "chansons",
                'description' => "Chansons dans Paaxio"
            ],
            'testing' => $chansons,
        ));
    }

    public function listerTableau()
    {
        $managerChanson = new ChansonDao($this->getPdo());
        $chansons = $managerChanson->findAll();

        //Génération de la vue
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

    public function ajouter()
    {
        $error = $_GET['error'] ?? null;
        $success = $_GET['success'] ?? null;
        $albums = [];
        $genres = [];

        // Récupérer l'email de l'artiste connecté
        $artisteEmail = $_SESSION['user_email'] ?? null;

        if ($artisteEmail) {
            // Récupérer la liste des albums de l'artiste
            $managerAlbum = new AlbumDao($this->getPdo());
            $albums = $managerAlbum->findByArtiste($artisteEmail);
        }

        // Récupérer tous les genres
        $managerGenre = new GenreDao($this->getPdo());
        $genres = $managerGenre->findAll();

        //Génération de la vue
        $template = $this->getTwig()->load('chanson_form.html.twig');
        echo $template->render(array(
            'page' => [
                'title' => "Ajouter une chanson",
                'name' => "chanson_gestion",
                'description' => "Ajouter une nouvelle chanson dans Paaxio"
            ],
            'error' => $error,
            'success' => $success,
            'albums' => $albums,
            'genres' => $genres
        ));
    }

    public function ajouterChanson()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $titre = $_POST['titreChanson'] ?? null;
            $albumId = $_POST['albumChanson'] ?? null;
            $genreId = $_POST['genreChanson'] ?? null;
            $duree = isset($_POST['dureeChanson']) ? (int)$_POST['dureeChanson'] : null;
            $audioFile = $_FILES['urlAudioChanson'] ?? null;
            $urlAudio = null;

            // --- Gestion de l'upload de fichier audio ---
            if (isset($audioFile) && $audioFile['error'] === UPLOAD_ERR_OK) {
                $uploadDir = 'assets/audio/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $extension = pathinfo($audioFile['name'], PATHINFO_EXTENSION);
                $newFilename = uniqid('audio_', true) . '.' . $extension;
                $uploadFile = $uploadDir . $newFilename;

                if (move_uploaded_file($audioFile['tmp_name'], $uploadFile)) {
                    $urlAudio = '/' . $uploadFile;
                }
            }
            // --- Fin de la gestion de l'upload ---
            
            if (!empty($titre) && !empty($albumId) && !empty($genreId) && !empty($duree) && $duree > 0 && !empty($urlAudio)) {
                $chanson = new Chanson();
                $chanson->setTitreChanson($titre);
                $chanson->setDureeChanson($duree);
                $chanson->setDateTeleversementChanson(new DateTime());
                $chanson->setEmailPublicateur($_SESSION['user_email']);
                $chanson->setUrlAudioChanson($urlAudio);
                $chanson->setAlbumChanson((new AlbumDAO($this->getPdo()))->find($albumId));
                $chanson->setGenreChanson((new GenreDAO($this->getPdo()))->find($genreId));

                $managerChanson = new ChansonDao($this->getPdo());
                if ($managerChanson->create($chanson)) {
                    header('Location: index.php?controller=chanson&method=ajouter&success=song_added');
                    exit;
                }
            }

            // Gestion des erreurs plus fine
            $errorType = 'invalid_form'; // Erreur par défaut
            if (isset($audioFile['error'])) {
                switch ($audioFile['error']) {
                    case UPLOAD_ERR_INI_SIZE:
                    case UPLOAD_ERR_FORM_SIZE:
                        $errorType = 'file_too_large';
                        break;
                    case UPLOAD_ERR_NO_FILE:
                        $errorType = 'no_file';
                        break;
                }
            }
            header('Location: index.php?controller=chanson&method=ajouter&error=' . $errorType);
            exit;
        }
    }
}