<?php

namespace App\Repository;

use App\Db\Mysql;

class ParticipationRepository
{
    private \PDO $conn;
    private string $table = 'participations';

    public function __construct()
    {
        $this->conn = Mysql::getInstance()->getPDO();
    }

    public function create(int $covoiturageId, int $passagerId, string $status = 'en_attente_validation'): bool
    {
        $sql = "INSERT INTO {$this->table} (covoiturage_id, passager_id, status, date_participation) VALUES (:covoiturage_id, :passager_id, :status, NOW())";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':covoiturage_id' => $covoiturageId,
            ':passager_id'    => $passagerId,
            ':status'         => $status,
        ]);
    }

    /**
     * Retourne les participations confirmées pour un covoiturage donné.
     * Utilisé notamment pour rembourser lors d'une annulation par le conducteur.     
     */
    public function findConfirmedByCovoiturageId(int $covoiturageId): array
    {
        $sql = "SELECT id AS participation_id, passager_id FROM {$this->table} WHERE covoiturage_id = :id AND status = 'confirmee'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $covoiturageId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    public function countConfirmedByCovoiturageId(int $covoiturageId): int
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE covoiturage_id = :id AND status = 'confirmee'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $covoiturageId]);
        return (int) $stmt->fetchColumn();
    }

    public function findByCovoiturageAndPassager(int $covoiturageId, int $passagerId): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE covoiturage_id = :c AND passager_id = :p LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':c' => $covoiturageId, ':p' => $passagerId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function updateStatus(int $participationId, string $status): bool
    {
        $allowed = ['en_attente_validation', 'confirmee', 'annulee'];
        if (!in_array($status, $allowed, true)) return false;
        $sql = "UPDATE {$this->table} SET status = :s WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':s' => $status, ':id' => $participationId]);
    }

    /**
     * Récupère une participation avec son covoiturage et infos véhicule/driver.
     */
    public function findWithCovoiturageById(int $participationId): ?array
    {
        $sql = "SELECT p.*, p.id AS participation_id, c.*, 
               c.id AS covoiturage_id,
                       c.status AS covoit_status,
                       v.places_dispo AS vehicle_places,
                       v.marque AS vehicle_marque, v.modele AS vehicle_modele,
                       u_driver.id AS driver_user_id, u_driver.pseudo AS driver_pseudo,
                       u_pass.pseudo AS passager_pseudo
                FROM {$this->table} p
                JOIN covoiturages c ON c.id = p.covoiturage_id
                JOIN users u_driver ON u_driver.id = c.driver_id
                JOIN users u_pass ON u_pass.id = p.passager_id
                LEFT JOIN vehicles v ON v.id = c.vehicle_id
                WHERE p.id = :id
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $participationId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Liste les demandes en attente pour tous les trajets d'un conducteur.
     */
    public function findPendingByDriverId(int $driverId): array
    {
        $sql = "SELECT p.*, p.id AS participation_id,
                       c.id AS covoiturage_id, c.adresse_depart, c.adresse_arrivee, c.depart, c.prix,
                       u_pass.pseudo AS passager_pseudo, u_pass.credits AS passager_credits
                FROM {$this->table} p
                JOIN covoiturages c ON c.id = p.covoiturage_id
                JOIN users u_pass ON u_pass.id = p.passager_id
                WHERE c.driver_id = :driver AND p.status = 'en_attente_validation'
                ORDER BY c.depart ASC, p.date_participation ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':driver' => $driverId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Liste toutes les participations d'un passager avec info trajet et conducteur.
     */
    public function findByPassagerId(int $passagerId): array
    {
        $sql = "SELECT p.*, p.id AS participation_id,
               c.id AS covoiturage_id, c.adresse_depart, c.adresse_arrivee, c.depart, c.prix, c.status AS covoit_status,
               v.marque AS vehicle_marque, v.modele AS vehicle_modele, v.couleur AS vehicle_couleur,
               u_driver.id AS driver_user_id, u_driver.pseudo AS driver_pseudo
                FROM {$this->table} p
                JOIN covoiturages c ON c.id = p.covoiturage_id
                LEFT JOIN vehicles v ON v.id = c.vehicle_id
                JOIN users u_driver ON u_driver.id = c.driver_id
                WHERE p.passager_id = :pid
                ORDER BY c.depart DESC, p.date_participation DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':pid' => $passagerId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // === Stats ===
    public function countAll(): int
    {
        $stmt = $this->conn->query("SELECT COUNT(*) FROM {$this->table}");
        return (int) $stmt->fetchColumn();
    }

    public function confirmationRateLastDays(int $days = 30): float
    {
        $days = max(1, min(90, $days));
        $sqlTot = $this->conn->prepare("SELECT COUNT(*) FROM {$this->table} WHERE date_participation >= DATE_SUB(CURDATE(), INTERVAL :d DAY)");
        $sqlTot->execute([':d' => $days]);
        $total = (int) $sqlTot->fetchColumn();
        if ($total === 0) return 0.0;
        $sqlOk = $this->conn->prepare("SELECT COUNT(*) FROM {$this->table} WHERE status='confirmee' AND date_participation >= DATE_SUB(CURDATE(), INTERVAL :d DAY)");
        $sqlOk->execute([':d' => $days]);
        $ok = (int) $sqlOk->fetchColumn();
        return round(($ok / $total) * 100, 2);
    }

    /**
     * Vérifie si un passager a déjà une participation CONFIRMÉE qui chevauche
     * un horaire donné dans une fenêtre [depart - windowMin ; depart + windowMin].
     * on évite les doubles réservations sur la même tranche.
     */
    public function hasConfirmedConflictAround(int $passagerId, \DateTime $depart, int $windowMinutes = 120): bool
    {
        $windowMinutes = max(15, min(480, $windowMinutes)); // borne 15min à 8h
        $start = (clone $depart)->modify('-' . $windowMinutes . ' minutes')->format('Y-m-d H:i:s');
        $end   = (clone $depart)->modify('+' . $windowMinutes . ' minutes')->format('Y-m-d H:i:s');

        $sql = "SELECT COUNT(*)
                FROM {$this->table} p
                JOIN covoiturages c ON c.id = p.covoiturage_id
                WHERE p.passager_id = :pid
                  AND p.status = 'confirmee'
                  AND c.depart BETWEEN :start AND :end";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':pid' => $passagerId,
            ':start' => $start,
            ':end' => $end,
        ]);
        return ((int)$stmt->fetchColumn()) > 0;
    }
}
