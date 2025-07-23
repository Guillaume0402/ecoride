<?php

namespace App\Db;

class Mysql
{
    
    private string $dbName;
    private string $dbUser;
    private string $dbPassword;
    private string $dbPort;
    private string $dbHost;

    private ?\PDO $pdo = null;
    private static ?self $_instance = null;
 
    private function __construct()
    {
        // Chargement de la configuration depuis le fichier .env.local
        $dbConf = parse_ini_file(APP_ROOT . "/" . APP_ENV);

        // Attribution des valeurs de configuration aux propriétés
        $this->dbHost = $dbConf['db_host'];
        $this->dbName = $dbConf['db_name'];
        $this->dbUser = $dbConf['db_user'];
        $this->dbPassword = $dbConf['db_password'];
        $this->dbPort = $dbConf['db_port'];
    }

    public static function getInstance(): self
    {
        // Création de l'instance si elle n'existe pas encore
        if (self::$_instance === null) {
            self::$_instance = new self();
        }
        return self::$_instance; 
    }

    public function getPDO():\PDO
    {
        // Création de la connexion seulement si elle n'existe pas encore (lazy loading)
        if (is_null($this->pdo)) {
            // Construction du DSN (Data Source Name) pour MySQL
            $dsn = "mysql:host={$this->dbHost};charset=utf8;dbname={$this->dbName};port={$this->dbPort}";

            // Création de la connexion PDO
            $this->pdo = new \PDO($dsn, $this->dbUser, $this->dbPassword);
        }
        return $this->pdo;
    }
}
