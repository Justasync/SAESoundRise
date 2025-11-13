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
            $_SESSION['user_role'] = $utilisateur->getRoleUtilisateur()?->getIdRole();
            $_SESSION['user_logged_in'] = true;

            // Log connection

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

    public function signup()
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
    }

    public function signout()
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

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Déconnexion réussie.'
        ]);
    }
}
