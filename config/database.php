<?php
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $port;
    public $conn;

    public function __construct() {
        $this->host     = getenv('DB_HOST') ?: 'db';
        $this->db_name  = getenv('DB_NAME') ?: 'ecoride';
        $this->username = getenv('DB_USER') ?: 'ecoride_user';
        $this->password = getenv('DB_PASSWORD') ?: 'ecoride_password';
        $this->port     = getenv('DB_PORT') ?: '3306';
    }

    public function getConnection() {
        $this->conn = null;

        try {
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db_name};charset=utf8";
            $this->conn = new PDO($dsn, $this->username, $this->password, [
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'",
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
        } catch (PDOException $exception) {
            if (getenv('APP_DEBUG') === 'true') {
                echo "Erreur de connexion : " . $exception->getMessage();
            } else {
                echo "Erreur de connexion à la base de données.";
            }
        }

        return $this->conn;
    }

    public function disconnect() {
        $this->conn = null;
    }
}
