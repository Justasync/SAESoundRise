<?php

class ControllerUtilisateur extends Controller
{
    public function __construct(\Twig\Environment $twig, \Twig\Loader\FilesystemLoader $loader)
    {
        parent::__construct($loader, $twig);
    }

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

        $errors = [];
        if (empty($email)) {
            $errors[] = 'L\'adresse e-mail est requise.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'L\'adresse e-mail n\'est pas valide.';
        }

        if (empty($password)) {
            $errors[] = 'Le mot de passe est requis.';
        }

        if (!empty($errors)) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => implode(' ', $errors)
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
            if ($statut && $statut !== StatutUtilisateur::Actif) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Votre compte est ' . ($statut === StatutUtilisateur::Suspendu ? 'suspendu' : 'supprimé') . '.'
                ]);
                return;
            }

            // session
            $_SESSION['user_email'] = $utilisateur->getEmailUtilisateur();
            $_SESSION['user_pseudo'] = $utilisateur->getPseudoUtilisateur();
            $_SESSION['user_role'] = $utilisateur->getRoleUtilisateur()?->getTypeRole();
            $_SESSION['user_logged_in'] = true;

            // Log connection

            // Déterminer l'URL de redirection en fonction du rôle
            $roleId = $utilisateur->getRoleUtilisateur()?->getIdRole();
            $redirectUrl = '/?controller=home&method=afficher'; // URL par défaut
            if ($roleId === 2) { // Supposons que l'ID du rôle artiste est 2
                $redirectUrl = '/?controller=utilisateur&method=artisteDashboard';
            }

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Connexion réussie!',
                'redirectUrl' => $redirectUrl,
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

        if (!array_key_exists($userType, $allowedTypes)) {
            $errors[] = 'Le type de profil sélectionné est invalide.';
        }

        if ($pseudo === '' || mb_strlen($pseudo) < 3 || mb_strlen($pseudo) > 50) {
            $errors[] = 'Le nom ou pseudonyme doit contenir entre 3 et 50 caractères.';
        }

        // Le pseudo ne doit contenir que des lettres, chiffres et underscores, sans espaces.
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $pseudo)) {
            $errors[] = 'Le pseudo ne doit contenir que des lettres, des chiffres et des underscores, sans espaces.';
        }

        if ($nom === '' || mb_strlen($nom) == 0 || mb_strlen($nom) > 255) {
            $errors[] = 'Le nom ou pseudonyme doit contenir entre 1 et 255 caractères.';
        }

        if ($description === '' || mb_strlen($description) < 10) {
            $errors[] = 'La description doit contenir au moins 10 caractères.';
        }

        if ($website !== '' && !filter_var($website, FILTER_VALIDATE_URL)) {
            $errors[] = 'L’URL du site web n’est pas valide.';
        }

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'L’adresse e-mail n’est pas valide.';
        }

        $birthDateTime = null;
        if ($birthdate === '') {
            $errors[] = 'La date de naissance est requise.';
        } else {
            $birthDateTime = DateTime::createFromFormat('Y-m-d', $birthdate);
            $birthDateErrors = DateTime::getLastErrors();
            if (!$birthDateTime || ($birthDateErrors['warning_count'] ?? 0) > 0 || ($birthDateErrors['error_count'] ?? 0) > 0) {
                $errors[] = 'La date de naissance fournie est invalide.';
            } else {
                $today = new DateTimeImmutable();
                $minDate = $today->modify('-13 years');
                if ($birthDateTime > $minDate) {
                    $errors[] = 'Vous devez avoir au moins 13 ans pour créer un compte.';
                }
            }
        }

        if (mb_strlen($password) < 8) {
            $errors[] = 'Le mot de passe doit contenir au moins 8 caractères.';
        }

        if ($password !== $passwordRepeat) {
            $errors[] = 'Les mots de passe ne correspondent pas.';
        }

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
            // add all paramaters
            $utilisateur->setEmailUtilisateur($email); // adresse e-mail
            $utilisateur->setNomUtilisateur($nom); // nom
            $utilisateur->setPseudoUtilisateur($pseudo); // pseudonyme
            $utilisateur->setMotDePasseUtilisateur($hashedPassword); // mot de passe hashé
            $dateNaissance = !empty($birthdate) ? DateTime::createFromFormat('Y-m-d', $birthdate) : null;
            $utilisateur->setDateDeNaissanceUtilisateur($dateNaissance); // date de naissance
            $utilisateur->setDateInscriptionUtilisateur(DateTime::createFromFormat('Y-m-d H:i:s', $createdAt)); // date d'inscription maintenant
            $utilisateur->setStatutUtilisateur(\StatutUtilisateur::Actif); // statut par défaut
            $utilisateur->setGenreUtilisateur(isset($genre) ? $genre : null); // instance Genre ou null
            $utilisateur->setEstAbonnee(false); // nouvel utilisateur non abonné par défaut
            $utilisateur->setDescriptionUtilisateur($description ?? null);
            $utilisateur->setSiteWebUtilisateur((isset($website) && $website !== '') ? $website : null);
            $utilisateur->setStatutAbonnement(\StatutAbonnement::Inactif); // par défaut non abonné
            $utilisateur->setDateDebutAbonnement(null);
            $utilisateur->setDateFinAbonnement(null);
            $utilisateur->setPointsDeRenommeeArtiste(null);
            $utilisateur->setNbAbonnesArtiste(null);
            $utilisateur->seturlPhotoUtilisateur(null);
            $utilisateur->setRoleUtilisateur($role);

            $creationReussie = $utilisateurDAO->create($utilisateur);

            if ($creationReussie) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Votre compte a été créé! Vérifiez vos e-mails pour confirmer votre inscription.',
                    'user' => [
                        'email' => $email,
                        'pseudo' => $pseudo,
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
        header('Location: /?controller=home&method=afficher');
        exit;
    }

    public function artisteDashboard()
    {
        // Vérifier si l'utilisateur est un artiste connecté
        if (!isset($_SESSION['user_logged_in']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] != 2) {
            header('Location: /?controller=home&method=afficher');
            exit();
        }

        $utilisateurDAO = new UtilisateurDAO($this->getPDO());
        $artistesSuggere = $utilisateurDAO->findAllArtistes($_SESSION['user_email']);

        // On récupère les albums de l'artiste
        $albumDAO = new AlbumDAO($this->getPDO());
        $albums = $albumDAO->findAllByArtistEmail($_SESSION['user_email']);

        $template = $this->getTwig()->load('artiste_dashboard.html.twig');
        echo $template->render([
            'session' => $_SESSION,
            'artistes' => $artistesSuggere,
            'albums' => $albums,
        ]);
    }
}
