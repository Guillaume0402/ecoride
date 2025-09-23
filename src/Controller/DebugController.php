<?php

namespace App\Controller;

class DebugController extends Controller
{
    // GET /debug/mongo
    public function mongo(): void
    {
        // Restreindre l'accès: admin (role_id=1) ou employé (role_id=2)
        if (!isset($_SESSION['user']) || !in_array((int)($_SESSION['user']['role_id'] ?? 0), [1, 2], true)) {
            abort(403, 'Accès interdit');
        }

        $info = [
            'env' => [
                'APP_ENV' => $_ENV['APP_ENV'] ?? null,
                'MONGO_DSN' => isset($_ENV['MONGO_DSN']) ? '[set]' : '[missing]',
                'MONGO_DB' => $_ENV['MONGO_DB'] ?? null,
            ],
            'status' => 'unknown',
            'error' => null,
            'db' => null,
            'collection_count' => null,
            'pending_count' => null,
        ];

        try {
            $dsn = $_ENV['MONGO_DSN'] ?? 'mongodb://mongo:27017';
            $dbName = $_ENV['MONGO_DB'] ?? 'ecoride';
            $client = new \MongoDB\Client($dsn);
            // Essai listDatabases (ping indirect)
            $client->listDatabases();
            $info['status'] = 'connected';
            $info['db'] = $dbName;
            $coll = $client->selectCollection($dbName, 'reviews');
            $info['collection_count'] = $coll->countDocuments();
            $info['pending_count'] = $coll->countDocuments(['status' => 'pending']);
        } catch (\Throwable $e) {
            $info['status'] = 'error';
            $info['error'] = $e->getMessage();
        }

        // Rendu simple JSON
        header('Content-Type: application/json');
        echo json_encode($info, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}
