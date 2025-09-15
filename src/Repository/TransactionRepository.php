<?php

namespace App\Repository;

use App\Db\Mysql;
use PDO;

class TransactionRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Mysql::getInstance()->getPDO();
    }

    /**
     * Crée une transaction de crédit/débit pour un utilisateur.
     * $type: 'debit' | 'credit'
     */
    public function create(int $userId, float $montant, string $type, ?string $motif = null): bool
    {
        $type = $type === 'credit' ? 'credit' : 'debit';
        $sql = "INSERT INTO transactions (user_id, montant, type, motif) VALUES (:uid, :m, :t, :motif)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':uid' => $userId,
            ':m' => $montant,
            ':t' => $type,
            ':motif' => $motif,
        ]);
    }
}
