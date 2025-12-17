<?php

/**
 * @file validator.class.php
 * @brief Classe Validator pour la validation de données selon des règles prédéfinies.
 *
 * Ce fichier contient la définition de la classe Validator qui permet de valider
 * des données (typiquement issues de formulaires) selon des règles de validation
 * souples et personnalisables. Les règles gèrent la présence, le format, la longueur,
 * la sécurité de champs tels que : e-mail, pseudo, mot de passe, etc.
 *
 * Exemple d'utilisation (formulaire d'inscription) :
 * @code{.php}
 * $rules = [
 *     'email' => ['required' => true, 'email' => true],
 *     'pseudo' => ['required' => true, 'pseudo' => true, 'min' => 3, 'max' => 50],
 *     'motDePasse' => ['required' => true, 'password_strong' => true],
 * ];
 * $validator = new Validator($rules);
 * if ($validator->valider($_POST)) {
 *     // Données valides
 * } else {
 *     $errors = $validator->getMessagesErreurs();
 * }
 * @endcode
 *
 * @see Validator
 */

/**
 * @class Validator
 * @brief Permet de valider des données selon des règles spécifiées.
 */
class Validator
{
    /**
     * @var array Les règles de validation à vérifier.
     */
    private array $regles;

    /**
     * @var array Les messages d'erreurs générés lors de la validation.
     */
    private array $messagesErreurs = [];

    /**
     * Expression régulière pour valider la force d'un mot de passe :
     * Au moins 8 caractères, 1 majuscule, 1 chiffre, 1 symbole.
     */
    private const PASSWORD_STRONG_REGEX = '/^(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#$%^&*()_+\-=\[\]{}|;:\'",.<>?\/~`]).{8,}$/';

    /**
     * Expression régulière pour valider un pseudo : uniquement lettres, chiffres et underscores.
     */
    private const PSEUDO_REGEX = '/^[a-zA-Z0-9_]+$/';

    /**
     * Constructeur de la classe Validator.
     *
     * @param array $regles Un tableau associatif définissant les règles de validation pour chaque champ.
     */
    public function __construct(array $regles)
    {
        $this->regles = $regles;
    }

    /**
     * Valide les données fournies par rapport aux règles de validation définies.
     *
     * @param array $donnees Un tableau associatif contenant les données du formulaire.
     * @return bool Retourne true si toutes les validations sont réussies, false sinon.
     */
    public function valider(array $donnees): bool
    {
        $valide = true;
        $this->messagesErreurs = []; // Réinitialise les erreurs à chaque validation

        foreach ($this->regles as $champ => $reglesChamp) {
            $valeur = $donnees[$champ] ?? null;

            if (!$this->validerChamp($champ, $valeur, $reglesChamp)) {
                $valide = false;
            }
        }

        return $valide;
    }

    /**
     * Valide un champ spécifique selon ses règles.
     *
     * @param string $champ Le nom du champ à valider.
     * @param mixed $valeur La valeur du champ à valider.
     * @param array $regles Les règles de validation pour ce champ.
     * @return bool Retourne true si toutes les règles sont respectées, false sinon.
     */
    private function validerChamp(string $champ, mixed $valeur, array $regles): bool
    {
        $estValide = true;

        // 1. Vérification préalable de la règle "obligatoire".
        if (isset($regles['obligatoire']) && $regles['obligatoire'] && empty($valeur)) {
            $this->messagesErreurs[] = "Le champ $champ est obligatoire.";
            return false; // Arrête si champ obligatoire vide
        }

        // 2. Si le champ est vide et non obligatoire, aucune autre validation nécessaire.
        if (empty($valeur) && (!isset($regles['obligatoire']) || !$regles['obligatoire'])) {
            return true;
        }

        // 3. Application des autres règles de validation.
        foreach ($regles as $regle => $parametre) {
            switch ($regle) {
                case 'type':
                    if ($parametre === 'string' && !is_string($valeur)) {
                        $this->messagesErreurs[] = "Le champ $champ doit être une chaîne de caractères.";
                        $estValide = false;
                    } elseif ($parametre === 'integer' && !filter_var($valeur, FILTER_VALIDATE_INT)) {
                        $this->messagesErreurs[] = "Le champ $champ doit être un nombre entier.";
                        $estValide = false;
                    } elseif ($parametre === 'numeric' && !is_numeric($valeur)) {
                        $this->messagesErreurs[] = "Le champ $champ doit être une valeur numérique.";
                        $estValide = false;
                    }
                    break;
                case 'longueur_min':
                    if (strlen($valeur) < $parametre) {
                        $this->messagesErreurs[] = "Le champ $champ doit comporter au moins $parametre caractères.";
                        $estValide = false;
                    }
                    break;
                case 'longueur_max':
                    if (strlen($valeur) > $parametre) {
                        $this->messagesErreurs[] = "Le champ $champ ne doit pas dépasser $parametre caractères.";
                        $estValide = false;
                    }
                    break;
                case 'longueur_exacte':
                    if (strlen($valeur) !== $parametre) {
                        $this->messagesErreurs[] = "Le champ $champ doit comporter exactement $parametre caractères.";
                        $estValide = false;
                    }
                    break;
                case 'format':
                    if (is_string($parametre) && !preg_match($parametre, $valeur)) {
                        $this->messagesErreurs[] = "Le format du champ $champ est invalide.";
                        $estValide = false;
                    } elseif ($parametre === FILTER_VALIDATE_EMAIL && !filter_var($valeur, FILTER_VALIDATE_EMAIL)) {
                        $this->messagesErreurs[] = "L'adresse email est invalide.";
                        $estValide = false;
                    } elseif ($parametre === FILTER_VALIDATE_URL && !filter_var($valeur, FILTER_VALIDATE_URL)) {
                        $this->messagesErreurs[] = "L'URL du site web est invalide.";
                        $estValide = false;
                    }
                    break;
                case 'plage_min':
                    if ($valeur < $parametre) {
                        $this->messagesErreurs[] = "La valeur de $champ doit être au minimum $parametre.";
                        $estValide = false;
                    }
                    break;
                case 'plage_max':
                    if ($valeur > $parametre) {
                        $this->messagesErreurs[] = "La valeur de $champ ne doit pas dépasser $parametre.";
                        $estValide = false;
                    }
                    break;
                case 'mot_de_passe_fort':
                    if ($parametre && !preg_match(self::PASSWORD_STRONG_REGEX, $valeur)) {
                        $this->messagesErreurs[] = "Le mot de passe doit contenir au moins 8 caractères, une majuscule, un chiffre et un symbole.";
                        $estValide = false;
                    }
                    break;
                case 'pseudo_format':
                    if ($parametre && !preg_match(self::PSEUDO_REGEX, $valeur)) {
                        $this->messagesErreurs[] = "Le champ $champ ne doit contenir que des lettres, des chiffres et des underscores, sans espaces.";
                        $estValide = false;
                    }
                    break;
                case 'age_minimum':
                    // Vérifie que l'utilisateur a au moins $parametre années.
                    $birthDate = \DateTime::createFromFormat('Y-m-d', $valeur);
                    if ($birthDate) {
                        $aujourdhui = new \DateTimeImmutable();
                        $dateMinimum = $aujourdhui->modify("-{$parametre} years");
                        if ($birthDate > $dateMinimum) {
                            $this->messagesErreurs[] = "Vous devez avoir au moins $parametre ans.";
                            $estValide = false;
                        }
                    } else {
                        $this->messagesErreurs[] = "La date de naissance fournie est invalide.";
                        $estValide = false;
                    }
                    break;
            }
        }

        return $estValide;
    }

    /**
     * Retourne les messages d'erreur générés lors de la validation.
     *
     * @return array Un tableau contenant les messages d'erreur pour chaque champ non valide.
     */
    public function getMessagesErreurs(): array
    {
        return $this->messagesErreurs;
    }
}
