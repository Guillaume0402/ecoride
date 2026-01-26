<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Repository\VehicleRepository;
use App\Service\ReviewService;


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
            $svc = new ReviewService();
            $reviews = $svc->getApprovedDriverReviews((int)$id, 100);
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
