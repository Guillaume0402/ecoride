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

    // Constructeur pour initialiser la connexion
    public function __construct()
    {
        // Initialise la connexion à la base de données via le singleton Mysql

        $this->conn = Mysql::getInstance()->getPDO();
    }

    // Crée un nouvel utilisateur
    public function create(User $user): bool
    {
        $sql = "INSERT INTO {$this->table} 
            (pseudo, email, password, role_id, credits, note, photo, created_at)
            VALUES (:pseudo, :email, :password, :role_id, :credits, :note, :photo, :created_at)";

        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute([
            ':pseudo' => $user->getPseudo(),
            ':email' => $user->getEmail(),
            ':password' => $user->getPassword(),
            ':role_id' => $user->getRoleId(),
            ':credits' => $user->getCredits(),
            ':note' => $user->getNote(),
            ':photo' => $user->getPhoto() ?? '/images/default-avatar.png',
            ':created_at' => $user->getCreatedAt()?->format('Y-m-d H:i:s') ?? date('Y-m-d H:i:s'),
        ]);
        if ($result) {
            $user->setId((int)$this->conn->lastInsertId());
        }
        return $result;
    }

    // Met à jour un utilisateur existant
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

    // Recherche un utilisateur par son ID
    public function findById(int $id): ?User
    {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $data ? $this->hydrate($data) : null;
    }

    // Recherche un utilisateur par son email
    public function findByEmail(string $email): ?User
    {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $data ? $this->hydrate($data) : null;
    }

    // Recherche un utilisateur par son pseudo
    public function findByPseudo(string $pseudo): ?User
    {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE pseudo = :pseudo");
        $stmt->execute([':pseudo' => $pseudo]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $data ? $this->hydrate($data) : null;
    }

    // Recherche tous les utilisateurs avec des rôles spécifiques
    public function findAllWithRoles(array $roleIds): array
    {
        $placeholders = implode(',', array_fill(0, count($roleIds), '?'));
        $sql = "SELECT u.*, r.role_name 
            FROM users u 
            JOIN roles r ON u.role_id = r.id 
            WHERE u.role_id IN ($placeholders)
            ORDER BY u.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($roleIds);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Recherche tous les employés
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

    // Met à jour les crédits d'un utilisateur
    public function updateCredits(int $userId, int $newCredits): bool
    {
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET credits = :credits WHERE id = :id");
        return $stmt->execute([':credits' => $newCredits, ':id' => $userId]);
    }

    // Met à jour la note d'un utilisateur
    public function updateNote(int $userId, float $newNote): bool
    {
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET note = :note WHERE id = :id");
        return $stmt->execute([':note' => $newNote, ':id' => $userId]);
    }

    // fonction hydrate pour convertir un tableau en objet User
    private function hydrate(array $data): User
    {
        $user = new User($data['pseudo'], $data['email']);

        $user->setId((int)$data['id'])
            ->setPseudo($data['pseudo'])
            ->setEmail($data['email'])
            ->setPassword($data['password'])
            ->setRoleId((int)$data['role_id'])
            ->setIsActive((int)$data['is_active'])
            ->setCredits((int)$data['credits'])
            ->setNote((float)$data['note'])
            ->setPhoto($data['photo'])
            ->setTravelRole($data['travel_role'] ?? 'passager');
        if (!empty($data['created_at'])) {
            $user->setCreatedAt(new \DateTimeImmutable($data['created_at']));
        }
        return $user;
    }

    // Met à jour le profil de l'utilisateur
    public function updateProfil(array $data): void
    {
        $sql = "UPDATE users SET 
        pseudo = :pseudo,
        role_id = :role_id,
        travel_role = :travel_role";
        if (!empty($data['photo'])) {
            $sql .= ", photo = :photo";
        }
        if (!empty($data['password'])) {
            $sql .= ", password = :password";
        }
        $sql .= " WHERE id = :id";
        $params = [
            'pseudo'      => $data['pseudo'],
            'role_id'     => $data['role_id'],
            'travel_role' => $data['travel_role'],
            'id'          => $data['id'],
        ];
        if (!empty($data['photo'])) {
            $params['photo'] = $data['photo'];
        }
        // ⚠️ Le password est déjà hashé dans le controller
        if (!empty($data['password'])) {
            $params['password'] = $data['password'];
        }
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
    }


    // Recherche tous les employés
    public function findAllEmployees(): array
    {
        $sql = "SELECT u.*, r.role_name AS role_name 
            FROM {$this->table} u
            LEFT JOIN roles r ON u.role_id = r.id
            WHERE u.role_id = 2";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Recherche tous les utilisateurs avec le rôle "Utilisateur"
    public function findAllUsers(): array
    {
        $sql = "SELECT u.*, r.role_name AS role_name 
            FROM {$this->table} u
            LEFT JOIN roles r ON u.role_id = r.id
            WHERE u.role_id = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Fonction pour activer/désactiver un utilisateur
    public function toggleActive(int $userId): bool
    {
        // Récupérer l'état actuel
        $stmt = $this->conn->prepare("SELECT is_active FROM {$this->table} WHERE id = :id");
        $stmt->execute([':id' => $userId]);
        $currentStatus = $stmt->fetchColumn();

        if ($currentStatus === false) {
            return false; // Aucun utilisateur trouvé
        }

        // Inverser l'état (1 -> 0, 0 -> 1)
        $newStatus = $currentStatus ? 0 : 1;

        // Mise à jour
        $updateStmt = $this->conn->prepare("UPDATE {$this->table} SET is_active = :status WHERE id = :id");
        return $updateStmt->execute([
            ':status' => $newStatus,
            ':id' => $userId
        ]);
    }

    // fonction pour supprimer un utilisateur
    public function delete(int $userId): bool
{
    $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id = :id");
    return $stmt->execute([':id' => $userId]);
}

}
