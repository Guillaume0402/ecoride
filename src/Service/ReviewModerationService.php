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
        $dsn = $dsn ?? ($_ENV['MONGO_DSN'] ?? ($_ENV['MONGODB_URI'] ?? 'mongodb://mongo:27017'));
        $this->dbName = $dbName ?? ($_ENV['MONGO_DB'] ?? 'ecoride');
        $this->collection = $collection ?? 'reviews';
        $this->client = new Client($dsn);
    }

    /**
     * Convertit un document Mongo (BSONDocument/objet) en tableau mutable
     * et ajoute une clé 'id' (doc_id ou _id) pour un usage pratique côté vues.
     */
    private function normalizeDoc($doc): array
    {
        if ($doc instanceof \MongoDB\Model\BSONDocument) {
            $doc = $doc->getArrayCopy();
        } elseif (is_object($doc)) {
            $doc = json_decode(json_encode($doc), true) ?? [];
        } elseif (!is_array($doc)) {
            $doc = [];
        }
        if (!isset($doc['id'])) {
            $doc['id'] = isset($doc['doc_id']) ? (string) $doc['doc_id'] : (string) ($doc['_id'] ?? '');
        }
        return $doc;
    }

    public function listPending(): array
    {
        $coll = $this->client->selectCollection($this->dbName, $this->collection);
        $cursor = $coll->find(['status' => 'pending']);
        $out = [];
        foreach ($cursor as $doc) {
            $out[] = $this->normalizeDoc($doc);
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
        return $this->normalizeDoc($doc);
    }
}
