<?php

namespace App\Service;

use MongoDB\Client;
use MongoDB\Model\BSONDocument;

class ReviewService
{
    private Client $client;
    private string $dbName;
    private string $collection;

    public function __construct(?string $dsn = null, ?string $dbName = null, ?string $collection = null)
    {
        $dsn = $dsn ?? ($_ENV['MONGO_DSN'] ?? ($_ENV['MONGODB_URI'] ?? 'mongodb://mongo:27017'));
        $this->dbName = $dbName ?? ($_ENV['MONGO_DB'] ?? 'ecoride');
        $this->collection = $collection ?? 'reviews';
        $this->client = new Client($dsn);
    }
    // Convertit un doc Mongo en tableau PHP (même logique que ReviewModerationService)
    private function normalizeDoc($doc): array
    {
        if ($doc instanceof BSONDocument) {
            $doc = $doc->getArrayCopy();
        } elseif (is_object($doc)) {
            $doc = json_decode(json_encode($doc), true) ?? [];
        } elseif (!is_array($doc)) {
            $doc = [];
        }

        if (!isset($doc['id'])) {
            $doc['id'] = isset($doc['doc_id']) ? (string)$doc['doc_id'] : (string)($doc['_id'] ?? '');
        }
        return $doc;
    }

    // Récupère les avis approuvés d’un conducteur
    public function getApprovedDriverReviews(int $driverId, int $limit = 100): array
    {
        $coll = $this->client->selectCollection($this->dbName, $this->collection);

        $cursor = $coll->find([
            'kind' => 'review',
            'status' => 'approved',
            'driver_id' => $driverId,
        ], [
            'sort' => ['created_at_ms' => -1],
            'limit' => $limit,
        ]);

        $out = [];
        foreach ($cursor as $doc) {
            $out[] = $this->normalizeDoc($doc);
        }
        return $out;
    }

    // Calcule moyenne + nb avis (sur les docs fournis)
    public function getDriverRatingStats(array $reviews): array
    {
        $sum = 0;
        $cnt = 0;

        foreach ($reviews as $r) {
            if (isset($r['rating']) && is_numeric($r['rating'])) {
                $v = (int)$r['rating'];
                if ($v >= 1 && $v <= 5) {
                    $sum += $v;
                    $cnt++;
                }
            }
        }

        return [
            'avg' => $cnt > 0 ? round($sum / $cnt, 1) : 0.0,
            'count' => count($reviews),
        ];
    }

    /**
     * Enregistre un avis (rating/comment) côté Mongo.
     * $data doit contenir au minimum: covoiturage_id, driver_id, passager_id, rating (1-5), comment
     */
    public function addReview(array $data): string
    {
        $payload = [
            'doc_id' => bin2hex(random_bytes(12)),
            'kind' => 'review',
            'status' => 'pending', // à valider par un employé
            'covoiturage_id' => (int)($data['covoiturage_id'] ?? 0),
            'driver_id' => (int)($data['driver_id'] ?? 0),
            'passager_id' => (int)($data['passager_id'] ?? 0),
            'rating' => isset($data['rating']) ? (int) $data['rating'] : null,
            'comment' => isset($data['comment']) ? (string) $data['comment'] : null,
            // Stocke un timestamp milliseconde simple pour éviter toute dépendance de type
            'created_at_ms' => (int) round(microtime(true) * 1000),
        ];
        $this->client->selectCollection($this->dbName, $this->collection)->insertOne($payload);
        return (string) $payload['doc_id'];
    }

    /**
     * Enregistre un signalement (plainte) côté Mongo.
     * $data: covoiturage_id, driver_id, passager_id, reason|comment
     */
    public function addReport(array $data): string
    {
        $payload = [
            'doc_id' => bin2hex(random_bytes(12)),
            'kind' => 'report',
            'status' => 'pending',
            'covoiturage_id' => (int)($data['covoiturage_id'] ?? 0),
            'driver_id' => (int)($data['driver_id'] ?? 0),
            'passager_id' => (int)($data['passager_id'] ?? 0),
            'reason' => isset($data['reason']) ? (string) $data['reason'] : null,
            'comment' => isset($data['comment']) ? (string) $data['comment'] : null,
            'created_at_ms' => (int) round(microtime(true) * 1000),
        ];
        $this->client->selectCollection($this->dbName, $this->collection)->insertOne($payload);
        return (string) $payload['doc_id'];
    }
}
