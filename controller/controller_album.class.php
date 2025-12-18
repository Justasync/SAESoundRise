<?php

/**
 * @file controller_album.class.php
 * @brief Fichier contenant le contrôleur de gestion des albums.
 * 
 * Ce fichier gère toutes les fonctionnalités liées aux albums musicaux
 * dans l'application Paaxio.
 * 
 */

/**
 * @class ControllerAlbum
 * @brief Contrôleur dédié à la gestion des albums.
 * 
 * Cette classe gère les opérations sur les albums :
 * - Affichage d'un album et de ses chansons
 * - Liste de tous les albums
 * - Création d'un nouvel album
 * - Ajout de chansons à un album existant
 * - Modification des chansons d'un album
 * 
 * @extends Controller
 */
class ControllerAlbum extends Controller
{
    /**
     * @brief Constructeur du contrôleur album.
     * 
     * @param \Twig\Environment $twig Environnement Twig pour le rendu des templates.
     * @param \Twig\Loader\FilesystemLoader $loader Chargeur de fichiers Twig.
     */
    public function __construct(\Twig\Environment $twig, \Twig\Loader\FilesystemLoader $loader)
    {
        parent::__construct($loader, $twig);
    }

    /**
     * @brief Affiche les détails d'un album avec ses chansons.
     * 
     * Récupère l'album par son ID et affiche la liste de ses chansons.
     * Génère également un token CSRF pour la protection des formulaires.
     * 
     * @return void
     */
    public function afficher()
    {
        $idAlbum = isset($_GET['idAlbum']) ? (int)$_GET['idAlbum'] : null;

        if (!$idAlbum) {
            $this->redirectTo('home', 'afficher');
        }

        // Récupération de l'album
        $managerAlbum = new AlbumDAO($this->getPdo());
        $album = $managerAlbum->find($idAlbum);

        if (!$album) {
            $this->redirectTo('home', 'afficher');
        }

        // Récupération des chansons de l'album
        $managerChanson = new ChansonDAO($this->getPdo());
        $chansons = $managerChanson->rechercherParAlbum($idAlbum);

        // Chargement du template
        $template = $this->getTwig()->load('chanson_album.html.twig');
        echo $template->render([
            'page' => [
                'title' => $album->getTitreAlbum(),
                'name' => "album",
                'description' => "Album dans Paaxio"
            ],
            'album' => $album,
            'chansons' => $chansons,
        ]);
    }

    /**
     * @brief Liste tous les albums de la plateforme.
     * 
     * Récupère tous les albums et les affiche dans un template de test.
     * 
     * @return void
     */
    public function lister()
    {
        // Récupération des albums
        $managerAlbum = new AlbumDao($this->getPdo());
        $albums = $managerAlbum->findAll();

        // Choix du template
        $template = $this->getTwig()->load('test.html.twig');

        // Affichage de la page
        echo $template->render(array(
            'page' => [
                'title' => "Albums",
                'name' => "albums",
                'description' => "Albums dans Paaxio"
            ],
            'testing' => $albums,
        ));
    }

    /**
     * @brief Liste tous les albums sous forme de tableau.
     * 
     * Récupère tous les albums et les affiche dans un format tableau.
     * 
     * @return void
     */
    public function listerTableau()
    {
        $managerAlbum = new AlbumDao($this->getPdo());
        $albums = $managerAlbum->findAll();

        // Génération de la vue
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

    /**
     * @brief Affiche le formulaire d'ajout d'album.
     * 
     * Permet à un artiste connecté de créer un nouvel album ou
     * d'ajouter des chansons à un album existant.
     * Nécessite le rôle Artiste.
     * 
     * @return void
     */
    public function afficherFormulaireAjout()
    {
        // Vérifier si l'utilisateur est un artiste connecté
        // Connexion obligatoire pour ajouter un album (redirige vers connect si non connecté)
        $this->requireRole(RoleEnum::Artiste);

        $idAlbum = $_GET['idAlbum'] ?? null;
        $albumExistant = null;
        $managerAlbum = new AlbumDAO($this->getPdo());

        if ($idAlbum) {
            $albumExistant = $managerAlbum->find((int)$idAlbum);
            // Vérifier que l'album appartient bien à l'artiste connecté
            if (!$albumExistant || $albumExistant->getArtisteAlbum() !== $_SESSION['user_email']) {
                $this->redirectTo('home', 'afficher');
            }
        }

        // Récupérer les albums de l'artiste
        $managerAlbum = new AlbumDAO($this->getPdo());
        $albumsArtiste = $managerAlbum->findAllByArtistEmail($_SESSION['user_email']);

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

    /**
     * @brief Traite l'ajout d'un nouvel album ou l'ajout de chansons à un album existant.
     * 
     * Cette méthode gère :
     * - La création d'un nouvel album avec sa pochette
     * - L'ajout de chansons à un album existant
     * - L'upload des fichiers audio et l'extraction de leurs métadonnées (durée)
     * - La création ou récupération des genres musicaux
     * 
     * Nécessite une requête POST et le rôle Artiste.
     * 
     * @return void
     */
    public function ajouterAlbum()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectTo('home', 'afficher');
        }

        $this->requireRole(RoleEnum::Artiste);

        $managerChanson = new ChansonDAO($this->getPdo());
        $managerGenre = new GenreDAO($this->getPdo());
        $managerAlbum = new AlbumDAO($this->getPdo());

        $idAlbumExistant = $_POST['id_album_existant'] ?? null;

        if ($idAlbumExistant) {
            // Ajout de chansons à un album existant
            $albumCree = $managerAlbum->find((int)$idAlbumExistant);
            if (!$albumCree || $albumCree->getArtisteAlbum() !== $_SESSION['user_email']) {
                // Gérer l'erreur : l'album n'existe pas ou n'appartient pas à l'utilisateur
                $this->redirectTo('home', 'afficher');
            }
            $idAlbum = $albumCree->getIdAlbum();
        } else {
            // Création d'un nouvel album
            $album = new Album();
            $album->setTitreAlbum($_POST['titre_album'] ?? '');
            $album->setDateSortieAlbum($_POST['date_sortie'] ?? '');
            $album->setArtisteAlbum($_SESSION['user_email']);

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

                $managerChanson->createChanson($chanson);
            }
        }

        if ($idAlbumExistant) {
            // Rediriger vers la page de détails de l'album mis à jour
            $this->redirectTo('album', 'afficherDetails', ['idAlbum' => $idAlbumExistant, 'success' => 1]);
        } else {
            // Rediriger vers le tableau de bord après la création d'un nouvel album
            $this->redirectTo('home', 'afficher', ['success' => 1]);
        }
    }

    /**
     * @brief Affiche les détails d'un album avec vue différenciée selon le rôle.
     * 
     * Affiche une vue différente selon que l'utilisateur est :
     * - L'artiste propriétaire de l'album (vue d'édition)
     * - Un auditeur ou autre artiste (vue de lecture seule)
     * 
     * @return void
     */
    public function afficherDetails()
    {

        $idAlbum = $_GET['idAlbum'] ?? null;
        $idChanson = $_GET['idChanson'] ?? null;

        if (!$idAlbum) {
            // Gérer l'erreur, par exemple rediriger
            $this->redirectTo('home', 'afficher');
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
        if ($userRole === RoleEnum::Artiste && $album->getArtisteAlbum() === $_SESSION['user_email']) {
            // Si l'utilisateur est l'artiste propriétaire de l'album, il voit la page d'édition.
            $template = 'album_details_artiste.html.twig';
        } else {
            // Sinon (auditeur, ou artiste regardant l'album d'un autre), il voit la page de lecture.
            $template = 'album_details_auditeur.html.twig';
        }

        $template = $this->getTwig()->load($template);
        echo $template->render([
            'album' => $album,
            'chansons' => $chansons,
            'chansonSelected' => $idChanson ? (int)$idChanson : null,
            'session' => $_SESSION,
        ]);
    }

    /**
     * @brief Modifie les informations d'une chanson.
     * 
     * Permet à l'artiste propriétaire d'un album de modifier les informations
     * d'une chanson (titre, genre).
     * Nécessite une requête POST et le rôle Artiste.
     * 
     * @return void
     */
    public function modifierChanson()
    {
        // Sécurité : vérifier la méthode, la session et le rôle
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectTo('home', 'afficher');
        }

        $this->requireRole(RoleEnum::Artiste);

        $idChanson = $_GET['idChanson'] ?? null;
        $idAlbum = $_POST['id_album'] ?? null; // Assurez-vous que ce champ est dans le formulaire de la modale

        if (!$idChanson || !$idAlbum) {
            // Rediriger si les IDs sont manquants
            $this->redirectTo('home', 'afficher', ['error' => 1]);
        }

        $chansonDAO = new ChansonDAO($this->getPDO());
        $chanson = $chansonDAO->findId((int)$idChanson);

        // Vérifier que la chanson existe et appartient bien à un album de l'artiste
        if (!$chanson || $chanson->getAlbumChanson()->getArtisteAlbum() !== $_SESSION['user_email']) {
            $this->redirectTo('home', 'afficher', ['error' => 'unauthorized']);
        }

        // Mettre à jour les informations
        $chanson->setTitreChanson($_POST['titre_chanson']);

        $genreDAO = new GenreDAO($this->getPDO());
        $nomGenre = $_POST['genre_chanson'] ?? '';
        $genre = $genreDAO->findOrCreateByName($nomGenre);
        $chanson->setGenreChanson($genre);

        $chansonDAO->updateChanson($chanson);

        // Rediriger vers la page de l'album avec un message de succès
        $this->redirectTo('album', 'afficherDetails', ['idAlbum' => $idAlbum, 'success_update' => 1]);
    }
}
