<?php
// scripts/mongo_create_indexes.php
// Crée des index utiles sur la collection Mongo `reviews`.

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/constants.php';

use Dotenv\Dotenv;
use MongoDB\Client;

// Charge .env (local) si présent
$dotenv = Dotenv::createMutable(dirname(__DIR__), ['.env', '.env.local']);
$dotenv->safeLoad();

$dsn = $_ENV['MONGO_DSN'] ?? ($_ENV['MONGODB_URI'] ?? 'mongodb://mongo:27017');
$dbName = $_ENV['MONGO_DB'] ?? 'ecoride';
$collection = 'reviews';

$client = new Client($dsn);
$coll = $client->selectCollection($dbName, $collection);

echo "Creating indexes on {$dbName}.{$collection}...\n";

try {
    // Unicité sur doc_id (identifiant applicatif)
    $name1 = $coll->createIndex(['doc_id' => 1], ['unique' => true, 'name' => 'doc_id_unique']);
    echo " - Created index: {$name1}\n";
} catch (Throwable $e) {
    fwrite(STDERR, " - doc_id_unique: " . $e->getMessage() . "\n");
}

try {
    // Requêtes fréquentes: status + tri par date de création
    $name2 = $coll->createIndex(['status' => 1, 'created_at_ms' => -1], ['name' => 'status_createdAt']);
    echo " - Created index: {$name2}\n";
} catch (Throwable $e) {
    fwrite(STDERR, " - status_createdAt: " . $e->getMessage() . "\n");
}

try {
    $name3 = $coll->createIndex(['driver_id' => 1], ['name' => 'driver_id']);
    echo " - Created index: {$name3}\n";
} catch (Throwable $e) {
    fwrite(STDERR, " - driver_id: " . $e->getMessage() . "\n");
}

try {
    $name4 = $coll->createIndex(['passager_id' => 1], ['name' => 'passager_id']);
    echo " - Created index: {$name4}\n";
} catch (Throwable $e) {
    fwrite(STDERR, " - passager_id: " . $e->getMessage() . "\n");
}

echo "Indexes creation done.\n";
