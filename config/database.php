<?php
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;    
    public $conn;

    public function __construct() {
        $this->host     = 'db';
        $this->db_name  = 'ecoride';
        $this->username = 'ecoride_user';
        $this->password = 'ecoride_password';
        
    }

    public function getConnection() {
        $this->conn = null;

        try {
            $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset=utf8";
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
