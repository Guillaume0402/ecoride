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
}
