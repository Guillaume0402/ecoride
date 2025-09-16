<?php

namespace App\Service;

use MongoDB\Client;

class ReviewModerationService
{
    private Client $client;
    private string $dbName;
    private string $collection;

    public function __construct(?string $dsn = null, ?string $dbName = null, ?string $collection = null)
    {
        $dsn = $dsn ?? ($_ENV['MONGO_DSN'] ?? 'mongodb://mongo:27017');
        $this->dbName = $dbName ?? ($_ENV['MONGO_DB'] ?? 'ecoride');
        $this->collection = $collection ?? 'reviews';
        $this->client = new Client($dsn);
    }

    public function listPending(): array
    {
        $coll = $this->client->selectCollection($this->dbName, $this->collection);
        $cursor = $coll->find(['status' => 'pending']);
        $out = [];
        foreach ($cursor as $doc) {
            // Préférer doc_id comme identifiant portable
            $doc['id'] = isset($doc['doc_id']) ? (string) $doc['doc_id'] : (string) ($doc['_id'] ?? '');
            $out[] = $doc;
        }
        return $out;
    }

    public function updateStatus(string $id, string $decision): bool
    {
        $decision = in_array($decision, ['approved', 'rejected'], true) ? $decision : 'rejected';
        $coll = $this->client->selectCollection($this->dbName, $this->collection);
        // On utilise doc_id (champ custom) pour cibler le document
        $res = $coll->updateOne(
            ['doc_id' => $id],
            ['$set' => ['status' => $decision, 'moderated_at_ms' => (int) round(microtime(true) * 1000)]]
        );
        return $res->getModifiedCount() > 0;
    }

    public function getById(string $id): ?array
    {
        $coll = $this->client->selectCollection($this->dbName, $this->collection);
        $doc = $coll->findOne(['doc_id' => $id]);
        if (!$doc) return null;
        $doc['id'] = isset($doc['doc_id']) ? (string) $doc['doc_id'] : (string) ($doc['_id'] ?? '');
        return $doc;
    }
}
