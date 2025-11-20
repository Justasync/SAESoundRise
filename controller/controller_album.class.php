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

                    $chansonsValides[] = [
                        'chemin' => $cheminCible,
                        'info' => $infoFichier
                    ];
                }
            }
        }

        $managerAlbum = new AlbumDAO($this->getPdo());
        $managerChanson = new ChansonDAO($this->getPdo());
        $managerGenre = new GenreDAO($this->getPdo());

        if (count($chansonsValides) === 1) {
            // Création d'un single
            $infoChanson = $chansonsValides[0]['info'];
            $commentaires = $infoChanson['comments_html'] ?? [];

            $album = new Album();
            $album->setTitreAlbum($commentaires['title'][0] ?? 'Single');
            $album->setDateSortieAlbum(date('Y-m-d'));
            $album->setArtisteAlbum($_SESSION['user_email']); // Ou un autre champ de l'utilisateur

            // Gérer la pochette si elle existe dans les métadonnées
            if (!empty($infoChanson['comments']['picture'][0])) {
                $pochetteData = $infoChanson['comments']['picture'][0]['data'];
                $pochetteMime = $infoChanson['comments']['picture'][0]['image_mime'];
                $pochetteExt = str_replace('image/', '', $pochetteMime);
                $pochetteNom = 'uploads/pochettes/' . uniqid() . '.' . $pochetteExt;
                if (!is_dir('uploads/pochettes/')) {
                    mkdir('uploads/pochettes/', 0777, true);
                }
                file_put_contents($pochetteNom, $pochetteData);
                $album->seturlPochetteAlbum($pochetteNom);
            }

            $idAlbum = $managerAlbum->create($album);

            $chanson = new Chanson();
            $chanson->setTitreChanson($commentaires['title'][0] ?? 'Titre inconnu');
            $chanson->setDureeChanson((int)($infoChanson['playtime_seconds'] ?? 0));
            $chanson->setDateTeleversementChanson(new DateTime());
            $chanson->setAlbumChanson($managerAlbum->find($idAlbum));
            $chanson->setEmailPublicateur($_SESSION['user_email']);
            $chanson->setUrlAudioChanson($chansonsValides[0]['chemin']);

            $nomGenre = $commentaires['genre'][0] ?? null;
            if ($nomGenre) {
                $genreExistant = $managerGenre->findByName($nomGenre);
                if ($genreExistant) {
                    $chanson->setGenreChanson($genreExistant);
                } else {
                    // Le genre n'existe pas, on le crée
                    $idNouveauGenre = $managerGenre->create($nomGenre);
                    $chanson->setGenreChanson($managerGenre->find($idNouveauGenre));
                }
            }
            $chanson->setEstPublieeChanson(true);

            $managerChanson->create($chanson);

            header('Location: index.php?controller=album&method=afficher&idAlbum=' . $idAlbum);
        } elseif (count($chansonsValides) > 1) {
            // Proposer de créer un album
            $template = $this->getTwig()->load('album_ajout.html.twig');
            echo $template->render([
                'page' => [
                    'title' => "Créer un nouvel album",
                    'name' => "album_ajout",
                    'description' => "Plusieurs chansons ont été téléversées. Veuillez fournir les détails de l'album."
                ],
                'session' => $_SESSION,
                'chansons_televersees' => $chansonsValides,
                'show_album_modal' => true
            ]);
        } else {
            echo "Aucune chanson valide n'a été téléversée.";
        }
    }

    public function creerAlbum()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_role']) || $_SESSION['user_role'] != 2) {
            header('Location: index.php?controller=home&method=afficher');
            return;
        }

        $managerAlbum = new AlbumDAO($this->getPdo());
        $managerChanson = new ChansonDAO($this->getPdo());
        $managerGenre = new GenreDAO($this->getPdo());

        // Créer l'album
        $album = new Album();
        $album->setTitreAlbum($_POST['titre_album']);
        $album->setDateSortieAlbum($_POST['date_sortie']);
        $album->setArtisteAlbum($_SESSION['user_email']);

        // Gérer la pochette
        if (isset($_FILES['pochette_album']) && $_FILES['pochette_album']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/pochettes/';
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

        // Créer les chansons et les lier à l'album
        if (isset($_POST['chansons'])) {
            foreach ($_POST['chansons'] as $index => $chansonData) {
                $chanson = new Chanson();
                $chanson->setTitreChanson($chansonData['titre']);
                $chanson->setDureeChanson((int)$chansonData['duree']);
                $chanson->setDateTeleversementChanson(new DateTime());
                $chanson->setAlbumChanson($albumCree);
                $chanson->setEmailPublicateur($_SESSION['user_email']);
                $chanson->setUrlAudioChanson($chansonData['chemin']);
                $chanson->setEstPublieeChanson(true);

                // Associer le genre
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

        header('Location: index.php?controller=album&method=afficher&idAlbum=' . $idAlbum);
    }
}
