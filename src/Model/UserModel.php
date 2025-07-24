<?php


namespace App\Model;

use App\Entity\User;
use App\Db\Mysql;

class UserModel
{
    // Connexion PDO à la base de données
    private \PDO $conn;
    // Nom de la table utilisée
    private string $table = "users";

    public function __construct()
    {
        // Initialise la connexion à la base de données via le singleton Mysql
        error_log("CONSTRUCTEUR UserModel AVANT Mysql::getInstance()");
        $this->conn = Mysql::getInstance()->getPDO();
        error_log("CONSTRUCTEUR UserModel APRÈS Mysql::getInstance()");
    }

    /**
     * Recherche un utilisateur par son ID
     * @param int $id
     * @return User|null
     */
    public function findById(int $id): ?User
    {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->execute([':id' => $id]);

        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $data ? $this->hydrate($data) : null;
    }

    /**
     * Recherche un utilisateur par son email
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User
    {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE email = :email");
        $stmt->execute([':email' => $email]);

        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $data ? $this->hydrate($data) : null;
    }

    /**
     * Recherche un utilisateur par son pseudo
     * @param string $pseudo
     * @return User|null
     */
    public function findByPseudo(string $pseudo): ?User
    {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE pseudo = :pseudo");
        $stmt->execute([':pseudo' => $pseudo]);

        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $data ? $this->hydrate($data) : null;
    }

    /**
     * Sauvegarde un utilisateur (création ou mise à jour)
     * @param User $user
     * @return bool
     */
    public function save(User $user): bool
    {
        // Validation avant sauvegarde
        $errors = $user->validate();
        if (!empty($errors)) {
            throw new \InvalidArgumentException('Données invalides: ' . implode(', ', $errors));
        }

        // Si l'utilisateur a déjà un ID, on met à jour, sinon on crée
        if ($user->getId()) {
            return $this->update($user);
        } else {
            return $this->create($user);
        }
    }

    /**
     * Crée un nouvel utilisateur en base de données
     * @param User $user
     * @return bool
     */
    private function create(User $user): bool
    {
        $sql = "INSERT INTO {$this->table} (pseudo, email, password, role_id, credits, note, photo, created_at) 
                VALUES (:pseudo, :email, :password, :role_id, :credits, :note, :photo, :created_at)";

        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute([
            ':pseudo' => $user->getPseudo(),
            ':email' => $user->getEmail(),
            ':password' => $user->getPassword(),
            ':role_id' => $user->getRoleId(),
            ':credits' => $user->getCredits(),
            ':note' => $user->getNote(),
            ':photo' => $user->getPhoto(),
            ':created_at' => $user->getCreatedAt()->format('Y-m-d H:i:s')
        ]);

        // Récupère l'ID généré et l'assigne à l'objet User
        if ($result) {
            $user->setId((int)$this->conn->lastInsertId());
        }

        return $result;
    }

    /**
     * Met à jour un utilisateur existant
     * @param User $user
     * @return bool
     */
    private function update(User $user): bool
    {
        $sql = "UPDATE {$this->table} 
                SET pseudo = :pseudo, email = :email, role_id = :role_id, 
                    credits = :credits, note = :note, photo = :photo 
                WHERE id = :id";

        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':pseudo' => $user->getPseudo(),
            ':email' => $user->getEmail(),
            ':role_id' => $user->getRoleId(),
            ':credits' => $user->getCredits(),
            ':note' => $user->getNote(),
            ':photo' => $user->getPhoto(),
            ':id' => $user->getId()
        ]);
    }

    /**
     * Met à jour le nombre de crédits d'un utilisateur
     * @param int $userId
     * @param int $newCredits
     * @return bool
     */
    public function updateCredits(int $userId, int $newCredits): bool
    {
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET credits = :credits WHERE id = :id");
        return $stmt->execute([':credits' => $newCredits, ':id' => $userId]);
    }

    /**
     * Met à jour la note d'un utilisateur
     * @param int $userId
     * @param float $newNote
     * @return bool
     */
    public function updateNote(int $userId, float $newNote): bool
    {
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET note = :note WHERE id = :id");
        return $stmt->execute([':note' => $newNote, ':id' => $userId]);
    }

    /**
     * Hydrate un objet User à partir d'un tableau de données
     * @param array $data
     * @return User
     */
    private function hydrate(array $data): User
    {
        $user = new User($data['pseudo'], $data['email']);
        $user->setId((int)$data['id'])
            ->setPassword($data['password'])
            ->setRoleId((int)$data['role_id'])
            ->setCredits((int)$data['credits'])
            ->setNote((float)$data['note'])
            ->setPhoto($data['photo']);

        // Si la date de création existe, on la convertit en objet DateTime
        if ($data['created_at']) {
            $user->setCreatedAt(new \DateTime($data['created_at']));
        }

        return $user;
    }
}
