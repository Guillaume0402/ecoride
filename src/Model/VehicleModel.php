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
     * Met à jour le véhicule d’un utilisateur
     */
    public function update(int $vehicleId, array $data): bool
    {
        $sql = "UPDATE {$this->table} SET 
                marque = :marque,
                modele = :modele,
                couleur = :couleur, 
                immatriculation = :immatriculation,
                date_premiere_immatriculation = :date_immat,
                fuel_type_id = :fuel_type_id,
                places_dispo = :places_dispo,
                preferences = :preferences,
                custom_preferences = :custom_preferences
            WHERE id = :id";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ':marque'          => $data['marque'],
            ':modele'          => $data['modele'],
            ':couleur'         => $data['couleur'],
            ':immatriculation' => $data['immatriculation'],
            ':date_immat'      => $data['date_premiere_immatriculation'],
            ':fuel_type_id'    => $data['fuel_type_id'],
            ':places_dispo'    => $data['places_dispo'],
            ':preferences'     => implode(',', $data['preferences']),
            ':custom_preferences' => $data['custom_preferences'],
            ':id'              => $vehicleId
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

    public function findById(int $id): ?array
    {
        $sql = "SELECT * FROM vehicles WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        $vehicle = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $vehicle ?: null;
    }

    public function create(array $vehicle): bool
    {
        $sql = "INSERT INTO {$this->table} (
                user_id, marque, modele, couleur, immatriculation,
                date_premiere_immatriculation, fuel_type_id, places_dispo,
                preferences, custom_preferences, created_at
            ) VALUES (
                :user_id, :marque, :modele, :couleur, :immatriculation,
                :date_immat, :fuel_type_id, :places_dispo,
                :preferences, :custom_preferences, NOW()
            )";

        $data = [
            ':user_id' => $vehicle['user_id'],
            ':marque' => $vehicle['marque'] ?? '',
            ':modele' => $vehicle['modele'] ?? '',
            ':couleur' => $vehicle['couleur'] ?? '',
            ':immatriculation' => $vehicle['immatriculation'] ?? '',
            ':date_immat' => $vehicle['date_premiere_immatriculation'] ?? null,
            ':fuel_type_id' => $vehicle['fuel_type_id'] ?? null,
            ':places_dispo' => ($vehicle['places_dispo'] === '4+') ? 4 : $vehicle['places_dispo'],
            ':preferences' => implode(',', $vehicle['preferences'] ?? []),
            ':custom_preferences' => $vehicle['custom_preferences'] ?? ''
        ];

        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($data);
    }



    public function getFuelTypes(): array
    {
        $sql = "SELECT id, type_name FROM fuel_types ORDER BY id ASC";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll();
    }

    public function findAllByUserId(int $userId): array
    {
        $sql = "SELECT v.*, f.type_name AS fuel_type_name
            FROM {$this->table} v
            LEFT JOIN fuel_types f ON v.fuel_type_id = f.id
            WHERE v.user_id = :user_id";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':user_id' => $userId]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }


    public function deleteById(int $vehicleId): bool
    {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute([':id' => $vehicleId]);
    }

    public function existsByImmatriculation(string $immatriculation, int $userId, ?int $excludeVehicleId = null): bool
    {
        $sql = "SELECT id FROM {$this->table} 
            WHERE immatriculation = :immatriculation 
            AND user_id = :user_id";

        $params = [
            ':immatriculation' => $immatriculation,
            ':user_id' => $userId
        ];

        if ($excludeVehicleId !== null) {
            $sql .= " AND id != :exclude_id";
            $params[':exclude_id'] = $excludeVehicleId;
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return (bool) $stmt->fetch();
    }

    public function findByImmatriculation(string $immatriculation)
    {
        $sql = "SELECT * FROM vehicles WHERE immatriculation = :immatriculation LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':immatriculation' => $immatriculation]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function updateById(array $data): bool
    {
        $sql = "UPDATE {$this->table} SET 
                marque = :marque,
                modele = :modele,
                couleur = :couleur, 
                immatriculation = :immatriculation,
                date_premiere_immatriculation = :date_immat,
                fuel_type_id = :fuel_type_id,
                places_dispo = :places_dispo
            WHERE id = :id";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ':marque'          => $data['marque'],
            ':modele'          => $data['modele'],
            ':couleur'         => $data['couleur'],
            ':immatriculation' => $data['immatriculation'],
            ':date_immat'      => $data['date_premiere_immatriculation'],
            ':fuel_type_id'    => $data['fuel_type_id'],
            ':places_dispo'    => $data['places_dispo'],
            ':id'              => $data['id']
        ]);
    }
}
