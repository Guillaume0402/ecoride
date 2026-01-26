<?php

namespace App\Controller;

use App\Repository\VehicleRepository;
use App\Repository\CovoiturageRepository;


// Contrôleur des pages (publiques/protégées): statiques, covoiturages, profil
class PageController extends Controller
{
    // Dépôt pour interagir avec les véhicules des utilisateurs.
    private VehicleRepository $vehicleRepository;

    // Initialisation des dépendances nécessaires aux pages.
    public function __construct()
    {
        parent::__construct();
        $this->vehicleRepository = new VehicleRepository();
    }

    // Page d'accueil.
    public function home(): void
    {
        $popular = [];
        try {
            $repo = new CovoiturageRepository();
            $popular = $repo->popularDestinations(6, 4, 30);
        } catch (\Throwable $e) {
            error_log('[home] popular destinations failed: ' . $e->getMessage());
        }

        $this->render('home', [
            'popularDestinations' => $popular,
            'pageTitle' => 'Accueil',
            'metaDescription' => "EcoRide, la plateforme de covoiturage responsable pour vos trajets du quotidien. Trouvez ou proposez un trajet en quelques clics.",
            'metaImage' => SITE_URL . 'assets/images/logo-share.png',
        ]);
    }

    // Page de contact.
    public function contact(): void
    {
        $this->render('pages/contact', [
            'pageTitle' => 'Contact',
            'metaDescription' => "Contactez l'équipe EcoRide pour toute question sur le covoiturage, votre compte ou l'application.",
        ]);
    }   
        
    // Profil public d'un utilisateur (lecture seule)
    public function showUserProfil(int $id): void
    {
        try {
            $uRepo = new \App\Repository\UserRepository();
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
            $cursor = $coll->find(['kind' => 'review', 'status' => 'approved', 'driver_id' => (int)$id], ['sort' => ['created_at_ms' => -1]]);
            foreach ($cursor as $doc) {
                if ($doc instanceof \MongoDB\Model\BSONDocument) {
                    $doc = $doc->getArrayCopy();
                }
                $reviews[] = $doc;
            }
        } catch (\Throwable $e) {
            // Silencieux: pas bloquant si Mongo indisponible
        }

        $this->render('pages/profil-public', [
            'profileUser' => $user,
            'vehicles' => $vehicles,
            'reviews' => $reviews,
        ]);
    }    

    // Page "À propos".
    public function about(): void
    {
        $this->render('pages/about');
    }

    // Page des conditions d'utilisation.
    public function terms(): void
    {
        $this->render('pages/terms');
    }

    // Page de politique de confidentialité.
    public function privacy(): void
    {
        $this->render('pages/privacy');
    }

    // Page des mentions légales.
    public function mentionsLegales(): void
    {
        $this->render('pages/mentions-legales');
    }


}
