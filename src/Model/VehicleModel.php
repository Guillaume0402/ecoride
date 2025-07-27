<?php

namespace App\Model;

use App\Db\Mysql;

class VehicleModel
{
    private \PDO $conn;
    private string $table = 'vehicles';

    public function __construct()
    {
        $this->conn = Mysql::getInstance()->getPDO();
    }

    /**
     * Crée un véhicule pour un utilisateur
     */
    public function create(array $data): bool
    {
        $sql = "INSERT INTO {$this->table} 
                (user_id, marque, modele, couleur, immatriculation, date_premiere_immatriculation, fuel_type_id, places_dispo, created_at)
                VALUES (:user_id, :marque, :modele, :couleur, :immatriculation, :date_immat, :fuel_type_id, :places_dispo, NOW())";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ':user_id'         => $data['user_id'],
            ':marque'          => $data['marque'],
            ':modele'          => $data['modele'],
            ':couleur'         => $data['couleur'],
            ':immatriculation' => $data['immatriculation'],
            ':date_immat'      => $data['date_premiere_immatriculation'],
            ':fuel_type_id'    => $data['fuel_type_id'],
            ':places_dispo'    => $data['places_dispo']
        ]);
    }

    /**
     * Met à jour le véhicule d’un utilisateur
     */
    public function update(int $userId, array $data): bool
    {
        $sql = "UPDATE {$this->table} SET 
                    marque = :marque,
                    modele = :modele,
                    couleur = :couleur,
                    immatriculation = :immatriculation,
                    date_premiere_immatriculation = :date_immat,
                    fuel_type_id = :fuel_type_id,
                    places_dispo = :places_dispo
                WHERE user_id = :user_id";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ':marque'          => $data['marque'],
            ':modele'          => $data['modele'],
            ':couleur'         => $data['couleur'],
            ':immatriculation' => $data['immatriculation'],
            ':date_immat'      => $data['date_premiere_immatriculation'],
            ':fuel_type_id'    => $data['fuel_type_id'],
            ':places_dispo'    => $data['places_dispo'],
            ':user_id'         => $userId
        ]);
    }

    /**
     * Supprime un véhicule
     */
    public function deleteByUserId(int $userId): bool
    {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE user_id = :user_id");
        return $stmt->execute([':user_id' => $userId]);
    }

    /**
     * Récupère le véhicule associé à un utilisateur
     */
    public function findByUserId(int $userId): ?array
    {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $userId]);

        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $data ?: null;
    }

    /**
     * Enregistre ou met à jour un véhicule
     * Si le véhicule existe déjà, il est mis à jour, sinon il est créé
     */
    public function save(array $vehicle): void
    {
        $sql = "INSERT INTO vehicles (
        user_id, marque, modele, couleur, immatriculation,
        date_premiere_immatriculation, fuel_type_id, places_dispo,
        preferences, custom_preferences, created_at
    ) VALUES (
        :user_id, :marque, :modele, :couleur, :immatriculation,
        :date_premiere_immatriculation, :fuel_type_id, :places_dispo,
        :preferences, :custom_preferences, NOW()
    )";

        $stmt = $this->conn->prepare($sql);

        $data = [
            ':user_id' => $vehicle['user_id'],
            ':marque' => $vehicle['marque'] ?? '',
            ':modele' => $vehicle['modele'] ?? '',
            ':couleur' => $vehicle['couleur'] ?? '',
            ':immatriculation' => $vehicle['immatriculation'] ?? '',
            ':date_premiere_immatriculation' => $vehicle['date_premiere_immatriculation'] ?? null,
            ':fuel_type_id' => $vehicle['fuel_type_id'] ?? null,
            ':places_dispo' => ($vehicle['places_dispo'] === '4+') ? 4 : $vehicle['places_dispo'],
            ':preferences' => implode(',', $vehicle['preferences'] ?? []),
            ':custom_preferences' => $vehicle['custom_preferences'] ?? ''
        ];

        if (!$stmt->execute($data)) {
            echo "<pre>";
            var_dump($data);
            var_dump($stmt->errorInfo());
            echo "</pre>";
            exit;
        }
    }

    public function getFuelTypes(): array
    {
        $sql = "SELECT id, type_name FROM fuel_types ORDER BY id ASC";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll();
    }

    public function findAllByUserId(int $userId): array
    {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
