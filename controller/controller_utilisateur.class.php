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
            // Vérification que le compte est actif
if (!empty($errors)) {
    // Si es Admin, recargamos la página pasando los errores
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
        $template = $this->getTwig()->load('utilisateur_ajout.html.twig');
        echo $template->render([
            'page' => ['title' => 'Ajouter un utilisateur'],
            'session' => $_SESSION,
            'errors' => $errors, // Pasamos la lista de errores
            'formData' => $post  // Pasamos lo que el usuario ya escribió para que no se borre
        ]);
        return;
    }

    // Si no es admin (es la API de registro normal), mantenemos el JSON
    echo json_encode([
        'success' => false,
        'message' => implode(' ', $errors)
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
     * Gère l'inscription d'un nouvel utilisateur (artiste, auditeur ou producteur).
     * Valide les données, crée le compte et envoie l'e-mail de bienvenue.
     *
     * Retourne du JSON indiquant le succès ou l'échec de la création.
     */
  public function signup()
{
    // Initialisation des genres pour l'affichage
    $genreDao = new GenreDAO($this->getPDO());
    $genres = $genreDao->findAll(); 

    // 1. AFFICHAGE (Requête GET)
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $template = $this->getTwig()->load('utilisateur_ajout.html.twig');
        echo $template->render([
            'page' => ['title' => 'Ajouter un utilisateur'],
            'session' => $_SESSION,
            'genres' => $genres
        ]);
        return;
    }

    // 2. TRAITEMENT (Requête POST)
    $post = $this->getPost() ?? [];

    // --- CORRECTION ICI : Gestion de l'Enum pour vérifier si l'utilisateur est admin ---
    $isAdmin = false;
    if (isset($_SESSION['user_role'])) {
        // Si c'est un objet Enum, on compare sa valeur, sinon on compare directement
        $roleValue = ($_SESSION['user_role'] instanceof \RoleEnum) ? $_SESSION['user_role']->value : $_SESSION['user_role'];
        $isAdmin = (strtolower($roleValue) === 'admin');
    }

    $allowedTypes = [
        'admin' => 'admin',
        'artiste' => 'artiste',
        'auditeur' => 'auditeur',
        'producteur' => 'producteur'
    ];

    $userType = strtolower(trim($post['type'] ?? ''));
    $nom = trim($post['nom'] ?? '');
    $pseudo = trim($post['pseudo'] ?? '');
    $description = trim($post['description'] ?? '');
    $email = strtolower(trim($post['email'] ?? ''));
    $birthdate = trim($post['birthdate'] ?? '');
    $password = $post['password'] ?? '';
    $passwordRepeat = $post['password_repeat'] ?? '';
    $genreId = isset($post['genre_id']) ? (int)$post['genre_id'] : null;

    $errors = [];

    // Validations de base
    if (!array_key_exists($userType, $allowedTypes)) {
        $errors[] = 'Le type de profil sélectionné est invalide.';
    }

    $signupRules = [
        'nom' => ['obligatoire' => true, 'longueur_min' => 1],
        'pseudo' => ['obligatoire' => true, 'longueur_min' => 3, 'pseudo_format' => true],
        'description' => ['obligatoire' => true, 'longueur_min' => 10],
        'email' => ['obligatoire' => true, 'format' => FILTER_VALIDATE_EMAIL],
        'birthdate' => ['obligatoire' => true, 'age_minimum' => 13],
        'password' => ['obligatoire' => true, 'longueur_min' => 8, 'mot_de_passe_fort' => true]
    ];

    $validator = new Validator($signupRules);
    if (!$validator->valider(['nom'=>$nom, 'pseudo'=>$pseudo, 'description'=>$description, 'email'=>$email, 'birthdate'=>$birthdate, 'password'=>$password])) {
        $errors = array_merge($errors, $validator->getMessagesErreurs());
    }

    if ($password !== $passwordRepeat) {
        $errors[] = 'Les mots de passe ne correspondent pas.';
    }

    if ($userType !== 'auditeur' && $userType !== 'admin' && !$genreId) {
    $errors[] = 'Veuillez sélectionner un genre musical.';
}

    $utilisateurDAO = new UtilisateurDAO($this->getPDO());
    if ($email !== '' && $utilisateurDAO->existsByEmail($email)) $errors[] = 'Email déjà utilisé.';
    if ($pseudo !== '' && $utilisateurDAO->existsByPseudo($pseudo)) $errors[] = 'Pseudo déjà utilisé.';

    // --- GESTION DES ERREURS D'AFFICHAGE ---
    if (!empty($errors)) {
        if ($isAdmin) {
            $template = $this->getTwig()->load('utilisateur_ajout.html.twig');
            echo $template->render([
                'page' => ['title' => 'Ajouter un utilisateur'],
                'session' => $_SESSION,
                'errors' => $errors,
                'formData' => $post,
                'genres' => $genres
            ]);
            return;
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
            return;
        }
    }

    // 3. CRÉATION
    try {
        $roleDao = new RoleDao($this->getPDO());
        $role = $roleDao->findByType($allowedTypes[$userType]);

        $utilisateur = new Utilisateur();
        $utilisateur->setEmailUtilisateur($email);
        $utilisateur->setNomUtilisateur($nom);
        $utilisateur->setPseudoUtilisateur($pseudo);
        $utilisateur->setMotDePasseUtilisateur(password_hash($password, PASSWORD_ARGON2ID));
        $utilisateur->setDateDeNaissanceUtilisateur(DateTime::createFromFormat('Y-m-d', $birthdate));
        $utilisateur->setDateInscriptionUtilisateur(new DateTime());
        $utilisateur->setStatutUtilisateur(\StatutUtilisateur::Actif);
        $utilisateur->setStatutAbonnement(\StatutAbonnement::Inactif);
        $utilisateur->setRoleUtilisateur($role);
        $utilisateur->setDescriptionUtilisateur($description);

        if ($genreId) {
            $genreObj = $genreDao->find($genreId);
            if ($genreObj) $utilisateur->setGenreUtilisateur($genreObj);
        }

        if ($utilisateurDAO->create($utilisateur)) {
            if ($isAdmin) {
                header('Location: ?controller=admin&method=afficher&success=1');
                exit;
            }
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Compte créé !']);
        }
    } catch (Exception $e) {
        die("Erreur technique : " . $e->getMessage());
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
