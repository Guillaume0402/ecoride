<?php
// scripts/check_mongo.php
// Vérifie la connexion Mongo et affiche un JSON (à exécuter en local ou via "heroku run").

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/constants.php';

use Dotenv\Dotenv;

// Charge .env/.env.local si présents (utile en local)
$dotenv = Dotenv::createMutable(dirname(__DIR__), ['.env', '.env.local']);
$dotenv->safeLoad();

$out = [
    'env' => [
        'APP_ENV' => $_ENV['APP_ENV'] ?? null,
        'MONGO_DSN' => isset($_ENV['MONGO_DSN']) ? '[set]' : '[missing]',
        'MONGODB_URI' => isset($_ENV['MONGODB_URI']) ? '[set]' : '[missing]',
        'MONGO_DB' => $_ENV['MONGO_DB'] ?? null,
    ],
    'status' => 'unknown',
    'error' => null,
    'db' => null,
    'collection_count' => null,
    'pending_count' => null,
];

try {
    $dsn = $_ENV['MONGO_DSN'] ?? ($_ENV['MONGODB_URI'] ?? 'mongodb://mongo:27017');
    $dbName = $_ENV['MONGO_DB'] ?? 'ecoride';
    $client = new MongoDB\Client($dsn);
    $client->listDatabases();
    $out['status'] = 'connected';
    $out['db'] = $dbName;
    $coll = $client->selectCollection($dbName, 'reviews');
    $out['collection_count'] = $coll->countDocuments();
    $out['pending_count'] = $coll->countDocuments(['status' => 'pending']);
} catch (Throwable $e) {
    $out['status'] = 'error';
    $out['error'] = $e->getMessage();
}

echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
