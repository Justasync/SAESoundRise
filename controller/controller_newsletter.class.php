<?php

class ControllerNewsletter extends Controller
{
    public function __construct(\Twig\Environment $twig, \Twig\Loader\FilesystemLoader $loader)
    {
        parent::__construct($loader, $twig);
    }

    public function afficher()
    {
        $template = $this->getTwig()->load('newsletter.html.twig');
        echo $template->render(array(
            "page" => [
                'title' => "Newsletter",
                'name' => "newsletter",
                'description' => "Newsletter de Paaxio"
            ],
        ));
    }

    public function ajouter()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /?controller=newsletter&method=afficher');
            return;
        }

        $post = $this->getPost() ?? [];
        $email = trim($post['email'] ?? '');

        $errors = [];
        $success = '';

        if (empty($email)) {
            $errors[] = 'L\'adresse e-mail est requise.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'L\'adresse e-mail n\'est pas valide.';
        }

        if (empty($errors)) {
            try {
                $pdo = $this->getPDO();
                $dao = new NewsletterDAO($pdo);

                if ($dao->existsByEmail($email)) {
                    $errors[] = 'Cette adresse e-mail est déjà inscrite.';
                } else {
                    $n = new Newsletter();
                    $n->setEmail($email);
                    $n->setDateInscription(new DateTime());
                    if ($dao->create($n)) {
                        $success = 'Merci ! Votre inscription à la newsletter a bien été prise en compte.';
                    } else {
                        $errors[] = 'Une erreur est survenue lors de l\'inscription. Veuillez réessayer.';
                    }
                }
            } catch (Exception $e) {
                $errors[] = 'Erreur serveur : ' . $e->getMessage();
            }
        }

        $template = $this->getTwig()->load('newsletter.html.twig');
        echo $template->render(array(
            "page" => [
                'title' => "Newsletter",
                'name' => "newsletter",
                'description' => "Newsletter de Paaxio"
            ],
            'success' => $success,
            'errors' => $errors,
            'form' => [
                'email' => $errors ? $email : ''
            ]
        ));
    }
}
