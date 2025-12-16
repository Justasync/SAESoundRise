<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * Classe de gestion des emails pour Paaxio
 * Utilise PHPMailer pour l'envoi d'emails via SMTP
 */
class Email
{
    private array $mailConfig;
    private \Twig\Environment $twig;

    /**
     * Constructeur de la classe Email
     * @param \Twig\Environment $twig Instance Twig pour le rendu des templates
     */
    public function __construct(\Twig\Environment $twig)
    {
        $this->twig = $twig;
        $this->loadMailConfig();
    }

    /**
     * Charge la configuration email depuis le fichier config.json
     * @throws Exception Si le fichier de configuration est introuvable ou invalide
     */
    private function loadMailConfig(): void
    {
        $configPath = __DIR__ . '/../config/config.json';
        if (!file_exists($configPath)) {
            throw new Exception('Fichier de configuration introuvable.');
        }

        $config = json_decode(file_get_contents($configPath), true);
        if (!isset($config['mail'])) {
            throw new Exception('Configuration email manquante dans config.json');
        }

        $this->mailConfig = $config['mail'];
    }

    /**
     * Crée et configure une instance PHPMailer
     * @return PHPMailer Instance configurée
     */
    private function createMailer(): PHPMailer
    {
        $mail = new PHPMailer(true);

        // Configuration du serveur SMTP
        $mail->isSMTP();
        $mail->Host = $this->mailConfig['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $this->mailConfig['username'];
        $mail->Password = $this->mailConfig['password'];
        $mail->Port = $this->mailConfig['port'];

        // Configuration du chiffrement
        $encryption = $this->mailConfig['encryption'] ?? 'tls';
        if ($encryption === 'tls') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        } elseif ($encryption === 'ssl') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        }

        // Configuration de l'expéditeur
        $mail->setFrom(
            $this->mailConfig['from_email'],
            $this->mailConfig['from_name']
        );

        // Configuration du charset
        $mail->CharSet = 'UTF-8';
        $mail->isHTML(true);

        return $mail;
    }

    /**
     * Envoie un email générique
     * @param string $to Adresse email du destinataire
     * @param string $toName Nom du destinataire
     * @param string $subject Sujet de l'email
     * @param string $htmlBody Corps HTML de l'email
     * @param string|null $textBody Corps texte brut (optionnel)
     * @return bool True si l'envoi a réussi
     * @throws Exception En cas d'erreur d'envoi
     */
    public function sendEmail(string $to, string $toName, string $subject, string $htmlBody, ?string $textBody = null): bool
    {
        try {
            $mail = $this->createMailer();
            $mail->addAddress($to, $toName);
            $mail->Subject = $subject;
            $mail->Body = $htmlBody;
            $mail->AltBody = $textBody ?? strip_tags($htmlBody);

            return $mail->send();
        } catch (Exception $e) {
            error_log("Erreur d'envoi d'email: " . $e->getMessage());
            throw new Exception("Impossible d'envoyer l'email: " . $e->getMessage());
        }
    }

    /**
     * Envoie un email de bienvenue à un nouvel utilisateur
     * @param string $email Adresse email de l'utilisateur
     * @param string $pseudo Pseudo de l'utilisateur
     * @param string $type Type de compte (artiste, auditeur, producteur)
     * @return bool True si l'envoi a réussi
     */
    public function sendWelcomeEmail(string $email, string $pseudo, string $type): bool
    {
        $subject = "Bienvenue sur Paaxio, $pseudo !";

        $htmlBody = $this->twig->render('emails/welcome.html.twig', [
            'pseudo' => $pseudo,
            'email' => $email,
            'type' => $type,
            'site_url' => $this->getSiteUrl()
        ]);

        $textBody = "Bienvenue sur Paaxio, $pseudo !\n\n";
        $textBody .= "Votre compte $type a été créé avec succès.\n";
        $textBody .= "Vous pouvez dès maintenant vous connecter et profiter de toutes les fonctionnalités de Paaxio.\n\n";
        $textBody .= "À bientôt sur Paaxio !";

        return $this->sendEmail($email, $pseudo, $subject, $htmlBody, $textBody);
    }

    /**
     * Envoie un email de confirmation d'inscription
     * @param string $email Adresse email de l'utilisateur
     * @param string $pseudo Pseudo de l'utilisateur
     * @param string $confirmationToken Token de confirmation
     * @return bool True si l'envoi a réussi
     */
    public function sendConfirmationEmail(string $email, string $pseudo, string $confirmationToken): bool
    {
        $subject = "Confirmez votre inscription sur Paaxio";

        $confirmationUrl = $this->getSiteUrl() . "/?controller=utilisateur&method=confirmer&token=" . urlencode($confirmationToken);

        $htmlBody = $this->twig->render('emails/confirmation.html.twig', [
            'pseudo' => $pseudo,
            'confirmation_url' => $confirmationUrl,
            'site_url' => $this->getSiteUrl()
        ]);

        $textBody = "Bonjour $pseudo,\n\n";
        $textBody .= "Merci de vous être inscrit sur Paaxio !\n\n";
        $textBody .= "Pour confirmer votre inscription, veuillez cliquer sur le lien suivant :\n";
        $textBody .= "$confirmationUrl\n\n";
        $textBody .= "Ce lien est valable 24 heures.\n\n";
        $textBody .= "Si vous n'avez pas créé de compte sur Paaxio, ignorez cet email.";

        return $this->sendEmail($email, $pseudo, $subject, $htmlBody, $textBody);
    }

    /**
     * Envoie un email de réinitialisation de mot de passe
     * @param string $email Adresse email de l'utilisateur
     * @param string $pseudo Pseudo de l'utilisateur
     * @param string $resetToken Token de réinitialisation
     * @return bool True si l'envoi a réussi
     */
    public function sendPasswordResetEmail(string $email, string $pseudo, string $resetToken): bool
    {
        $subject = "Réinitialisation de votre mot de passe Paaxio";

        $resetUrl = $this->getSiteUrl() . "/?controller=utilisateur&method=resetPassword&token=" . urlencode($resetToken);

        $htmlBody = $this->twig->render('emails/password_reset.html.twig', [
            'pseudo' => $pseudo,
            'reset_url' => $resetUrl,
            'site_url' => $this->getSiteUrl()
        ]);

        $textBody = "Bonjour $pseudo,\n\n";
        $textBody .= "Vous avez demandé la réinitialisation de votre mot de passe sur Paaxio.\n\n";
        $textBody .= "Pour réinitialiser votre mot de passe, cliquez sur le lien suivant :\n";
        $textBody .= "$resetUrl\n\n";
        $textBody .= "Ce lien est valable 1 heure.\n\n";
        $textBody .= "Si vous n'avez pas demandé cette réinitialisation, ignorez cet email.";

        return $this->sendEmail($email, $pseudo, $subject, $htmlBody, $textBody);
    }

    /**
     * Envoie un email de newsletter
     * @param string $email Adresse email du destinataire
     * @param string $subject Sujet de la newsletter
     * @param string $content Contenu HTML de la newsletter
     * @param string|null $unsubscribeToken Token de désinscription
     * @return bool True si l'envoi a réussi
     */
    public function sendNewsletterEmail(string $email, string $subject, string $content, ?string $unsubscribeToken = null): bool
    {
        $unsubscribeUrl = $unsubscribeToken
            ? $this->getSiteUrl() . "/?controller=newsletter&method=desinscrire&token=" . urlencode($unsubscribeToken)
            : null;

        $htmlBody = $this->twig->render('emails/newsletter.html.twig', [
            'content' => $content,
            'unsubscribe_url' => $unsubscribeUrl,
            'site_url' => $this->getSiteUrl()
        ]);

        $textBody = strip_tags($content);
        if ($unsubscribeUrl) {
            $textBody .= "\n\n---\nPour vous désinscrire : $unsubscribeUrl";
        }

        return $this->sendEmail($email, '', $subject, $htmlBody, $textBody);
    }

    /**
     * Envoie un email de notification (nouvel album, nouveau follower, etc.)
     * @param string $email Adresse email du destinataire
     * @param string $pseudo Pseudo du destinataire
     * @param string $type Type de notification
     * @param array $data Données additionnelles pour la notification
     * @return bool True si l'envoi a réussi
     */
    public function sendNotificationEmail(string $email, string $pseudo, string $type, array $data = []): bool
    {
        $subjects = [
            'new_album' => 'Nouvel album disponible !',
            'new_follower' => 'Vous avez un nouveau follower !',
            'new_like' => 'Quelqu\'un a aimé votre musique !',
            'battle_invite' => 'Invitation à une battle musicale !',
            'battle_result' => 'Résultats de la battle musicale'
        ];

        $subject = $subjects[$type] ?? 'Notification Paaxio';

        $htmlBody = $this->twig->render("emails/notification_$type.html.twig", array_merge([
            'pseudo' => $pseudo,
            'site_url' => $this->getSiteUrl()
        ], $data));

        return $this->sendEmail($email, $pseudo, $subject, $htmlBody);
    }

    /**
     * Envoie un email de contact (depuis le formulaire de contact)
     * @param string $fromEmail Email de l'expéditeur
     * @param string $fromName Nom de l'expéditeur
     * @param string $subject Sujet du message
     * @param string $message Message
     * @return bool True si l'envoi a réussi
     */
    public function sendContactEmail(string $fromEmail, string $fromName, string $subject, string $message): bool
    {
        $contactEmail = $this->mailConfig['from_email']; // Email de contact de Paaxio

        $htmlBody = $this->twig->render('emails/contact.html.twig', [
            'from_email' => $fromEmail,
            'from_name' => $fromName,
            'subject' => $subject,
            'message' => $message,
            'site_url' => $this->getSiteUrl()
        ]);

        $textBody = "Message de contact reçu\n\n";
        $textBody .= "De: $fromName <$fromEmail>\n";
        $textBody .= "Sujet: $subject\n\n";
        $textBody .= "Message:\n$message";

        try {
            $mail = $this->createMailer();
            $mail->addAddress($contactEmail, 'Paaxio Contact');
            $mail->addReplyTo($fromEmail, $fromName);
            $mail->Subject = "[Contact] $subject";
            $mail->Body = $htmlBody;
            $mail->AltBody = $textBody;

            return $mail->send();
        } catch (Exception $e) {
            error_log("Erreur d'envoi d'email de contact: " . $e->getMessage());
            throw new Exception("Impossible d'envoyer le message de contact: " . $e->getMessage());
        }
    }

    /**
     * Envoie un email en masse à plusieurs destinataires (pour la newsletter)
     * @param array $recipients Tableau d'emails des destinataires
     * @param string $subject Sujet de l'email
     * @param string $content Contenu HTML
     * @return array Résultats de l'envoi ['success' => int, 'failed' => int, 'errors' => array]
     */
    public function sendBulkEmail(array $recipients, string $subject, string $content): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => []
        ];

        foreach ($recipients as $recipient) {
            try {
                $email = is_array($recipient) ? $recipient['email'] : $recipient;
                $token = is_array($recipient) ? ($recipient['token'] ?? null) : null;

                if ($this->sendNewsletterEmail($email, $subject, $content, $token)) {
                    $results['success']++;
                } else {
                    $results['failed']++;
                    $results['errors'][] = "Échec de l'envoi à $email";
                }

                // Petite pause pour éviter de surcharger le serveur SMTP
                usleep(100000); // 100ms
            } catch (Exception $e) {
                $results['failed']++;
                $results['errors'][] = "Erreur pour $email: " . $e->getMessage();
            }
        }

        return $results;
    }

    /**
     * Récupère l'URL du site
     * @return string URL de base du site
     */
    private function getSiteUrl(): string
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return "$protocol://$host";
    }

    /**
     * Génère un token sécurisé
     * @param int $length Longueur du token
     * @return string Token généré
     */
    public static function generateToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }

    /**
     * Teste la configuration SMTP
     * @return array Résultat du test
     */
    public function testSmtpConnection(): array
    {
        try {
            $mail = $this->createMailer();
            $mail->SMTPDebug = SMTP::DEBUG_CONNECTION;

            ob_start();
            $mail->smtpConnect();
            $mail->smtpClose();
            $debug = ob_get_clean();

            return [
                'success' => true,
                'message' => 'Connexion SMTP réussie',
                'debug' => $debug
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Échec de la connexion SMTP: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Fonction de test simple de la classe Email avec un nom et un email factices.
     * Cette méthode peut être appelée pour vérifier l'envoi d'un email.
     * @return array Informations sur le résultat du test
     */
    public function testEmail(): array
    {
        $fakeName = "Angel Ramirez";
        $fakeEmail = "contact@angelbatalla.com";
        $fakeType = "auditeur";

        try {
            $result = $this->sendWelcomeEmail($fakeEmail, $fakeName, $fakeType);
            if ($result) {
                return [
                    'success' => true,
                    'message' => "L'email de bienvenue a bien été envoyé à $fakeEmail."
                ];
            } else {
                return [
                    'success' => false,
                    'message' => "L'envoi de l'email de bienvenue a échoué pour $fakeEmail."
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => "Erreur lors de l'envoi de l'email : " . $e->getMessage()
            ];
        }
    }
}
