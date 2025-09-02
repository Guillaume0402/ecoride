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

    /**
     * Crée un nouvel utilisateur en base de données
     * @param UserEntity $user L'entité utilisateur à créer
     * @return bool True si la création a réussi, false sinon
     */
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

    /**
     * Met à jour un utilisateur existant en base de données
     * @param UserEntity $user L'entité utilisateur à mettre à jour
     * @return bool True si la mise à jour a réussi, false sinon
     */
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

    /**
     * Trouve un utilisateur par son ID
     * @param int $id L'ID de l'utilisateur à rechercher
     * @return UserEntity|null L'entité utilisateur ou null si non trouvé
     */
    public function findById(int $id): ?UserEntity
    {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $data ? new UserEntity($data) : null;
    }

    /**
     * Trouve un utilisateur par son email
     * @param string $email L'email de l'utilisateur à rechercher
     * @return UserEntity|null L'entité utilisateur ou null si non trouvé
     */
    public function findByEmail(string $email): ?UserEntity
    {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $data ? new UserEntity($data) : null;
    }

    /**
     * Trouve un utilisateur par son pseudo
     * @param string $pseudo Le pseudo de l'utilisateur à rechercher
     * @return UserEntity|null L'entité utilisateur ou null si non trouvé
     */
    public function findByPseudo(string $pseudo): ?UserEntity
    {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE pseudo = :pseudo");
        $stmt->execute([':pseudo' => $pseudo]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $data ? new UserEntity($data) : null;
    }

    /**
     * Trouve tous les utilisateurs ayant certains rôles avec leurs informations de rôle
     * @param array $roleIds Tableau des IDs de rôles à rechercher
     * @return array Tableau d'entités UserEntity
     */
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

    /**
     * Met à jour le nombre de crédits d'un utilisateur
     * @param int $userId L'ID de l'utilisateur
     * @param int $newCredits Le nouveau nombre de crédits
     * @return bool True si la mise à jour a réussi, false sinon
     */
    public function updateCredits(int $userId, int $newCredits): bool
    {
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET credits = :credits WHERE id = :id");
        return $stmt->execute([':credits' => $newCredits, ':id' => $userId]);
    }

    /**
     * Met à jour la note d'un utilisateur
     * @param int $userId L'ID de l'utilisateur
     * @param float $newNote La nouvelle note
     * @return bool True si la mise à jour a réussi, false sinon
     */
    public function updateNote(int $userId, float $newNote): bool
    {
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET note = :note WHERE id = :id");
        return $stmt->execute([':note' => $newNote, ':id' => $userId]);
    }

    /**
     * Met à jour le profil d'un utilisateur avec des données partielles
     * @param array $data Tableau associatif contenant les données à mettre à jour
     * @return void
     */
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

    /**
     * Récupère tous les employés (utilisateurs avec role_id = 2)
     * @return array Tableau d'entités UserEntity représentant les employés
     */
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

    /**
     * Récupère tous les utilisateurs standards (utilisateurs avec role_id = 1)
     * @return array Tableau d'entités UserEntity représentant les utilisateurs
     */
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

    /**
     * Bascule le statut actif/inactif d'un utilisateur
     * @param int $userId L'ID de l'utilisateur
     * @return bool True si la bascule a réussi, false sinon
     */
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

    /**
     * Supprime un utilisateur de la base de données
     * @param int $userId L'ID de l'utilisateur à supprimer
     * @return bool True si la suppression a réussi, false sinon
     */
    public function delete(int $userId): bool
    {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute([':id' => $userId]);
    }


    /*
    Valide et corrige le rôle de voyage d'un utilisateur     
    */
    private function validateTravelRole(UserEntity $user): void
    {
        $validRoles = ['passager', 'chauffeur', 'les-deux'];
        if (!in_array($user->getTravelRole(), $validRoles)) {
            $user->setTravelRole('passager');
        }
    }
}
