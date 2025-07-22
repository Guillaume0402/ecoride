<?php

namespace App\Db;

class Mysql
{
    
    private string $DB_NAME;
    private string $DB_USER;
    private string $DB_PASSWORD;
    private string $DB_PORT;
    private string $DB_HOST;
    private ?\PDO $pdo = null;
    private static ?self $_instance = null;

    private function __construct()
    {
        // Chargement de la configuration depuis le fichier .env.local
        $dbConf = parse_ini_file(APP_ROOT . "/" . APP_ENV);

        // Attribution des valeurs de configuration aux propriétés
        $this->DB_HOST = $dbConf['DB_HOST'];
        $this->DB_NAME = $dbConf['DB_NAME'];
        $this->DB_USER = $dbConf['DB_USER'];
        $this->DB_PASSWORD = $dbConf['DB_PASSWORD'];
        $this->DB_PORT = $dbConf['DB_PORT'];
    }

    public static function getInstance(): self
    {
        // Création de l'instance si elle n'existe pas encore
        if (self::$_instance === null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function getPDO(): \PDO
    {
        // Création de la connexion seulement si elle n'existe pas encore (lazy loading)
        if (is_null($this->pdo)) {
            // Construction du DSN (Data Source Name) pour MySQL
            $dsn = "mysql:host={$this->DB_HOST};charset=utf8;dbname={$this->DB_NAME};port={$this->DB_PORT}";

            // Création de la connexion PDO
            $this->pdo = new \PDO($dsn, $this->DB_USER, $this->DB_PASSWORD);
        }
        return $this->pdo;
    }
}
