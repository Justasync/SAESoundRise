<?php
/**
 * @file modeles/bd.class.php
 * @brief Classe Singleton pour la gestion de la connexion à la base de données
 */

class bd
{
    /**
     * @var bd|null $instance Instance singleton de la classe bd.
     */
    private static ?bd $instance = null;

    /**
     * @var PDO|null $pdo L'instance PDO pour la connexion à la base de données.
     */
    private ?PDO $pdo;

    /**
     * Constructeur privé pour empêcher l'instanciation directe.
     * Crée une connexion PDO à la base de données via la configuration stockée.
     * @throws PDOException En cas d'échec de connexion à la base de données.
     */
    private function __construct()
    {
        try {
            $this->pdo = new PDO('mysql:host=' . Constantes::getInstance()->getConfig()['db']['host'] . ';port=' . Constantes::getInstance()->getConfig()['db']['port'] . ';dbname=' . Constantes::getInstance()->getConfig()['db']['dbname'], Constantes::getInstance()->getConfig()['db']['username'], Constantes::getInstance()->getConfig()['db']['password']);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die('Connexion a la BD échouée: ' . $e->getMessage());
        }
    }

    /**
     * Retourne l'instance unique de la classe bd (pattern Singleton).
     * @return bd L'instance unique de la connexion à la base de données.
     */
    public static function getInstance(): bd
    {
        if (self::$instance == null) {
            self::$instance = new bd();
        }
        return self::$instance;
    }

    /**
     * Retourne l'instance PDO de la connexion à la base de données.
     * @return PDO L'instance PDO pour exécuter des requêtes SQL.
     */
    public function getConnexion(): pdo
    {
        return $this->pdo;
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
