<?php

/**
 * @file controller_utilisateur.class.php
 * @brief Fichier contenant le contrôleur de gestion des utilisateurs.
 * 
 * Ce fichier gère toutes les fonctionnalités liées aux utilisateurs
 * dans l'application Paaxio : authentification, inscription, profils.
 * 
 */

/**
 * @class ControllerUtilisateur
 * @brief Contrôleur dédié à la gestion des utilisateurs.
 * 
 * Cette classe gère les opérations sur les utilisateurs :
 * - Connexion (signin)
 * - Inscription (signup)
 * - Déconnexion (logout)
 * - Création d'utilisateurs par l'admin
 * - Affichage des chansons likées
 * - Affichage du profil artiste
 * 
 * @extends Controller
 */
class ControllerUtilisateur extends Controller
{
    /**
     * @brief Constructeur du contrôleur utilisateur.
     *
     * @param \Twig\Environment $twig Environnement Twig pour le rendu des templates.
     * @param \Twig\Loader\FilesystemLoader $loader Chargeur de fichiers Twig.
     */
    public function __construct(\Twig\Environment $twig, \Twig\Loader\FilesystemLoader $loader)
    {
        parent::__construct($loader, $twig);
    }

    /**
     * @brief Gère la connexion (authentification) d'un utilisateur.
     *
     * Reçoit une requête POST contenant l'email et le mot de passe.
     * Valide les données, vérifie les identifiants et initialise la session.
     * Retourne une réponse JSON indiquant le succès ou l'échec de la connexion.
     * 
     * @return void Retourne une réponse JSON et termine le script.
     */
    public function signin()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Méthode non autorisée'
            ]);
            return;
        }

        $post = $this->getPost();
        $email = trim($post['email'] ?? '');
        $password = $post['password'] ?? '';

        // Validation via la classe Validator
        $signinRules = [
            'email' => [
                'obligatoire' => true,
                'type' => 'string',
                'format' => FILTER_VALIDATE_EMAIL
            ],
            'password' => [
                'obligatoire' => true,
                'type' => 'string',
            ]
        ];

        $validator = new Validator($signinRules);
        $signinData = [
            'email' => $email,
            'password' => $password
        ];

        if (!$validator->valider($signinData)) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => implode(' ', $validator->getMessagesErreurs())
            ]);
            return;
        }

        try {
            $utilisateurDAO = new UtilisateurDAO($this->getPDO());
            $utilisateur = $utilisateurDAO->find($email);

            if (!$utilisateur) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Adresse e-mail ou mot de passe incorrect.'
                ]);
                return;
            }

            $hashedPassword = $utilisateur->getMotDePasseUtilisateur();

            if (!password_verify($password, $hashedPassword)) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Adresse e-mail ou mot de passe incorrect.'
                ]);
                return;
            }

            $statut = $utilisateur->getStatutUtilisateur();
            // Vérification que le compte est actif
            if ($statut && $statut !== StatutUtilisateur::Actif) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Votre compte est ' . ($statut === StatutUtilisateur::Suspendu ? 'suspendu' : 'supprimé') . '.'
                ]);
                return;
            }

            // Connexion réussie : initialisation de la session
            $_SESSION['user_email'] = $utilisateur->getEmailUtilisateur();
            $_SESSION['user_pseudo'] = $utilisateur->getPseudoUtilisateur();
            $_SESSION['user_role'] = $utilisateur->getRoleUtilisateur()?->getRoleEnum();
            $_SESSION['user_logged_in'] = true;

            // Réponse de succès
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Connexion réussie!',
                'user' => [
                    'email' => $utilisateur->getEmailUtilisateur(),
                    'pseudo' => $utilisateur->getPseudoUtilisateur()
                ]
            ]);
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la connexion. Veuillez réessayer plus tard (' . $e->getMessage() . ').'
            ]);
        }
    }

    /**
     * @brief Gère l'inscription d'un nouvel utilisateur.
     * 
     * Permet l'inscription d'un artiste, auditeur ou producteur.
     * Valide les données, vérifie l'unicité de l'email et du pseudo,
     * crée le compte et envoie l'e-mail de bienvenue.
     *
     * Retourne une réponse JSON indiquant le succès ou l'échec de la création.
     * 
     * @return void Retourne une réponse JSON et termine le script.
     */
    public function signup()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                'success' => false,
                'message' => 'Méthode non autorisée.'
            ]);
            return;
        }

        $post = $this->getPost() ?? [];

        // Types de profils autorisés
        $allowedTypes = [
            'artiste' => 'artiste',
            'auditeur' => 'auditeur',
            'producteur' => 'producteur'
        ];

        $userType = strtolower(trim($post['type'] ?? ''));
        $nom = trim($post['nom'] ?? '');
        $pseudo = trim($post['pseudo'] ?? '');
        $description = trim($post['description'] ?? '');
        $website = trim($post['website'] ?? '');
        $email = strtolower(trim($post['email'] ?? ''));
        $birthdate = trim($post['birthdate'] ?? '');
        $password = $post['password'] ?? '';
        $passwordRepeat = $post['password_repeat'] ?? '';
        $genreId = isset($post['genre_id']) ? (int)$post['genre_id'] : null;

        $errors = [];

        // Validation préalable du type d'utilisateur (non dans les règles Validator)
        if (!array_key_exists($userType, $allowedTypes)) {
            $errors[] = 'Le type de profil sélectionné est invalide.';
        }

        // Règles de validation des données pour l'inscription
        $signupRules = [
            'nom' => [
                'obligatoire' => true,
                'type' => 'string',
                'longueur_min' => 1,
                'longueur_max' => 255
            ],
            'pseudo' => [
                'obligatoire' => true,
                'type' => 'string',
                'longueur_min' => 3,
                'longueur_max' => 50,
                'pseudo_format' => true
            ],
            'description' => [
                'obligatoire' => true,
                'type' => 'string',
                'longueur_min' => 10,
                'longueur_max' => 1000
            ],
            'website' => [
                'obligatoire' => false,
                'format' => FILTER_VALIDATE_URL
            ],
            'email' => [
                'obligatoire' => true,
                'type' => 'string',
                'longueur_min' => 5,
                'longueur_max' => 320,
                'format' => FILTER_VALIDATE_EMAIL
            ],
            'birthdate' => [
                'obligatoire' => true,
                'type' => 'string',
                'age_minimum' => 13
            ],
            'password' => [
                'obligatoire' => true,
                'type' => 'string',
                'longueur_min' => 8,
                'longueur_max' => 128,
                'mot_de_passe_fort' => true
            ]
        ];

        $signupData = [
            'nom' => $nom,
            'pseudo' => $pseudo,
            'description' => $description,
            'website' => $website,
            'email' => $email,
            'birthdate' => $birthdate,
            'password' => $password
        ];

        $validator = new Validator($signupRules);
        if (!$validator->valider($signupData)) {
            $errors = array_merge($errors, $validator->getMessagesErreurs());
        }

        // Vérification supplémentaire : confirmation du mot de passe
        if ($password !== $passwordRepeat) {
            $errors[] = 'Les mots de passe ne correspondent pas.';
        }

        // Pour les artistes/producteurs, vérifier la sélection d'un genre
        if ($userType != 'auditeur') {
            if (!$genreId) {
                $errors[] = 'Veuillez sélectionner un genre musical.';
            } else {
                $genreDao = new GenreDAO($this->getPDO());
                try {
                    $genre = $genreDao->find((int)$genreId);
                } catch (Exception $e) {
                    $genre = null;
                }
                if (!$genre) {
                    $errors[] = 'Le genre sélectionné est invalide.';
                }
            }
        }

        // Vérification unicité de l'email et du pseudo
        $utilisateurDAO = new UtilisateurDAO($this->getPDO());

        if ($email !== '' && $utilisateurDAO->existsByEmail($email)) {
            $errors[] = 'Un compte existe déjà avec cette adresse e-mail.';
        }

        if ($pseudo !== '' && $utilisateurDAO->existsByPseudo($pseudo)) {
            $errors[] = 'Ce nom ou pseudonyme est déjà utilisé.';
        }

        if (!empty($errors)) {
            echo json_encode([
                'success' => false,
                'message' => implode(' ', $errors)
            ]);
            return;
        }

        // Analyse de la date de naissance
        $birthDateTime = DateTime::createFromFormat('Y-m-d', $birthdate);

        $roleDao = new RoleDao($this->getPDO());
        $role = $roleDao->findByType($allowedTypes[$userType]);

        if (!$role) {
            echo json_encode([
                'success' => false,
                'message' => 'Impossible de déterminer le rôle à attribuer à ce compte.'
            ]);
            return;
        }

        $hashedPassword = password_hash($password, PASSWORD_ARGON2ID);
        $createdAt = (new DateTime())->format('Y-m-d H:i:s');
        $pdo = $this->getPDO();

        try {
            $utilisateur = new Utilisateur();
            // Affectation des différents attributs de l'utilisateur
            $utilisateur->setEmailUtilisateur($email);
            $utilisateur->setNomUtilisateur($nom);
            $utilisateur->setPseudoUtilisateur($pseudo);
            $utilisateur->setMotDePasseUtilisateur($hashedPassword);
            $dateNaissance = !empty($birthdate) ? DateTime::createFromFormat('Y-m-d', $birthdate) : null;
            $utilisateur->setDateDeNaissanceUtilisateur($dateNaissance);
            $utilisateur->setDateInscriptionUtilisateur(DateTime::createFromFormat('Y-m-d H:i:s', $createdAt));
            $utilisateur->setStatutUtilisateur(\StatutUtilisateur::Actif);
            $utilisateur->setGenreUtilisateur(isset($genre) ? $genre : null);
            $utilisateur->setEstAbonnee(false);
            $utilisateur->setDescriptionUtilisateur($description ?? null);
            $utilisateur->setSiteWebUtilisateur((isset($website) && $website !== '') ? $website : null);
            $utilisateur->setStatutAbonnement(\StatutAbonnement::Inactif);
            $utilisateur->setDateDebutAbonnement(null);
            $utilisateur->setDateFinAbonnement(null);
            $utilisateur->setPointsDeRenommeeArtiste(null);
            $utilisateur->setNbAbonnesArtiste(null);
            $utilisateur->seturlPhotoUtilisateur(null);
            $utilisateur->setRoleUtilisateur($role);

            $creationReussie = $utilisateurDAO->create($utilisateur);

            if ($creationReussie) {

                $emailSender = new Email($this->getTwig());
                $emailSender->sendWelcomeEmail(
                    $utilisateur->getEmailUtilisateur(),
                    $utilisateur->getPseudoUtilisateur(),
                    $userType
                );

                echo json_encode([
                    'success' => true,
                    'message' => 'Votre compte a été créé! Vérifiez vos e-mails pour confirmer votre inscription.',
                    'user' => [
                        'email' => $utilisateur->getEmailUtilisateur(),
                        'pseudo' => $utilisateur->getPseudoUtilisateur(),
                        'type' => $userType
                    ]
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Impossible de créer votre compte pour le moment.'
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Impossible de créer votre compte pour le moment (' . $e->getMessage() . ').'
            ]);
        }
    }

    /**
     * @brief Gère la création d'un nouvel utilisateur par l'administrateur.
     *
     * Cette méthode effectue les opérations suivantes :
     * 1. Vérifie si l'utilisateur connecté a les droits d'administrateur.
     * 2. Traite la requête POST pour récupérer les données (pseudo, email, mot de passe, rôle).
     * 3. Vérifie l'unicité de l'email et du pseudo via le DAO.
     * 4. Initialise une nouvelle entité Utilisateur avec les données fournies et des valeurs par défaut
     *    (hachage du mot de passe, date de naissance par défaut, statut actif, etc.).
     * 5. Persiste le nouvel utilisateur dans la base de données.
     * 6. Redirige vers la liste des utilisateurs en cas de succès ou affiche le formulaire avec les erreurs.
     *
     * @return void
     * @throws Exception En cas d'erreur lors de la récupération du rôle ou de l'insertion en base de données.
     */
    public function inscription()
    {
        // On vérifie que l'utilisateur courant est bien un administrateur
        $this->requireRole(RoleEnum::Admin);

        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $pseudo = trim($_POST['pseudo'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['mdp'] ?? '';
            $roleType = $_POST['role'] ?? 'auditeur';

            $pdo = $this->getPDO();
            $utilisateurDAO = new UtilisateurDAO($pdo);

            // Vérification si l'e-mail ou le pseudo existe déjà
            if ($utilisateurDAO->existsByEmail($email)) {
                $error = "Cet email est déjà utilisé.";
            } elseif ($utilisateurDAO->existsByPseudo($pseudo)) {
                $error = "Ce pseudo est déjà pris.";
            } else {
                try {
                    $roleDao = new RoleDao($pdo);
                    $role = $roleDao->findByType($roleType);

                    if ($role) {
                        $user = new Utilisateur();
                        $user->setPseudoUtilisateur($pseudo);
                        $user->setNomUtilisateur($pseudo);
                        $user->setEmailUtilisateur($email);
                        $user->setMotDePasseUtilisateur(password_hash($password, PASSWORD_ARGON2ID));
                        $user->setRoleUtilisateur($role);

                        $user->setDateInscriptionUtilisateur(new DateTime());
                        $user->setDateDeNaissanceUtilisateur(new DateTime('2000-01-01'));

                        $user->setStatutUtilisateur(\StatutUtilisateur::Actif);
                        $user->setEstAbonnee(false);
                        $user->setStatutAbonnement(\StatutAbonnement::Inactif);

                        $user->setGenreUtilisateur(null);
                        $user->setDescriptionUtilisateur("Compte créé par admin");
                        $user->setSiteWebUtilisateur(null);
                        $user->seturlPhotoUtilisateur(null);
                        $user->setDateDebutAbonnement(null);
                        $user->setDateFinAbonnement(null);
                        $user->setPointsDeRenommeeArtiste(0);
                        $user->setNbAbonnesArtiste(0);

                        if ($utilisateurDAO->create($user)) {
                            $this->redirectTo('admin', 'afficher', ['success' => 1]);
                            exit();
                        } else {
                            $error = "Erreur lors de la création en base de données.";
                        }
                    } else {
                        $error = "Rôle introuvable.";
                    }
                } catch (Exception $e) {
                    $error = "Erreur système: " . $e->getMessage();
                }
            }
        }

        $template = $this->getTwig()->load('utilisateur_ajout.html.twig');
        echo $template->render([
            'page' => ['title' => 'Ajouter Utilisateur'],
            'session' => $_SESSION,
            'error' => $error
        ]);
    }

    /**
     * @brief Déconnecte l'utilisateur et détruit la session.
     * 
     * Supprime toutes les données de session et le cookie de session,
     * puis redirige vers la page d'accueil.
     *
     * @return void
     */
    public function logout()
    {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        session_destroy();

        // Redirection vers la page d'accueil (controller=home, method=afficher)
        $this->redirectTo('home', 'afficher');
    }

    /**
     * @brief Affiche la page des chansons aimées (likées) par l'utilisateur connecté.
     * 
     * Récupère toutes les chansons que l'utilisateur a likées et les affiche
     * dans un format similaire à un album virtuel.
     * Nécessite que l'utilisateur soit authentifié.
     *
     * @return void
     */
    public function afficherMesLikes()
    {
        // Vérification de la connexion de l'utilisateur
        $this->requireAuth();

        $emailUtilisateur = $_SESSION['user_email'] ?? null;

        // DAO : récupération des chansons likées par l'utilisateur courant
        $managerLike = new ChansonDAO($this->getPdo());
        $chansonsLikees = $managerLike->findChansonsLikees($emailUtilisateur);

        // Marquer toutes les chansons chargées comme étant likées
        foreach ($chansonsLikees as $chanson) {
            $chanson->setIsLiked(true);
        }

        // Création d'un "album virtuel" pour la page d'affichage
        $albumVirtuel = (object) [
            "getTitreAlbum" => function () {
                return "Chansons Likées";
            },
            "getUrlImageAlbum" => function () {
                return "public/assets/like_default.png";
            },
            "getArtisteAlbum" => function () {
                return "Moi";
            },
            "getDateSortieAlbum" => function () {
                return null;
            },
        ];

        // Chargement du template Twig et affichage
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

    /**
     * @brief Affiche le profil public d'un artiste.
     * 
     * Récupère l'artiste par son pseudo passé en paramètre GET
     * et affiche son profil avec ses albums.
     *
     * @return void
     */
    public function afficherProfilArtiste()
    {
        // Récupération du pseudo dans la query string
        $pseudo = $_GET['pseudo'] ?? null;

        if (!$pseudo) {
            $this->redirectTo('home', 'afficher');
        }

        $utilisateurDAO = new UtilisateurDAO($this->getPDO());
        $albumDAO = new AlbumDAO($this->getPDO());

        // Recherche de l'artiste via son pseudo
        $utilisateur = $utilisateurDAO->findByPseudo($pseudo);

        // Vérification de l'abonnement si connecté
        $estAbonneAArtiste = false;
        if (isset($_SESSION['user_email'])) {
            $estAbonneAArtiste = $utilisateurDAO->estAbonneAArtiste($_SESSION['user_email'], $utilisateur->getEmailUtilisateur());
        }

        if (!$utilisateur) {
            $this->redirectTo('home', 'afficher');
        }

        // Récupération des albums de cet artiste par son e-mail
        $emailArtiste = $utilisateur->getEmailUtilisateur();

        $albums = $albumDAO->findAllByArtistEmail($emailArtiste);

        $template = $this->getTwig()->load('artiste_profil.html.twig');
        echo $template->render([
            'session'     => $_SESSION,
            'utilisateur' => $utilisateur,
            'albums'      => $albums,
            'estAbonneAArtiste' => $estAbonneAArtiste
        ]);
    }

    /**
     * Gère l'abonnement/désabonnement à un artiste via une requête AJAX.
     *
     * Reçoit une requête POST avec l'email de l'artiste.
     * Retourne une réponse JSON indiquant le succès et l'état de l'abonnement.
     * 
     * @return void
     */
    public function suivreArtiste()
    {
        header('Content-Type: application/json');

        // Vérification de la connexion 
        if (!isset($_SESSION['user_email'])) {
            echo json_encode(['success' => false, 'message' => 'Non connecté']);
            return;
        }

        $emailArtiste = $_POST['emailArtiste'] ?? null;
        $emailAbonne = $_SESSION['user_email'];

        // --- SÉCURITÉ : Empêcher l'auto-abonnement ---
        if ($emailAbonne === $emailArtiste) {
            echo json_encode([
                'success' => false, 
                'message' => 'Vous ne pouvez pas vous abonner à votre propre profil.'
            ]);
            return;
        }

        // Traitement de l'abonnement/désabonnement
        if ($emailArtiste) {
            $dao = new UtilisateurDAO($this->getPDO());
            $result = $dao->basculerAbonnement($emailAbonne, $emailArtiste);

            $increment = ($result === 'followed') ? 1 : -1;
            $dao->updateNbAbonnes($emailArtiste, $increment);

            $artisteAJour = $dao->find($emailArtiste);
            $nouveauNombre = $artisteAJour ? $artisteAJour->getNbAbonnesArtiste() : 0;
            
            echo json_encode([
                'success' => true, 
                'action' => $result,
                'newText' => ($result === 'followed') ? 'Abonné(e)' : 'S\'abonner',
                'nbAbonnes' => $nouveauNombre
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Email artiste manquant']);
        }
    }
}
