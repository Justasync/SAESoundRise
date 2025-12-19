<?php

class ControllerUtilisateur extends Controller
{
    /**
     * Constructeur du contrôleur utilisateur.
     *
     * @param \Twig\Environment $twig Environnement Twig.
     * @param \Twig\Loader\FilesystemLoader $loader Chargement des templates.
     */
    public function __construct(\Twig\Environment $twig, \Twig\Loader\FilesystemLoader $loader)
    {
        parent::__construct($loader, $twig);
    }

    /**
     * Gère la connexion (authentification) d'un utilisateur.
     *
     * Reçoit une requête POST contenant l'email et le mot de passe.
     * Retourne une réponse JSON indiquant le succès ou l'échec de la connexion.
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
     * Gère l'inscription d'un nouvel utilisateur (artiste, auditeur ou producteur).
     * Valide les données, crée le compte et envoie l'e-mail de bienvenue.
     *
     * Retourne du JSON indiquant le succès ou l'échec de la création.
     */
    public function signup(){

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectTo('home', 'afficher');
            return;
        }

        $post = $this->getPost() ?? [];
        $isAdmin = isset($post['is_admin']) && $post['is_admin'] == '1';

        if ($isAdmin) {
            $this->requireRole(RoleEnum::Admin);
        }

        header('Content-Type: application/json');

    // Types de profils autorisés
    $allowedTypes = [
        'artiste' => 'artiste',
        'auditeur' => 'auditeur',
        'producteur' => 'producteur',
        'admin' => 'admin'
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
    if ($userType != 'auditeur'&& $userType !== 'admin') {
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
        // Limpiamos cualquier salida previa (como espacios o errores de PHP)
        if (ob_get_length()) ob_clean();
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => implode('<br>', $errors)
        ]);
        exit; 
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
        $utilisateur->setDescriptionUtilisateur($description !== '' ? $description : null);
        $utilisateur->setSiteWebUtilisateur(($website !== '') ? $website : null);
        $utilisateur->setStatutAbonnement(\StatutAbonnement::Inactif);
        $utilisateur->setDateDebutAbonnement(null);
        $utilisateur->setDateFinAbonnement(null);
        $utilisateur->setPointsDeRenommeeArtiste(null);
        $utilisateur->setNbAbonnesArtiste(null);
        $utilisateur->seturlPhotoUtilisateur(null);
        $utilisateur->setRoleUtilisateur($role);

        $creationReussie = $utilisateurDAO->create($utilisateur);

        if ($creationReussie) {
            // Limpiamos cualquier buffer para asegurar un JSON puro
            if (ob_get_length()) ob_clean();

            $emailSender = new Email($this->getTwig());
            $emailSender->sendWelcomeEmail(
                $utilisateur->getEmailUtilisateur(),
                $utilisateur->getPseudoUtilisateur(),
                $userType
            );

            // Si es Admin, redirigimos por servidor
            if ($isAdmin) {
                $this->redirectTo('admin', 'afficher', ['success' => 1]);
                exit; 
            }   

            // Si es un registro normal desde el formulario, enviamos JSON
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Votre compte a été créé! Vérifiez vos e-mails.',
                'user' => [
                    'email' => $utilisateur->getEmailUtilisateur(),
                    'pseudo' => $utilisateur->getPseudoUtilisateur(),
                    'type' => $userType
                ]
            ]);
            exit; 
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
     * Gère la création d'un nouvel utilisateur par l'administrateur.
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
  

    /**
     * Déconnecte l'utilisateur et détruit la session.
     * Redirige vers la page d'accueil.
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
     * Affiche la page des chansons aimées (likées) par l'utilisateur connecté.
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

        // Génération d'un token CSRF si nécessaire
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        // Chargement du template Twig et affichage
        $template = $this->getTwig()->load('chanson_album.html.twig');

        echo $template->render([
            'page' => [
                'title' => "Chansons likées",
                'name'  => "musique_likee",
                'description' => "Chansons likées par l'utilisateur"
            ],
            'album' => $albumVirtuel,
            'chansons' => $chansonsLikees,
            'csrf_token' => $_SESSION['csrf_token']
        ]);
    }

    /**
     * Affichage du profil public d'un artiste à partir de son pseudo.
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
            'albums'      => $albums
        ]);
    }
}
