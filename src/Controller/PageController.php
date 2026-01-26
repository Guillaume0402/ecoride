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
   


    // Liste des covoiturages (vue listant les annonces).
    public function listeCovoiturages(): void
    {
        // Lecture des critères GET (simples)
        $depart = isset($_GET['depart']) ? trim((string)$_GET['depart']) : null;
        $arrivee = isset($_GET['arrivee']) ? trim((string)$_GET['arrivee']) : null;
        $date = isset($_GET['date']) ? trim((string)$_GET['date']) : null; // format YYYY-MM-DD
        // Pref peut être string (ancienne UI) ou tableau (multi sélection)
        $prefParam = $_GET['pref'] ?? null;
        $prefs = [];
        if (is_array($prefParam)) {
            $prefs = array_values(array_filter(array_map('strval', $prefParam)));
        } elseif (is_string($prefParam) && $prefParam !== '') {
            $prefs = [$prefParam];
        }
        $sort = isset($_GET['sort']) ? trim((string)$_GET['sort']) : null; // 'date' | 'price'
        $dir  = isset($_GET['dir'])  ? trim((string)$_GET['dir'])  : null; // 'asc' | 'desc'

        $results = [];
        try {
            $repo = new CovoiturageRepository();
            // Toujours effectuer la recherche pour permettre le tri même sans filtres
            $currentUserId = isset($_SESSION['user']) ? (int) $_SESSION['user']['id'] : null;
            $results = $repo->search($depart, $arrivee, $date, $prefs, $sort, $dir, $currentUserId);
        } catch (\Throwable $e) {
            error_log('Search error: ' . $e->getMessage());
        }

        $this->render('pages/liste-covoiturages', [
            'criteria' => [
                'depart' => $depart,
                'arrivee' => $arrivee,
                'date' => $date,
                'pref' => $prefs,
                'sort' => $sort,
                'dir' => $dir,
            ],
            'results' => $results,
            'pageTitle' => 'Covoiturages',
            'metaDescription' => 'Parcourez les annonces de covoiturage EcoRide et trouvez un conducteur ou un passager correspondant à vos critères.',
        ]);
    }


    // Page de création/édition du profil (protégée), précharge le véhicule
    public function creationProfil(): void
    {
        if (!isset($_SESSION['user'])) {
            $_SESSION['error'] = 'Vous devez être connecté pour accéder à cette page.';
            redirect('/login');
        }

        $user = $_SESSION['user'];

        $vehicleEntity = !empty($_GET['id'])
            ? $this->vehicleRepository->findById((int) $_GET['id'])
            : $this->vehicleRepository->findByUserId($user['id']);

        $vehicle = $vehicleEntity ? $vehicleEntity->toArray() : null;

        $this->render('pages/creation-profil', [
            'user' => $user,
            'vehicle' => $vehicle,
        ]);
    }

    // Page listant les covoiturages de l'utilisateur courant.
    public function mesCovoiturages(): void
    {
        if (!isset($_SESSION['user'])) {
            redirect('/login');
        }
        // Balayage léger de maintenance (annulations auto + rattrapage remboursements)
        try {
            (new \App\Service\MaintenanceService())->sweep();
        } catch (\Throwable $e) {
            error_log('[mesCovoiturages maintenance sweep] ' . $e->getMessage());
        }
        $userId = (int) $_SESSION['user']['id'];
        $covoitRepo = new CovoiturageRepository();
        $partRepo = new \App\Repository\ParticipationRepository();

        $asDriverAll = $covoitRepo->findByDriverId($userId);
        $asPassengerAll = $partRepo->findByPassagerId($userId);

        // Filtrage: aligner avec le header
        $now = new \DateTime();
        $graceMinutes = defined('AUTO_CANCEL_MINUTES') ? (int) AUTO_CANCEL_MINUTES : 60;
        $asDriver = array_values(array_filter($asDriverAll, function ($c) use ($now, $graceMinutes) {
            try {
                $depart = new \DateTime($c['depart']);
            } catch (\Throwable $e) {
                return false;
            }
            $status = (string)($c['status'] ?? 'en_attente');
            // Règle: un trajet démarré reste dans l'onglet Conducteur même si l'heure est passée,
            // jusqu'à ce qu'il soit marqué "terminé" (ou annulé).
            if ($status === 'demarre') {
                return true;
            }
            // Sinon, on affiche ici les trajets à venir ET ceux dont l'heure est passée depuis moins de N minutes (grâce),
            // tant qu'ils ne sont pas annulés/terminés. Cela laisse le temps au conducteur de démarrer.
            $graceThreshold = (clone $depart)->modify("+{$graceMinutes} minutes");
            return ($depart >= $now || $graceThreshold >= $now) && !in_array($status, ['annule', 'termine'], true);
        }));
        $asPassenger = array_values(array_filter($asPassengerAll, function ($p) use ($now) {
            $status = (string)($p['status'] ?? '');
            $cStatus = (string)($p['covoit_status'] ?? 'en_attente');
            $isUpcoming = false;
            try {
                $isUpcoming = (new \DateTime($p['depart'])) >= $now;
            } catch (\Throwable $e) {
            }
            // Montrer dans l'onglet Passager:
            // - demandes en attente de validation
            // - participations confirmées
            // uniquement pour des trajets à venir et non annulés/terminés
            return in_array($status, ['en_attente_validation', 'confirmee'], true)
                && $isUpcoming
                && !in_array($cStatus, ['annule', 'termine'], true);
        }));

        // Historique
        $historyDriver = array_values(array_filter($asDriverAll, function ($c) use ($now, $graceMinutes) {
            try {
                $depart = new \DateTime($c['depart']);
            } catch (\Throwable $e) {
                return false;
            }
            $status = (string)($c['status'] ?? 'en_attente');
            // Historique si:
            // - terminé ou annulé, ou
            // - dépassé de plus de N minutes après l'heure de départ ET ce n'est pas un trajet en cours (démarré)
            if (in_array($status, ['annule', 'termine'], true)) {
                return true;
            }
            $graceThreshold = (clone $depart)->modify("+{$graceMinutes} minutes");
            return $graceThreshold < $now && $status !== 'demarre';
        }));
        $historyPassenger = array_values(array_filter($asPassengerAll, function ($p) use ($now) {
            $status = (string)($p['status'] ?? '');
            $cStatus = (string)($p['covoit_status'] ?? 'en_attente');
            $isPast = false;
            try {
                $isPast = (new \DateTime($p['depart'])) < $now;
            } catch (\Throwable $e) {
            }
            // Classer en historique si:
            // - participation annulée ou autre statut non actif
            // - trajet annulé/terminé
            // - trajet passé (même si la participation était en attente ou confirmée)
            $isActiveParticipation = in_array($status, ['en_attente_validation', 'confirmee'], true);
            $isActiveRide = !in_array($cStatus, ['annule', 'termine'], true);
            return !$isActiveParticipation || !$isActiveRide || $isPast;
        }));

        // Compte des validations en attente (participations confirmées dont le covoit est terminé mais non encore validées côté passager)
        $pendingValidations = 0;
        $txRepo = new \App\Repository\TransactionRepository();
        foreach ($asPassengerAll as $p) {
            if (($p['status'] ?? '') === 'confirmee' && ($p['covoit_status'] ?? '') === 'termine') {
                $driverId = (int)($p['driver_user_id'] ?? 0);
                $covoiturageId = (int)($p['covoiturage_id'] ?? 0);
                $motif = 'Crédit conducteur trajet #' . $covoiturageId . ' - passager #' . $userId;
                if (!$txRepo->existsForMotif($driverId, $motif)) {
                    $pendingValidations++;
                }
            }
        }

        $this->render('pages/mes-covoiturages', [
            'asDriver' => $asDriver,
            'asPassenger' => $asPassenger,
            'historyDriver' => $historyDriver,
            'historyPassenger' => $historyPassenger,
            'pendingValidations' => $pendingValidations,
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

    // Détail d'un covoiturage (public)
    public function showCovoiturage(int $id): void
    {
        $repo = new CovoiturageRepository();
        $ride = $repo->findOneWithVehicleById($id);
        if (!$ride) {
            abort(404, 'Covoiturage introuvable');
        }
        // Meta dynamiques
        $from = (string)($ride['adresse_depart'] ?? 'Départ');
        $to = (string)($ride['adresse_arrivee'] ?? 'Arrivée');
        $when = null;
        try {
            $when = (new \DateTime((string)($ride['depart'] ?? '')))->format('d/m/Y H\hi');
        } catch (\Throwable $e) {
        }
        $titleBits = [$from . ' → ' . $to];
        if ($when) {
            $titleBits[] = $when;
        }
        $pageTitle = implode(' • ', $titleBits);
        $desc = 'Trajet de ' . $from . ' à ' . $to . ($when ? ' le ' . $when : '') . ' — trouvez votre place avec EcoRide.';
        $this->render('pages/covoiturages/show', [
            'ride' => $ride,
            'pageTitle' => $pageTitle,
            'metaDescription' => $desc,
            'canonical' => SITE_URL . 'covoiturages/' . $id,
        ]);
    }
}
