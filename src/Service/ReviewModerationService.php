<?php

namespace App\Service;

use MongoDB\Client;

// Service chargé de dialoguer avec MongoDB pour la modération des avis
// (liste des avis en attente, changement de statut, récupération par id)
class ReviewModerationService
{
    // Client MongoDB partagé par les différentes méthodes
    private Client $client;
    // Nom de la base MongoDB à utiliser
    private string $dbName;
    // Nom de la collection où sont stockés les avis
    private string $collection;

    // Le constructeur initialise la connexion Mongo et les noms de base/collection
    public function __construct(?string $dsn = null, ?string $dbName = null, ?string $collection = null)
    {
        // DSN MongoDB : on essaie différentes variables d'environnement puis une valeur par défaut
        $dsn = $dsn ?? ($_ENV['MONGO_DSN'] ?? ($_ENV['MONGODB_URI'] ?? 'mongodb://mongo:27017'));
        // Nom de la base de données (par défaut "ecoride")
        $this->dbName = $dbName ?? ($_ENV['MONGO_DB'] ?? 'ecoride');
        // Nom de la collection qui contient les avis
        $this->collection = $collection ?? 'reviews';
        // Instancie le client MongoDB à partir du DSN
        $this->client = new Client($dsn);
    }

    /**
     * Convertit un document Mongo (BSONDocument/objet) en tableau mutable
     * et ajoute une clé 'id' (doc_id ou _id) pour un usage pratique côté vues.
     */
    private function normalizeDoc($doc): array
    {
        // Si c'est un BSONDocument, on le convertit en tableau PHP
        if ($doc instanceof \MongoDB\Model\BSONDocument) {
            $doc = $doc->getArrayCopy();
            // Si c'est un objet générique, on passe par JSON pour obtenir un tableau
        } elseif (is_object($doc)) {
            $doc = json_decode(json_encode($doc), true) ?? [];
            // Si ce n'est ni un objet ni un tableau, on part d'un tableau vide
        } elseif (!is_array($doc)) {
            $doc = [];
        }
        // On garantit la présence d'un champ 'id' pratique pour les vues
        if (!isset($doc['id'])) {
            $doc['id'] = isset($doc['doc_id']) ? (string) $doc['doc_id'] : (string) ($doc['_id'] ?? '');
        }
        return $doc;
    }

    // Récupère tous les avis dont le statut est "pending" (en attente de modération)
    public function listPending(): array
    {
        // Sélectionne la collection d'avis
        $coll = $this->client->selectCollection($this->dbName, $this->collection);
        // Cherche les documents avec status = 'pending'
        $cursor = $coll->find(['status' => 'pending']);
        $out = [];
        // On normalise chaque document pour avoir un tableau PHP simple
        foreach ($cursor as $doc) {
            $out[] = $this->normalizeDoc($doc);
        }
        return $out;
    }

    // Met à jour le statut d'un avis (approved ou rejected) à partir de son doc_id
    public function updateStatus(string $id, string $decision): bool
    {
        // Ne conserver que les décisions autorisées, sinon forcer à 'rejected'
        $decision = in_array($decision, ['approved', 'rejected'], true) ? $decision : 'rejected';
        $coll = $this->client->selectCollection($this->dbName, $this->collection);
        // On utilise doc_id (champ custom) pour cibler le document
        $res = $coll->updateOne(
            ['doc_id' => $id],
            ['$set' => ['status' => $decision, 'moderated_at_ms' => (int) round(microtime(true) * 1000)]]
        );
        // Renvoie true si au moins un document a été modifié
        return $res->getModifiedCount() > 0;
    }

    // Récupère un avis par son doc_id et le normalise pour usage côté PHP/vue
    public function getById(string $id): ?array
    {
        $coll = $this->client->selectCollection($this->dbName, $this->collection);
        $doc = $coll->findOne(['doc_id' => $id]);
        // Si aucun document trouvé, on retourne null
        if (!$doc) return null;
        return $this->normalizeDoc($doc);
    }
}
