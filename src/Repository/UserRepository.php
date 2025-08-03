<?php

namespace App\Repository;

use App\Entity\UserEntity;
use App\Db\Mysql;

class UserRepository
{
    private \PDO $conn;
    private string $table = "users";

    public function __construct()
    {
        $this->conn = Mysql::getInstance()->getPDO();
    }

    public function create(UserEntity $user): bool
    {
        $this->validateTravelRole($user);

        $sql = "INSERT INTO {$this->table} 
            (pseudo, email, password, role_id, credits, note, photo, created_at, travel_role, is_active)
            VALUES (:pseudo, :email, :password, :role_id, :credits, :note, :photo, :created_at, :travel_role, :is_active)";

        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute([
            ':pseudo'      => $user->getPseudo(),
            ':email'       => $user->getEmail(),
            ':password'    => $user->getPassword(),
            ':role_id'     => $user->getRoleId(),
            ':credits'     => $user->getCredits(),
            ':note'        => $user->getNote(),
            ':photo'       => $user->getPhoto() ?? '/images/default-avatar.png',
            ':created_at'  => $user->getCreatedAt()?->format('Y-m-d H:i:s') ?? date('Y-m-d H:i:s'),
            ':travel_role' => $user->getTravelRole(),
            ':is_active'   => $user->getIsActive()
        ]);

        if ($result) {
            $user->setId((int)$this->conn->lastInsertId());
        }
        return $result;
    }

    public function update(UserEntity $user): bool
    {
        $this->validateTravelRole($user);

        $sql = "UPDATE {$this->table} 
                SET pseudo = :pseudo, email = :email, password = :password, role_id = :role_id, 
                    credits = :credits, note = :note, photo = :photo, travel_role = :travel_role, 
                    is_active = :is_active 
                WHERE id = :id";

        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':pseudo'      => $user->getPseudo(),
            ':email'       => $user->getEmail(),
            ':password'    => $user->getPassword(),
            ':role_id'     => $user->getRoleId(),
            ':credits'     => $user->getCredits(),
            ':note'        => $user->getNote(),
            ':photo'       => $user->getPhoto(),
            ':travel_role' => $user->getTravelRole(),
            ':is_active'   => $user->getIsActive(),
            ':id'          => $user->getId()
        ]);
    }

    public function findById(int $id): ?UserEntity
    {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $data ? new UserEntity($data) : null;
    }

    public function findByEmail(string $email): ?UserEntity
    {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $data ? new UserEntity($data) : null;
    }

    public function findByPseudo(string $pseudo): ?UserEntity
    {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE pseudo = :pseudo");
        $stmt->execute([':pseudo' => $pseudo]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $data ? new UserEntity($data) : null;
    }

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
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return array_map(fn($data) => new UserEntity($data), $results);
    }

    public function updateCredits(int $userId, int $newCredits): bool
    {
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET credits = :credits WHERE id = :id");
        return $stmt->execute([':credits' => $newCredits, ':id' => $userId]);
    }

    public function updateNote(int $userId, float $newNote): bool
    {
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET note = :note WHERE id = :id");
        return $stmt->execute([':note' => $newNote, ':id' => $userId]);
    }

    public function updateProfil(array $data): void
    {
        if (!in_array($data['travel_role'], ['passager', 'chauffeur', 'les-deux'])) {
            $data['travel_role'] = 'passager';
        }

        $sql = "UPDATE {$this->table} SET 
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
        if (!empty($data['password'])) {
            $params['password'] = $data['password'];
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
    }

    public function findAllEmployees(): array
    {
        $sql = "SELECT u.*, r.role_name AS role_name 
                FROM {$this->table} u
                LEFT JOIN roles r ON u.role_id = r.id
                WHERE u.role_id = 2";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return array_map(fn($data) => new UserEntity($data), $results);
    }

    public function findAllUsers(): array
    {
        $sql = "SELECT u.*, r.role_name AS role_name 
                FROM {$this->table} u
                LEFT JOIN roles r ON u.role_id = r.id
                WHERE u.role_id = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return array_map(fn($data) => new UserEntity($data), $results);
    }

    public function toggleActive(int $userId): bool
    {
        $stmt = $this->conn->prepare("SELECT is_active FROM {$this->table} WHERE id = :id");
        $stmt->execute([':id' => $userId]);
        $currentStatus = $stmt->fetchColumn();

        if ($currentStatus === false) {
            return false;
        }

        $newStatus = $currentStatus ? 0 : 1;

        $updateStmt = $this->conn->prepare("UPDATE {$this->table} SET is_active = :status WHERE id = :id");
        return $updateStmt->execute([':status' => $newStatus, ':id' => $userId]);
    }

    public function delete(int $userId): bool
    {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute([':id' => $userId]);
    }



    private function validateTravelRole(UserEntity $user): void
    {
        $validRoles = ['passager', 'chauffeur', 'les-deux'];
        if (!in_array($user->getTravelRole(), $validRoles)) {
            $user->setTravelRole('passager');
        }
    }
}
