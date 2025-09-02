<?php

namespace App\Repository;

use App\Db\Mysql;
use App\Entity\VehicleEntity;

class VehicleRepository
{
    private \PDO $conn;
    private string $table = 'vehicles';

    public function __construct()
    {
        $this->conn = Mysql::getInstance()->getPDO();
    }

    /**
     * Crée un nouveau véhicule en base de données
     * @param VehicleEntity $vehicle L'entité véhicule à créer
     * @return bool True si la création a réussi, false sinon
     */
    public function create(VehicleEntity $vehicle): bool
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

        $stmt = $this->conn->prepare($sql);

        $result = $stmt->execute([
            ':user_id'        => $vehicle->getUserId(),
            ':marque'         => $vehicle->getMarque(),
            ':modele'         => $vehicle->getModele(),
            ':couleur'        => $vehicle->getCouleur(),
            ':immatriculation' => $vehicle->getImmatriculation(),
            ':date_immat'     => $vehicle->getDatePremiereImmatriculation(),
            ':fuel_type_id'   => $vehicle->getFuelTypeId(),
            ':places_dispo'   => $vehicle->getPlacesDispo(),
            ':preferences'    => $vehicle->getPreferences() ?? '',
            ':custom_preferences' => $vehicle->getCustomPreferences() ?? '',
        ]);

        if ($result) {
            $vehicle->setId((int)$this->conn->lastInsertId());
        }

        return $result;
    }

    /**
     * Met à jour un véhicule existant en base de données
     * @param VehicleEntity $vehicle L'entité véhicule à mettre à jour
     * @return bool True si la mise à jour a réussi, false sinon
     */
    public function update(VehicleEntity $vehicle): bool
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
            ':marque'          => $vehicle->getMarque(),
            ':modele'          => $vehicle->getModele(),
            ':couleur'         => $vehicle->getCouleur(),
            ':immatriculation' => $vehicle->getImmatriculation(),
            ':date_immat'      => $vehicle->getDatePremiereImmatriculation(),
            ':fuel_type_id'    => $vehicle->getFuelTypeId(),
            ':places_dispo'    => $vehicle->getPlacesDispo(),
            ':preferences'     => $vehicle->getPreferences(),
            ':custom_preferences' => $vehicle->getCustomPreferences(),
            ':id'              => $vehicle->getId()
        ]);
    }


    /**
     * Supprime un véhicule par son ID
     * @param int $vehicleId L'ID du véhicule à supprimer
     * @return bool True si la suppression a réussi, false sinon
     */
    public function deleteById(int $vehicleId): bool
    {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute([':id' => $vehicleId]);
    }

    /**
     * Supprime tous les véhicules d'un utilisateur
     * @param int $userId L'ID de l'utilisateur dont supprimer les véhicules
     * @return bool True si la suppression a réussi, false sinon
     */
    public function deleteByUserId(int $userId): bool
    {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE user_id = :user_id");
        return $stmt->execute([':user_id' => $userId]);
    }

    /**
     * Trouve un véhicule par son ID avec les informations du type de carburant
     * @param int $id L'ID du véhicule à rechercher
     * @return VehicleEntity|null L'entité véhicule ou null si non trouvé
     */
    public function findById(int $id): ?VehicleEntity
    {
        $sql = "SELECT v.*, f.type_name AS fuel_type_name
        FROM {$this->table} v
        LEFT JOIN fuel_types f ON v.fuel_type_id = f.id
        WHERE v.id = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $data ? new VehicleEntity($data) : null;
    }

    /**
     * Trouve le premier véhicule d'un utilisateur avec les informations du type de carburant
     * @param int $userId L'ID de l'utilisateur
     * @return VehicleEntity|null L'entité véhicule ou null si non trouvé
     */
    public function findByUserId(int $userId): ?VehicleEntity
    {
        $sql = "SELECT v.*, f.type_name AS fuel_type_name
        FROM {$this->table} v
        LEFT JOIN fuel_types f ON v.fuel_type_id = f.id
        WHERE v.user_id = :user_id
        LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $data ? new VehicleEntity($data) : null;
    }


    /**
     * Trouve tous les véhicules d'un utilisateur avec les informations du type de carburant
     * @param int $userId L'ID de l'utilisateur
     * @return array Tableau d'entités VehicleEntity
     */
    public function findAllByUserId(int $userId): array
    {
        $sql = "SELECT v.*, f.type_name AS fuel_type_name
        FROM {$this->table} v
        LEFT JOIN fuel_types f ON v.fuel_type_id = f.id
        WHERE v.user_id = :user_id";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':user_id' => $userId]);

        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return array_map(fn($data) => new VehicleEntity($data), $results);
    }

    /**
     * Vérifie si une plaque d'immatriculation existe déjà pour un utilisateur
     * @param string $immatriculation La plaque d'immatriculation à vérifier
     * @param int $userId L'ID de l'utilisateur
     * @param int|null $excludeVehicleId ID du véhicule à exclure de la vérification (pour les mises à jour)
     * @return bool True si la plaque existe déjà, false sinon
     */
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

    /**
     * Trouve un véhicule par sa plaque d'immatriculation
     * @param string $immatriculation La plaque d'immatriculation à rechercher
     * @return VehicleEntity|null L'entité véhicule ou null si non trouvé
     */
    public function findByImmatriculation(string $immatriculation): ?VehicleEntity
    {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE immatriculation = :immatriculation LIMIT 1");
        $stmt->execute([':immatriculation' => $immatriculation]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $data ? new VehicleEntity($data) : null;
    }

    /**
     * Récupère tous les types de carburant disponibles
     * @return array Tableau associatif contenant les IDs et noms des types de carburant
     */
    public function getFuelTypes(): array
    {
        $stmt = $this->conn->query("SELECT id, type_name FROM fuel_types ORDER BY id ASC");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
