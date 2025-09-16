<?php
// scripts/seed_mongo.php
// Remplit la collection Mongo `reviews` avec quelques avis et signalements de démo.

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/constants.php';

use Dotenv\Dotenv;
use MongoDB\Client;

$dotenv = Dotenv::createMutable(dirname(__DIR__), ['.env', '.env.local']);
$dotenv->load();

$dsn = $_ENV['MONGO_DSN'] ?? 'mongodb://mongo:27017';
$dbName = $_ENV['MONGO_DB'] ?? 'ecoride';
$collection = 'reviews';

$client = new Client($dsn);
$coll = $client->selectCollection($dbName, $collection);

function doc_id()
{
    return bin2hex(random_bytes(12));
}
function now_ms()
{
    return (int) round(microtime(true) * 1000);
}

$docs = [
    // Reviews (avis)
    [
        'doc_id' => doc_id(),
        'kind' => 'review',
        'status' => 'pending',
        'covoiturage_id' => 1001,
        'driver_id' => 11,
        'passager_id' => 21,
        'rating' => 5,
        'comment' => 'Super trajet, très sympa et ponctuel.',
        'created_at_ms' => now_ms(),
    ],
    [
        'doc_id' => doc_id(),
        'kind' => 'review',
        'status' => 'pending',
        'covoiturage_id' => 1002,
        'driver_id' => 12,
        'passager_id' => 22,
        'rating' => 3,
        'comment' => 'Correct mais un peu de retard.',
        'created_at_ms' => now_ms(),
    ],

    // Reports (signalements)
    [
        'doc_id' => doc_id(),
        'kind' => 'report',
        'status' => 'pending',
        'covoiturage_id' => 1003,
        'driver_id' => 13,
        'passager_id' => 23,
        'reason' => 'Retard important',
        'comment' => 'Plus de 45 minutes de retard, pas de communication.',
        'created_at_ms' => now_ms(),
    ],
    [
        'doc_id' => doc_id(),
        'kind' => 'report',
        'status' => 'pending',
        'covoiturage_id' => 1004,
        'driver_id' => 14,
        'passager_id' => 24,
        'reason' => 'Conduite dangereuse',
        'comment' => 'Plusieurs dépassements risqués.',
        'created_at_ms' => now_ms(),
    ],
];

$inserted = 0;
foreach ($docs as $d) {
    try {
        $coll->insertOne($d);
        $inserted++;
    } catch (Throwable $e) {
        fwrite(STDERR, "Insert failed: " . $e->getMessage() . "\n");
    }
}

echo "Mongo seed done. Inserted: {$inserted}\n";
