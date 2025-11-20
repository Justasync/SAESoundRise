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

    public function afficherFormulaireAjout()
    {
        // Vérifier si l'utilisateur est un artiste
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 2) { // Supposons que le rôle artiste a l'ID 2
            header('Location: index.php?controller=home&method=afficher');
            exit();
        }

        // Récupérer les albums de l'artiste
        $managerAlbum = new AlbumDAO($this->getPdo());
        $albumsArtiste = $managerAlbum->findByArtiste($_SESSION['user_email']);

        $template = $this->getTwig()->load('album_ajout.html.twig');
        echo $template->render([
            'page' => [
                'title' => "Ajouter un Album/Single",
                'name' => "album_ajout",
                'description' => "Téléversez vos chansons pour créer un album ou un single."
            ],
            'session' => $_SESSION,
            'albums_artiste' => $albumsArtiste
        ]);
    }

    public function ajouterChansons()
    {
        // Vérifier si l'utilisateur est un artiste
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 2) {
            echo "Accès non autorisé.";
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['chansons'])) {
            header('Location: index.php?controller=album&method=afficherFormulaireAjout');
            return;
        }

        $fichiersChansons = $_FILES['chansons'];
        $chansonsValides = [];
        $uploadDir = 'uploads/musique/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Inclure getID3
        require_once 'vendor/james-heinrich/getid3/getid3/getid3.php';
        $getID3 = new getID3;

        foreach ($fichiersChansons['tmp_name'] as $index => $tmpName) {
            if ($fichiersChansons['error'][$index] === UPLOAD_ERR_OK) {
                $nomFichier = basename($fichiersChansons['name'][$index]);
                $cheminCible = $uploadDir . uniqid() . '-' . $nomFichier;

                if (move_uploaded_file($tmpName, $cheminCible)) {
                    $infoFichier = $getID3->analyze($cheminCible);
                    getid3_lib::CopyTagsToComments($infoFichier);

                    $commentaires = $infoFichier['comments_html'] ?? [];
                    $titre = $commentaires['title'][0] ?? pathinfo($nomFichier, PATHINFO_FILENAME);
                    $genre = $commentaires['genre'][0] ?? '';
                    $duree = (int)($infoFichier['playtime_seconds'] ?? 0);

                    $chansonsValides[] = [
                        'chemin' => $cheminCible,
                        'titre' => $titre,
                        'genre' => $genre,
                        'duree' => $duree,
                        // On garde les infos complètes au cas où
                        'info' => $infoFichier 
                    ];
                }
            }
        }

        $managerAlbum = new AlbumDAO($this->getPdo());
        $managerChanson = new ChansonDAO($this->getPdo());
        $managerGenre = new GenreDAO($this->getPdo());

        if (count($chansonsValides) > 0) {
            $defaultAlbumTitle = '';
            if (count($chansonsValides) === 1) {
                $defaultAlbumTitle = $chansonsValides[0]['titre'];
            }
            $template = $this->getTwig()->load('album_ajout.html.twig');
            echo $template->render([
                'page' => [
                    'title' => "Créer un nouvel album",
                    'name' => "album_ajout",
                    'description' => "Veuillez fournir les détails de l'album/single."
                ],
                'session' => $_SESSION,
                'chansons_televersees' => $chansonsValides,
                'show_album_modal' => true,
                'default_album_title' => $defaultAlbumTitle
            ]);
        } else {
            echo "Aucune chanson valide n'a été téléversée.";
        }
    }

    public function ajouterAlbum()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_role']) || $_SESSION['user_role'] != 2) {
            header('Location: index.php?controller=home&method=afficher');
            return;
        }

        $managerAlbum = new AlbumDAO($this->getPdo());
        $managerChanson = new ChansonDAO($this->getPdo());
        $managerGenre = new GenreDAO($this->getPdo());

        // Gérer l'album
        $album = new Album();
        $album->setTitreAlbum($_POST['titre_album'] ?? '');
        $album->setDateSortieAlbum($_POST['date_sortie'] ?? '');
        $album->setArtisteAlbum($_SESSION['user_email']);

        // Gérer la pochette de l'album
        if (isset($_FILES['pochette_album']) && $_FILES['pochette_album']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'assets/images/albums/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $nomFichier = basename($_FILES['pochette_album']['name']);
            $cheminPochette = $uploadDir . uniqid() . '-' . $nomFichier;
            if (move_uploaded_file($_FILES['pochette_album']['tmp_name'], $cheminPochette)) {
                $album->seturlPochetteAlbum($cheminPochette);
            }
        }

        $idAlbum = $managerAlbum->create($album);
        $albumCree = $managerAlbum->find($idAlbum);

        // Gérer les chansons
        if (isset($_POST['chansons']) && isset($_FILES['chansons_files'])) {
            $uploadDirMusique = 'assets/audio/';
            if (!is_dir($uploadDirMusique)) {
                mkdir($uploadDirMusique, 0777, true);
            }

            foreach ($_POST['chansons'] as $index => $chansonData) {
                // Vérifier si le fichier correspondant a été uploadé
                if (!isset($_FILES['chansons_files']['tmp_name'][$index]) || $_FILES['chansons_files']['error'][$index] !== UPLOAD_ERR_OK) {
                    continue; // Passer à la chanson suivante si le fichier est manquant ou a une erreur
                }

                $nomFichierOriginal = basename($_FILES['chansons_files']['name'][$index]);
                $cheminCible = $uploadDirMusique . uniqid() . '-' . $nomFichierOriginal;

                if (!move_uploaded_file($_FILES['chansons_files']['tmp_name'][$index], $cheminCible)) {
                    continue; // Erreur lors du déplacement du fichier
                }

                $chanson = new Chanson();
                $chanson->setTitreChanson($chansonData['titre']);
                $chanson->setDureeChanson((int)$chansonData['duree']);
                $chanson->setDateTeleversementChanson(new DateTime());
                $chanson->setAlbumChanson($albumCree);
                $chanson->setEmailPublicateur($_SESSION['user_email']);
                $chanson->setUrlAudioChanson($cheminCible);
                $chanson->setEstPublieeChanson(true);

                $nomGenre = $chansonData['genre'] ?? null;
                if ($nomGenre) {
                    $genreExistant = $managerGenre->findByName($nomGenre);
                    if ($genreExistant) {
                        $chanson->setGenreChanson($genreExistant);
                    } else {
                        $idNouveauGenre = $managerGenre->create($nomGenre);
                        $chanson->setGenreChanson($managerGenre->find($idNouveauGenre));
                    }
                }

                $managerChanson->create($chanson);
            }
        }

        // Rediriger avec un message de succès
        header('Location: index.php?controller=album&method=afficherFormulaireAjout&success=1');
    }
}
