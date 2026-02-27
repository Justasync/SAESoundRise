<?php

/**
 * @file controller_message.class.php
 * @brief Fichier contenant le contrôleur de gestion des messages.
 * 
 * Ce fichier gère toutes les fonctionnalités liées aux messages
 * dans l'application Paaxio : envoi, réception, affichage.
 */

/**
 * @class ControllerMessage
 * @brief Contrôleur dédié à la gestion des messages.
 * 
 * Cette classe gère les opérations sur les messages :
 * - Envoi
 * - Réception
 * - Affichage
 * 
 * @extends Controller
 */

/**
 * @file controller_message.class.php
 * @brief Contrôleur pour la messagerie privée.
 */

class ControllerMessage extends Controller {

    public function __construct(\Twig\Environment $twig, \Twig\Loader\FilesystemLoader $loader)
    {
        parent::__construct($loader, $twig);
    }

    /**
     * Affiche la liste des conversations de l'utilisateur connecté.
     */
    public function lister()
    {
        $this->requireAuth();
        $myEmail = $_SESSION['user_email']; // On suppose que l'email est stocké en session

        $messageDAO = new MessageDAO($this->getPDO());
        $conversations = $messageDAO->getConversations($myEmail);

        $template = $this->getTwig()->load('message_liste.html.twig');
        echo $template->render([
            'page' => ['title' => 'Mes Messages', 'name' => 'messages'],
            'contacts' => $conversations
        ]);
    }

    /**
     * Affiche une discussion précise et gère l'envoi d'un nouveau message.
     */
    public function conversation()
    {
        $this->requireAuth();
        $myEmail = $_SESSION['user_email']; // Email de l'utilisateur connecté
        
        $contactPseudo = $this->getGet()['contact'] ?? null;

        if (!$contactPseudo) {
            $this->redirectTo('message', 'lister');
        }

        $utilisateurDAO = new UtilisateurDAO($this->getPDO());
        $contact = $utilisateurDAO->findByPseudo($contactPseudo); // Recherche par pseudo
        
        if (!$contact) {
            $this->redirectTo('message', 'lister');
        }

        $contactEmail = $contact->getEmailUtilisateur();

        if ($myEmail === $contactEmail) {
            $this->redirectTo('message', 'lister');
            return; 
        }

        $messageDAO = new MessageDAO($this->getPDO());

        if ($this->getPost() && isset($this->getPost()['contenu'])) {
            $contenu = trim($this->getPost()['contenu']);
            if (!empty($contenu)) {
                $me = $utilisateurDAO->find($myEmail);
                $nouveauMessage = new Message(
                    null, 
                    $contenu, 
                    new DateTime(), 
                    false, 
                    $me, 
                    $contact
                );
                $messageDAO->create($nouveauMessage);
                
                $this->redirectTo('message', 'conversation', ['contact' => $contact->getPseudoUtilisateur()]);
            }
        }

        $messages = $messageDAO->getMessagesConversation($myEmail, $contactEmail);

        foreach ($messages as $msg) {
            // Si le message m'est destiné ET qu'il n'est pas encore lu
            if ($msg->getEmailDestinataire()->getEmailUtilisateur() === $myEmail && !$msg->getEstLu()) {
                // On met à jour en base de données
                $messageDAO->markAsRead($msg->getIdMessage());
                // On met à jour l'objet pour la vue courante
                $msg->setEstLu(true);
            }
        }

        $template = $this->getTwig()->load('message_conversation.html.twig');
        echo $template->render([
            'page' => ['title' => 'Discussion avec ' . $contact->getPseudoUtilisateur()],
            'contact' => $contact,
            'messages' => $messages,
            'myEmail' => $myEmail
        ]);
    }
}