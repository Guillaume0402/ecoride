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
     * $prefs: tableau de préférences exactes (ex: ['fumeur','pas-animaux'])
     */
    public function search(?string $depart = null, ?string $arrivee = null, ?string $date = null, array $prefs = [], ?string $sort = null, ?string $dir = null): array
    {
        $sql = "SELECT c.*,
                       u.pseudo AS driver_pseudo, u.photo AS driver_photo, u.note AS driver_note,
                       v.marque AS vehicle_marque, v.modele AS vehicle_modele, v.couleur AS vehicle_couleur,
                       v.places_dispo AS vehicle_places,
                       v.preferences AS vehicle_preferences, v.custom_preferences AS vehicle_prefs_custom,
                       COALESCE(v.places_dispo, 0) - COALESCE(COUNT(p.id), 0) AS places_restantes,
                       COALESCE(COUNT(p.id), 0) AS reservations_count
                FROM {$this->table} c
                LEFT JOIN users u ON u.id = c.driver_id
                LEFT JOIN vehicles v ON v.id = c.vehicle_id
                LEFT JOIN participations p ON p.covoiturage_id = c.id AND p.status = 'confirmee'
                WHERE 1=1";
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
        if (!empty($prefs)) {
            // Whitelist simple
            $allowed = ['fumeur', 'non-fumeur', 'animaux', 'pas-animaux'];
            $prefs = array_values(array_intersect($allowed, array_map('strval', (array) $prefs)));
            foreach ($prefs as $idx => $p) {
                $ph = ":pref$idx";
                $sql .= " AND FIND_IN_SET($ph, v.preferences) > 0";
                $params[$ph] = $p;
            }
        }
        $sql .= " GROUP BY c.id";

        // Tri sécurisé
        $allowedSort = [
            'date'  => 'c.depart',
            'price' => 'c.prix'
        ];
        $orderBy = $allowedSort[$sort ?? 'date'] ?? 'c.depart';
        $direction = strtoupper($dir ?? 'ASC');
        if (!in_array($direction, ['ASC', 'DESC'], true)) {
            $direction = 'ASC';
        }
        $sql .= " ORDER BY $orderBy $direction LIMIT 100";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
