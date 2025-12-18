<?php
/**
 * @file modeles/constantes.class.php
 * @brief Classe Singleton pour charger la configuration depuis un fichier JSON
 */

class Constantes
{
    /**
     * @var Constantes|null $instance Instance singleton de la classe Constantes.
     */
    private static ?Constantes $instance = null;

    /**
     * @var array|mixed $config Les paramètres de configuration chargés depuis config.json.
     */
    private $config;

    /**
     * Constructeur privé pour empêcher l'instanciation directe.
     * Charge la configuration depuis le fichier JSON.
     * @throws Exception Si le fichier de configuration est introuvable ou invalide.
     */
    private function __construct()
    {
        try {
            $json = file_get_contents(__DIR__ . "/../config/config.json");
            $this->config = json_decode($json, true);
        } catch (Exception $e) {
            die('Récupération du fichier de configuration échouer: ' . $e->getMessage());
        }
    }

    /**
     * Retourne l'instance unique de la classe Constantes (pattern Singleton).
     * @return Constantes L'instance unique contenant la configuration.
     */
    public static function getInstance(): Constantes
    {
        if (self::$instance == null) {
            self::$instance = new Constantes();
        }
        return self::$instance;
    }

    /**
     * Retourne le tableau de configuration complet.
     * @return array La configuration de l'application.
     */
    public function getConfig(): array
    {
        return $this->config;
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

