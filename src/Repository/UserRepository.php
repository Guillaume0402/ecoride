<?php

namespace App\Repository;

use App\Entity\UserEntity;
use App\Db\Mysql;

class UserRepository
{
    private \PDO $conn; // connexion PDO partagée
    private string $table = "users"; // nom de la table principale

    public function __construct()
    {
        // Récupère la connexion via le singleton Mysql
        $this->conn = Mysql::getInstance()->getPDO();
    }

    // Création d'un nouvel utilisateur en base
    public function create(UserEntity $user): bool
    {
        // Sécurise la valeur du rôle de voyage
        $this->validateTravelRole($user);

        // Prépare l'INSERT avec tous les champs persistés
        $sql = "INSERT INTO {$this->table} 
            (pseudo, email, password, role_id, credits, note, photo, created_at, travel_role, is_active)
            VALUES (:pseudo, :email, :password, :role_id, :credits, :note, :photo, :created_at, :travel_role, :is_active)";

        $stmt = $this->conn->prepare($sql);
        // Lie chaque placeholder à la valeur de l'entité
        $result = $stmt->execute([
            ':pseudo'      => $user->getPseudo(),
            ':email'       => $user->getEmail(),
            ':password'    => $user->getPassword(),
            ':role_id'     => $user->getRoleId(),
            ':credits'     => $user->getCredits(),
            ':note'        => $user->getNote(),
            ':photo'       => $user->getPhoto() ?? (defined('DEFAULT_AVATAR_URL') ? DEFAULT_AVATAR_URL : '/assets/images/logo.svg'),
            ':created_at'  => $user->getCreatedAt()?->format('Y-m-d H:i:s') ?? date('Y-m-d H:i:s'),
            ':travel_role' => $user->getTravelRole(),
            ':is_active'   => $user->getIsActive()
        ]);

        if ($result) {
            // Récupère l'ID auto-incrémenté et l'injecte dans l'entité
            $user->setId((int)$this->conn->lastInsertId());
        }
        return $result;
    }

    // Mise à jour d'un utilisateur existant
    public function update(UserEntity $user): bool
    {
        // Sécurise la valeur du rôle de voyage avant update
        $this->validateTravelRole($user);

        $sql = "UPDATE {$this->table} 
                SET pseudo = :pseudo, email = :email, password = :password, role_id = :role_id, 
                    credits = :credits, note = :note, photo = :photo, travel_role = :travel_role, 
                    is_active = :is_active 
                WHERE id = :id";

        $stmt = $this->conn->prepare($sql);
        // Aligne strictement tous les placeholders avec leurs valeurs
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

    // Recherche par identifiant
    public function findById(int $id): ?UserEntity
    {
        // Récupère un enregistrement par ID et hydrate une entité
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $data ? new UserEntity($data) : null;
    }

    // Recherche par email
    public function findByEmail(string $email): ?UserEntity
    {
        // Index de recherche typique côté authentification
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE LOWER(email) = :email");
        $stmt->execute([':email' => $email]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $data ? new UserEntity($data) : null;
    }

    // Recherche par pseudo
    public function findByPseudo(string $pseudo): ?UserEntity
    {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE pseudo = :pseudo");
        $stmt->execute([':pseudo' => $pseudo]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $data ? new UserEntity($data) : null;
    }

    // Liste des utilisateurs pour un ensemble de rôles
    public function findAllWithRoles(array $roleIds): array
    {
        // Construit dynamiquement la liste de placeholders pour l'IN()
        $placeholders = implode(',', array_fill(0, count($roleIds), '?'));
        $sql = "SELECT u.*, r.role_name 
            FROM users u 
            JOIN roles r ON u.role_id = r.id 
            WHERE u.role_id IN ($placeholders)
            ORDER BY u.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($roleIds);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Transforme chaque ligne en entité UserEntity
        return array_map(fn($data) => new UserEntity($data), $results);
    }

    public function updatePasswordById(int $userId, string $newHash): bool
    {
        $sql = "UPDATE {$this->table} SET password = :hash WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':hash' => $newHash,
            ':id'   => $userId,
        ]);
    }


    // Met à jour le nombre de crédits
    public function updateCredits(int $userId, int $newCredits): bool
    {
        // Met à jour uniquement le champ credits
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET credits = :credits WHERE id = :id");
        return $stmt->execute([':credits' => $newCredits, ':id' => $userId]);
    }

    // Met à jour la note
    public function updateNote(int $userId, float $newNote): bool
    {
        // Met à jour uniquement le champ note
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET note = :note WHERE id = :id");
        return $stmt->execute([':note' => $newNote, ':id' => $userId]);
    }

    // Met à jour le profil avec données partielles
    public function updateProfil(array $data): void
    {
        // Normalise la valeur de travel_role si invalide
        if (!in_array($data['travel_role'], ['passager', 'chauffeur', 'les-deux'])) {
            $data['travel_role'] = 'passager';
        }

        // Construit dynamiquement le SQL uniquement avec les champs fournis
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

        // Prépare les paramètres de base
        $params = [
            'pseudo'      => $data['pseudo'],
            'role_id'     => $data['role_id'],
            'travel_role' => $data['travel_role'],
            'id'          => $data['id'],
        ];
        // Ajoute conditionnellement les champs optionnels
        if (!empty($data['photo'])) {
            $params['photo'] = $data['photo'];
        }
        if (!empty($data['password'])) {
            $params['password'] = $data['password'];
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
    }

    // Récupère tous les employés (role_id = 2)
    public function findAllEmployees(): array
    {
        // Filtre sur role_id = 2 (employés)
        $sql = "SELECT u.*, r.role_name AS role_name 
                FROM {$this->table} u
                LEFT JOIN roles r ON u.role_id = r.id
                WHERE u.role_id = 2";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return array_map(fn($data) => new UserEntity($data), $results);
    }

    // Récupère tous les utilisateurs (role_id = 1)
    public function findAllUsers(): array
    {
        // Filtre sur role_id = 1 (utilisateurs standards)
        $sql = "SELECT u.*, r.role_name AS role_name 
                FROM {$this->table} u
                LEFT JOIN roles r ON u.role_id = r.id
                WHERE u.role_id = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return array_map(fn($data) => new UserEntity($data), $results);
    }

    // Bascule le statut actif/inactif d'un utilisateur
    public function toggleActive(int $userId): bool
    {
        // Récupère le statut actuel puis le bascule (0/1)
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

    // Supprime un utilisateur par ID
    public function delete(int $userId): bool
    {
        // Suppression définitive par identifiant
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute([':id' => $userId]);
    }


    // Valide et corrige le rôle de voyage d'un utilisateur
    private function validateTravelRole(UserEntity $user): void
    {
        $validRoles = ['passager', 'chauffeur', 'les-deux'];
        if (!in_array($user->getTravelRole(), $validRoles)) {
            $user->setTravelRole('passager');
        }
    }
}
