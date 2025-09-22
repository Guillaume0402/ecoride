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
        // Lis depuis $_ENV, sinon $_SERVER, sinon getenv(), avec valeurs par défaut
        $env = fn(string $key, ?string $def = null) =>
        $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key) ?: $def;

        $this->dbHost     = $env('DB_HOST', '127.0.0.1');
        $this->dbName     = $env('DB_NAME', 'ecoride');
        $this->dbUser     = $env('DB_USER', 'root');
        $this->dbPassword = $env('DB_PASSWORD', '');
        $this->dbPort     = $env('DB_PORT', '3306');
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
        if ($this->pdo === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                $this->dbHost,
                $this->dbPort,
                $this->dbName
            );
            try {
                $this->pdo = new \PDO($dsn, $this->dbUser, $this->dbPassword, [
                    \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                ]);
            } catch (\PDOException $e) {
                // En dev : remonter l'erreur ; en prod tu pourrais afficher un message neutre
                throw $e;
            }
        }
        return $this->pdo;
    }
}
