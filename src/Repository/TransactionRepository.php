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

    /**
     * Récupère les transactions récentes d'un utilisateur.
     * @return array<int, array{id:int,user_id:int,montant:string,type:string,motif:?string,created_at:string}>
     */
    public function findByUserId(int $userId, int $limit = 20): array
    {
        $limit = max(1, min(100, $limit));
        $sql = "SELECT id, user_id, montant, type, motif, created_at
                FROM transactions
                WHERE user_id = :uid
                ORDER BY created_at DESC, id DESC
                LIMIT {$limit}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
