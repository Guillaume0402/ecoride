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

    /**
     * Recherche simple par villes (LIKE) et date exacte (DATE(depart) = :date)
     */
    public function search(?string $depart = null, ?string $arrivee = null, ?string $date = null): array
    {
        $sql = "SELECT c.* FROM {$this->table} c WHERE 1=1";
        $params = [];
        if ($depart !== null && $depart !== '') {
            $sql .= " AND c.adresse_depart LIKE :depart";
            $params[':depart'] = '%' . $depart . '%';
        }
        if ($arrivee !== null && $arrivee !== '') {
            $sql .= " AND c.adresse_arrivee LIKE :arrivee";
            $params[':arrivee'] = '%' . $arrivee . '%';
        }
        if ($date !== null && $date !== '') {
            $sql .= " AND DATE(c.depart) = :date";
            $params[':date'] = $date;
        }
        $sql .= " ORDER BY c.depart ASC LIMIT 100";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
