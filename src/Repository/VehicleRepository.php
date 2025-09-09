<?php

namespace App\Repository;

use App\Db\Mysql;
use App\Entity\VehicleEntity;

class VehicleRepository
{
    private \PDO $conn; // connexion PDO partagée
    private string $table = 'vehicles'; // nom de la table véhicules

    public function __construct()
    {
        // Récupère la connexion via le singleton Mysql
        $this->conn = Mysql::getInstance()->getPDO();
    }

    // Crée un nouveau véhicule en base
    public function create(VehicleEntity $vehicle): bool
    {
        // Prépare l'INSERT de toutes les colonnes pertinentes
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

        // Alimente chaque placeholder avec les valeurs de l'entité
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
            // Renseigne l'ID créé sur l'entité
            $vehicle->setId((int)$this->conn->lastInsertId());
        }

        return $result;
    }

    // Met à jour un véhicule existant
    public function update(VehicleEntity $vehicle): bool
    {
        // Met à jour toutes les colonnes éditables identifiées par l'ID
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
        // Aligne strictement les placeholders et les valeurs
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


    // Supprime un véhicule par ID
    public function deleteById(int $vehicleId): bool
    {
        // Suppression d'un véhicule par son identifiant
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute([':id' => $vehicleId]);
    }

    // Supprime tous les véhicules d'un utilisateur
    public function deleteByUserId(int $userId): bool
    {
        // Suppression en cascade des véhicules d'un utilisateur
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE user_id = :user_id");
        return $stmt->execute([':user_id' => $userId]);
    }

    // Recherche par ID avec fuel_type_name (LEFT JOIN)
    public function findById(int $id): ?VehicleEntity
    {
        // Jointure avec fuel_types pour récupérer le nom du carburant
        $sql = "SELECT v.*, f.type_name AS fuel_type_name
        FROM {$this->table} v
        LEFT JOIN fuel_types f ON v.fuel_type_id = f.id
        WHERE v.id = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $data ? new VehicleEntity($data) : null;
    }

    // Premier véhicule pour un utilisateur (avec fuel_type_name)
    public function findByUserId(int $userId): ?VehicleEntity
    {
        // Récupère le premier véhicule associé à l'utilisateur
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


    // Tous les véhicules pour un utilisateur (avec fuel_type_name)
    public function findAllByUserId(int $userId): array
    {
        // Récupère tous les véhicules d'un utilisateur avec libellé carburant
        $sql = "SELECT v.*, f.type_name AS fuel_type_name
        FROM {$this->table} v
        LEFT JOIN fuel_types f ON v.fuel_type_id = f.id
        WHERE v.user_id = :user_id";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':user_id' => $userId]);

        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return array_map(fn($data) => new VehicleEntity($data), $results);
    }

    // Vérifie l'existence d'une immatriculation pour un utilisateur (avec exclusion optionnelle)
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

        $sql .= " LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return (bool) $stmt->fetch();
    }


    // Recherche par plaque (globale)
    public function findByImmatriculation(string $immatriculation): ?VehicleEntity
    {
        // Recherche directe par plaque (sans contrainte d'utilisateur)
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE immatriculation = :immatriculation LIMIT 1");
        $stmt->execute([':immatriculation' => $immatriculation]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $data ? new VehicleEntity($data) : null;
    }

    // Liste des types de carburant disponibles
    public function getFuelTypes(): array
    {
        // Liste des types de carburant pour alimenter les formulaires
        $stmt = $this->conn->query("SELECT id, type_name FROM fuel_types ORDER BY id ASC");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function normalizePlate(?string $plate): string
    {
        $plate = strtoupper(trim((string)$plate));
        // supprime espaces/traits/points selon ton choix:
        return preg_replace('/[\s\.\-]/', '', $plate);
    }
}
