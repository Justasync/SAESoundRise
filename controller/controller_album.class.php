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
        // Vérifier si l'utilisateur est un artiste connecté
        if (!isset($_SESSION['user_logged_in']) || !isset($_SESSION['user_role']) || ($_SESSION['user_role'] != RoleEnum::Artiste)) {
            header('Location: /?controller=home&method=afficher');
            exit();
        }

        $idAlbum = $_GET['idAlbum'] ?? null;
        $albumExistant = null;
        $managerAlbum = new AlbumDAO($this->getPdo());

        if ($idAlbum) {
            $albumExistant = $managerAlbum->find((int)$idAlbum);
            // Vérifier que l'album appartient bien à l'artiste connecté
            if (!$albumExistant || $albumExistant->getArtisteAlbum() !== $_SESSION['user_pseudo']) {
                header('Location: /?controller=home&method=afficher');
                exit();
            }
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
            'albums_artiste' => $albumsArtiste,
            'album_existant' => $albumExistant
        ]);
    }


/*
    public function ajouterAlbum()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_role']) || $_SESSION['user_role'] != RoleEnum::Artiste) {
            header('Location: /?controller=home&method=afficher');
            return;
        }

        $managerChanson = new ChansonDAO($this->getPdo());
        $managerGenre = new GenreDAO($this->getPdo());
        $managerAlbum = new AlbumDAO($this->getPdo());

        $idAlbumExistant = $_POST['id_album_existant'] ?? null;

        if ($idAlbumExistant) {
            // Ajout de chansons à un album existant
            $albumCree = $managerAlbum->find((int)$idAlbumExistant);
            if (!$albumCree || $albumCree->getArtisteAlbum() !== $_SESSION['user_pseudo']) {
                // Gérer l'erreur : l'album n'existe pas ou n'appartient pas à l'utilisateur
                header('Location: /?controller=home&method=afficher');
                return;
            }
            $idAlbum = $albumCree->getIdAlbum();
        } else {
            // Création d'un nouvel album
            $album = new Album();
            $album->setTitreAlbum($_POST['titre_album'] ?? '');
            $album->setDateSortieAlbum($_POST['date_sortie'] ?? '');
            $album->setArtisteAlbum($_SESSION['user_pseudo']);

            // Gérer la pochette de l'album
            if (isset($_FILES['pochette_album']) && $_FILES['pochette_album']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'albums' . DIRECTORY_SEPARATOR;
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $nomFichier = basename($_FILES['pochette_album']['name']);
                $cheminPochette = $uploadDir . uniqid() . '-' . $nomFichier;
                if (move_uploaded_file($_FILES['pochette_album']['tmp_name'], $cheminPochette)) {
                    $album->seturlPochetteAlbum($cheminPochette);
                }
            } else {
                // Fournir une URL de pochette par défaut si aucune n'est téléchargée
                $album->seturlPochetteAlbum('assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'albums' . DIRECTORY_SEPARATOR . 'default.png');
            }
            $idAlbum = $managerAlbum->create($album);
            $albumCree = $managerAlbum->find($idAlbum);
        }

        // Gérer les chansons
        if (isset($_POST['tracks']) && isset($_FILES['tracks'])) {
            $uploadDirMusique = 'assets' . DIRECTORY_SEPARATOR . 'audio' . DIRECTORY_SEPARATOR;
            // Inclure getID3 pour analyser les fichiers audio
            require_once 'vendor/james-heinrich/getid3/getid3/getid3.php';
            $getID3 = new getID3;

            if (!is_dir($uploadDirMusique)) {
                mkdir($uploadDirMusique, 0777, true);
            }

            foreach ($_POST['tracks'] as $index => $chansonData) {
                // Vérifier si le fichier correspondant a été uploadé
                if (!isset($_FILES['tracks']['tmp_name'][$index]['file']) || $_FILES['tracks']['error'][$index]['file'] !== UPLOAD_ERR_OK) {
                    continue; // Passer à la chanson suivante si le fichier est manquant ou a une erreur
                }

                $nomFichierOriginal = basename($_FILES['tracks']['name'][$index]['file']);
                $cheminCible = $uploadDirMusique . uniqid() . '-' . $nomFichierOriginal;

                if (!move_uploaded_file($_FILES['tracks']['tmp_name'][$index]['file'], $cheminCible)) {
                    continue; // Erreur lors du déplacement du fichier
                }

                // Analyser le fichier pour obtenir les métadonnées, y compris la durée
                $infoFichier = $getID3->analyze($cheminCible);
                $duree = (int)($infoFichier['playtime_seconds'] ?? 0);

                $chanson = new Chanson();
                $chanson->setTitreChanson($chansonData['title']);
                $chanson->setDureeChanson($duree);
                $chanson->setDateTeleversementChanson(new DateTime());
                $chanson->setAlbumChanson($albumCree);
                $chanson->setEmailPublicateur($_SESSION['user_email']);
                $chanson->setUrlAudioChanson($cheminCible);

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

        if ($idAlbumExistant) {
            // Rediriger vers la page de détails de l'album mis à jour
            header('Location: /?controller=album&method=afficherDetails&idAlbum=' . $idAlbumExistant . '&success=1');
        } else {
            // Rediriger vers le tableau de bord après la création d'un nouvel album
            header('Location: /?controller=home&method=afficher&success=1');
        }
    }
*/
public function ajouterAlbum()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_role']) || $_SESSION['user_role'] != RoleEnum::Artiste) {
            echo json_encode(['success' => false, 'message' => 'Accès non autorisé.']);
            return;
        }

        $errors = [];
        $uploadedFiles = [];
        $albumCree = null;

        $managerChanson = new ChansonDAO($this->getPdo());
        $managerGenre = new GenreDAO($this->getPdo());
        $managerAlbum = new AlbumDAO($this->getPdo());

        $idAlbumExistant = $_POST['id_album_existant'] ?? null;

        if ($idAlbumExistant && $idAlbumExistant !== '') {
            $albumCree = $managerAlbum->find((int)$idAlbumExistant);
            if (!$albumCree || $albumCree->getArtisteAlbum() !== $_SESSION['user_pseudo']) {
                echo json_encode(['success' => false, 'message' => 'L\'album spécifié est invalide ou ne vous appartient pas.']);
                return;
            }
        } else {
            $titreAlbum = $_POST['titre_album'] ?? '';
            if (empty($titreAlbum)) {
                $errors[] = 'Le titre de l\'album est requis.';
            }

            $album = new Album();
            $album->setTitreAlbum($titreAlbum);
            $album->setDateSortieAlbum($_POST['date_sortie'] ?? '');
            $album->setArtisteAlbum($_SESSION['user_pseudo']);

            if (isset($_FILES['pochette_album']) && $_FILES['pochette_album']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'albums' . DIRECTORY_SEPARATOR;
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $nomFichier = basename($_FILES['pochette_album']['name']);
                $safeFileName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $nomFichier);
                $cheminPochette = $uploadDir . uniqid() . '-' . $safeFileName;
                if (move_uploaded_file($_FILES['pochette_album']['tmp_name'], $cheminPochette)) {
                    $album->seturlPochetteAlbum($cheminPochette);
                } else {
                    $errors[] = 'Erreur lors du téléversement de la pochette.';
                }
            } else {
                $album->seturlPochetteAlbum('assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'albums' . DIRECTORY_SEPARATOR . 'default.png');
            }

            if (empty($errors)) {
                $idAlbum = $managerAlbum->create($album);
                $albumCree = $managerAlbum->find($idAlbum);
            }
        }

        if (empty($errors) && $albumCree && isset($_POST['tracks']) && isset($_FILES['tracks'])) {
            $uploadDirMusique = 'assets' . DIRECTORY_SEPARATOR . 'audio' . DIRECTORY_SEPARATOR;
            $maxFileSize = 50 * 1024 * 1024; // 50MB
            $allowedTypes = ['audio/mpeg', 'audio/mp3'];

            require_once 'vendor/james-heinrich/getid3/getid3/getid3.php';
            $getID3 = new getID3;

            if (!is_dir($uploadDirMusique)) {
                mkdir($uploadDirMusique, 0777, true);
            }

            foreach ($_POST['tracks'] as $index => $chansonData) {
                $file = $_FILES['tracks'];
                $fileName = $file['name'][$index]['file'];

                if ($file['error'][$index]['file'] !== UPLOAD_ERR_OK) {
                    $errors[] = $fileName . ': Erreur de téléversement (code ' . $file['error'][$index]['file'] . ').';
                    continue;
                }

                if ($file['size'][$index]['file'] > $maxFileSize) {
                    $errors[] = $fileName . ': Le fichier dépasse la limite de 50MB.';
                    continue;
                }

                $fileType = $file['type'][$index]['file'];
                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                if (!in_array($fileType, $allowedTypes) && $fileExtension !== 'mp3') {
                    $errors[] = $fileName . ': Type de fichier invalide. Seuls les MP3 sont autorisés.';
                    continue;
                }

                $sanitizedFileName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $fileName);
                $sanitizedFileName = uniqid() . '-' . $sanitizedFileName;
                $cheminCible = $uploadDirMusique . $sanitizedFileName;

                if (move_uploaded_file($file['tmp_name'][$index]['file'], $cheminCible)) {
                    $uploadedFiles[] = $sanitizedFileName;
                } else {
                    $errors[] = $fileName . ': Échec du déplacement du fichier téléversé.';
                    continue;
                }

                $infoFichier = $getID3->analyze($cheminCible);
                $duree = (int)($infoFichier['playtime_seconds'] ?? 0);

                $chanson = new Chanson();
                $chanson->setTitreChanson($chansonData['title']);
                $chanson->setDureeChanson($duree);
                $chanson->setDateTeleversementChanson(new \DateTime());
                $chanson->setAlbumChanson($albumCree);
                $chanson->setEmailPublicateur($_SESSION['user_email']);
                $chanson->setUrlAudioChanson($cheminCible);

                $nomGenre = $chansonData['genre'] ?? null;
                if ($nomGenre) {
                    $genre = $managerGenre->findOrCreateByName($nomGenre);
                    $chanson->setGenreChanson($genre);
                }

                if (!$managerChanson->create($chanson)) {
                    $errors[] = 'Erreur lors de la sauvegarde de la chanson ' . $chansonData['title'];
                }
            }
        }

        if (empty($errors) && count($uploadedFiles) > 0) {
            $message = count($uploadedFiles) . ' chanson(s) ajoutée(s) avec succès à l\'album "' . $albumCree->getTitreAlbum() . '".';
            echo json_encode([
                'success' => true,
                'message' => $message,
                'albumId' => $albumCree->getIdAlbum()
            ]);
        } else {
            $errorMessage = 'L\'opération a échoué.';
            if (!empty($errors)) {
                $errorMessage .= ' Erreurs: ' . implode(' ', $errors);
            }
            echo json_encode([
                'success' => false,
                'message' => $errorMessage,
                'errors' => $errors
            ]);
        }
    }


    public function afficherDetails()
    {
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['user_logged_in'])) {
            header('Location: /?controller=home&method=afficher');
            exit();
        }

        $idAlbum = $_GET['idAlbum'] ?? null;
        if (!$idAlbum) {
            // Gérer l'erreur, par exemple rediriger
            header('Location: /?controller=home&method=afficher');
            exit();
        }

        $albumDAO = new AlbumDAO($this->getPDO());
        $album = $albumDAO->find((int)$idAlbum);

        $chansonDAO = new ChansonDAO($this->getPDO());
        $chansons = $chansonDAO->rechercherParAlbum((int)$idAlbum);

        // Déterminer le rôle de l'utilisateur à partir de la session
        $userRole = $_SESSION['user_role'] ?? null;
        $template = '';

        // Choisir le template en fonction du rôle
        // 2 pour artiste, 1 (ou autre) pour auditeur
        if ($userRole === RoleEnum::Artiste && $album->getArtisteAlbum() === $_SESSION['user_pseudo']) {
            // Si l'utilisateur est l'artiste propriétaire de l'album, il voit la page d'édition.
            $template = 'album_details_artiste.html.twig';
        } else {
            // Sinon (auditeur, ou artiste regardant l'album d'un autre), il voit la page de lecture.
            $template = 'album_details_auditeur.html.twig';
        }

        if (empty($template)) {
            // Sécurité : si aucun template n'est défini, rediriger
            header('Location: /?controller=home&method=afficher');
            exit();
        }

        $template = $this->getTwig()->load($template);
        echo $template->render([
            'album' => $album,
            'chansons' => $chansons,
            'session' => $_SESSION,
        ]);
    }

    public function modifierChanson()
    {
        // Sécurité : vérifier la méthode, la session et le rôle
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_logged_in']) || $_SESSION['user_role'] != RoleEnum::Artiste) {
            header('Location: /?controller=home&method=afficher');
            exit();
        }

        $idChanson = $_GET['idChanson'] ?? null;
        $idAlbum = $_POST['id_album'] ?? null; // Assurez-vous que ce champ est dans le formulaire de la modale

        if (!$idChanson || !$idAlbum) {
            // Rediriger si les IDs sont manquants
            header('Location: /?controller=home&method=afficher&error=1');
            exit();
        }

        $chansonDAO = new ChansonDAO($this->getPDO());
        $chanson = $chansonDAO->findId((int)$idChanson);

        // Vérifier que la chanson existe et appartient bien à un album de l'artiste
        if (!$chanson || $chanson->getAlbumChanson()->getArtisteAlbum() !== $_SESSION['user_pseudo']) {
            header('Location: /?controller=home&method=afficher&error=unauthorized');
            exit();
        }

        // Mettre à jour les informations
        $chanson->setTitreChanson($_POST['titre_chanson']);

        $genreDAO = new GenreDAO($this->getPDO());
        $nomGenre = $_POST['genre_chanson'] ?? '';
        $genre = $genreDAO->findOrCreateByName($nomGenre); // Méthode à créer dans GenreDAO
        $chanson->setGenreChanson($genre);

        $chansonDAO->update($chanson); // Méthode à créer dans ChansonDAO

        // Rediriger vers la page de l'album avec un message de succès
        header('Location: /?controller=album&method=afficherDetails&idAlbum=' . $idAlbum . '&success_update=1');
        exit();
    }


}
