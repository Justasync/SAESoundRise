<?php

/**
 * @file modeles/bd.class.php
 * @brief Classe Singleton pour la gestion des connexions à la base de données (multi-rôles)
 *
 * Trois connexions distinctes selon le rôle :
 * - site_user : utilisateur public (connexion par défaut pour les visiteurs)
 * - site_administrateur : SELECT, INSERT, UPDATE, DELETE (utilisé après login admin)
 * - db_administrateur : ALL PRIVILEGES (scripts de maintenance, backup, populate)
 */

class bd
{
    public const ROLE_SITE_USER = 'site_user';
    public const ROLE_SITE_ADMINISTRATEUR = 'site_administrateur';
    public const ROLE_DB_ADMINISTRATEUR = 'db_administrateur';

    /**
     * @var bd|null $instance Instance singleton de la classe bd.
     */
    private static ?bd $instance = null;

    /**
     * @var array<string, PDO> Connexions PDO par rôle (lazy-initialisées).
     */
    private array $connections = [];

    /**
     * @var array Configuration DB partagée (host, dbname, port).
     */
    private array $dbConfig;

    /**
     * Constructeur privé pour empêcher l'instanciation directe.
     * Charge la configuration DB sans ouvrir de connexion.
     */
    private function __construct()
    {
        $config = Constantes::getInstance()->getConfig()['db'];
        $this->dbConfig = [
            'host' => $config['host'],
            'dbname' => $config['dbname'],
            'port' => (int) ($config['port'] ?? 3306),
        ];
    }

    /**
     * Retourne l'instance unique de la classe bd (pattern Singleton).
     * @return bd L'instance unique.
     */
    public static function getInstance(): bd
    {
        if (self::$instance === null) {
            self::$instance = new bd();
        }
        return self::$instance;
    }

    /**
     * Construit le DSN MySQL à partir de la config partagée.
     */
    private function getDsn(): string
    {
        return sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            $this->dbConfig['host'],
            $this->dbConfig['port'],
            $this->dbConfig['dbname']
        );
    }

    /**
     * Crée et retourne une connexion PDO pour le rôle donné (lazy).
     * @param string $role Un parmi : site_user, site_administrateur, db_administrateur
     * @return PDO
     * @throws PDOException En cas d'échec de connexion.
     */
    private function createConnection(string $role): PDO
    {
        $config = Constantes::getInstance()->getConfig()['db'];
        if (!isset($config[$role]['username']) || !isset($config[$role]['password'])) {
            throw new PDOException("Configuration DB manquante pour le rôle : {$role}");
        }
        $username = $config[$role]['username'];
        $password = $config[$role]['password'];
        $pdo = new PDO($this->getDsn(), $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        $this->connections[$role] = $pdo;
        return $pdo;
    }

    /**
     * Retourne la connexion PDO pour le rôle demandé.
     * Par défaut : site_user (utilisateur public).
     *
     * @param string $role Un parmi : site_user, site_administrateur, db_administrateur
     * @return PDO L'instance PDO pour exécuter des requêtes SQL.
     */
    public function getConnexion(string $role = self::ROLE_SITE_USER): PDO
    {
        if (!isset($this->connections[$role])) {
            $this->createConnection($role);
        }
        return $this->connections[$role];
    }

    /**
     * Empêche le clonage du singleton.
     */
    private function __clone() {}

    /**
     * Empêche la désérialisation du singleton.
     * @throws Exception
     */
    public function __wakeup()
    {
        throw new Exception("Un singleton ne doit pas être deserialisé.");
    }
}
