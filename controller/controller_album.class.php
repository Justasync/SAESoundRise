<?php

class ControllerAlbum extends Controller
{
    public function __construct(\Twig\Environment $twig, \Twig\Loader\FilesystemLoader $loader)
    {
        parent::__construct($loader, $twig);
    }

    public function afficher()
    {
        $idAlbum = isset($_GET['idAlbum']) ? $_GET['idAlbum'] : null;

        //Récupération de la catégorie
        $managerAlbum = new AlbumDao($this->getPdo());
        $album = $managerAlbum->find($idAlbum);

        $template = $this->getTwig()->load('test.html.twig');
        echo $template->render(array(
            'page' => [
                'title' => "Album",
                'name' => "album",
                'description' => "Album dans Paaxio"
            ],
            'testing' => $album,
        ));
    }

    public function lister()
    {
        //recupération des catégories
        $managerAlbum = new AlbumDao($this->getPdo());
        $albums = $managerAlbum->findAll();

        //Choix du template
        $template = $this->getTwig()->load('test.html.twig');

        //Affichage de la page
        echo $template->render(array(
            'page' => [
                'title' => "Albums",
                'name' => "albums",
                'description' => "Albums dans Paaxio"
            ],
            'testing' => $albums,
        ));
    }

    public function listerTableau()
    {
        $managerAlbum = new AlbumDao($this->getPdo());
        $albums = $managerAlbum->findAll();

        //Génération de la vue
        $template = $this->getTwig()->load('test.html.twig');
        echo $template->render(array(
            'page' => [
                'title' => "Albums tableau",
                'name' => "albumt",
                'description' => "Albums tableau dans Paaxio"
            ],
            'testing' => $albums,
        ));
    }

    public function ajouter()
    {
        $error = isset($_GET['error']);

        //Génération de la vue
        $template = $this->getTwig()->load('album_form.html.twig');
        echo $template->render(array(
            'page' => [
                'title' => "Ajouter un album",
                'name' => "album_add",
                'description' => "Ajouter un nouvel album dans Paaxio"
            ],
            'error' => $error
        ));
    }

    public function ajouterAlbum()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $titre = $_POST['titreAlbum'] ?? null;
            $dateSortie = $_POST['dateSortieAlbum'] ?? null;
<<<<<<< HEAD
            $urlPochetteAlbum = $_POST['urlPochetteAlbum'] ?? null;
            $artisteAlbum = $_SESSION['user_email'] ?? null;
=======
            $pochetteFile = $_FILES['urlPochetteAlbum'] ?? null;
            $urlPochetteAlbum = null;
>>>>>>> 7be73c3 (Sauvegarde de mes changements en cours avant la fusion de main)

            // --- Gestion de l'upload de fichier ---
            if (isset($pochetteFile) && $pochetteFile['error'] === UPLOAD_ERR_OK) {
                // Utiliser le chemin de destination demandé
                $uploadDir = 'assets/images/albums/';
                if (!is_dir($uploadDir)) {
                    // Crée le dossier s'il n'existe pas
                    mkdir($uploadDir, 0777, true);
                }

                $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
                $fileType = mime_content_type($pochetteFile['tmp_name']);

                if (in_array($fileType, $allowedTypes)) {
                    // Générer un nom de fichier unique pour éviter les conflits
                    $extension = pathinfo($pochetteFile['name'], PATHINFO_EXTENSION);
                    $newFilename = uniqid('cover_', true) . '.' . $extension;
                    $uploadFile = $uploadDir . $newFilename;

                    if (move_uploaded_file($pochetteFile['tmp_name'], $uploadFile)) {
                        // Le fichier a été uploadé avec succès, on stocke le chemin relatif
                        $urlPochetteAlbum = '/' . $uploadFile;
                    }
                }
            }
            // --- Fin de la gestion de l'upload ---

            // On vérifie que toutes les données nécessaires sont présentes
            if (!empty($titre) && !empty($dateSortie) && !empty($urlPochetteAlbum)) {
<<<<<<< HEAD
                $album = new Album(null, $titre, $dateSortie, trim($urlPochetteAlbum), trim($artisteAlbum));
=======
                $album = new Album(null, $titre, $dateSortie, $urlPochetteAlbum);
>>>>>>> 7be73c3 (Sauvegarde de mes changements en cours avant la fusion de main)

                $managerAlbum = new AlbumDao($this->getPdo());
                $success = $managerAlbum->create($album);

                if ($success) {
                    header('Location: index.php?controller=album&method=lister'); // Redirection vers la liste des albums
                    exit;
                }
            }
        }
        // Gérer l'échec ou l'accès direct ici, peut-être rediriger vers le formulaire avec un message d'erreur.
        header('Location: index.php?controller=album&method=ajouter&error=1');
        exit;
    }

    public function modifierAlbum()
    {
        // Logique pour la modification
    }

    public function supprimerAlbum()
    {
        // Logique pour la suppression
    }
}