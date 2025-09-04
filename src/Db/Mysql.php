<?php

namespace App\Db;

class Mysql
{

    // Paramètres de connexion lus depuis le fichier d'environnement (ex: .env.local)
    private string $dbName;
    private string $dbUser;
    private string $dbPassword;
    private string $dbPort;
    private string $dbHost;

    // Instance PDO réutilisée (connexion ouverte à la demande)
    private ?\PDO $pdo = null;
    // Implémentation du pattern Singleton (instance unique)
    private static ?self $_instance = null;

    private function __construct()
    {
        // Charge la configuration depuis le fichier défini par APP_ENV (dans APP_ROOT)
        // Exemple: APP_ENV = ".env.local" => lecture de APP_ROOT/.env.local
        $dbConf = parse_ini_file(APP_ROOT . "/" . APP_ENV);

        // Hydrate les propriétés de connexion à partir des clés du .env
        $this->dbHost = $dbConf['DB_HOST'];
        $this->dbName = $dbConf['DB_NAME'];
        $this->dbUser = $dbConf['DB_USER'];
        $this->dbPassword = $dbConf['DB_PASSWORD'];
        $this->dbPort = $dbConf['DB_PORT'];
    }

    public static function getInstance(): self
    {

        // Crée l'instance au premier appel uniquement (Singleton)
        if (self::$_instance === null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function getPDO(): \PDO
    {
        // Ouvre la connexion seulement si elle n'existe pas encore (lazy)
        if (is_null($this->pdo)) {
            // DSN MySQL: inclut l'hôte, le charset, la base et le port
            $dsn = "mysql:host={$this->dbHost};charset=utf8;dbname={$this->dbName};port={$this->dbPort}";
            try {
                // Création de l'objet PDO avec identifiants du .env
                // Astuce (optionnel): configurer les attributs PDO (ERRMODE_EXCEPTION, utf8mb4, etc.)
                $this->pdo = new \PDO($dsn, $this->dbUser, $this->dbPassword);
            } catch (\PDOException $e) {

                // Remonte l'erreur afin qu'elle soit gérée par le contrôleur/gestionnaire global
                throw $e; // (optionnel : à commenter si tu veux une erreur contrôlée)
            }
        }
        return $this->pdo;
    }
}
