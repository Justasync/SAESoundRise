<?php
require_once 'include.php';

$errors = [];
$success = null;
$submittedEmail = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedEmail = trim($_POST['email'] ?? '');

    if ($submittedEmail === '') {
        $errors[] = "Veuillez renseigner votre adresse e-mail.";
    } elseif (!filter_var($submittedEmail, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'adresse e-mail fournie n'est pas valide.";
    } else {
        try {
            $pdo = bd::getInstance()->getConnexion();
            $stmt = $pdo->prepare('INSERT INTO newsletter (email) VALUES (:email)');
            $stmt->bindParam(':email', $submittedEmail, PDO::PARAM_STR);
            $stmt->execute();
            $success = "Merci! Votre inscription à la newsletter est bien prise en compte.";
            $submittedEmail = null;
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $errors[] = "Cette adresse e-mail est déjà inscrite à la newsletter.";
            } else {
                $errors[] = "Une erreur est survenue lors de l'inscription. Merci de réessayer plus tard.";
            }
        }
    }
}

$template = $twig->load('newsletter.html.twig');
echo $template->render([
    "page" => [
        'title' => "Newsletter",
        'name' => "newsletter",
        'description' => "Newsletter de Paaxio"
    ],
    "form" => [
        'email' => $submittedEmail
    ],
    "errors" => $errors,
    "success" => $success
]);
