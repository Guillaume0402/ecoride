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
            (pseudo, email, password, role_id, credits, note, photo, created_at, travel_role, is_active,
             email_verified, email_verification_token, email_verification_expires)
            VALUES (:pseudo, :email, :password, :role_id, :credits, :note, :photo, :created_at, :travel_role, :is_active,
                    :email_verified, :email_verification_token, :email_verification_expires)";

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
            ':is_active'   => $user->getIsActive(),
            ':email_verified' => method_exists($user, 'getEmailVerified') ? $user->getEmailVerified() : 0,
            ':email_verification_token' => method_exists($user, 'getEmailVerificationToken') ? $user->getEmailVerificationToken() : null,
            ':email_verification_expires' => method_exists($user, 'getEmailVerificationExpires') ? ($user->getEmailVerificationExpires()?->format('Y-m-d H:i:s')) : null,
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
                    is_active = :is_active,
                    email_verified = COALESCE(:email_verified, email_verified),
                    email_verification_token = COALESCE(:email_verification_token, email_verification_token),
                    email_verification_expires = COALESCE(:email_verification_expires, email_verification_expires)
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
            ':email_verified' => method_exists($user, 'getEmailVerified') ? $user->getEmailVerified() : null,
            ':email_verification_token' => method_exists($user, 'getEmailVerificationToken') ? $user->getEmailVerificationToken() : null,
            ':email_verification_expires' => method_exists($user, 'getEmailVerificationExpires') ? ($user->getEmailVerificationExpires()?->format('Y-m-d H:i:s')) : null,
            ':id'          => $user->getId()
        ]);
    }

    public function verifyEmailByToken(string $email, string $token): bool
    {
        $sql = "UPDATE {$this->table}
                SET email_verified = 1, email_verification_token = NULL, email_verification_expires = NULL
                WHERE LOWER(email) = :email AND email_verification_token = :t AND (email_verification_expires IS NULL OR email_verification_expires >= NOW())";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':email' => mb_strtolower($email), ':t' => $token]);
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
            FROM {$this->table} u 
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

    // === Stats simples ===
    public function countAll(): int
    {
        $stmt = $this->conn->query("SELECT COUNT(*) FROM {$this->table}");
        return (int) $stmt->fetchColumn();
    }

    
    //  Gestion des crédits (débit/credit)    
    /**
     * Débite le compte de l'utilisateur si le solde est suffisant.
     * Retourne true si succès, false si solde insuffisant ou erreur.
     */
    public function debitIfEnough(int $userId, int $amount): bool
    {
        if ($amount <= 0) return true; // rien à débiter
        try {
            $this->conn->beginTransaction();
            // Verrouillage pessimiste de la ligne
            $stmt = $this->conn->prepare("SELECT credits FROM {$this->table} WHERE id = :id FOR UPDATE");
            $stmt->execute([':id' => $userId]);
            $credits = (int) ($stmt->fetchColumn() ?? 0);
            if ($credits < $amount) {
                $this->conn->rollBack();
                return false;
            }
            $upd = $this->conn->prepare("UPDATE {$this->table} SET credits = credits - :amt WHERE id = :id");
            $upd->execute([':amt' => $amount, ':id' => $userId]);
            $this->conn->commit();
            return true;
        } catch (\Throwable $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log('[UserRepository::debitIfEnough] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Crédite le compte de l'utilisateur du montant donné (opération simple).
     */
    public function credit(int $userId, int $amount): bool
    {
        if ($amount <= 0) return true;
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET credits = credits + :amt WHERE id = :id");
        return $stmt->execute([':amt' => $amount, ':id' => $userId]);
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
