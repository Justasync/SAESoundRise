<?php

/**
 * @file controller_newsletter.class.php
 * @brief Fichier contenant le contrôleur de gestion de la newsletter.
 * 
 * Ce fichier gère les fonctionnalités liées à l'inscription
 * à la newsletter de l'application Paaxio.
 * 
 */

/**
 * @class ControllerNewsletter
 * @brief Contrôleur dédié à la gestion de la newsletter.
 * 
 * Cette classe gère :
 * - L'affichage du formulaire d'inscription à la newsletter
 * - L'ajout d'une adresse e-mail à la liste de diffusion
 * 
 * @extends Controller
 */
class ControllerNewsletter extends Controller
{
    /**
     * @brief Constructeur du contrôleur newsletter.
     * 
     * @param \Twig\Environment $twig Environnement Twig pour le rendu des templates.
     * @param \Twig\Loader\FilesystemLoader $loader Chargeur de fichiers Twig.
     */
    public function __construct(\Twig\Environment $twig, \Twig\Loader\FilesystemLoader $loader)
    {
        parent::__construct($loader, $twig);
    }

    /**
     * @brief Affiche le formulaire d'inscription à la newsletter.
     * 
     * @return void
     */
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

    /**
     * @brief Traite l'inscription à la newsletter.
     * 
     * Valide l'adresse e-mail soumise et l'ajoute à la base de données.
     * Par mesure de sécurité, ne révèle pas si l'e-mail existe déjà.
     * Nécessite une requête POST.
     * 
     * @return void
     */
    public function ajouter()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectTo('newsletter', 'afficher');
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
                    // Pour la sécurité, on ne révèle pas si l'email est déjà présent : message générique succès
                    $success = 'Merci ! Votre inscription à la newsletter a bien été prise en compte.';
                    // On garde les erreurs vide pour ne pas afficher de bloc erreur
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
