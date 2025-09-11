<?php

namespace App\Repository;

use App\Db\Mysql;
use App\Entity\CovoiturageEntity;

class CovoiturageRepository
{
    private \PDO $conn;
    private string $table = 'covoiturages';

    public function __construct()
    {
        $this->conn = Mysql::getInstance()->getPDO();
    }

    public function create(CovoiturageEntity $c): bool
    {
        $sql = "INSERT INTO {$this->table} (driver_id, vehicle_id, adresse_depart, adresse_arrivee, depart, arrivee, prix, status, created_at)
                VALUES (:driver_id, :vehicle_id, :adresse_depart, :adresse_arrivee, :depart, :arrivee, :prix, :status, NOW())";

        $stmt = $this->conn->prepare($sql);
        $ok = $stmt->execute([
            ':driver_id' => $c->getDriverId(),
            ':vehicle_id' => $c->getVehicleId(),
            ':adresse_depart' => $c->getAdresseDepart(),
            ':adresse_arrivee' => $c->getAdresseArrivee(),
            ':depart' => $c->getDepart(),
            ':arrivee' => $c->getArrivee(),
            ':prix' => $c->getPrix(),
            ':status' => $c->getStatus(),
        ]);

        if ($ok) {
            $c->setId((int)$this->conn->lastInsertId());
        }
        return $ok;
    }
}
