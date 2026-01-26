<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Repository\VehicleRepository;

class PublicProfileController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    // GET /profil/{id}
    public function show(int $id): void
    {
        try {
            $uRepo = new UserRepository();
            $vehRepo = new VehicleRepository();
        } catch (\Throwable $e) {
            abort(500, 'Dépendances indisponibles');
        }

        $userEntity = $uRepo->findById($id);
        if (!$userEntity) {
            abort(404, 'Utilisateur introuvable');
        }

        $user = [
            'id' => $userEntity->getId(),
            'pseudo' => $userEntity->getPseudo(),
            'photo' => $userEntity->getPhoto(),
            'created_at' => $userEntity->getCreatedAt()?->format('Y-m-d H:i:s'),
            'note' => $userEntity->getNote(),
            'travel_role' => $userEntity->getTravelRole(),
        ];

        $vehicles = $vehRepo->findAllByUserId($id);

        // Avis approuvés (reviews) pour ce conducteur
        $reviews = [];
        try {
            $coll = (new \MongoDB\Client($_ENV['MONGO_DSN'] ?? ($_ENV['MONGODB_URI'] ?? 'mongodb://mongo:27017')))
                ->selectCollection($_ENV['MONGO_DB'] ?? 'ecoride', 'reviews');

            $cursor = $coll->find(
                ['kind' => 'review', 'status' => 'approved', 'driver_id' => (int)$id],
                ['sort' => ['created_at_ms' => -1]]
            );

            foreach ($cursor as $doc) {
                if ($doc instanceof \MongoDB\Model\BSONDocument) {
                    $doc = $doc->getArrayCopy();
                }
                $reviews[] = $doc;
            }
        } catch (\Throwable $e) {
            // pas bloquant
        }

        $this->render('pages/profil-public', [
            'profileUser' => $user,
            'vehicles' => $vehicles,
            'reviews' => $reviews,
        ]);
    }
}
