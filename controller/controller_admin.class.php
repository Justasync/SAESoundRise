<?php

/**
 * @file controller_admin.class.php
 * @brief Fichier contenant le contrôleur d'administration.
 * 
 * Ce fichier gère toutes les fonctionnalités d'administration
 * de l'application Paaxio, notamment la gestion des utilisateurs.
 * 
 */

/**
 * @class ControllerAdmin
 * @brief Contrôleur dédié à la gestion de l'administration.
 * 
 * Cette classe gère les opérations d'administration telles que :
 * - Affichage du tableau de bord administrateur
 * - Suppression d'utilisateurs
 * - Modification d'utilisateurs
 * 
 * @extends Controller
 */
class ControllerAdmin extends Controller
{
    /**
     * @brief Constructeur du contrôleur admin.
     * 
     * @param \Twig\Environment $twig Environnement Twig pour le rendu des templates.
     * @param \Twig\Loader\FilesystemLoader $loader Chargeur de fichiers Twig.
     */
    public function __construct(\Twig\Environment $twig, \Twig\Loader\FilesystemLoader $loader)
    {
        parent::__construct($loader, $twig);
    }

    /**
     * @brief Affiche le tableau de bord de l'administrateur.
     * 
     * Récupère la liste de tous les utilisateurs et les affiche
     * dans le template du tableau de bord admin.
     * Nécessite le rôle Admin.
     * 
     * @return void
     */
    public function afficher()
    {
        // Vérification du rôle Admin
        $this->requireRole(RoleEnum::Admin);

        $pdo = Bd::getInstance()->getConnexion();
        $utilisateurDAO = new UtilisateurDAO($pdo);
        $utilisateurs = $utilisateurDAO->findAll();

        $successMessage = null;
        if (isset($_GET['success'])) {
            if ($_GET['success'] == 1) $successMessage = "L'utilisateur a été créé avec succès !";
            if ($_GET['success'] == 2) $successMessage = "L'utilisateur a été modifié avec succès !";
        }

        $restoreSuccess = null;
        $restoreError = null;
        if (isset($_GET['restore']) && $_GET['restore'] == 1) {
            $restoreSuccess = "La base de données a été restaurée avec succès.";
        }
        if (!empty($_GET['restore_error'])) {
            $restoreError = $_GET['restore_error'];
        }

        $availableBackups = $this->getAvailableBackups();

        $template = $this->getTwig()->load('admin_dashboard.html.twig');
        echo $template->render([
            'page' => ['title' => "Admin Dashboard", 'name' => "admin"],
            'session' => $_SESSION,
            'utilisateurs' => $utilisateurs,
            'success' => $successMessage,
            'restoreSuccess' => $restoreSuccess,
            'restoreError' => $restoreError,
            'availableBackups' => $availableBackups
        ]);
    }

    /**
     * @brief Restaure la base de données depuis un fichier SQL (.sql, .gz, .sql.gz).
     *
    * Nécessite le rôle Admin et une requête POST avec soit un fichier uploadé,
    * soit un fichier déjà présent dans le dossier backups.
     *
     * @return void
     */
    public function restaurer()
    {
        $this->requireRole(RoleEnum::Admin);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->show405();
            return;
        }

        try {
            $sqlContent = null;

            $backupName = trim($_POST['backup_name'] ?? '');
            if ($backupName !== '') {
                $backupPath = $this->resolveBackupPath($backupName);
                if ($backupPath === null || !is_file($backupPath)) {
                    throw new RuntimeException('Le fichier de sauvegarde sélectionné est introuvable.');
                }
                $sqlContent = $this->readSqlContentFromFile($backupPath);
            } elseif (isset($_FILES['backup_file']) && $_FILES['backup_file']['error'] === UPLOAD_ERR_OK) {
                $uploadedFile = $_FILES['backup_file'];
                $tmpPath = $uploadedFile['tmp_name'] ?? '';

                if (!is_uploaded_file($tmpPath)) {
                    throw new RuntimeException('Fichier de restauration invalide.');
                }

                $sqlContent = $this->readSqlContentFromFile($tmpPath, $uploadedFile['name'] ?? '');
            } else {
                throw new RuntimeException('Aucun fichier de sauvegarde sélectionné.');
            }

            $sqlContent = trim($sqlContent);
            if ($sqlContent === '') {
                throw new RuntimeException('Le fichier de restauration est vide.');
            }

            $pdo = $this->getPDO();
            $pdo->beginTransaction();
            $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');

            $statements = $this->splitSqlStatements($sqlContent);
            foreach ($statements as $statement) {
                $trimmedStatement = trim($statement);
                if ($trimmedStatement === '') {
                    continue;
                }
                $pdo->exec($trimmedStatement);
            }

            $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
            $pdo->commit();

            $this->redirectTo('admin', 'afficher', ['restore' => 1]);
        } catch (Throwable $e) {
            $pdo = $this->getPDO();
            if ($pdo instanceof PDO) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                try {
                    $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
                } catch (Throwable $ignored) {
                }
            }

            $this->redirectTo('admin', 'afficher', ['restore_error' => 'Restauration échouée : ' . $e->getMessage()]);
        }
    }

    /**
     * @brief Lit le contenu SQL d'un fichier backup (compressé ou non).
     *
     * @param string $path Chemin réel du fichier.
     * @param string|null $originalName Nom du fichier pour vérifier l'extension.
     * @return string
     */
    private function readSqlContentFromFile(string $path, ?string $originalName = null): string
    {
        $filename = strtolower($originalName ?? basename($path));
        $isSql = str_ends_with($filename, '.sql');
        $isGz = str_ends_with($filename, '.gz');

        if (!$isSql && !$isGz) {
            throw new RuntimeException('Format non supporté. Utilisez un fichier .sql, .gz ou .sql.gz');
        }

        $rawContent = file_get_contents($path);
        if ($rawContent === false) {
            throw new RuntimeException('Lecture du fichier impossible.');
        }

        if ($isGz) {
            $decoded = gzdecode($rawContent);
            if ($decoded === false) {
                throw new RuntimeException('Le fichier compressé est invalide ou corrompu.');
            }
            return $decoded;
        }

        return $rawContent;
    }

    /**
     * @brief Retourne le chemin du dossier des sauvegardes.
     *
     * @return string
     */
    private function getBackupsDirectory(): string
    {
        return realpath(__DIR__ . '/../') . '/backups';
    }

    /**
     * @brief Vérifie qu'un nom de fichier backup est autorisé.
     *
     * @param string $filename
     * @return bool
     */
    private function isAllowedBackupName(string $filename): bool
    {
        return (bool) preg_match('/^[a-zA-Z0-9._-]+\.(sql|gz)$/', $filename);
    }

    /**
     * @brief Résout et valide le chemin d'un fichier backup local.
     *
     * @param string $filename
     * @return string|null
     */
    private function resolveBackupPath(string $filename): ?string
    {
        $basename = basename($filename);
        if (!$this->isAllowedBackupName($basename)) {
            return null;
        }

        $directory = $this->getBackupsDirectory();
        $fullPath = $directory . '/' . $basename;
        $realPath = realpath($fullPath);

        if ($realPath === false) {
            return null;
        }

        $directoryRealPath = realpath($directory);
        if ($directoryRealPath === false || strpos($realPath, $directoryRealPath) !== 0) {
            return null;
        }

        return $realPath;
    }

    /**
     * @brief Liste les sauvegardes disponibles dans le dossier backups.
     *
     * @return array
     */
    private function getAvailableBackups(): array
    {
        $directory = $this->getBackupsDirectory();
        if (!is_dir($directory)) {
            return [];
        }

        $entries = scandir($directory);
        if ($entries === false) {
            return [];
        }

        $backups = [];
        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            if (!$this->isAllowedBackupName($entry)) {
                continue;
            }

            $resolvedPath = $this->resolveBackupPath($entry);
            if ($resolvedPath === null || !is_file($resolvedPath)) {
                continue;
            }

            $backups[] = [
                'name' => $entry,
                'size' => filesize($resolvedPath) ?: 0,
                'mtime' => filemtime($resolvedPath) ?: 0,
            ];
        }

        usort($backups, function ($a, $b) {
            return $b['mtime'] <=> $a['mtime'];
        });

        return $backups;
    }

    /**
     * @brief Découpe un script SQL en requêtes individuelles.
     *
     * @param string $sql Script SQL brut.
     * @return array Tableau de requêtes SQL.
     */
    private function splitSqlStatements(string $sql): array
    {
        $statements = [];
        $buffer = '';

        $inSingleQuote = false;
        $inDoubleQuote = false;
        $inBacktick = false;

        $length = strlen($sql);
        for ($i = 0; $i < $length; $i++) {
            $char = $sql[$i];
            $nextChar = ($i + 1 < $length) ? $sql[$i + 1] : '';
            $prevChar = ($i > 0) ? $sql[$i - 1] : '';

            if (!$inSingleQuote && !$inDoubleQuote && !$inBacktick) {
                if ($char === '-' && $nextChar === '-' && ($i === 0 || ctype_space($prevChar))) {
                    while ($i < $length && $sql[$i] !== "\n") {
                        $i++;
                    }
                    continue;
                }

                if ($char === '#') {
                    while ($i < $length && $sql[$i] !== "\n") {
                        $i++;
                    }
                    continue;
                }

                if ($char === '/' && $nextChar === '*') {
                    $i += 2;
                    while ($i + 1 < $length && !($sql[$i] === '*' && $sql[$i + 1] === '/')) {
                        $i++;
                    }
                    $i++;
                    continue;
                }
            }

            if ($char === "'" && !$inDoubleQuote && !$inBacktick && $prevChar !== '\\') {
                $inSingleQuote = !$inSingleQuote;
            } elseif ($char === '"' && !$inSingleQuote && !$inBacktick && $prevChar !== '\\') {
                $inDoubleQuote = !$inDoubleQuote;
            } elseif ($char === '`' && !$inSingleQuote && !$inDoubleQuote) {
                $inBacktick = !$inBacktick;
            }

            if ($char === ';' && !$inSingleQuote && !$inDoubleQuote && !$inBacktick) {
                $trimmed = trim($buffer);
                if ($trimmed !== '') {
                    $statements[] = $trimmed;
                }
                $buffer = '';
                continue;
            }

            $buffer .= $char;
        }

        $trimmed = trim($buffer);
        if ($trimmed !== '') {
            $statements[] = $trimmed;
        }

        return $statements;
    }

    /**
     * @brief Supprime un utilisateur spécifique.
     * 
     * Supprime l'utilisateur identifié par son ID (email) passé en paramètre GET.
     * Protection : un administrateur ne peut pas se supprimer lui-même.
     * Nécessite le rôle Admin.
     * 
     * @return void
     */
    public function supprimer()
    {
        // Vérification du rôle Admin
        $this->requireRole(RoleEnum::Admin);

        if (isset($_GET['id'])) {
            $pdo = Bd::getInstance()->getConnexion();
            $utilisateurDAO = new UtilisateurDAO($pdo);

            // Protection : ne pas se supprimer soi-même
            if (isset($_SESSION['user_email']) && $_GET['id'] == $_SESSION['user_email']) {
                // Redirection propre avec la méthode du contrôleur parent
                $this->redirectTo('admin', 'afficher');
                return;
            }

            $utilisateurDAO->delete($_GET['id']);
        }

        // Redirection propre après suppression
        $this->redirectTo('admin', 'afficher');
    }

    /**
     * @brief Consulte les détails d'un utilisateur.
     * 
     * Affiche les informations complètes d'un utilisateur spécifique
     * identifié par son email passé en paramètre GET.
     * Nécessite le rôle Admin.
     * 
     * @return void
     */
    public function consulter()
    {
        // Vérification du rôle Admin
        $this->requireRole(RoleEnum::Admin);

        if (!isset($_GET['id'])) {
            $this->redirectTo('admin', 'afficher');
            return;
        }

        $pdo = Bd::getInstance()->getConnexion();
        $utilisateurDAO = new UtilisateurDAO($pdo);

        $user = $utilisateurDAO->find($_GET['id']);

        if (!$user) {
            $this->redirectTo('admin', 'afficher');
            return;
        }

        $template = $this->getTwig()->load('admin_utilisateur_consulter.html.twig');
        echo $template->render([
            'page' => ['title' => "Consulter Utilisateur - " . $user->getPseudoUtilisateur(), 'name' => "admin"],
            'session' => $_SESSION,
            'user' => $user
        ]);
    }

    /**
     * @brief Modifie un utilisateur existant.
     * 
     * Permet à l'administrateur de modifier les informations d'un utilisateur :
     * - Pseudo
     * - Rôle
     * - Mot de passe (optionnel)
     * 
     * L'identifiant de l'utilisateur est récupéré via GET (id) ou POST (original_email).
     * Nécessite le rôle Admin.
     * 
     * @return void
     */
    public function modifier()
    {
        // Sécurité : Vérification manuelle du rôle Admin
        $this->requireRole(RoleEnum::Admin);

        $pdo = $this->getPDO();
        $utilisateurDAO = new UtilisateurDAO($pdo);
        $roleDao = new RoleDAO($pdo);

        $error = null;
        $user = null;

        // Récupération de l'identifiant (Email) via GET ou POST
        $emailTarget = $_GET['id'] ?? $_POST['original_email'] ?? null;

        if (!$emailTarget) {
            $this->redirectTo('admin', 'afficher');
            return;
        }

        // Recherche de l'utilisateur
        $user = $utilisateurDAO->find($emailTarget);
        if (!$user) {
            $this->redirectTo('admin', 'afficher');
            return;
        }

        // Traitement du formulaire (POST)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $pseudo = trim($_POST['pseudo'] ?? '');
            $roleType = $_POST['role'] ?? 'auditeur';
            $newPassword = $_POST['mdp'] ?? '';

            // Vérification si le pseudo est déjà pris (sauf si c'est le sien)
            if ($pseudo !== $user->getPseudoUtilisateur() && $utilisateurDAO->existsByPseudo($pseudo)) {
                $error = "Ce pseudo est déjà utilisé par un autre membre.";
            } else {
                try {
                    // Mise à jour des infos de base
                    $user->setPseudoUtilisateur($pseudo);
                    $user->setNomUtilisateur($pseudo);

                    // Mise à jour du Rôle
                    $newRole = $roleDao->findByType($roleType);
                    if ($newRole) {
                        $user->setRoleUtilisateur($newRole);
                    }

                    // Mise à jour du Mot de passe (Uniquement si rempli)
                    if (!empty($newPassword)) {
                        $user->setMotDePasseUtilisateur(password_hash($newPassword, PASSWORD_ARGON2ID));
                    }

                    // Sauvegarde en base de données
                    if ($utilisateurDAO->update($user)) {
                        // Redirection vers le dashboard avec message de succès (success=2)
                        $this->redirectTo('admin', 'afficher', ['success' => 2]);
                        return;
                    } else {
                        $error = "Erreur lors de la mise à jour.";
                    }
                } catch (Exception $e) {
                    $error = "Erreur système : " . $e->getMessage();
                }
            }
        }

        // Affichage du formulaire
        $template = $this->getTwig()->load('utilisateur_modifier.html.twig');
        echo $template->render([
            'page' => ['title' => 'Modifier Utilisateur'],
            'session' => $_SESSION,
            'user' => $user,
            'error' => $error
        ]);
    }
}
