<?php

class bd
{
    private static ?bd $instance = null;
    private ?PDO $pdo;

    private function __construct()
    {
        try {
            $this->pdo = new PDO('mysql:host=' . Constantes::getInstance()->getConfig()['db']['host'] . ';port=' . Constantes::getInstance()->getConfig()['db']['port'] . ';dbname=' . Constantes::getInstance()->getConfig()['db']['dbname'], Constantes::getInstance()->getConfig()['db']['username'], Constantes::getInstance()->getConfig()['db']['password']);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die('Connexion a la BD échouée: ' . $e->getMessage());
        }
    }

    public static function getInstance(): bd
    {
        if (self::$instance == null) {
            self::$instance = new bd();
        }
        return self::$instance;
    }

    public function getConnexion(): pdo
    {
        return $this->pdo;
    }

    private function __clone() {}

    public function __wakeup()
    {
        throw new Exception("Un singleton ne doit pas être deserialisé.");
    }
}
