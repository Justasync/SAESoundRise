<?php

// Load environment variables from .env file if it exists
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
            putenv(trim($key) . '=' . trim($value));
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: newsletter.html', true, 303);
    exit;
}

$email = isset($_POST['email']) ? filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) : false;
$captchaResponse = $_POST['g-recaptcha-response'] ?? '';

if (!$email) {
    http_response_code(400);
    echo 'Adresse e-mail invalide';
    exit;
}

if (!$captchaResponse) {
    http_response_code(400);
    echo 'Veuillez valider le reCAPTCHA';
    exit;
}

// cree

$recaptchaSecret = getenv('RECAPTCHA_SECRET') ?? $_ENV['RECAPTCHA_SECRET'] ?? '';

if ($recaptchaSecret == '') {
    http_response_code(500);
    echo 'Erreur de configuration : la clé secrète reCAPTCHA est manquante';
    exit;
}

$verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';
$response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$recaptchaSecret&response=$captchaResponse");
$response_keys = json_decode($response, true);

if (intval($response_keys["success"]) !== 1) {
    http_response_code(400);
    echo 'Erreur de validation reCAPTCHA.';
    exit();
}

// DB requets
// INSERT INTO Newsletter (email) VALUES (:email);

header('Content-Type: text/plain; charset=UTF-8');
echo 'Merci! Votre inscription a bien été enregistrée.';
exit;
