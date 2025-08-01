<?php

namespace App\Repository;

use App\Db\Mysql;
use App\Entity\Vehicle;

class VehicleRepository
{
    private \PDO $conn;
    private string $table = 'vehicles';

    public function __construct()
    {
        $this->conn = Mysql::getInstance()->getPDO();
    }

    public function create(Vehicle $vehicle): bool
    {
        $sql = "INSERT INTO {$this->table} (
                    user_id, marque, modele, couleur, immatriculation,
                    date_premiere_immatriculation, fuel_type_id, places_dispo,
                    created_at
                ) VALUES (
                    :user_id, :marque, :modele, :couleur, :immatriculation,
                    :date_immat, :fuel_type_id, :places_dispo,
                    NOW()
                )";

        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute([
            ':user_id'        => $vehicle->getUserId(),
            ':marque'         => $vehicle->getMarque(),
            ':modele'         => $vehicle->getModele(),
            ':couleur'        => $vehicle->getCouleur(),
            ':immatriculation'=> $vehicle->getImmatriculation(),
            ':date_immat'     => $vehicle->getDatePremiereImmatriculation(),
            ':fuel_type_id'   => $vehicle->getFuelTypeId(),
            ':places_dispo'   => $vehicle->getPlacesDispo(),
        ]);

        if ($result) {
            $vehicle->setId((int)$this->conn->lastInsertId());
        }

        return $result;
    }

    public function update(Vehicle $vehicle): bool
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
            ':marque'          => $vehicle->getMarque(),
            ':modele'          => $vehicle->getModele(),
            ':couleur'         => $vehicle->getCouleur(),
            ':immatriculation' => $vehicle->getImmatriculation(),
            ':date_immat'      => $vehicle->getDatePremiereImmatriculation(),
            ':fuel_type_id'    => $vehicle->getFuelTypeId(),
            ':places_dispo'    => $vehicle->getPlacesDispo(),
            ':id'              => $vehicle->getId()
        ]);
    }

    public function deleteById(int $vehicleId): bool
    {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute([':id' => $vehicleId]);
    }

    public function deleteByUserId(int $userId): bool
    {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE user_id = :user_id");
        return $stmt->execute([':user_id' => $userId]);
    }

    public function findById(int $id): ?Vehicle
    {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $data ? new Vehicle($data) : null;
    }

    public function findByUserId(int $userId): ?Vehicle
    {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $userId]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $data ? new Vehicle($data) : null;
    }

    public function findAllByUserId(int $userId): array
    {
        $sql = "SELECT v.*, f.type_name AS fuel_type_name
                FROM {$this->table} v
                LEFT JOIN fuel_types f ON v.fuel_type_id = f.id
                WHERE v.user_id = :user_id";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':user_id' => $userId]);

        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return array_map(fn($data) => new Vehicle($data), $results);
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

    public function findByImmatriculation(string $immatriculation): ?Vehicle
    {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE immatriculation = :immatriculation LIMIT 1");
        $stmt->execute([':immatriculation' => $immatriculation]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $data ? new Vehicle($data) : null;
    }

    public function getFuelTypes(): array
    {
        $stmt = $this->conn->query("SELECT id, type_name FROM fuel_types ORDER BY id ASC");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
