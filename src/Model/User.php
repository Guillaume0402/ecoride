<?php

class User {
    private $conn;
    private $table = "users"; // à adapter

    public function __construct($db) {
        $this->conn = $db;
    }

    public function findByEmail($email) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch();
    }

    // add verifyPassword($input, $hash) etc.
}
